<?php

namespace ProVallo;

use Favez\Mvc\App;
use Favez\Mvc\Middleware\JsonResponseMiddleware;
use ProVallo\Components\Plugin\Manager;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

/**
 * Class Core
 *
 * @package ProVallo
 *
 * @method \ProVallo\Components\HttpCache      httpCache()
 * @method \ProVallo\Components\Plugin\Manager plugins()
 *
 * @method static \ProVallo\Core instance()
 */
class Core extends \Favez\Mvc\App
{
    
    const API_CONSOLE = 'console';
    
    const API_WEB = 'web';
    
    /**
     * @var string
     */
    protected $api;
    
    public function __construct (Config $config = null, $loader = null, $api = self::API_CONSOLE)
    {
        App::$instance = $this;
        $this->api     = $api;
        
        $this->setLoader($loader);

        if ($config === null)
        {
            $config = new Config();
            $config->loadFrom(__DIR__ . '/Config/Default.php');
            $config->extendFrom(self::path() . 'config.php');
        }
        
        parent::__construct($config->toArray());

        $this->registerServices();
        
        if ($this->config('httpCache.enabled') === true)
        {
            $this->httpCache()->register();
        }
    }
    
    public function run ($silent = false)
    {
        $this->executePlugins();
        $this->registerRoutes();
        
        return parent::run($silent);
    }
    
    public function getApi ()
    {
        return $this->api;
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
     * Executes plugin logic (only installed plugins)
     */
    public function executePlugins ()
    {
        self::plugins()->execute();
        
        $this->events()->publish('core.plugin.execute');
    }
    
    /**
     * Registers basic services.
     */
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
    
    /**
     * Registers middleware and custom routes.
     * If no route for "/" is defined a default one will be registered.
     */
    protected function registerRoutes ()
    {
        $this->add(new JsonResponseMiddleware());
    
        Core::instance()->getContainer()['notFoundHandler'] = function() {
            return function (Request $request, Response $response) {
                $html = file_get_contents(__DIR__ . '/Resources/html/index.html');
                $html = str_replace('../public', '/src/Resources/public', $html);
                
                $response->getBody()->write($html);
                
                return $response;
            };
        };
        
        $this->events()->publish('core.route.register');
    }
    
}