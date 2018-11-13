<?php

namespace ProVallo\Models\Schema;

use Favez\Mvc\ORM\Entity;

class Schema extends Entity
{
    
    const SOURCE = 'schema_version';
    
    public $id;
    
    public $version;
    
    public $startDate;
    
    public $endDate;
    
    public $error;
    
    public $pluginID;
    
}