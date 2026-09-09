#!/bin/bash
set -e

a2enmod ssl
a2disconf glpi-plugins || true
rm -f /var/www/html/glpi/public/plugins
 a2ensite default-ssl
exec /opt/glpi-start.sh
