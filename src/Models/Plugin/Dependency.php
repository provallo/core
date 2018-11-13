<?php

namespace ProVallo\Models\Plugin;

use Favez\Mvc\ORM\Entity;

class Dependency extends Entity
{
    
    const SOURCE = 'plugin_dependency';
    
    public $id;
    
    public $pluginID;
    
    public $name;
    
    public $version;
    
    public function initialize ()
    {
        $this->belongsTo(Plugin::class, 'pluginID', 'id')
            ->setName('plugins');
    }
    
}