<?php

namespace ProVallo\Models\Schema;

use Favez\Mvc\ORM\Entity;
use Illuminate\Database\Schema\Blueprint;

class Schema extends Entity
{
    
    const SOURCE = 'schema_version';
    
    public $id;
    
    public $version;
    
    public $startDate;
    
    public $endDate;
    
    public $error;
    
    public $pluginID;
    
    public static function createSchema (Blueprint $t)
    {
        $t->increments('id')->unique();
        $t->integer('version')->unique();
        $t->timestamp('startDate');
        $t->timestamp('endDate');
        $t->longText('error');
        $t->integer('pluginID');
    }
    
}