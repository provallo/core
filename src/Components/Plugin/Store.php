<?php

namespace ProVallo\Components\Plugin;

use Favez\Mvc\DI\Injectable;

class Store
{
    use Injectable;
    
    public function list ()
    {
        return $this->search();
    }
    
    public function search ($phrase = '')
    {
        $params = [
            'platform' => 'provallo-plugin',
            'channel' => 'stable'
        ];
        
        if (!empty($phrase))
        {
            $params['search'] = $phrase;
        }
        
        $url = $this->getURL('search', $params);
        $data = $this->fetch($url);
        
        return $data['data'];
    }
    
    public function getAvailableVersions ($name)
    {
        $url = $this->getURL('updates', [
            'id'       => $name,
            'platform' => 'provallo-plugin',
            'channel'  => 'stable',
            'mode'     => 2
        ]);
    
        $data = $this->fetch($url);
    
        return $data['data'];
    }
    
    private function fetch ($url)
    {
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        
        return $data;
    }
    
    private function getURL ($path, $params)
    {
        $baseUrl = self::config('update_api.endpoint');
        $baseUrl .= '/api/v1/' . $path;
        
        $i = 0;
        
        foreach ($params as $key => $value)
        {
            $baseUrl .= ($i === 0 ? '?' : '&') . urlencode($key) . '=' . urlencode($value);
            ++$i;
        }
        
        return $baseUrl;
    }
    
}