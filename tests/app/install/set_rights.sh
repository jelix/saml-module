#!/bin/sh
APPDIR=$(dirname $0)/..

USER="$1"
GROUP="$2"

if [ "$USER" = "" ]; then
    USER="www-data"
fi

if [ "$GROUP" = "" ]; then
    GROUP="www-data"
fi


DIRS="$APPDIR/var/config $APPDIR/var/db $APPDIR/var/log $APPDIR/var/mails $APPDIR/var/uploads $APPDIR/temp/app"

chown -R $USER:$GROUP $DIRS
chmod -R ugo+w $DIRS
