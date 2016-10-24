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

cp $htdocs/build/templates/drupal/elastic_search.default.settings.yml $serviceRoot/elastic_search.settings.yml
sed -i -e "s/HOSTNAME_SERVICE/$hostnameapi/g" $serviceRoot/elastic_search.settings.yml

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

test -e $htdocs/db/update || forceCompile=true
databaseChanges=`diff --exclude="*.git*" -r $htdocs/db/update $htdocs/db/init | grep "Common subdirectories" -v || true`

if [ ! -z "$databaseChanges" ] || [ ! -z "$forceCompile" ];then
    echo "db/update has changed:"
    echo "updating database"

    $htdocs/db/setup.sh
    # Uninstall and reinstall module due to configuration on install
    $htdocs/bin/drush pm-uninstall  elastic_search opportunities events -y
    $htdocs/bin/drush pm-enable     opportunities events elastic_search -y
    $htdocs/bin/drush cr

    mkdir -p $htdocs/db/update
    cp -r $htdocs/db/init/* $htdocs/db/update
else
    echo "db/init has not changed, clearing the cache"
    $htdocs/bin/drush cr
fi

