#!/bin/bash
####################################
#
# Drupal, installation and configuration
#
####################################

#exit on error
set -e

cd $htdocs/drupal

$htdocs/bin/drush config-import -y
$htdocs/bin/drush cr