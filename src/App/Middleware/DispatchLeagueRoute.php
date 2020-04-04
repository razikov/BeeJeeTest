<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatchLeagueRoute implements MiddlewareInterface
{
    private $_container;
    
    public function __construct($container)
    {
        $this->_container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $container = $this->_container;
        $router = $container['router'];
        $templateEngine = $container[\League\Plates\Engine::class];

        try {
//            foreach ($route->attributes as $key => $val) {
//                $request = $request->withAttribute($key, $val);
//            }
            return $router->dispatch($request);
        } catch (\League\Route\Http\Exception\NotFoundException $e) {
            $response = new \Laminas\Diactoros\Response();
            $response->getBody()->write($templateEngine->render('app/404', ['e' => $e]));
            $response->withStatus(404);
        } catch (\League\Route\Http\Exception\UnauthorizedException $e) {
            return new \Laminas\Diactoros\Response\RedirectResponse('/login');
        } catch (Exception $exc) {
            return $handler->handle($request);
        }
        return $handler->handle($request);
    }
}
