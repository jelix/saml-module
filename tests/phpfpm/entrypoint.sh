#!/bin/sh

set -e
set -x

# Copy config files to mount point
[ ! -f /opt/tests/app/var/config/localconfig.ini.php  ] && cp /opt/tests/app/var/config/localconfig.ini.php.dist  /opt/tests/app/var/config/localconfig.ini.php
[ ! -f /opt/tests/app/var/config/profiles.ini.php     ] && cp /opt/tests/app/var/config/profiles.ini.php.dist     /opt/tests/app/var/config/profiles.ini.php

jelix_archive=https://download.jelix.org/jelix/releases/1.6.x/1.6.23/jelix-1.6.23-dev.tar.gz
jelix_directory=jelix-1.6.23-dev

if [ ! -d /opt/tests/jelix ]; then
    wget -O /opt/tests/jelix.tar.gz $jelix_archive
    cd /opt/tests/ && tar xzf jelix.tar.gz
    mv $jelix_directory jelix
    rm jelix.tar.gz
fi

cd /opt/tests/app
composer install --prefer-dist --no-progress --no-ansi --no-interaction

# Set up Configuration
php install/installer.php

# Set owner/and group
sh install/set_rights.sh

# Clean cache files in case we are
# Restarting the container
sh install/clean_vartmp.sh

if [ ! -f /opt/tests/app/var/config/saml/certs/sp.crt ]; then
    openssl req -x509 -newkey rsa:4096 -keyout /opt/tests/app/var/config/saml/certs/sp.key -out /opt/tests/app/var/config/saml/certs/sp.crt -days 3650 -nodes -subj "/C=FR/ST=France/L=Paris/O=jelix/OU=tests/CN=samltest.jelix.org"
    chown 1000:1000 /opt/tests/app/var/config/saml/certs/sp.key /opt/tests/app/var/config/saml/certs/sp.crt
    chmod 644 /opt/tests/app/var/config/saml/certs/sp.key /opt/tests/app/var/config/saml/certs/sp.crt

fi

echo "launch exec $@"
exec "$@"
