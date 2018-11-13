<?php

namespace ProVallo;

class Config
{
    
    /**
     * @var array
     */
    protected $data;
    
    public function loadFrom ($filename)
    {
        $this->data = require $filename;
    }
    
    public function toArray ()
    {
        return $this->data;
    }
    
}