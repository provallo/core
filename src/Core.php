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
            $config->loadFrom(__DIR__ . '/Config/Default.php');
            $config->extendFrom(self::path() . 'config.php');
        }
        
        parent::__construct($config->toArray());
        $this->setLoader($loader);
        
        $this->registerServices();
        
        if ($this->config('httpCache.enabled') === true)
        {
            $this->httpCache()->register();
        }
    }
    
    public function run ($silent = false)
    {
        $this->executePlugins($this->plugins());
        $this->registerRoutes();
        
        return parent::run($silent);
    }
    
    /**
     * Registers a new controller module.
     *
     * Core::instance()->registerModule('frontend', [
     *     'controller' => [
     *         'namespace'     => 'ProVallo\\Controllers\\Frontend\\',
     *         'class_suffix'  => 'Controller',
     *         'method_suffix' => 'Action'
     *     ]
     * ])
     *
     * @param string $name
     * @param array  $config
     *
     * @return self
     */
    public function registerModule ($name, $config)
    {
        $modules = self::config()->get('modules');
        $modules[$name] = $config;
        
        self::config()->set('modules', $modules);
        
        return $this;
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