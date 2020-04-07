<?php

namespace App\Middlewares;

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
        $flashMessages = $request->getAttribute('flash');
        $user = $request->getAttribute($this->userAttribute);
        if ($user === null && $this->allowed !== '*') {
            $flashMessages->flash('failMessage', 'Операция доступна только администратору.');
            return new \Laminas\Diactoros\Response\RedirectResponse('/login');
        }
        return $handler->handle($request);
    }
}
