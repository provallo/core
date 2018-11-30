<?php

namespace ProVallo\Components\Plugin;

use Favez\Mvc\DI\Injectable;

class Updater
{
    use Injectable;

    public function checkForUpdate(Instance $plugin)
    {
        $url = $this->buildRequestUri($plugin);
        $data = file_get_contents($url);
        $data = json_decode($data, true);
        
        if ($data['isNewer'] === true)
        {
        
        }
        
        return null;
    }
    
    protected function buildRequestUri (Instance $plugin)
    {
        $baseUrl = self::config('update_api.endpoint');
        $baseUrl .= 'api/v1/updates';
        
        // Add several params
        $baseUrl .= '?id=' . $plugin->getInfo()->toArray()['name'];
        $baseUrl .= '&channel=stable';
        $baseUrl .= '&platform=provallo-plugin';
        $baseUrl .= '&version=' . $plugin->getInfo()->getVersion();
        
        return $baseUrl;
    }

}