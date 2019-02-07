#!/bin/sh
####################################
#
# Install PHP dependencies via Composer
# 
####################################

# exit on error
set -e

# force compile on first build
test -e compiled/drupal/composer.lock || forceCompile=true

composerChanges=`diff drupal/composer.lock compiled/drupal/composer.lock || true`

if [ ! -z "$composerChanges" ] || [ ! -z "$forceCompile" ];then
    echo "drupal/composer.lock has changed:"
    echo $composerChanges

    cd drupal

    echo "running composer (with dev packages)"
    php ./composer.phar install --optimize-autoloader

    cd ..
else
    echo "drupal/composer.lock has not changed, only running autoload"

    cd drupal
    php ./composer.phar dump-autoload
    cd ..
fi

