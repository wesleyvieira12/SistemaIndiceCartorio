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
      echo "❌ Argumento desconhecido: $arg" >&2
      exit 1
      ;;
  esac
done

log() { echo -e "\n🚀 ==> $*"; }
ok()  { echo "✅ $*"; }

if [[ ! -f docker-compose.yml ]]; then
  echo "❌ docker-compose.yml não encontrado em ${APP_DIR}" >&2
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "❌ .env não encontrado. Rode antes: sudo bash scripts/setup-vps.sh" >&2
  exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "❌ Docker não encontrado. Rode: sudo bash scripts/setup-vps.sh" >&2
  exit 1
fi

if [[ "$DO_PULL" -eq 1 ]]; then
  if [[ -d .git ]]; then
    log "📥 Atualizando código (git fetch + reset para o remoto)"
    # Descarta sujeira local de builds anteriores para o pull não falhar
    git checkout -- \
      bootstrap/cache/.gitignore \
      storage \
      package-lock.json \
      public/css/app.css \
      public/js/app.js \
      2>/dev/null || true
    git clean -fd -- public/fonts 2>/dev/null || true
    rm -f public/mix-manifest.json

    git fetch origin
    BRANCH="$(git rev-parse --abbrev-ref HEAD)"
    # Servidor de deploy: código = exatamente o remoto (.env permanece, está no .gitignore)
    git reset --hard "origin/${BRANCH}"
    ok "Código em $(git rev-parse --short HEAD) (${BRANCH})"
  else
    echo "⚠️  Aviso: diretório sem .git — pulando atualização remota"
  fi
fi

log "📁 Garantindo diretórios de storage"
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
# Remove caches antigos do Laravel (evita rota / e /painel desatualizadas)
rm -f bootstrap/cache/config.php bootstrap/cache/routes.php bootstrap/cache/services.php

log "🏗️  Rebuild e restart dos containers"
docker compose build
docker compose up -d --remove-orphans
ok "Containers no ar"

# Espera o app responder
log "⏳ Aguardando container app"
for i in {1..30}; do
  if docker compose exec -T app php -v >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

log "🐘 Dependências PHP (Composer)"
docker compose exec -T app composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

log "🎨 Assets frontend (npm)"
# --no-package-lock: não reescreve package-lock.json no servidor
docker compose exec -T app npm install --no-package-lock --legacy-peer-deps
docker compose exec -T app npm run production

log "⚙️  Laravel: storage link, migrate e caches"
docker compose exec -T app php artisan storage:link || true
docker compose exec -T app php artisan migrate --force

if [[ "$DO_SEED" -eq 1 ]]; then
  docker compose exec -T app php artisan db:seed --force
fi

docker compose exec -T app php artisan config:clear || true
docker compose exec -T app php artisan route:clear || true
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
# Laravel 5.5 não tem view:cache
docker compose exec -T app php artisan queue:restart || true

log "🔎 Conferindo rotas públicas"
docker compose exec -T app php artisan route:list 2>/dev/null | grep -E 'landing|/painel|\sGET.*/\s' || true
ok "Commit ativo: $(git rev-parse --short HEAD 2>/dev/null || echo 'n/a')"

log "🔐 Ajustando permissões"
docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache || true
docker compose exec -T app bash -lc '
  find storage bootstrap/cache -type d -exec chmod ug+rwx {} \;
  find storage bootstrap/cache -type f -exec chmod ug+rw {} \;
' || true

# Não deixar o deploy sujar o git status
if [[ -d .git ]]; then
  log "🧹 Restaurando working tree após o build"
  git checkout -- \
    bootstrap/cache/.gitignore \
    storage/app/.gitignore \
    storage/app/public/.gitignore \
    storage/framework/.gitignore \
    storage/framework/cache/.gitignore \
    storage/framework/sessions/.gitignore \
    storage/framework/testing/.gitignore \
    storage/framework/views/.gitignore \
    storage/logs/.gitignore \
    package-lock.json \
    public/css/app.css \
    public/js/app.js \
    2>/dev/null || true
  git clean -fd -- public/fonts 2>/dev/null || true
  rm -f public/mix-manifest.json
fi

log "📊 Status"
docker compose ps

ok "🎉 Deploy concluído em $(date '+%Y-%m-%d %H:%M:%S')"
