<?php

namespace ProVallo\Components\Plugin;

use ProVallo\Components\Database\MigrationRunner;
use ProVallo\Core;

abstract class Bootstrap
{
    
    /**
     * @var Instance
     */
    protected $instance;
    
    /**
     * @var string
     */
    protected $relativePath;
    
    final public function __construct ()
    {
    
    }
    
    public function setInstance ($instance)
    {
        $this->instance = $instance;
    }
    
    public function getInstance ()
    {
        return $this->instance;
    }
    
    public function getPath ()
    {
        return $this->instance->getPath();
    }
    
    public function getRelativePath ()
    {
        if ($this->relativePath === null)
        {
            $this->relativePath = substr($this->getPath(), strlen(Core::path()));
        }
        
        return $this->relativePath;
    }
    
    public function getInfo ()
    {
        return $this->instance->getInfo();
    }
    
    public function install ()
    {
        return true;
    }
    
    public function uninstall ()
    {
        return true;
    }
    
    public function update ($previousVersion)
    {
        return true;
    }
    
    abstract public function execute ();
    
    protected function registerController ($moduleName, $controllerName, $registerRoutes = true)
    {
        $controllerClass = 'ProVallo\\Controllers\\' . $moduleName . '\\' . $controllerName . 'Controller';
        $eventName       = 'controller.resolve.' . strtolower($moduleName) . '.' . $controllerName;
        $controllerFile  = $this->getPath() . 'Controllers/' . $moduleName . '/' . $controllerName . 'Controller.php';
        
        $this->subscribeEvent($eventName, function () use ($controllerFile, $controllerClass) {
            if (!class_exists($controllerClass))
            {
                require_once $controllerFile;
            }
        });
        
        if ($registerRoutes)
        {
            $pattern = '/' . strtolower($moduleName) . '/' . strtolower($controllerName) . '[/{action}]';
            $target  = strtolower($moduleName) . ':' . $controllerName . ':{action}';
            
            Core::instance()->any($pattern, $target);
        }
    }
    
    protected function subscribeEvent ($eventName, $handler)
    {
        if (!is_callable($handler))
        {
            if (is_string($handler) && method_exists($this, $handler))
            {
                $handler = [
                    $this,
                    $handler,
                ];
            }
            else
            {
                throw new \Exception('Invalid event handler.');
            }
        }
        
        Core::events()->subscribe($eventName, $handler);
    }
    
    /**
     * Run migrations for the current plugin.
     */
    protected function installDB ()
    {
        $pluginID  = $this->getInstance()->getModel()->id;
        $directory = path($this->getPath(), 'Migrations');
        $namespace = 'ProVallo\\Plugins\\' . $this->getInstance()->getModel()->name . '\\Migrations\\';
        
        $runner = new MigrationRunner($pluginID, $directory);
        $runner->setNamespace($namespace);
        
        $runner->run();
    }
    
}