# Migracao definitiva do MySQL para volume Docker

O diretorio `mysql_data` ja foi versionado no Git. Isso torna qualquer
`git pull` perigoso: arquivos fisicos do InnoDB podem ser misturados com uma
versao antiga e o MySQL passa a reclamar de redo logs ausentes.

O `docker-compose.yml` atual usa o volume Docker `glpi_mysql_data`, fora do
repositorio. Esta migracao deve ser feita uma unica vez no servidor.

## Antes do primeiro git pull com esta correcao

Confirme que existe um dump logico valido e copie-o para fora do repositorio:

```bash
test -s /opt/glpi/glpidb-recovery-20260910.sql
mkdir -p /opt/backups_glpi
cp -a /opt/glpi/glpidb-recovery-20260910.sql /opt/backups_glpi/glpidb-before-volume.sql
sha256sum /opt/backups_glpi/glpidb-before-volume.sql
```

Se o primeiro comando falhar, nao continue. Gere outro dump pelo container de
recuperacao antes de alterar o armazenamento.

Pare o banco e preserve o diretorio fisico atual. Ele nao deve ser apagado:

```bash
docker stop glpi_web glpi_db 2>/dev/null || true
mv /opt/glpi/mysql_data /opt/backups_glpi/mysql_data-corrompido-20260910
```

Agora atualize o repositorio e execute a migracao automatizada:

```bash
cd /opt/glpi
git pull --ff-only
bash ./migrate-mysql-to-volume.sh /opt/backups_glpi/glpidb-before-volume.sql
```

Valide o resultado:

```bash
docker compose ps
docker exec glpi_db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqladmin ping --host=127.0.0.1 --user=root --silent'
docker exec glpi_db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql --batch --skip-column-names --user=root glpidb --execute="SELECT COUNT(*) FROM glpi_users;"'
```

Somente depois dessas validacoes instale ou atualize o plugin:

```bash
docker exec -u www-data glpi_web php /var/www/html/glpi/bin/console glpi:plugin:install ativawallpaper --username=glpi --force
docker exec -u www-data glpi_web php /var/www/html/glpi/bin/console glpi:plugin:activate ativawallpaper
```

## Atualizacoes futuras

Depois da migracao, use o script abaixo. Ele valida o banco, cria um dump
consistente fora do repositorio, executa o `git pull` e atualiza o plugin sem
reiniciar o MySQL:

```bash
cd /opt/glpi
bash ./update-glpi.sh ativawallpaper
```

Nunca volte a mapear `./mysql_data:/var/lib/mysql` e nunca copie arquivos
individuais de um datadir InnoDB. A restauracao deve ser feita por dump SQL.
