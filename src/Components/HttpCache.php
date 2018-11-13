<?php

namespace ProVallo\Components;

use Favez\Mvc\App;
use Slim\Http\Body;
use Slim\Http\Request;
use Slim\Http\Response;

class HttpCache
{
    
    /**
     * @var \Favez\Mvc\App
     */
    protected $app;
    
    /**
     * @var string
     */
    protected $cacheDir;
    
    /**
     * @var string
     */
    protected $cacheKey;
    
    public function __construct (\Favez\Mvc\App $app)
    {
        $this->app      = $app;
        $this->cacheKey = null;
    }
    
    public function register ()
    {
        $httpCache = $this;
        
        $this->app->add(function (Request $request, Response $response, $next) use ($httpCache)
        {
            return $httpCache->afterRequest($request, $response, $next);
        });
        
        $this->beforeRequest($this->app->request(), $this->app->response());
    }
    
    public function setCacheKey ($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }
    
    protected function beforeRequest (Request $request, Response $response)
    {
        $cacheKey = $this->getCacheKey();
        $item     = App::cache()->getItem($cacheKey);
        
        if ($item->isHit())
        {
            $content = $item->get();
            
            foreach ($content['headers'] as $key => $value)
            {
                $response = $response->withHeader($key, $value);
            }
            
            $body = new Body(fopen('php://temp', 'w'));
            $body->write($content['body']);
            
            $response = $response->withBody($body);
            
            $response = $response->withHeader('Age', time() - $item->getCreation()->getTimestamp());
            $response = $response->withHeader('Cache-Control', 'no-cache, private');
            
            $this->app->respond($response);
            die;
        }
    }
    
    protected function afterRequest (Request $request, Response $response, $next)
    {
        /** @var Response $response */
        $response = $next($request, $response);
        
        if ($this->needsCache($request, $response))
        {
            $cacheKey = $this->getCacheKey();
            $item     = App::cache()->getItem($cacheKey);
            $content  = [
                'headers' => $response->getHeaders(),
                'body'    => (string) $response->getBody()
            ];
            
            $item->lock();
            $item->set($content);
            $item->save();
            
            //
            $item = App::cache()->getItem('http_cache/keys/' . $this->cacheKey);
            $keys = [];
            
            if (!$item->isMiss())
            {
                $keys = $item->get();
            }
            
            $keys[] = $cacheKey;
            
            $item->lock();
            $item->set($keys);
            $item->save();
        }
        
        return $response;
    }
    
    protected function getCacheKey ()
    {
        return 'http_cache/items/' . md5((string) App::request()->getUri());
    }
    
    protected function needsCache (Request $request, Response $response)
    {
        if (empty($this->cacheKey))
        {
            return false;
        }
        
        $path = $request->getUri()->getPath();
        
        if (strpos($path, '/api') === 0)
        {
            return false;
        }
        
        return $response->getStatusCode() === 200;
    }
    
    protected function read ($filename, Response $response)
    {
        $content = unserialize(file_get_contents($filename));
        
        foreach ($content['headers'] as $key => $value)
        {
            $response = $response->withHeader($key, $value);
        }
        
        $body = new Body(fopen('php://temp', 'w'));
        $body->write($content['body']);
        
        $response = $response->withBody($body);
        
        return $response;
    }
    
    protected function isFresh ($filename, $maxAge = 3600)
    {
        return is_file($filename)
            ? filectime($filename) <= time() + $maxAge
            : false;
    }
    
}