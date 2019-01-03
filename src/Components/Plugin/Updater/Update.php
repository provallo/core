<?php

namespace ProVallo\Components\Plugin\Updater;

use Favez\Mvc\DI\Injectable;

class Update
{
    use Injectable;
    
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
    
    public function download ()
    {
        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(time() . $this->version . $this->filename);
        $contents = file_get_contents($this->filename);
        
        file_put_contents($filename, $contents);
        
        return $filename;
    }
    
    public function extract ($filename)
    {
        $zip = new \ZipArchive();
        
        if (!$zip->open($filename))
        {
            return false;
        }
        
        if ($zip->extractTo($this->plugin->getPath()))
        {
            $zip->close();
            
            return true;
        }
        
        return false;
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