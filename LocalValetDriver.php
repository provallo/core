<?php

class LocalValetDriver extends LaravelValetDriver
{

    public function serves($sitePath, $siteName, $uri)
    {
        return true;
    }

    public function frontControllerPath($sitePath, $siteName, $uri)
    {
        $filename = $sitePath . $uri;

        if (is_file($filename))
        {
            return $filename;
        }

        return $sitePath . '/index.php';
    }

    public function isStaticFile($sitePath, $siteName, $uri)
    {
        if (strpos($uri, '.php') !== strlen($uri) - 4 && is_file($sitePath . $uri))
        {
            return $sitePath . $uri;
        }

        return false;
    }

}