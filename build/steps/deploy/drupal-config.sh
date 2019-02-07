#!/bin/bash
####################################
#
# Drupal, installation and configuration
#
####################################

#exit on error
set -e

cd $htdocs/drupal
$htdocs/bin/drush cr

$htdocs/bin/drush config-import -y  --no-halt-on-error

$htdocs/bin/drush cr
$htdocs/bin/drush updb -y
$htdocs/bin/drush entup -y

