$settings['hash_salt'] = 'uDEiOdg2rx7LaJG9qpaX2zs0NIOxtXfF55eFuPHJdFJak_SPTQIANE4fJyN36ElDRdlZoqQORQ';

$settings['trusted_host_patterns'] = [
    '^HOSTNAME$',
    '^www.HOSTNAME$',
    '^HOSTNAMEADMIN$',
    '^www.enterprise-europe.co.uk$',
    '^enterprise-europe.co.uk$'
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

$config_directories['sync'] = '../db/config';
