#!/bin/bash
####################################
#
# Create required directories, extract base resources, set folder permissions
#
####################################

# create required directories
test -e $htdocs || mkdir $htdocs
test -e $htdocs/cache || mkdir $htdocs/cache
test -e $htdocs/logs || mkdir $htdocs/logs

test -e $htdocs/drupal/sites/default/files/config/sync || mkdir -p $htdocs/drupal/sites/default/files/config/sync

# create log file
touch $htdocs/logs/error.log

# change directory permissions
chmod 775 $htdocs/cache -R
chmod 775 $htdocs/logs -R

chmod 775 $htdocs/drupal
chmod 775 $htdocs/drupal/sites
chmod 775 $htdocs/drupal/sites/default
chmod 775 $htdocs/drupal/sites/default/files
chmod 775 $htdocs/drupal/sites/default/files/config/sync
