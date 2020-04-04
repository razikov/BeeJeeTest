<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatchRoute implements MiddlewareInterface
{
    private $_container;
    
    public function __construct($container)
    {
        $this->_container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $c = $this->_container;
        $route = $c['router']->getMatcher()->match($request);

        if (!$route) {
            return $handler->handle($request);
        }

        foreach ($route->attributes as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }

        // TODO:
        // 'middleware::class'
        // 'callback($request, $handler=null)'
        // ['controller:class, action_name']
        $getResponse = function($instance) use ($request, $handler)  {
            if ($instance instanceof \Psr\Http\Server\MiddlewareInterface) {
                return $instance->handle($request, $handler);
            } elseif ($instance instanceof App\Actions\RendarableControllerInterface) {
                return $instance->action($request, $handler);
            } elseif (is_callable($instance)) {
                return $instance($request, $handler);
            } else {
                throw new Exception("Не верно задан обработчик маршрута");
            }
        };
        $getInstance = function(string $className) use ($c) {
            if (isset($c[$className])) {
                $instance = $c[$className];
            } else {
                $instance = (new $className());
            }
            return $instance;
        };
        
        $handler = $route->handler;
        if (is_string($handler)) {
            $instance = $getInstance($handler);
            $response = $getResponse($instance);
        } elseif (is_callable($handler)) {
            $instance = $handler;
            $response = $getResponse($instance);
        } elseif (is_array($handler)) {
            $instance = $getInstance($handler[0]);
            $action = $handler[1];
            $response = $instance->$action($request);
        } else {
            throw new Exception("Не верно задан обработчик маршрута");
        }
        
        return $response;
    }
}
