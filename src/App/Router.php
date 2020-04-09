<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router extends \League\Route\Router
{
    private $dispatcher;
    
    public function match($httpMethod, $uri)
    {
        return $this->dispatcher->dispatch($httpMethod, $uri);
    }
    
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->getStrategy() === null) {
            $this->setStrategy(new \League\Route\Strategy\ApplicationStrategy());
        }

        $this->prepRoutes($request);

        /** @var Dispatcher $dispatcher */
        $dispatcher = (new Dispatcher($this->getData()))->setStrategy($this->getStrategy());
        $this->dispatcher = $dispatcher;

        foreach ($this->getMiddlewareStack() as $middleware) {
            if (is_string($middleware)) {
                $dispatcher->lazyMiddleware($middleware);
                continue;
            }

            $dispatcher->middleware($middleware);
        }
        
        return $dispatcher->dispatchRequest($request);
    }
    
    public function generateUri(string $routeName, array $params, array $options = []): string
    {
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#']);
        
        $routeData = $this->routeParser->parse(
            $this->parseRoutePath(
                $this->getNamedRoute($routeName)->getPath()
            )
        );
        $routeData = array_pop($routeData);
        
        $route = [];
        foreach ($routeData as $key => $data) {
            if ($key % 2 == 0) { // часть пути
                $route[] = $data;
            } else { // placeholder
                $key = $data[0];
                $keyPattern = $data[1];
                if (!isset($params[$key])) {
                    array_pop($route);
                    break;
                }
                $route[] = $this->encode($params[$key], $options['isRaw'] ?? false);
                unset($params[$key]);
            }
        }
        $url = ltrim(implode('', $route));
        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query . $anchor;
        }
        return $url;
    }
    
    protected function encode($val, $isRaw = false)
    {
        if ($isRaw) {
            return $val;
        }

        return is_scalar($val) ? rawurlencode($val) : null;
    }
}
