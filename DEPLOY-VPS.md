# Deploy na VPS (do zero)

Guia para subir o **Sistema Índice Cartório** em uma VPS Ubuntu zerada, usando Docker.

## O que sobe

| Serviço     | Porta padrão | URL                         |
|-------------|--------------|-----------------------------|
| Aplicação   | 80           | `http://IP_DA_VPS`          |
| phpMyAdmin  | 8080         | `http://IP_DA_VPS:8080`     |
| MySQL       | 3306         | uso interno / opcional      |

---

## 1. Requisitos

- VPS Ubuntu 20.04 / 22.04 / 24.04
- Acesso SSH com usuário que possa usar `sudo`
- Repositório Git do projeto (GitHub/GitLab/etc.)
- Portas **22**, **80**, **443** e **8080** liberadas no painel do provedor (security group / firewall)

---

## 2. Conectar na VPS

No seu computador:

```bash
ssh usuario@IP_DA_VPS
```

---

## 3. Instalar Git (se ainda não tiver)

```bash
sudo apt update
sudo apt install -y git
```

---

## 4. Clonar o projeto

Escolha um diretório (exemplo: `/var/www`):

```bash
sudo mkdir -p /var/www
sudo chown "$USER":"$USER" /var/www
cd /var/www
git clone URL_DO_SEU_REPOSITORIO SistemaIndiceCartorio
cd SistemaIndiceCartorio
```

> Troque `URL_DO_SEU_REPOSITORIO` pela URL real do Git.

---

## 5. Rodar o setup (única vez)

Este script instala Docker, Docker Compose, configura o firewall, cria o `.env`, sobe os containers e faz o primeiro deploy:

```bash
sudo bash scripts/setup-vps.sh
```

Aguarde terminar (pode demorar alguns minutos no primeiro build).

Ao final, anote:
- URL da aplicação
- URL do phpMyAdmin
- Senhas geradas no `.env` (`DB_PASSWORD`, `DB_ROOT_PASSWORD`)

Para usar `docker` sem `sudo` depois do setup:

```bash
newgrp docker
```

(ou faça logout/login no SSH)

---

## 6. Ajustar o `.env`

```bash
nano .env
```

Campos importantes:

```env
APP_URL=http://IP_DA_VPS
# ou, se já tiver domínio:
# APP_URL=http://seusite.com.br

APP_ENV=production
APP_DEBUG=false

DB_HOST=db
DB_DATABASE=cartoriobd
DB_USERNAME=cartorio
DB_PASSWORD=...          # gerado no setup (não apague sem necessidade)
DB_ROOT_PASSWORD=...     # gerado no setup

APP_PORT=80
PHPMYADMIN_PORT=8080
```

Salve (`Ctrl+O`, Enter) e saia (`Ctrl+X`).

Depois, recarregue o cache do Laravel:

```bash
docker compose exec -T app php artisan config:cache
```

---

## 7. Conferir se está no ar

```bash
docker compose ps
```

Os containers `indice_app`, `indice_nginx`, `indice_db` e `indice_phpmyadmin` devem estar **Up**.

Teste no navegador:

- App: `http://IP_DA_VPS`
- phpMyAdmin: `http://IP_DA_VPS:8080`

No phpMyAdmin, entre com:
- usuário: valor de `DB_USERNAME` (ou `root`)
- senha: valor de `DB_PASSWORD` (ou `DB_ROOT_PASSWORD`)

---

## 8. Deploys seguintes (atualizações)

Sempre que houver mudança no código:

```bash
cd /var/www/SistemaIndiceCartorio
bash scripts/deploy.sh
```

Opções:

```bash
bash scripts/deploy.sh --no-pull   # não faz git pull
bash scripts/deploy.sh --seed      # roda seeders após migrate
```

---

## Comandos úteis

```bash
# Ver logs
docker compose logs -f

# Logs só do app
docker compose logs -f app

# Reiniciar tudo
docker compose restart

# Parar tudo
docker compose down

# Subir de novo
docker compose up -d

# Entrar no container PHP
docker compose exec app bash

# Rodar artisan
docker compose exec app php artisan ...
```

---

## Problemas comuns

### Porta 80 ou 8080 não abre
- Libere no **firewall do provedor** (além do UFW da VPS).
- Confira: `sudo ufw status`

### Página em branco / erro 500
```bash
docker compose logs -f app nginx
docker compose exec app tail -n 50 storage/logs/laravel.log
```

### Erro de permissão em `storage`
```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R ug+rwx storage bootstrap/cache
```

### Banco não conecta
- Confirme `DB_HOST=db` no `.env`
- Confirme que o MySQL está healthy: `docker compose ps`

---

## Resumo rápido

```bash
ssh usuario@IP_DA_VPS
sudo apt update && sudo apt install -y git
cd /var/www && git clone URL_DO_REPO SistemaIndiceCartorio
cd SistemaIndiceCartorio
sudo bash scripts/setup-vps.sh
nano .env   # ajuste APP_URL
docker compose exec -T app php artisan config:cache
```

Pronto: app na porta **80**, phpMyAdmin na **8080**.
