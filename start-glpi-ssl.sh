#!/bin/bash
set -e

a2enmod ssl
a2disconf glpi-plugins || true
rm -f /var/www/html/glpi/public/plugins
a2ensite default-ssl

# Update packages can contain the official GLPI Agent MSI. Keep the PHP limit
# slightly above the 250 MB limit enforced by the plugin itself.
for php_conf_dir in /etc/php/*/apache2/conf.d; do
    [ -d "$php_conf_dir" ] || continue
    printf '%s\n' 'upload_max_filesize=256M' 'post_max_size=260M' 'memory_limit=512M' > "$php_conf_dir/99-ativa-uploads.ini"
done

# The GLPI tree is a bind mount. Files created by maintenance commands on the
# host may therefore arrive with an owner that Apache/PHP cannot write as.
glpi_files_dir=/var/www/html/glpi/files
if [ -d "$glpi_files_dir" ]; then
    chown -R www-data:www-data "$glpi_files_dir"
    find "$glpi_files_dir" -type d -exec chmod 0750 {} +
    find "$glpi_files_dir" -type f -exec chmod 0640 {} +
fi

exec /opt/glpi-start.sh
