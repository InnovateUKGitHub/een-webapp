#!/bin/bash
####################################
#
# Apache virtualhosts, test and enable them
#
####################################

#exit on error
set -e 

cd $htdocs/drupal

if [ -f $htdocs/drupal/sites/default/settings.php ]; then

    rm -rf $htdocs/drupal/sites/default/settings.php
    $htdocs/db/setup.sh

fi

echo "Installing drupal default site"
../bin/drush si --db-url=mysql://$dbuser:$dbpass@$dbhost/$dbname -y

echo "Clearing drupal cache"
../bin/drush cr

echo "Deleting shortcut_set due to drupal bug"
../bin/drush ev "\Drupal::entityManager()->getStorage('shortcut_set')->load('default')->delete();"

../bin/drush cset system.site uuid $dbsiteuuid -y

echo "Importing drupal configuration"
../bin/drush config-import deploy -y

echo "Enabling correct module"
../bin/drush en opportunities -y
../bin/drush en elastic_search -y
