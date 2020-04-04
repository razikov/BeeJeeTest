<?php

namespace App;

use League\Route\Strategy\ApplicationStrategy as DefaultApplicationStrategy;
use League\Route\Route;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApplicationStrategy extends DefaultApplicationStrategy
{
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());

        foreach ($route->getVars() as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }
        
        $response = $controller($request);
        $response = $this->applyDefaultResponseHeaders($response);

        return $response;
    }
}
