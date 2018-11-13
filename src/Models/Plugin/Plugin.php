<?php

namespace ProVallo\Models\Plugin;

use Favez\Mvc\ORM\Entity;

class Plugin extends Entity
{
    
    const SOURCE = 'plugin';
    
    public $id;
    
    public $active;
    
    public $namespace;
    
    public $name;
    
    public $label;
    
    public $description;
    
    public $version;
    
    public $author;
    
    public $email;
    
    public $website;
    
    public $created;
    
    public $changed;
    
    public function initialize ()
    {
        $this->hasMany(Dependency::class, 'pluginID', 'id')
            ->setName('dependencies');
    }
    
}