#!/usr/bin/env bash
set -Eeuo pipefail

project_dir="${GLPI_PROJECT_DIR:-/opt/glpi}"
plugin_name="${1:-ativawallpaper}"

cd "$project_dir"

if ! docker exec glpi_db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqladmin ping --host=127.0.0.1 --user=root --silent' >/dev/null 2>&1; then
    echo "ERRO: glpi_db nao esta saudavel. A atualizacao foi cancelada antes do git pull." >&2
    exit 1
fi

bash "${project_dir}/backup.sh"

echo "Atualizando o repositorio..."
git pull --ff-only
docker compose config --quiet

echo "Configurando o limite para pacotes de atualizacao..."
docker exec glpi_web sh -c 'for php_conf_dir in /etc/php/*/apache2/conf.d; do [ -d "$php_conf_dir" ] || continue; printf "%s\n" "upload_max_filesize=256M" "post_max_size=260M" "memory_limit=512M" > "$php_conf_dir/99-ativa-uploads.ini"; done; apache2ctl graceful'

if ! docker exec glpi_db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqladmin ping --host=127.0.0.1 --user=root --silent' >/dev/null 2>&1; then
    echo "ERRO: o banco deixou de responder. A instalacao do plugin nao sera executada." >&2
    exit 1
fi

echo "Instalando/atualizando o plugin ${plugin_name}..."
docker exec -u www-data glpi_web php /var/www/html/glpi/bin/console glpi:plugin:install "$plugin_name" --username=glpi --force
docker exec -u www-data glpi_web php /var/www/html/glpi/bin/console glpi:plugin:activate "$plugin_name"

echo "Limpando o cache do GLPI..."
if ! docker exec -u www-data glpi_web php /var/www/html/glpi/bin/console cache:clear; then
    echo "Ajustando permissoes do cache e dos logs antes de tentar novamente..."
    docker exec -u root glpi_web chown -R www-data:www-data \
        /var/www/html/glpi/files/_cache \
        /var/www/html/glpi/files/_log
    docker exec -u www-data glpi_web php /var/www/html/glpi/bin/console cache:clear
fi

echo "Atualizacao concluida sem reiniciar o banco."
