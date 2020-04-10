<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteResult implements MiddlewareInterface
{
    private $success;
    private $route = null;
    private $matchedParams = [];
    private $allowedMethods = [];
    
    public function __constructor($match)
    {
        $this->match = $match;
    }
    
    public function setFound($match)
    {
        $this->success = true;
        $this->route = $match[1];
        $this->matchedParams = $match[2];
        $this->allowedMethods = [$this->route->getMethod()];
    }
    
    public function setNotFound($methods)
    {
        $this->success = false;
        $this->allowedMethods = $methods;
    }
    
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
    
    public function getMatchedParams(): array
    {
        return $this->matchedParams;
    }
    
    public function getMatchedRoute()
    {
        return !$this->isSuccess() ? false : $this->route;
    }
    
    public function getMatchedRouteName()
    {
        return $this->route->getName();
    }
    
    public function isSuccess(): bool
    {
        return $this->success;
    }
    
    public function isFailed(): bool
    {
        return !$this->isSuccess();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->route->process($request, $handler);
    }
}
