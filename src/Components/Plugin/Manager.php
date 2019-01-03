<?php

namespace ProVallo\Components\Plugin;

use Exception;
use Favez\Mvc\DI\Injectable;
use ProVallo\Models\Plugin\Plugin;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Manager
{
    use Injectable;
    
    /**
     * @var \Favez\ORM\App
     */
    private $db;
    
    /**
     * Holds the instance of every plugin.
     *
     * @var Instance[]
     */
    private $instances;

    /**
     * The plugin dependency manager.
     *
     * @var \ProVallo\Components\Plugin\DependencyManager
     */
    private $dependencyManager;
    
    /**
     * The plugin updater
     *
     * @var Updater
     */
    private $updater;
    
    public function __construct (\Favez\ORM\App $db)
    {
        $this->db                = $db;
        $this->instances         = [];
        $this->dependencyManager = new DependencyManager();
        $this->updater           = new Updater();
    }
    
    /**
     * @return Instance[]
     * @throws \Exception
     */
    public function list ()
    {
        $plugins   = $this->db->getRepository(Plugin::class)->findAll();
        $instances = [];
        
        /** @var Plugin $plugin */
        foreach ($plugins as $plugin)
        {
            $instances[] = $this->loadInstance($plugin->name, $plugin);
        }
        
        return $instances;
    }
    
    /**
     * Synchronize plugins from database with filesystem and the other way
     * around.
     *
     * @throws \Exception
     */
    public function synchronize ()
    {
        // Loop through all plugins in database and disable a plugin when it does not exists in filesystem.
        $plugins = Plugin::repository()->findAll();
        
        /** @var Plugin $plugin */
        foreach ($plugins as $plugin)
        {
            if (!$this->exists($plugin->name))
            {
                $plugin->remove();
            }
        }
        
        // Loop through all plugins in filesystem and write/update it in database.
        $plugins = [];
        
        $iterator = new \IteratorIterator(new \DirectoryIterator($this->getPluginDirectory()));

        /**
         * @var integer            $key
         * @var \DirectoryIterator $file
         */
        foreach ($iterator as $key => $file)
        {
            if ($file->isDot() || $file->isFile())
            {
                continue; // Skip "." and ".." and normal files.
            }

            $name  = $file->getBasename();
            $model = $this->getModel($name);

            if (!($model instanceof Plugin))
            {
                $model            = Plugin::create();
                $model->active    = 0;
                $model->name      = $name;
                $model->created   = date('Y-m-d H:i:s');
                $model->changed   = date('Y-m-d H:i:s');
            }

            $instance = $this->loadInstance($model->name, $model);
            $info     = $instance->getInfo();

            $model->label       = $info->getLabel();
            $model->description = $info->getDescription();
            $model->author      = $info->getAuthor();
            $model->website     = $info->getWebsite();
            $model->email       = $info->getEmail();

            // Do not update the model version every time for future updates.
            // Update plugin version when plugin is not installed.
            if (empty($model->version) || $model->active == 0)
            {
                $model->version = $info->getVersion();
            }

            $model->save();

            $this->dependencyManager->update($instance);
        }

        return $plugins;
    }
    
    /**
     * Checks whether the plugin exists on filesystem or not.
     *
     * @param string $name
     * @param string $className
     * @param string $path
     *
     * @return bool
     * @throws \Exception
     */
    public function exists ($name, &$className = null, &$path = null)
    {
        $namespace = 'ProVallo\\Plugins\\' . $name . '\\';
        $className = $namespace . 'Bootstrap';
        $path      = $this->getPluginDirectory() . $name . '/';
        
        self::app()->loader()->setPsr4($namespace, $path);

        return class_exists($className);
    }
    
    /**
     * Creates and returns a plugin instance.
     *
     * @param string $namespace
     * @param string $name
     * @param Plugin $model
     *
     * @throws \Exception
     * @return Instance
     */
    public function loadInstance ($name, $model = null)
    {
        if (!isset($this->instances[$name]))
        {
            if (!$this->exists($name, $className, $path))
            {
                throw new Exception('Trying to access unknown plugin: ' . $name);
            }
            
            $bootstrap = new $className();
            $instance  = new Instance($bootstrap, $path);
            
            if ($model instanceof Plugin)
            {
                $instance->setModel($model);
            }
            
            $this->instances[$name] = $instance;
        }
        
        return $this->instances[$name];
    }
    
    /**
     * The preferred plugin bootstrap class name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getClassName ($name)
    {
        return 'ProVallo\\Plugins\\' . $name;
    }
    
    /**
     * Returns the actually plugin directory for the given namespace.
     *
     * @return string
     * @throws \Exception Usually when the namespace does not exists.
     */
    public function getPluginDirectory ()
    {
        $directory = self::config('app.path') . self::config('plugin.path');
        
        if (!is_dir($directory))
        {
            throw new \Exception('Plugin directory not found!');
        }
        
        return $directory;
    }
    
    /**
     * Execute all enabled plugins.
     */
    public function execute ()
    {
        $repository = $this->db->getRepository(Plugin::class);
        $plugins    = $repository->findBy(['active' => true]);
        
        /** @var Plugin $plugin */
        foreach ($plugins as $plugin)
        {
            // If the plugin does not exists in filesystem disable it and do not try to execute it to prevent errors.
            if (!$this->exists($plugin->name))
            {
                $plugin->active = 0;
                $plugin->save();
                continue;
            }
            
            $instance = $this->loadInstance($plugin->name, $plugin);
            $instance->getBootstrap()->execute();
        }
    }
    
    /**
     * Install a plugin by its name.
     *
     * @param string $name
     *
     * @return array|bool
     */
    public function install ($name)
    {
        try
        {
            $model    = $this->getModel($name);
            $instance = $this->loadInstance($model->name, $model);
            
            if ((int) $model->active === 1)
            {
                throw new Exception('Plugin already installed!');
            }
            
            // Check if the required plugins within the required version are installed. Throws an exception if not.
            $this->dependencyManager->checkRequirements($instance);
            
            self::events()->publish('core.plugin.pre_install', ['instance' => $instance]);
            
            $result = $instance->getBootstrap()->install();
            
            self::events()->publish('core.plugin.post_install', ['instance' => $instance]);
            
            if (isSuccess($result))
            {
                $model->active = 1;
                $model->save();
            }
            
            return $result;
        }
        catch (\Exception $ex)
        {
            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }
    
    /**
     * Uninstall a plugin by its name.
     *
     * @param string $name
     *
     * @return array|bool
     */
    public function uninstall ($name)
    {
        try
        {
            $model    = $this->getModel($name);
            $instance = $this->loadInstance($model->name, $model);
            
            if ((int) $model->active === 0)
            {
                throw new Exception('Plugin already uninstalled.');
            }
            
            if ($dependencies = $this->dependencyManager->getDependencies($name))
            {
                throw new Exception('Unable to uninstall because of the following depended plugins: ' . implode(', ', $dependencies));
            }
            
            self::events()->publish('core.plugin.pre_uninstall', ['instance' => $instance]);
            
            $result = $instance->getBootstrap()->uninstall();
            
            self::events()->publish('core.plugin.post_uninstall', ['instance' => $instance]);
            
            if (isSuccess($result))
            {
                $model->active = 0;
                $model->save();
            }
            
            return $result;
        }
        catch (\Exception $ex)
        {
            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }
    
    public function update ($name)
    {
        try
        {
            $model    = $this->getModel($name);
            $instance = $this->loadInstance($model->name, $model);
            
            if ((int) $model->active === 0)
            {
                throw new Exception('The plugin must be installed to be updated.');
            }
            
            if (version_compare($model->version, $instance->getInfo()->getVersion(), '>='))
            {
                throw new Exception('The plugin is already up-to-date.', 304);
            }
            
            self::events()->publish('core.plugin.pre_update', ['instance' => $instance]);
            
            $result = $instance->getBootstrap()->update($model->version);
            
            self::events()->publish('core.plugin.post_update', ['instance' => $instance]);
            
            if (isSuccess($result))
            {
                $model->version = $instance->getInfo()->getVersion();
                $model->save();
            }
            
            return $result;
        }
        catch (\Exception $ex)
        {
            return [
                'success' => false,
                'message' => $ex->getMessage(),
                'code'    => $ex->getCode()
            ];
        }
    }
    
    /**
     * Returns the plugin model identified by its unique name.
     *
     * @param string $name
     *
     * @return \Favez\ORM\Entity\Entity|Plugin
     *
     * @throws Exception
     */
    public function getModel ($name)
    {
        return Plugin::repository()->findOneBy(['name' => $name]);
    }
    
    /**
     * Returns the plugin bootstrap by name.
     *
     * @param string $name
     *
     * @return Bootstrap
     *
     * @throws Exception
     */
    public function get ($name)
    {
        if (!isset($this->instances[$name]))
        {
            $model = $this->getModel($name);
            
            $this->loadInstance($model->name, $model);
        }
        
        return $this->instances[$name]->getBootstrap();
    }
    
    /**
     * Removes a plugin from filesystem.
     *
     * @param string $name
     *
     * @return array|boolean
     */
    public function remove ($name)
    {
        try
        {
            $model    = $this->getModel($name);
            $instance = $this->loadInstance($model->name, $model);
            $files    = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($instance->getPath(), RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            
            /**
             * @var string       $name
             * @var \SplFileInfo $file
             */
            foreach ($files as $name => $file)
            {
                if ($file->isFile())
                {
                    unlink($name);
                }
                else
                {
                    if ($file->isDir())
                    {
                        rmdir($name);
                    }
                }
            }
            
            rmdir($instance->getPath());
            
            return true;
        }
        catch (\Exception $ex)
        {
            return [
                'success' => false,
                'message' => $ex->getMessage(),
            ];
        }
    }
    
    public function getUpdater ()
    {
        return $this->updater;
    }
    
    /**
     * Method to reset a cached plugin instance.
     *
     * @param string $name
     */
    public function resetInstance ($name)
    {
        if (isset($this->instances[$name]))
        {
            unset ($this->instances[$name]);
        }
    }
    
}