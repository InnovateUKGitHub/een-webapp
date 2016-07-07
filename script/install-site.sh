#!/bin/bash -e

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
source "${SCRIPT_DIR}/mysql.environment"

## Create files directory
mkdir -p drupal/sites/default/files

## Fix rigths due to a bug with vagrant and mac os
sudo chmod 777 drupal drupal/sites drupal/sites/default drupal/sites/default/files

## Delete old settings
sudo rm -rf drupal/sites/default/settings.php
sudo rm -rf drupal/sites/default/files/*

cd drupal && ../bin/drush si --db-url=mysql://${MYSQL_APP_USER}:${MYSQL_APP_PASSWORD}@${MYSQL_HOST}/${DB} -y

sudo chmod 777 sites/default/settings.php
