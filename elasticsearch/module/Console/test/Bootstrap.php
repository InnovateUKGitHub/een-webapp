<?php

namespace ConsoleTest;

use Zend\Console\Console;
use Zend\ServiceManager\ServiceManager;
use Zend\Test\Util\ModuleLoader;

class Bootstrap
{
    protected $configPath = '/../../../config/application.config.php';

    /** @var Bootstrap */
    protected static $instance;
    /** @var  ServiceManager */
    protected $serviceManager;

    /**
     * Get the singleton instance
     *
     * @return Bootstrap
     */
    protected static function getInstance($isolated = false)
    {
        if (static::$instance === null || $isolated === true) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Return service manager (create if not exists)
     *
     * @param bool|false $isolated  If true, create new instance of service manager (isolated)
     *
     * @return null|ServiceManager
     */
    public static function getServiceManager($isolated = false)
    {
        $instance = self::getInstance($isolated);

        if ($instance->serviceManager === null) {
            $instance->createServiceManager();
        }

        return $instance->serviceManager;
    }

    /**
     * Create service manager
     *
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function createServiceManager()
    {
        Console::overrideIsConsole(false);

        $applicationConfig = $this->getPathToCfg();
        $applicationConfig['module_listener_options']['config_cache_enabled'] = false;

        $this->serviceManager = (new ModuleLoader($applicationConfig))->getServiceManager();
    }

    protected function getPathToCfg()
    {
        return require __DIR__ . $this->configPath;
    }
}