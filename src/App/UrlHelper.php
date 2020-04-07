<?php

namespace App;

use Laminas\Diactoros\ServerRequest;
use League\Route\Router;

class UrlHelper
{
    public $raw = true;
    
    protected $router;
    protected $request;

    public function __construct(Router $router)
    {
//        TODO:
//        не проверены группы
//        нет кэширования
//        не реализованы суффиксы: *.html
        
        $this->router = $router;
    }
    
    public function setRequest($request)
    {
        $this->request = $request;
    }
    
    public function getCurrent()
    {
        if (!$this->request) {
            return null;
        }
        return $this->request->getUri()->getPath();
    }

    public function createUrl($name, array $data = [])
    {
        $url = $this->router->generateUri($name, $data);
        return $url;
    }

    public function createAbsoluteUrl($name, array $data = [])
    {
        if (!$this->request) {
            return null;
        }
        $scheme = $this->request->getUri()->getScheme();
        $host = $this->request->getUri()->getHost();
        $basePath = $scheme . '://' . $host;
        $url = $basePath . $this->createUrl($name, $data);
        return $url;
    }
}
