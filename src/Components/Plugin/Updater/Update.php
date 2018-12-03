<?php

namespace ProVallo\Components\Plugin\Updater;

class Update
{
    
    /**
     * @var string
     */
    protected $version;
    
    /**
     * @var string
     */
    protected $released;
    
    /**
     * @var integer
     */
    protected $size;
    
    /**
     * @var string
     */
    protected $releaseNotes;
    
    /**
     * @var string
     */
    protected $filename;
    
    /**
     * @var \ProVallo\Components\Plugin\Instance
     */
    protected $plugin;
 
    public function __construct ($data, $plugin)
    {
        $this->version      = $data['version'];
        $this->released     = $data['released'];
        $this->size         = $data['size'];
        $this->releaseNotes = $data['releaseNotes'];
        $this->filename     = $data['filename'];
        $this->plugin       = $plugin;
    }
    
    /**
     * @return string
     */
    public function getVersion ()
    {
        return $this->version;
    }
    
    /**
     * @return string
     */
    public function getReleased ()
    {
        return $this->released;
    }
    
    /**
     * @return integer
     */
    public function getSize ()
    {
        return $this->size;
    }
    
    /**
     * @return string
     */
    public function getReleaseNotes ()
    {
        return $this->releaseNotes;
    }
    
    /**
     * @return string
     */
    public function getFilename ()
    {
        return $this->filename;
    }
    
    /**
     * @return \ProVallo\Components\Plugin\Instance
     */
    public function getPlugin ()
    {
        return $this->plugin;
    }
    
}