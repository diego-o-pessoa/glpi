#!/bin/bash
set -e

a2enmod ssl
a2disconf glpi-plugins || true
rm -f /var/www/html/glpi/public/plugins
a2ensite default-ssl

# The GLPI tree is a bind mount. Files created by maintenance commands on the
# host may therefore arrive with an owner that Apache/PHP cannot write as.
glpi_files_dir=/var/www/html/glpi/files
if [ -d "$glpi_files_dir" ]; then
    chown -R www-data:www-data "$glpi_files_dir"
    find "$glpi_files_dir" -type d -exec chmod 0750 {} +
    find "$glpi_files_dir" -type f -exec chmod 0640 {} +
fi

exec /opt/glpi-start.sh
