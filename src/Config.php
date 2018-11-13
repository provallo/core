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

    public function extendFrom ($filename)
    {
        if (is_file($filename))
        {
            $data = require $filename;
            $this->data = array_replace_recursive($this->data, $data);
        }
    }
    
    public function toArray ()
    {
        return $this->data;
    }
    
}