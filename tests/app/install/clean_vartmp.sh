#!/bin/sh
APPDIR=$(dirname $0)/..

DIRS="$APPDIR/var/log $APPDIR/var/mails $APPDIR/var/uploads $APPDIR/temp/app/"

rm -rf $APPDIR/var/log/*
rm -rf $APPDIR/var/mails/*
rm -rf $APPDIR/var/uploads/*
rm -rf $APPDIR/temp/app/*
touch $APPDIR/temp/app/.empty
