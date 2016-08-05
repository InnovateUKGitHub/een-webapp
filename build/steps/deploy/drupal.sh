#!/bin/bash
####################################
#
# Drupal, installation and configuration
#
####################################

#exit on error
set -e 

cd $htdocs/drupal


configSource=$htdocs/drupal/modules/custom/elastic_search/config/install/elastic_search.default.settings.yml
configDest=$htdocs/drupal/modules/custom/elastic_search/config/install/elastic_search.settings.yml

cp $configSource $configDest
sed -i -e "s/HOSTNAME_SERVICE/$hostnameapi/g" $configDest

$htdocs/db/setup.sh

cp $htdocs/drupal/sites/default/default.settings.php $htdocs/drupal/sites/default/settings.php

# TODO Generate the hash
echo "

\$settings['hash_salt'] = 'HASH_SALT';

\$settings['trusted_host_patterns'] = [
    '^127.0.0.1$',
    '^localhost$',
    '^$hostname$'
];

\$databases['default']['default'] = array (
  'database' => '$dbname',
  'username' => '$dbuser',
  'password' => '$dbpass',
  'prefix' => '',
  'host' => '$dbhost',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

\$config_directories['sync'] = 'sites/default/files/config/sync';

" >> $htdocs/drupal/sites/default/settings.php

echo "Clearing drupal cache"
$htdocs/bin/drush cr
$htdocs/bin/drush cc css-js
