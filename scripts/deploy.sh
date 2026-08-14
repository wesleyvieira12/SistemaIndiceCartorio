#!/usr/bin/env bash
# Deploy / atualização do Sistema Índice Cartório na VPS.
# Uso:
#   bash scripts/deploy.sh              # deploy normal (git pull + build + migrate)
#   bash scripts/deploy.sh --no-pull    # sem git pull
#   bash scripts/deploy.sh --seed       # roda db:seed após migrate
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

DO_PULL=1
DO_SEED=0

for arg in "$@"; do
  case "$arg" in
    --no-pull) DO_PULL=0 ;;
    --seed)    DO_SEED=1 ;;
    -h|--help)
      sed -n '2,7p' "$0"
      exit 0
      ;;
    *)
      echo "Argumento desconhecido: $arg" >&2
      exit 1
      ;;
  esac
done

log() { echo -e "\n==> $*"; }
ok()  { echo "✓ $*"; }

if [[ ! -f docker-compose.yml ]]; then
  echo "docker-compose.yml não encontrado em ${APP_DIR}" >&2
  exit 1
fi

if [[ ! -f .env ]]; then
  echo ".env não encontrado. Rode antes: sudo bash scripts/setup-vps.sh" >&2
  exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker não encontrado. Rode: sudo bash scripts/setup-vps.sh" >&2
  exit 1
fi

if [[ "$DO_PULL" -eq 1 ]]; then
  if [[ -d .git ]]; then
    log "Atualizando código (git pull)"
    git pull --ff-only
    ok "Código atualizado"
  else
    echo "Aviso: diretório sem .git — pulando git pull"
  fi
fi

log "Garantindo diretórios de storage"
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache

log "Rebuild e restart dos containers"
docker compose build
docker compose up -d --remove-orphans
ok "Containers no ar"

# Espera o app responder
log "Aguardando container app"
for i in {1..30}; do
  if docker compose exec -T app php -v >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

log "Dependências PHP (Composer)"
docker compose exec -T app composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

log "Assets frontend (npm)"
docker compose exec -T app bash -lc 'if [[ -f package-lock.json ]]; then npm ci --legacy-peer-deps; else npm install --legacy-peer-deps; fi'
docker compose exec -T app npm run production

log "Laravel: storage link, migrate e caches"
docker compose exec -T app php artisan storage:link || true
docker compose exec -T app php artisan migrate --force

if [[ "$DO_SEED" -eq 1 ]]; then
  docker compose exec -T app php artisan db:seed --force
fi

docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache || true
docker compose exec -T app php artisan queue:restart || true

log "Ajustando permissões"
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache || true
docker compose exec -T app chmod -R ug+rwx storage bootstrap/cache || true

log "Status"
docker compose ps

ok "Deploy concluído em $(date '+%Y-%m-%d %H:%M:%S')"
