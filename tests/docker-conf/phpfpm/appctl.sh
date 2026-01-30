#!/bin/bash
APPDIR="/opt/tests/app"
APP_USER=userphp
APP_GROUP=groupphp

COMMAND="$1"

if [ "$COMMAND" == "" ]; then
    echo "Error: command is missing"
    exit 1;
fi


function cleanTmp() {
    if [ ! -d $APPDIR/var/log ]; then
        mkdir $APPDIR/var/log
        chown $APP_USER:$APP_GROUP $APPDIR/var/log
    fi

    if [ ! -d $APPDIR/temp/ ]; then
        mkdir $APPDIR/temp/
        chown $APP_USER:$APP_GROUP $APPDIR/temp
    else
        rm -rf $APPDIR/temp/*
    fi
    touch $APPDIR/temp/.dummy
    chown $APP_USER:$APP_GROUP $APPDIR/temp/.dummy
}


function cleanApp() {
    if [ -f $APPDIR/var/config/CLOSED ]; then
        rm -f $APPDIR/var/config/CLOSED
    fi

    if [ ! -d $APPDIR/var/log ]; then
        mkdir $APPDIR/var/log
        chown $APP_USER:$APP_GROUP $APPDIR/var/log
    fi

    if [ -f $APPDIR/var/config/installer.ini.php ]; then
        rm -f $APPDIR/var/config/installer.ini.php
    fi
    if [ -f $APPDIR/var/config/liveconfig.ini.php ]; then
        rm -f $APPDIR/var/config/liveconfig.ini.php
    fi
    if [ -f $APPDIR/var/config/localurls.xml ]; then
        rm -f $APPDIR/var/config/localurls.xml
    fi

    if [ -f $APPDIR/var/config/localconfig.ini.php ]; then
        rm -f $APPDIR/var/config/localconfig.ini.php
    fi

    if [ -f $APPDIR/var/config/profiles.ini.php ]; then
        rm -f $APPDIR/var/config/profiles.ini.php
    fi
    if [ -f $APPDIR/var/db/users.ini.php ]; then
        rm -f $APPDIR/var/db/users.ini.php
    fi

    rm -rf $APPDIR/var/log/*
    rm -rf $APPDIR/var/db/*
    rm -rf $APPDIR/var/mails/*
    rm -rf $APPDIR/var/uploads/*
    touch $APPDIR/var/log/.dummy && chown $APP_USER:$APP_GROUP $APPDIR/var/log/.dummy
    touch $APPDIR/var/db/.dummy && chown $APP_USER:$APP_GROUP $APPDIR/var/db/.dummy
    touch $APPDIR/var/mails/.dummy && chown $APP_USER:$APP_GROUP $APPDIR/var/mails/.dummy
    touch $APPDIR/var/uploads/.dummy && chown $APP_USER:$APP_GROUP $APPDIR/var/uploads/.dummy

    cleanTmp

}


function resetApp() {
  cleanApp

  if [ -f $APPDIR/var/config/profiles.ini.php.dist ]; then
      cp $APPDIR/var/config/profiles.ini.php.dist $APPDIR/var/config/profiles.ini.php
  fi
  if [ -f $APPDIR/var/config/localconfig.ini.php.dist ]; then
      cp $APPDIR/var/config/localconfig.ini.php.dist $APPDIR/var/config/localconfig.ini.php
  fi
  if [ -f $APPDIR/var/users.ini.php.dist ]; then
      cp $APPDIR/var/users.ini.php.dist $APPDIR/var/db/users.ini.php
  fi

  chown -R $APP_USER:$APP_GROUP $APPDIR/var/config/profiles.ini.php $APPDIR/var/config/localconfig.ini.php  $APPDIR/var/db/users.ini.php
  setRights

}


function launchInstaller() {
    su $APP_USER -c "php $APPDIR/install/configurator.php --verbose saml"
    su $APP_USER -c "php $APPDIR/install/configurator.php --verbose samladmin"
    su $APP_USER -c "php $APPDIR/install/installer.php --verbose"
}

function setRights() {
    USER="$1"
    GROUP="$2"

    if [ "$USER" = "" ]; then
        USER="$APP_USER"
    fi

    if [ "$GROUP" = "" ]; then
        GROUP="$APP_GROUP"
    fi

    DIRS="$APPDIR/var/config $APPDIR/var/db $APPDIR/var/log $APPDIR/var/mails $APPDIR/temp/"

    chown -R $USER:$GROUP $DIRS
    chmod -R ug+w $DIRS
    chmod -R o-w $DIRS

}

function composerInstall() {
    if [ -f $APPDIR/composer.lock ]; then
        rm -f $APPDIR/composer.lock
    fi
    composer install --prefer-dist --no-progress --no-ansi --no-interaction --working-dir=$APPDIR
    chown -R $APP_USER:$APP_GROUP $APPDIR/vendor $APPDIR/composer.lock
}

function composerUpdate() {
    if [ -f $APPDIR/composer.lock ]; then
        rm -f $APPDIR/composer.lock
    fi
    composer update --prefer-dist --no-progress --no-ansi --no-interaction --working-dir=$APPDIR
    chown -R $APP_USER:$APP_GROUP $APPDIR/vendor $APPDIR/composer.lock
}

function launch() {

    if [ ! -f $APPDIR/var/config/profiles.ini.php ]; then
        cp $APPDIR/var/config/profiles.ini.php.dist $APPDIR/var/config/profiles.ini.php
    fi
    if [ -f $APPDIR/var/config/localconfig.ini.php ]; then
        cp $APPDIR/var/config/localconfig.ini.php.dist $APPDIR/var/config/localconfig.ini.php
    fi
    if [ ! -f $APPDIR/var/db/users.ini.php -a -f $APPDIR/var/users.ini.php.dist ]; then
        cp $APPDIR/var/users.ini.php.dist $APPDIR/var/db/users.ini.php
    fi
    chown -R $APP_USER:$APP_GROUP $APPDIR/var/config/profiles.ini.php $APPDIR/var/config/localconfig.ini.php  $APPDIR/var/db/users.ini.php

    if [ ! -d $APPDIR/vendor ]; then
      composerInstall
    fi

    if [[ $JELIX_VERSION == 1.6* ]]; then
        if [ "$JELIX_STABILITY" == "release" ]; then
            jelix_archive=https://download.jelix.org/jelix/releases/1.6.x/${JELIX_VERSION}/jelix-${JELIX_VERSION}-dev.tar.gz
        else
            jelix_archive=https://download.jelix.org/jelix/nightly/1.6.x/jelix-${JELIX_VERSION}-dev.tar.gz
        fi
        echo $jelix_archive
        jelix_directory=jelix-${JELIX_VERSION}-dev


        if [ ! -d /opt/tests/jelix ]; then
            wget -O /opt/tests/jelix.tar.gz $jelix_archive
            (
              cd /opt/tests/
              tar xzf jelix.tar.gz
              chown -R $APP_USER:$APP_GROUP $jelix_directory
              mv $jelix_directory jelix
              rm jelix.tar.gz
            )
        fi
    fi

    if [ ! -f $APPDIR/var/config/saml/certs/sp.crt ]; then
        openssl req -x509 -newkey rsa:4096 -keyout $APPDIR/var/config/saml/certs/sp.key -out $APPDIR/var/config/saml/certs/sp.crt -days 3650 -nodes -subj "/C=FR/ST=France/L=Paris/O=jelix/OU=tests/CN=samltest.jelix.org"
        chown 1000:1000 $APPDIR/var/config/saml/certs/sp.key $APPDIR/var/config/saml/certs/sp.crt
        chmod 644 $APPDIR/var/config/saml/certs/sp.key $APPDIR/var/config/saml/certs/sp.crt
    fi

    launchInstaller
    setRights
    cleanTmp
}


function initData()
{
  php $APPDIR/console.php account:create admin admin-test@jelix.org Bob SuperAdmin
  php $APPDIR/console.php account:login:create admin --backend=inifile --set-pass=admin
  php $APPDIR/console.php account:create john john@jelix.org John Doe
  php $APPDIR/console.php account:idp:set john loginpass john
  php $APPDIR/console.php account:create dwho dwho@jelix.org Doctor Who
  php $APPDIR/console.php account:idp:set dwho loginpass dwho
}

case $COMMAND in
    clean_tmp)
        cleanTmp;;
    clean)
        cleanApp;;
    reset)
        cleanApp
        launchInstaller
        ;;
    launch)
        launch;;
    install)
        launchInstaller;;
    init-data)
        initData
        ;;
    rights)
        setRights;;
    composer_install)
        composerInstall;;
    composer_update)
        composerUpdate;;
    *)
        echo "wrong command"
        exit 2
        ;;
esac

