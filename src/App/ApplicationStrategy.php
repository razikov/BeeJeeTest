<?php

namespace App;

use App\Events\AfterActionEvent;
use App\Events\BeforeActionEvent;
use League\Plates\Engine;
use League\Route\Route;
use League\Route\Strategy\ApplicationStrategy as DefaultApplicationStrategy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response;
use Throwable;

class ApplicationStrategy extends DefaultApplicationStrategy implements StrategyInterface
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
    
//    public function getExceptionHandler($exception): \Psr\Http\Server\MiddlewareInterface
//    {
//        if ((bool)getenv('DEBUG') == true) {
//            throw $exception;
//        }
//        $response = new \Laminas\Diactoros\Response();
//        $response->getBody()->write(
//            $this->container->get(\League\Plates\Engine::class)->render('app/error', ['e' => $exception])
//        );
//        $response->withStatus(500);
//    }
//    
//    public function getMethodNotAllowedDecorator($exception): \Psr\Http\Server\MiddlewareInterface
//    {
//        return;
//    }
//    
    public function getNotFoundDecorator($exception): MiddlewareInterface
    {
        return new class ($exception, $this->container->get(Engine::class)) implements MiddlewareInterface
        {
            protected $error;
            private $templateRenderer;

            public function __construct(Throwable $error, Engine $templateRenderer)
            {
                $this->error = $error;
                $this->templateRenderer = $templateRenderer;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                $response = new Response();
                $response->getBody()->write(
                    $this->templateRenderer
                        ->render('app/404', ['e' => $this->error])
                );
                $response->withStatus($this->error->getStatusCode());
                return $response;
            }
        };
    }

    public function isPrependThrowableDecorator(): bool
    {
        return false;
    }

}
