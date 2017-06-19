#!/bin/bash
####################################
#
# Create required directories, extract base resources, set folder permissions
#
####################################

# create required directories
test -e $htdocs || mkdir $htdocs --parents
test -e $htdocs/cache || mkdir $htdocs/cache
test -e $htdocs/logs || mkdir $htdocs/logs

# create log file
touch $htdocs/logs/error.log

echo "Updating folder permissions..."

chmod 777 $htdocs/drupal/sites/default/settings.php
chmod 777 $htdocs/drupal/sites/default/settings.local.php
chmod 777 $htdocs/drupal/sites/default/services.yml

# change directory permissions
chmod 775 $htdocs/cache -R
chmod 775 $htdocs/logs -R

chmod 775 $htdocs/db
chmod 777 $htdocs/db/config

test -e $htdocs/drupal/sites/default/files || mkdir $htdocs/drupal/sites/default/files
chmod 777 $htdocs/drupal/sites/default/files

chmod 777 $htdocs/drupal
chmod 777 $htdocs/drupal/sites
chmod 777 $htdocs/drupal/sites/default
