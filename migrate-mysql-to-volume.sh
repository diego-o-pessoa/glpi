#!/usr/bin/env bash
set -Eeuo pipefail

project_dir="${GLPI_PROJECT_DIR:-/opt/glpi}"
dump_file="${1:-}"
volume_name="glpi_mysql_data"

if [[ -z "$dump_file" || ! -s "$dump_file" ]]; then
    echo "Uso: $0 /caminho/para/glpidb.sql" >&2
    echo "O arquivo deve existir e nao pode estar vazio." >&2
    exit 2
fi

dump_file="$(readlink -f "$dump_file")"
cd "$project_dir"
docker compose config --quiet

if docker ps --format '{{.Names}}' | grep -Fxq glpi_db_recovery; then
    echo "ERRO: glpi_db_recovery ainda esta em execucao. Pare-o antes da migracao." >&2
    exit 1
fi

if docker volume inspect "$volume_name" >/dev/null 2>&1; then
    echo "ERRO: o volume ${volume_name} ja existe." >&2
    echo "Nada foi alterado. Verifique o volume antes de tentar novamente." >&2
    exit 1
fi

echo "Parando somente os servicos que usam o banco..."
docker compose stop glpi mysql
docker compose rm -f mysql

echo "Criando o volume Docker e inicializando um MySQL limpo..."
docker compose up -d mysql

healthy=false
for _ in $(seq 1 60); do
    status="$(docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' glpi_db 2>/dev/null || true)"
    if [[ "$status" == "healthy" ]]; then
        healthy=true
        break
    fi
    if [[ "$status" == "unhealthy" || "$status" == "exited" ]]; then
        docker logs glpi_db --tail 100 >&2 || true
        echo "ERRO: o novo MySQL nao iniciou corretamente." >&2
        exit 1
    fi
    sleep 2
done

if [[ "$healthy" != true ]]; then
    docker logs glpi_db --tail 100 >&2 || true
    echo "ERRO: tempo limite aguardando o novo MySQL." >&2
    exit 1
fi

echo "Importando ${dump_file}..."
docker exec -i glpi_db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --user=root glpidb' < "$dump_file"

table_count="$(docker exec glpi_db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql --batch --skip-column-names --user=root --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=\"glpidb\";"')"
if [[ ! "$table_count" =~ ^[0-9]+$ || "$table_count" -lt 1 ]]; then
    echo "ERRO: a importacao terminou sem tabelas no glpidb." >&2
    exit 1
fi

echo "Banco restaurado com ${table_count} tabelas. Iniciando o GLPI..."
docker compose up -d glpi portal_registro

echo "Migracao concluida. Os dados agora estao no volume ${volume_name}, fora do Git."
