<?php

namespace App;

use App\Router;
use DomainException;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use League\Uri\UriModifier;
use League\Uri\UriString;

use function array_merge;
use function sprintf;

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
    
    private function getCurrentUri()
    {
        if (!$this->request) {
            throw DomainException("В компонент не передан экземпляр ServerRequest");
        }
        return $this->request->getUri();
    }

    public function createUrl($name, array $data = [], bool $isAbsolute = false)
    {
        $uri = new Uri($this->router->generateUri($name, $data));
        if ($isAbsolute) {
            $uri = $this->createAbsoluteUrl($uri);
            return $uri->__toString();
        } else {
            $query = !empty($uri->getQuery()) ? "?" . $uri->getQuery() : '';
            $fragment = !empty($uri->getFragment()) ? "#" . $uri->getFragment() : '';
            return sprintf("%s%s%s", $uri->getPath(), $query, $fragment);
        }
    }

    private function createAbsoluteUrl($uri)
    {
        $uriComponent = UriString::parse($uri);
        $currentUri = $this->getCurrentUri();
        $currentUriComponent = UriString::parse($currentUri);
        $newUri = array_merge($currentUriComponent, $uriComponent);
        return $newUri;
    }
    
    public function modifyCurrentUrl(string $query, bool $isAbsolute = false)
    {
        $currentUri = $this->getCurrentUri();
        $uri = UriModifier::mergeQuery($currentUri, $query);
        if ($isAbsolute) {
            return $uri->__toString();
        } else {
            $query = $uri->getQuery() ? "?" . $uri->getQuery() : '';
            $fragment = $uri->getFragment() ? "#" . $uri->getFragment() : '';
            return sprintf("%s%s%s", $uri->getPath(), $query, $fragment);
        }
    }
}
