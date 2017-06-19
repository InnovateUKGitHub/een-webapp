#!/bin/bash
####################################
#
# Drupal, installation and configuration
#
####################################

#exit on error
set -e

echo "Copying drupal configuration files..."

drupalSettings=$htdocs/drupal/sites/default/settings.php

cp $htdocs/drupal/sites/default/default.services.yml $htdocs/drupal/sites/default/services.yml
cp $htdocs/drupal/sites/default/default.settings.php $drupalSettings

cp $htdocs/drupal/sites/example.settings.local.php $htdocs/drupal/sites/default/settings.local.php

cat $htdocs/build/templates/drupal/settings.php >> $drupalSettings

sed -i -e "s/HOSTNAMEADMIN/$hostnameadmin/g" $drupalSettings
sed -i -e "s/HOSTNAME/$hostname/g" $drupalSettings
sed -i -e "s/DB_NAME/$dbname/g" $drupalSettings
sed -i -e "s/DB_USERNAME/$dbuser/g" $drupalSettings
sed -i -e "s/DB_PASSWORD/$dbpass/g" $drupalSettings
sed -i -e "s/DB_HOST/$dbhost/g" $drupalSettings

elasticSearchSettings=$htdocs/db/config/service_connection.settings.yml

cp $htdocs/build/templates/drupal/service_connection.settings.yml $elasticSearchSettings
sed -i -e "s/ELASTICSEARCHHOSTNAME/$servicehostname/g" "$elasticSearchSettings"
sed -i -e "s/ELASTICSEARCHPROTO/$serviceproto/g" "$elasticSearchSettings"

#reverse proxy settings

if [ "$reverse_proxy" = "true" ]; then

cat <<EOT >> $drupalSettings

\$settings['reverse_proxy'] = TRUE;

\$settings['reverse_proxy_addresses'] = [
    \$_SERVER['REMOTE_ADDR']
];

EOT

fi

#chmod 544 $htdocs/drupal/sites/default/settings.php
#chmod 544 $htdocs/drupal/sites/default/settings.local.php
#chmod 544 $htdocs/drupal/sites/default/services.yml