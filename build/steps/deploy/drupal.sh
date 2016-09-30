#!/bin/bash
####################################
#
# Drupal, installation and configuration
#
####################################

#exit on error
set -e 

cd $htdocs/drupal

echo "Coping drupal configuration files"
serviceRoot=$htdocs/drupal/modules/custom/elastic_search/config/install
drupalRoot=$htdocs/drupal/sites
drupalSettings=$htdocs/drupal/sites/default/settings.php

cp $serviceRoot/elastic_search.default.settings.yml $serviceRoot/elastic_search.settings.yml
sed -i -e "s/HOSTNAME_SERVICE/$hostnameapi/g" $serviceRoot/elastic_search.settings.yml

$htdocs/db/setup.sh

cp $drupalRoot/default/default.settings.php $drupalSettings
cp $drupalRoot/example.settings.local.php $drupalRoot/default/settings.local.php
chmod 755 $drupalRoot/default/settings.php
chmod 755 $drupalRoot/default/settings.local.php

# TODO Generate the hash
cat $htdocs/build/templates/drupal/settings.$APPLICATION_ENV.php >> $drupalSettings

sed -i -e "s/HOSTNAME/$hostname/g" $drupalSettings
sed -i -e "s/DB_NAME/$dbname/g" $drupalSettings
sed -i -e "s/DB_USERNAME/$dbuser/g" $drupalSettings
sed -i -e "s/DB_PASSWORD/$dbpass/g" $drupalSettings
sed -i -e "s/DB_HOST/$dbhost/g" $drupalSettings

echo "Clearing drupal cache"
$htdocs/bin/drush cr

echo "Installing modules"
$htdocs/bin/drush en opportunities events elastic_search twig_extensions ie8 -y
