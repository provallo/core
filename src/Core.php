<?php

namespace ProVallo;

use Favez\Mvc\App;
use Favez\Mvc\Middleware\JsonResponseMiddleware;
use ProVallo\Components\Plugin\Manager;

/**
 * Class Core
 *
 * @package ProVallo
 *
 * @method \ProVallo\Components\HttpCache      httpCache()
 * @method \ProVallo\Components\Plugin\Manager plugins()
 */
class Core extends \Favez\Mvc\App
{
    
    public function __construct (Config $config = null)
    {
        App::$instance = $this;
        
        /** @var \Composer\Autoload\ClassLoader $loader */
        $loader = require_once __DIR__ . '/../vendor/autoload.php';
        $this->setLoader($loader);
        
        if ($config === null)
        {
            $config = new Config();
            $config->loadFrom(__DIR__ . '/../config.inc.php');
        }
        
        parent::__construct($config->toArray());
        $this->setLoader($loader);
        
        $this->registerServices();
        
        if ($this->config('httpCache.enabled') === true)
        {
            $this->httpCache()->register();
        }
        
        $this->executePlugins($this->plugins());
        $this->registerRoutes();
    }
    
    protected function registerServices ()
    {
        $container = $this->di();
        
        $container->registerShared('httpCache', function () {
            return new \ProVallo\Components\HttpCache($this);
        });
        
        $container->registerShared('plugins', function () {
            return new \ProVallo\Components\Plugin\Manager($this->models());
        });
    }
    
    protected function executePlugins (Manager $pluginManager)
    {
        $pluginManager->execute();
        
        $this->events()->publish('core.plugin.execute');
    }
    
    protected function registerRoutes ()
    {
        $this->add(new JsonResponseMiddleware());
        
        $this->events()->publish('core.route.register');
    }
    
}