#!/usr/bin/env bash
# Deploy / atualização do Sistema Índice Cartório.
#
# Na VPS (local no servidor):
#   bash scripts/deploy.sh
#   bash scripts/deploy.sh --no-pull
#   bash scripts/deploy.sh --seed
#
# Do seu PC (sem parâmetros):
#   1) cp scripts/deploy.env.example scripts/deploy.env  # e edite
#   2) ./scripts/deploy.sh
#
# Override opcional por flag: --remote, --key, --dir, --no-push, --seed, --no-pull
set -euo pipefail

SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Carrega scripts/deploy.env automaticamente (ignorado pelo git).
# Na VPS o SSH define DEPLOY_FORCE_LOCAL=1 para não tentar remoto de novo.
if [[ -z "${DEPLOY_FORCE_LOCAL:-}" && -f "${APP_DIR}/scripts/deploy.env" ]]; then
  set -a
  # shellcheck disable=SC1091
  source "${APP_DIR}/scripts/deploy.env"
  set +a
fi

DO_PULL=1
DO_SEED=0
DO_PUSH=1
REMOTE="${DEPLOY_REMOTE:-}"
SSH_KEY="${DEPLOY_SSH_KEY:-}"
REMOTE_DIR="${DEPLOY_DIR:-/var/www/SistemaIndiceCartorio}"
SSH_PORT="${DEPLOY_SSH_PORT:-22}"
PASS_ARGS=()

while [[ $# -gt 0 ]]; do
  case "$1" in
    --no-pull)
      DO_PULL=0
      PASS_ARGS+=(--no-pull)
      shift
      ;;
    --seed)
      DO_SEED=1
      PASS_ARGS+=(--seed)
      shift
      ;;
    --no-push)
      DO_PUSH=0
      shift
      ;;
    --remote)
      REMOTE="${2:-}"
      if [[ -z "$REMOTE" ]]; then
        echo "❌ Use: --remote usuario@host" >&2
        exit 1
      fi
      shift 2
      ;;
    --remote=*)
      REMOTE="${1#*=}"
      shift
      ;;
    --key)
      SSH_KEY="${2:-}"
      if [[ -z "$SSH_KEY" ]]; then
        echo "❌ Use: --key ~/.ssh/sua_chave" >&2
        exit 1
      fi
      shift 2
      ;;
    --key=*)
      SSH_KEY="${1#*=}"
      shift
      ;;
    --dir)
      REMOTE_DIR="${2:-}"
      shift 2
      ;;
    --dir=*)
      REMOTE_DIR="${1#*=}"
      shift
      ;;
    -h|--help)
      sed -n '2,18p' "$SCRIPT_PATH"
      exit 0
      ;;
    *)
      echo "❌ Argumento desconhecido: $1" >&2
      exit 1
      ;;
  esac
done

log() { echo -e "\n🚀 ==> $*"; }
ok()  { echo "✅ $*"; }

# ---------------------------------------------------------------------------
# Modo remoto: do PC → SSH na VPS → roda este mesmo script no servidor
# ---------------------------------------------------------------------------
if [[ -n "$REMOTE" ]]; then
  if ! command -v ssh >/dev/null 2>&1; then
    echo "❌ ssh não encontrado no PATH" >&2
    exit 1
  fi

  SSH_OPTS=(
    -p "$SSH_PORT"
    -o BatchMode=yes
    -o StrictHostKeyChecking=accept-new
  )
  if [[ -n "$SSH_KEY" ]]; then
    SSH_KEY_EXPANDED="${SSH_KEY/#\~/$HOME}"
    if [[ ! -f "$SSH_KEY_EXPANDED" ]]; then
      echo "❌ Chave SSH não encontrada: $SSH_KEY_EXPANDED" >&2
      exit 1
    fi
    SSH_OPTS+=(-o IdentitiesOnly=yes -i "$SSH_KEY_EXPANDED")
  fi

  cd "$APP_DIR"

  if [[ "$DO_PUSH" -eq 1 ]]; then
    if [[ -d .git ]]; then
      BRANCH="$(git rev-parse --abbrev-ref HEAD)"
      log "📤 Enviando commits locais (git push origin ${BRANCH})"
      git push origin "$BRANCH"
      ok "Push concluído"
    else
      echo "⚠️  Sem .git local — pulando push"
    fi
  else
    echo "ℹ️  --no-push: não enviou commits do PC"
  fi

  REMOTE_ARGS=""
  if [[ ${#PASS_ARGS[@]} -gt 0 ]]; then
    REMOTE_ARGS="${PASS_ARGS[*]}"
  fi

  log "🔐 Conectando em ${REMOTE} (porta ${SSH_PORT})"
  if ! ssh "${SSH_OPTS[@]}" -o PreferredAuthentications=publickey -o PasswordAuthentication=no \
      "$REMOTE" 'echo ok' >/dev/null 2>&1; then
    PUB_HINT="${SSH_KEY:-$HOME/.ssh/id_rsa}.pub"
    cat >&2 <<EOF
❌ SSH com chave falhou para ${REMOTE}.

Instale a chave pública na VPS (pede a senha root só desta vez):

  ssh-copy-id -i ${PUB_HINT} ${REMOTE}

Depois teste e rode de novo:

  ssh -o BatchMode=yes -i ${SSH_KEY:-$HOME/.ssh/id_rsa} ${REMOTE} 'hostname'
  ./scripts/deploy.sh
EOF
    exit 1
  fi

  # shellcheck disable=SC2029
  ssh "${SSH_OPTS[@]}" -o PreferredAuthentications=publickey -o PasswordAuthentication=no \
    "$REMOTE" \
    "bash -lc 'set -euo pipefail; cd \"${REMOTE_DIR}\" && DEPLOY_FORCE_LOCAL=1 bash scripts/deploy.sh ${REMOTE_ARGS}'"

  ok "🎉 Deploy remoto concluído em $(date '+%Y-%m-%d %H:%M:%S')"
  exit 0
fi

# ---------------------------------------------------------------------------
# Modo local: executa na própria máquina (VPS)
# ---------------------------------------------------------------------------
cd "$APP_DIR"

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
# Opcache do PHP-FPM: reinicia para carregar o código novo do git
docker compose restart app
# Recarrega nginx para não ficar com IP antigo do app (evita 502)
docker compose restart nginx
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

log "🩹 Patch egulias/email-validator (PHP 7.4)"
docker compose exec -T app php /var/www/html/scripts/patch-email-validator.php

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
