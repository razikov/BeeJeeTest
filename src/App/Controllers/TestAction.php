<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;

class TestAction extends BaseController
{
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        exit;
        
        return $this->render('app/test', [
            'isAdmin' => false,
            'flashes' => $request->getAttribute('flash'),
            'router' => $this->router,
        ]);
    }
    
    protected function render($view, $params = []): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write($this->templateEngine->render($view, $params));
        $response->withStatus(200);
        
        return $response;
    }
}
