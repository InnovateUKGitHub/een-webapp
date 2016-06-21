#!/bin/bash -e

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
source "${SCRIPT_DIR}/mysql.environment"

rm -rf drupal/sites/default/settings.php
cd drupal && ../bin/drush si --db-url=mysql://${MYSQL_APP_USER}:${MYSQL_APP_PASSWORD}@${MYSQL_HOST}/${DB} -y

config="
// Default config directories provided by the installer.
\$config_directories['active'] = '../config/active';
\$config_directories['staging'] = '../config/staging';

// Config directory used for deployments.
\$config_directories['deploy'] = '../config/deploy';"

echo "$config" >> ../drupal/sites/default/settings.php
