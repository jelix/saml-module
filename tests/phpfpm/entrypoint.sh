#!/bin/sh

set -e
set -x

sed -i "/^user =/c\user = usertest"   /etc/php7/php-fpm.d/www.conf
sed -i "/^group =/c\group = grouptest" /etc/php7/php-fpm.d/www.conf

sh /bin/appctl.sh launch

echo "launch exec $@"
exec "$@"
