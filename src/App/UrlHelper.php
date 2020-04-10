<?php

namespace App;

use Laminas\Diactoros\ServerRequest;
use App\Router;

class UrlHelper
{
    public $raw = true;
    
    protected $router;
    protected $request;

    public function __construct(Router $router)
    {
//        TODO:
//        нет кэширования
//        не реализованы суффиксы: *.html
        
        $this->router = $router;
    }
    
    public function setRequest(ServerRequest $request)
    {
        $this->request = $request;
    }
    
    public function getCurrentUri()
    {
        if (!$this->request) {
            return null;
        }
        return $this->request->getUri();
    }

    public function createUrl($name, array $data = [])
    {
        $url = $this->router->generateUri($name, $data);
        return $url;
    }

    public function createAbsoluteUrl($name, array $data = [])
    {
        $request = $this->request;
        if (!$request) {
            return $this->createUrl($name, $data);
        }
        $scheme = $request->getUri()->getScheme();
        $host = $request->getUri()->getHost();
        $basePath = $scheme . '://' . $host;
        $url = $basePath . $this->createUrl($name, $data);
        return $url;
    }
}
