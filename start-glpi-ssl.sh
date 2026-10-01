#!/bin/bash
set -e

a2enmod ssl
a2disconf glpi-plugins || true
rm -f /var/www/html/glpi/public/plugins
a2ensite default-ssl

# Update packages can contain the official GLPI Agent MSI. Keep the PHP limit
# slightly above the 250 MB limit enforced by the plugin itself.
#
# Sessions: the Debian PHP packages ship with session.gc_probability=0 and rely
# on a host cron (/etc/cron.d/php) that never runs inside this container, so
# expired session files were never removed (hundreds of thousands piled up in
# files/_sessions). Turn PHP's own garbage collector on. A session idle for
# more than 8 hours expires (users have to log in again after that).
session_lifetime=28800
for php_conf_dir in /etc/php/*/apache2/conf.d; do
    [ -d "$php_conf_dir" ] || continue
    printf '%s\n' 'upload_max_filesize=256M' 'post_max_size=260M' 'memory_limit=512M' > "$php_conf_dir/99-ativa-uploads.ini"
    printf '%s\n' 'session.gc_probability=1' 'session.gc_divisor=1000' "session.gc_maxlifetime=${session_lifetime}" > "$php_conf_dir/99-ativa-sessions.ini"
done

# The GLPI tree is a bind mount. Files created by maintenance commands on the
# host may therefore arrive with an owner that Apache/PHP cannot write as.
glpi_files_dir=/var/www/html/glpi/files
if [ -d "$glpi_files_dir" ]; then
    # Drop sessions that already expired before fixing permissions: the
    # recursive pass below would otherwise walk every stale session file.
    if [ -d "$glpi_files_dir/_sessions" ]; then
        find "$glpi_files_dir/_sessions" -maxdepth 1 -type f -name 'sess_*' -mmin +$((session_lifetime / 60)) -delete
    fi
    chown -R www-data:www-data "$glpi_files_dir"
    find "$glpi_files_dir" -type d -exec chmod 0750 {} +
    find "$glpi_files_dir" -type f -exec chmod 0640 {} +
fi

exec /opt/glpi-start.sh
