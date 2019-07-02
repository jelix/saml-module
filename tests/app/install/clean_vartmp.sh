#!/bin/sh
APPDIR=$(dirname $0)/..

rm -rf $APPDIR/var/log/*
rm -rf $APPDIR/var/mails/*
rm -rf $APPDIR/var/uploads/*
rm -rf $APPDIR/temp/app/*
touch $APPDIR/temp/app/.dummy
touch $APPDIR/var/log/.dummy
touch $APPDIR/var/mails/.dummy
touch $APPDIR/var/uploads/.dummy
