#!/bin/bash
# Add credentials that are setup in build/properties/{environment}.properties
# 
git checkout https://devops.innovateuk.org/code-repository/projects/EEN/repos/een-config /tmp/een-config

cp -pr /tmp/een-config/een-webapp/build $htdocs/build

rm -rf /tmp/een-config

drupalDevSettings=$htdocs/build/templates/drupal/settings.development_vagrant.php
drupalIntegrationSettings=$htdocs/build/templates/drupal/settings.integration.php
initialdatabase=$htdocs/db/init/initial-database.sql

sed -i -e "s/%%HASH_SALT%%/$hashsalt/g" $drupalDevSettings
sed -i -e "s/%%HASH_SALT%%/$hashsalt/g" $drupalIntegrationSettings
sed -i -e "s/%%CRON_DEFAULT_HASH%%/$crondefaulthash/g" $initialdatabase
sed -i -e "s/%%DB_USER_KEY1%%/$dbuserkey1/g" $initialdatabase
sed -i -e "s/%%DB_USER_KEY2%%/$dbuserkey2/g" $initialdatabase
sed -i -e "s/%%DB_USER_KEY3%%/$dbuserkey3/g" $initialdatabase
sed -i -e "s/%%USER_DATA_KEY_FIELD1%%/$userdatakeyfield1/g" $initialdatabase
sed -i -e "s/%%A_USER%%/$auser/g" $initialdatabase

