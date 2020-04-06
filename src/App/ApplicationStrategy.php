<?php

namespace App;

use App\Events\AfterActionEvent;
use App\Events\BeforeActionEvent;
use League\Route\Route;
use League\Route\Strategy\ApplicationStrategy as DefaultApplicationStrategy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApplicationStrategy extends DefaultApplicationStrategy
{
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());

        foreach ($route->getVars() as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }
        
        $controller[0]->action = $controller[1];
        $listeners = $this->container->get(ListenerProviderInterface::class);
        $listeners->add(BeforeActionEvent::class, 'controllers.beforeAction', [$controller[0], 'beforeAction']);
        $listeners->add(AfterActionEvent::class, 'controllers.afterAction', [$controller[0], 'afterAction']);
        $dispatcher = $this->container->get(EventDispatcherInterface::class);
        
        $dispatcher->dispatch(new BeforeActionEvent($request, $controller[0]));
        
        $response = $controller($request);
        $response = $this->applyDefaultResponseHeaders($response);
        
        $dispatcher->dispatch(new AfterActionEvent($response, $controller[0]));

        return $response;
    }
}
