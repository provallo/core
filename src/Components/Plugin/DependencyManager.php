<?php

namespace ProVallo\Components\Plugin;

use Exception;
use ProVallo\Core;
use ProVallo\Models\Plugin\Dependency;
use ProVallo\Models\Plugin\Plugin;

class DependencyManager
{
    
    /**
     * Update the plugin dependencies in database.
     *
     * @param \ProVallo\Components\Plugin\Instance $instance
     *
     * @throws \Exception
     */
    public function update (Instance $instance)
    {
        $info  = $instance->getInfo();
        $model = $instance->getModel();
        
        // Loop through all local plugin dependencies and update/write it in database.
        foreach ($info->getRequires() as $name => $version)
        {
            $dependency = Dependency::repository()->findOneBy([
                'pluginID' => $model->id,
                'name'     => $name
            ]);
            
            if (!($dependency instanceof Dependency))
            {
                $dependency           = Dependency::create();
                $dependency->pluginID = $model->id;
                $dependency->name     = $name;
            }
            
            $dependency->version = $version;
            $dependency->save();
        }
        
        // Loop through all plugin dependencies in database and remove it when not exists locally.
        $dependencies = Dependency::repository()->findBy(['pluginID' => $model->id]);
        
        /** @var Dependency $dependency */
        foreach ($dependencies as $dependency)
        {
            foreach ($info->getRequires() as $name => $version)
            {
                if ($name === $dependency->name)
                {
                    continue 2;
                    break;
                }
            }
            
            $dependency->remove();
        }
    }
    
    /**
     * Load the depended and enabled plugins for the given plugin name.
     *
     * @param string $name
     *
     * @return array
     */
    public function getDependencies ($name)
    {
        $dependencies = Core::db()->from('plugin p')
            ->select(null)->select('p.name')
            ->leftJoin('plugin_dependency pd ON pd.pluginID = p.id')
            ->where('p.active = 1')
            ->where('pd.name = ?', $name)
            ->fetchAll();
        
        $result = [];
        
        foreach ($dependencies as $dependency)
        {
            $result[] = $dependency['name'];
        }
        
        return $result;
    }
    
    /**
     * Checks whether the requirements of the plugin are met or not.
     *
     * @param \ProVallo\Components\Plugin\Instance $instance
     *
     * @return bool
     * @throws \Exception
     */
    public function checkRequirements (Instance $instance)
    {
        $requires = $instance->getInfo()->getRequires();
        
        foreach ($requires as $name => $version)
        {
            $plugin = Plugin::repository()->findOneBy([
                'name'   => $name,
                'active' => true
            ]);
            
            if (!($plugin instanceof Plugin))
            {
                throw new Exception('The plugin requires "' . $name . '" to be installed.');
            }
            
            if (!version_compare($version, $plugin->version, '<='))
            {
                throw new Exception('The plugin requires "' . $name . '" to be installed in version ' . $version . ' or higher.');
            }
        }
        
        return true;
    }
    
}