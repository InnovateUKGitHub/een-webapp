#!/bin/bash
####################################
#
# Drupal, installation and configuration
#
####################################

#exit on error
set -e 

cd $htdocs/drupal


configServiceSource=$htdocs/drupal/modules/custom/elastic_search/config/install/elastic_search.default.settings.yml
configServiceDestination=$htdocs/drupal/modules/custom/elastic_search/config/install/elastic_search.settings.yml

cp $configServiceSource $configServiceDestination
sed -i -e "s/HOSTNAME_SERVICE/$hostnameapi/g" $configServiceDestination

$htdocs/db/setup.sh

configDrupalSource=$htdocs/drupal/sites/default/default.settings.php
configDrupalDestination=$htdocs/drupal/sites/default/settings.php


cp $configDrupalSource $configDrupalDestination
chmod 775 $configDrupalDestination

# TODO Generate the hash
cat $htdocs/build/templates/drupal/settings.$APPLICATION_ENV.php >> $configDrupalDestination
sed -i -e "s/HOSTNAME/$hostname/g" $configDrupalDestination
sed -i -e "s/DB_NAME/$dbname/g" $configDrupalDestination
sed -i -e "s/DB_USERNAME/$dbuser/g" $configDrupalDestination
sed -i -e "s/DB_PASSWORD/$dbpass/g" $configDrupalDestination
sed -i -e "s/DB_HOST/$dbhost/g" $configDrupalDestination

echo "Clearing drupal cache"
$htdocs/bin/drush cr

echo "Installing modules"
$htdocs/bin/drush en opportunities elastic_search -y
