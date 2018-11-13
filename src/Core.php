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
    
    /**
     * Set default paths which are generally required
     *
     * Core::instance()->setPaths([
     *     'app_path'         => 'absolute path',
     *     'app_cache_path'   => 'relative path to app_path',
     *     'theme_path'       => 'relative path to app_path',
     *     'theme_cache_path' => 'relative path to app_path',
     *     'plugin_path'      => 'relative path to app_path'
     * ])
     *
     * @param array $paths
     *
     * @return self
     */
    public function setPaths ($paths)
    {
        foreach ($paths as $key => $path)
        {
            switch ($key)
            {
                case 'app_path':
                    $app = self::config()->get('app');
                    $app['path'] = $path;
                    
                    self::config()->set('app');
                break;
                case 'app_cache_path':
                    $app = self::config()->get('app');
                    $app['cache_path'] = $path;
    
                    self::config()->set('app');
                break;
                case 'theme_path':
                    $view = self::config()->get('view');
                    $view['theme_path'] = $path;
                    
                    self::config()->set('view', $view);
                break;
                case 'theme_cache_path':
                    $view = self::config()->get('view');
                    $view['theme_path'] = $path;
        
                    self::config()->set('cache_path', $view);
                break;
                case 'plugin_path':
                    $plugin = self::config()->get('plugin');
                    $plugin['path'] = $path;
                    
                    self::config()->set('plugin', $plugin);
                break;
            }
        }
        
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