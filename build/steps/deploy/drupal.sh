#!/bin/bash
####################################
#
# Drupal, installation and configuration
#
####################################

#exit on error
set -e 

cd $htdocs/drupal

$htdocs/db/setup.sh

echo "Clearing drupal cache"
$htdocs/bin/drush cr
