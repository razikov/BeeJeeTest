<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Authorize implements MiddlewareInterface
{
    private $allowed;
    private $userAttribute = 'user';
    
    /**
     * Allowed role
     * @param string $allowed allowed user role
     */
    public function __construct(string $allowed = '*')
    {
        $this->allowed = $allowed;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute($this->userAttribute);
        if ($user === null && $this->allowed !== '*') {
            return new \Laminas\Diactoros\Response\RedirectResponse('/login');
        }
        return $handler->handle($request);
        
    }
}
