#!/bin/bash
####################################
#
# Drupal, installation and configuration
#
####################################

#exit on error
set -e

echo "Copying drupal configuration files..."

#write password hash to config file.
echo hash_key: $passwordhash > $htdocs/db/config/een_salesforce.settings.yml

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



canonicalSettings=$htdocs/db/config/metatag.metatag_defaults.global.yml
cp $htdocs/build/templates/drupal/metatag.metatag_defaults.global.yml $canonicalSettings
sed -i -e "s/SITEHOSTNAME/$sitehostname/g" "$canonicalSettings"
sed -i -e "s/SITEPROTO/$siteproto/g" "$canonicalSettings"


# s3 settings
if [ -f "$htdocs/build/templates/drupal/s3fs.$APPLICATION_ENV.settings.yml" ]; then
    cp $htdocs/build/templates/drupal/s3fs.$APPLICATION_ENV.settings.yml $htdocs/db/config/s3fs.settings.yml
fi

# elasticsearch config

if [ -f "$htdocs/build/templates/drupal/elasticsearch_connector.$APPLICATION_ENV.cluster.een_cluster.yml" ]; then
    cp $htdocs/build/templates/drupal/elasticsearch_connector.$APPLICATION_ENV.cluster.een_cluster.yml $htdocs/db/config/elasticsearch_connector.cluster.een_cluster.yml
fi



if [ "$elasticsearchhostname" = "" ]; then
	echo 'Elasticsearch hostname not set.. check settings'
else 
    echo "\$settings['create_elasticsearch_indices_from_drupal'] = TRUE;" >> $workspace/drupal/sites/default/settings.php
fi

if [ "$memcachehost" = "" ]; then
    echo 'Memcache hostname not set.. skipping'
else 

cat <<EOT >> $drupalSettings

\$settings['memcache']['servers'] = ['$memcachehost' => 'default'];
\$settings['memcache']['bins'] = ['default' => 'default'];
\$settings['memcache']['key_prefix'] = 'eendrupal';
\$settings['cache']['default'] = 'cache.backend.memcache';
\$settings['cache']['bins']['render'] = 'cache.backend.memcache';
\$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.memcache';

EOT

fi

# Robots.txt
if [ "$APPLICATION_ENV" = "production_een_aws" ]; then 
	echo "Enabling robots.txt"
	cp $htdocs/drupal/robots-allow.txt $htdocs/drupal/robots.txt
else
	echo "Disabling robots.txt"
	cp $htdocs/drupal/robots-block.txt $htdocs/drupal/robots.txt
fi


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
