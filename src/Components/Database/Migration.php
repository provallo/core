<?php

namespace ProVallo\Components\Database;

use ProVallo\Core;

abstract class Migration
{
    
    /**
     * @var \Favez\Mvc\App
     */
    protected $app;
    
    /**
     * @var string[]
     */
    protected $sql = [];
    
    /**
     * @var integer
     */
    protected $version;
    
    public function __construct (Core $app, $version)
    {
        $this->app     = $app;
        $this->version = $version;
    }
    
    abstract public function up ();
    
    abstract public function down ();
    
    public function getSQL ()
    {
        return $this->sql;
    }
    
    protected function addSQL ($sql, $params = [])
    {
        $this->sql[] = [
            $sql,
            $params
        ];
    }
    
    protected function needsRun ()
    {
        $row = $this->app->db()->from('schema_version')->where('version', $this->version)->fetch();
        
        echo $row;
    }
    
}