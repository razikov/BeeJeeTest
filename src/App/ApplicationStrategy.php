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
use Throwable;

class ApplicationStrategy extends DefaultApplicationStrategy implements StrategyInterface
{
    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $container = $this->container;
        $controller = $route->getCallable($this->getContainer());

        foreach ($route->getVars() as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }
        
        $listeners = $container->get(ListenerProviderInterface::class);
        $listeners->add(BeforeActionEvent::class, 'controllers.beforeAction', [$controller[0], 'beforeAction']);
        $listeners->add(AfterActionEvent::class, 'controllers.afterAction', [$controller[0], 'afterAction']);
        $dispatcher = $container->get(EventDispatcherInterface::class);
        
        $dispatcher->dispatch(new BeforeActionEvent($request));
        
        $response = $controller($request);
        $response = $this->applyDefaultResponseHeaders($response);
        
        $dispatcher->dispatch(new AfterActionEvent($response));

        return $response;
    }
    
    public function getExceptionHandler(): MiddlewareInterface
    {
        return new class ($this->container->get(ResponseInterface::class), $this->container->get(Engine::class)) implements MiddlewareInterface
        {
            private $response;
            private $templateRenderer;
            
            public function __construct($response, Engine $templateRenderer)
            {
                $this->response = $response();
                $this->templateRenderer = $templateRenderer;
            }
            
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                try {
                    return $requestHandler->handle($request);
                } catch (Throwable $exception) {
                    if ((bool)getenv('DEBUG') === true) {
                        throw $exception;
                    }
                    $this->response->getBody()->write(
                        $this->templateRenderer->render('app/error', [
                            'e' => $exception,
                            'flashes' => [],
                            'isAdmin' => false,
                        ])
                    );
                    $response = $this->response->withStatus(500);
                    return $response;
                }
            }
        };
    }
    
//    public function getMethodNotAllowedDecorator($exception): \Psr\Http\Server\MiddlewareInterface
//    {
//        return;
//    }
    
    public function getNotFoundDecorator($exception): MiddlewareInterface
    {
        return new class ($exception, $this->container->get(ResponseInterface::class), $this->container->get(Engine::class)) implements MiddlewareInterface
        {
            protected $error;
            private $response;
            private $templateRenderer;

            public function __construct(Throwable $error, $response, Engine $templateRenderer)
            {
                $this->error = $error;
                $this->response = $response();
                $this->templateRenderer = $templateRenderer;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $requestHandler
            ): ResponseInterface {
                $this->response->getBody()->write(
                    $this->templateRenderer
                        ->render('app/404', [
                            'e' => $this->error,
                            'flashes' => $request->getAttribute('flash')->getFlashes(),
                            'isAdmin' => false,
                        ])
                );
                $response = $this->response->withStatus($this->error->getStatusCode());
                return $response;
            }
        };
    }

    public function isPrependThrowableDecorator(): bool
    {
        return false;
    }
}
