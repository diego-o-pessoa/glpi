#!/bin/bash
 
# Define os diretórios
DIR_GLPI="/opt/glpi"
DIR_BACKUPS="/opt/backups_glpi"
DATA=$(date +%Y%m%d_%H%M%S)
 
# Cria a pasta de backups se não existir
mkdir -p $DIR_BACKUPS
 
echo "Iniciando o backup do banco de dados..."
docker exec glpi_db mysqldump -u root -p'Ativ@Adm2026' glpidb > "$DIR_GLPI/dump_banco.sql"
 
echo "Iniciando o backup dos arquivos do GLPI (web, configs e dados)..."
tar -czvf "$DIR_BACKUPS/backup_glpi_$DATA.tar.gz" -C /opt glpi
 
echo "Limpando dump temporário..."
rm "$DIR_GLPI/dump_banco.sql"
 
echo "Backup concluído com sucesso e salvo em: $DIR_BACKUPS/backup_glpi_$DATA.tar.gz"

