<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

class AuthMiddleware implements MiddlewareInterface
{
    
    protected $users = [
        'admin' => '123',
    ];


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        
        
        
        return $handler->handle($request);
    }
}
