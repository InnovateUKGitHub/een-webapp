
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

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
$config['system.performance']['css']['preprocess'] = false;
$config['system.performance']['js']['preprocess'] = false;

$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';

$config['system.logging']['error_level'] = 'verbose';
$conf['error_level'] = 2;

error_reporting(-1);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
