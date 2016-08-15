
$settings['install_profile'] = 'standard';

$settings['hash_salt'] = 'HASH_SALT';

$settings['trusted_host_patterns'] = [
'^127.0.0.1$',
'^localhost$',
'^HOSTNAME$'
];

$databases['default']['default'] = array (
'database' => 'DB_NAME',
'username' => 'DB_USERNAME',
'password' => 'DB_PASSWORD',
'prefix' => '',
'host' => 'DB_HOST',
'port' => '',
'namespace' => 'Drupal\Core\Database\Driver\mysql',
'driver' => 'mysql',
);

$config_directories['sync'] = 'sites/default/files/config/sync';
