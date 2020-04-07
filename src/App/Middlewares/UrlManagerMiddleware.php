<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UrlManagerMiddleware implements MiddlewareInterface
{
    private $urlHelper;
    
    public function __construct(\App\UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->urlHelper->setRequest($request);
        return $handler->handle($request);
    }
}
