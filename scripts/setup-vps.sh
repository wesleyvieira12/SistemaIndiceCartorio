#!/usr/bin/env bash
# Instala Docker, Docker Compose e prepara a VPS para o Sistema Índice Cartório.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

log()  { echo -e "\n🚀 ==> $*"; }
ok()   { echo "✅ $*"; }
fail() { echo "❌ $*" >&2; exit 1; }

if [[ "$(id -u)" -ne 0 ]]; then
  fail "Execute como root: sudo bash scripts/setup-vps.sh"
fi

REAL_USER="${SUDO_USER:-$USER}"
REAL_HOME="$(getent passwd "$REAL_USER" | cut -d: -f6)"

log "📦 Atualizando pacotes do sistema"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y \
  ca-certificates \
  curl \
  gnupg \
  lsb-release \
  git \
  ufw

log "🐳 Instalando Docker Engine"
if ! command -v docker >/dev/null 2>&1; then
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
    | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
  chmod a+r /etc/apt/keyrings/docker.gpg

  ARCH="$(dpkg --print-architecture)"
  CODENAME="$(. /etc/os-release && echo "${VERSION_CODENAME}")"

  echo \
    "deb [arch=${ARCH} signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu ${CODENAME} stable" \
    > /etc/apt/sources.list.d/docker.list

  apt-get update -y
  apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  systemctl enable --now docker
  ok "Docker instalado"
else
  ok "Docker já estava instalado ($(docker --version))"
fi

if ! docker compose version >/dev/null 2>&1; then
  fail "Docker Compose plugin não encontrado após a instalação"
fi
ok "Docker Compose: $(docker compose version --short)"

log "👤 Adicionando usuário ${REAL_USER} ao grupo docker"
usermod -aG docker "$REAL_USER" || true

log "🔥 Configurando firewall (UFW)"
ufw allow OpenSSH >/dev/null 2>&1 || true
ufw allow 80/tcp >/dev/null 2>&1 || true
ufw allow 443/tcp >/dev/null 2>&1 || true
ufw allow 8082/tcp >/dev/null 2>&1 || true
ufw allow 8081/tcp >/dev/null 2>&1 || true
# Ativa UFW sem travar SSH se já estiver ativo
ufw --force enable >/dev/null 2>&1 || true
ok "Portas 22/80/443/8081/8082 liberadas"

log "📝 Preparando arquivo .env"
if [[ ! -f .env ]]; then
  if [[ -f .env.example ]]; then
    cp .env.example .env
    ok ".env criado a partir de .env.example"
  else
    fail "Arquivo .env.example não encontrado"
  fi
else
  ok ".env já existe (mantido)"
fi

# Ajustes mínimos para ambiente Docker na VPS
set_env() {
  local key="$1"
  local value="$2"
  if grep -qE "^${key}=" .env; then
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    echo "${key}=${value}" >> .env
  fi
}

set_env "APP_ENV" "production"
set_env "APP_DEBUG" "false"
set_env "DB_HOST" "db"
set_env "DB_PORT" "3306"

CURRENT_DB_PASS="$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2- || true)"
if [[ -z "$CURRENT_DB_PASS" || "$CURRENT_DB_PASS" == "secret" ]]; then
  GENERATED_PASS="$(openssl rand -base64 18 | tr -dc 'A-Za-z0-9' | head -c 24)"
  set_env "DB_PASSWORD" "$GENERATED_PASS"
  ok "DB_PASSWORD gerado automaticamente"
fi

if ! grep -qE '^DB_ROOT_PASSWORD=' .env; then
  ROOT_PASS="$(openssl rand -base64 18 | tr -dc 'A-Za-z0-9' | head -c 24)"
  echo "DB_ROOT_PASSWORD=${ROOT_PASS}" >> .env
  ok "DB_ROOT_PASSWORD gerado"
fi

grep -qE '^APP_PORT=' .env || echo "APP_PORT=8081" >> .env
grep -qE '^PHPMYADMIN_PORT=' .env || echo "PHPMYADMIN_PORT=8082" >> .env

# Se APP_PORT ainda for 80 (conflito com Traefik), migra para 8081
CURRENT_APP_PORT="$(grep -E '^APP_PORT=' .env | cut -d= -f2- || true)"
if [[ "$CURRENT_APP_PORT" == "80" ]]; then
  set_env "APP_PORT" "8081"
  ok "APP_PORT alterado de 80 para 8081 (Traefik na 80)"
fi

log "📁 Criando diretórios de storage e permissões"
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R "${REAL_USER}:${REAL_USER}" storage bootstrap/cache || true
find storage bootstrap/cache -type d -exec chmod ug+rwx {} \;
find storage bootstrap/cache -type f -exec chmod ug+rw {} \;

log "🏗️  Build e subida inicial dos containers"
docker compose build
docker compose up -d

log "📦 Instalando dependências e aplicando migrations (primeiro deploy)"
# Aguarda o PHP-FPM ficar pronto
sleep 3
docker compose exec -T app composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Gera APP_KEY se estiver vazio
if ! grep -qE '^APP_KEY=base64:' .env; then
  docker compose exec -T app php artisan key:generate --force
  ok "APP_KEY gerado"
fi

docker compose exec -T app npm install --no-package-lock --legacy-peer-deps
docker compose exec -T app npm run production
docker compose exec -T app php artisan storage:link || true
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
# Laravel 5.5 não tem view:cache

docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache || true
docker compose exec -T app bash -lc '
  find storage bootstrap/cache -type d -exec chmod ug+rwx {} \;
  find storage bootstrap/cache -type f -exec chmod ug+rw {} \;
' || true

log "📊 Status dos containers"
docker compose ps

PMA_PORT_MSG="$(grep -E '^PHPMYADMIN_PORT=' .env | cut -d= -f2- || echo 8082)"
APP_PORT_MSG="$(grep -E '^APP_PORT=' .env | cut -d= -f2- || echo 8081)"
HOST_IP="$(hostname -I | awk '{print $1}')"

cat <<EOF

============================================================
🎉 Setup da VPS concluído!

📌 Próximos passos:
  1. Edite o .env: APP_URL, TRAEFIK_HOST, TRAEFIK_ACME_EMAIL
  2. Faça logout/login (ou: newgrp docker) para usar docker sem sudo
  3. Para novos deploys:  bash scripts/deploy.sh
  4. Traefik escuta 80/443; nginx do app também em ${APP_PORT_MSG}

🌐 Domínio (Traefik): https://1serventiaimoveisoeiras.com.br
🌐 Direto:           http://${HOST_IP}:${APP_PORT_MSG}
🗄️  phpMyAdmin:       http://${HOST_IP}:${PMA_PORT_MSG}
📄 Arquivo de ambiente: ${APP_DIR}/.env
============================================================
EOF
