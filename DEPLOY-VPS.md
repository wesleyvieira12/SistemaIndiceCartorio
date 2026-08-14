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

## 4. Adicionar chave SSH no GitHub

Necessário se o repositório for privado (ou se preferir clonar via SSH).

Na VPS, gere uma chave (se ainda não tiver):

```bash
ssh-keygen -t ed25519 -C "vps-deploy" -f ~/.ssh/id_ed25519 -N ""
```

Mostre a chave **pública** e copie o conteúdo:

```bash
cat ~/.ssh/id_ed25519.pub
```

No GitHub:

1. Abra **Settings** → **SSH and GPG keys** → **New SSH key**  
   (ou, para acesso só a este repositório: **Settings** do repo → **Deploy keys** → **Add deploy key**)
2. Título: ex. `VPS deploy`
3. Cole o conteúdo de `id_ed25519.pub`
4. Salve

Teste a conexão:

```bash
ssh -T git@github.com
```

Deve aparecer algo como: `Hi SEU_USUARIO! You've successfully authenticated...`

---

## 5. Clonar o projeto

Escolha um diretório (exemplo: `/var/www`):

```bash
sudo mkdir -p /var/www
sudo chown "$USER":"$USER" /var/www
cd /var/www
git clone git@github.com:SEU_USUARIO/SistemaIndiceCartorio.git SistemaIndiceCartorio
cd SistemaIndiceCartorio
```

> Troque `SEU_USUARIO` pelo usuário/organização do GitHub.  
> Se preferir HTTPS: `git clone https://github.com/SEU_USUARIO/SistemaIndiceCartorio.git`

---

## 6. Liberar a porta 80 (e 8080, se ocupada)

O container `indice_nginx` usa a porta **80** do host. Se outro programa já estiver nela, o Docker falha com `address already in use`.

Verifique o que está usando:

```bash
sudo ss -tlnp | grep -E ':80 |:8080 '
```

Em VPS Ubuntu, costuma ser **Apache** ou **nginx** do sistema. Pare e desabilite:

```bash
# Apache
sudo systemctl stop apache2
sudo systemctl disable apache2

# nginx do host (não o container Docker)
sudo systemctl stop nginx
sudo systemctl disable nginx
```

Confirme que a porta ficou livre:

```bash
sudo ss -tlnp | grep -E ':80 |:8080 '
```

(não deve listar nada, ou só processos que você realmente quer manter)

Opcional — remover de vez (só se não precisar mais desses serviços):

```bash
sudo apt remove -y apache2 nginx
sudo apt autoremove -y
```

---

## 7. Rodar o setup (única vez)

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

## 8. Ajustar o `.env`

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

## 9. Conferir se está no ar

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

## 10. Deploys seguintes (atualizações)

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

### `address already in use` na porta 80
Algo no host já usa a porta (Apache/nginx, etc.). Veja a [seção 6](#6-liberar-a-porta-80-e-8080-se-ocupada), liberte a porta e rode de novo:

```bash
docker compose up -d
```

### Porta 80 ou 8080 não abre no navegador
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

### `Undefined index: name` no `package:discover`
Laravel **5.5.32** não lê o `installed.json` do Composer 2. O `Dockerfile` usa Composer **1.10**.

Na VPS, após atualizar o código:

```bash
git pull
bash scripts/deploy.sh --no-pull
```

Atalho sem rebuild (só para destravar agora):

```bash
docker compose exec -T app composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
echo '<?php return [];' > bootstrap/cache/packages.php
docker compose exec -T app npm ci --legacy-peer-deps || docker compose exec -T app npm install --legacy-peer-deps
docker compose exec -T app npm run production
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan storage:link || true
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
```

---

## Resumo rápido

```bash
ssh usuario@IP_DA_VPS
sudo apt update && sudo apt install -y git
ssh-keygen -t ed25519 -C "vps-deploy" -f ~/.ssh/id_ed25519 -N ""
cat ~/.ssh/id_ed25519.pub   # cole no GitHub (SSH keys ou Deploy keys)
ssh -T git@github.com
cd /var/www && git clone git@github.com:SEU_USUARIO/SistemaIndiceCartorio.git SistemaIndiceCartorio
cd SistemaIndiceCartorio
sudo ss -tlnp | grep -E ':80 |:8080 '   # se ocupada, stop/disable apache2 ou nginx
sudo systemctl stop apache2 nginx 2>/dev/null; sudo systemctl disable apache2 nginx 2>/dev/null
sudo bash scripts/setup-vps.sh
nano .env   # ajuste APP_URL
docker compose exec -T app php artisan config:cache
```

Pronto: app na porta **80**, phpMyAdmin na **8080**.
