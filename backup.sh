#!/usr/bin/env bash
set -Eeuo pipefail

project_dir="${GLPI_PROJECT_DIR:-/opt/glpi}"
backup_dir="${GLPI_BACKUP_DIR:-/opt/backups_glpi}"
timestamp="$(date +%Y%m%d_%H%M%S)"
dump_file="${backup_dir}/glpidb_${timestamp}.sql"
dump_partial="${dump_file}.partial"
files_file="${backup_dir}/glpi_files_${timestamp}.tar.gz"

cleanup() {
    rm -f -- "$dump_partial"
}
trap cleanup EXIT

mkdir -p -- "$backup_dir"

if ! docker inspect glpi_db >/dev/null 2>&1; then
    echo "ERRO: o container glpi_db nao existe." >&2
    exit 1
fi

if ! docker exec glpi_db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqladmin ping --host=127.0.0.1 --user=root --silent' >/dev/null 2>&1; then
    echo "ERRO: o MySQL nao esta saudavel. O backup foi cancelado." >&2
    exit 1
fi

echo "Criando backup consistente do banco em ${dump_file}..."
docker exec glpi_db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump --user=root --single-transaction --quick --skip-lock-tables --routines --events --triggers --hex-blob --no-tablespaces --set-gtid-purged=OFF glpidb' > "$dump_partial"

if [[ ! -s "$dump_partial" ]]; then
    echo "ERRO: o dump do banco ficou vazio." >&2
    exit 1
fi

mv -- "$dump_partial" "$dump_file"

echo "Criando backup dos arquivos persistentes do GLPI..."
tar -czf "$files_file" -C "$project_dir" glpi_data

sha256sum "$dump_file" "$files_file" > "${backup_dir}/checksums_${timestamp}.sha256"

echo "Backup concluido:"
echo "  Banco:    ${dump_file}"
echo "  Arquivos: ${files_file}"
