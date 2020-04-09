<?php

namespace App\Middlewares;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @var \Mezzio\Authentication\Session\PhpSession
     */
    protected $auth;

    public function __construct(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->authenticate($request);
        if (null !== $user) {
            return $handler->handle($request->withAttribute(UserInterface::class, $user));
        }
        return $handler->handle($request);
    }
}
