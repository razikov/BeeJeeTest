<?php

namespace App\Controller;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BaseController
{
    protected $flashMessages;
    protected $session;
    protected $container;
    protected $isAdmin = false;
    protected $flashMsg;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function render($view, $params = []): ResponseInterface
    {
        $templateRenderer = $this->container->get(Engine::class);
        $response = new Response();
        $params['flashes'] = $this->flashMessages->getFlashes();
        $params['isAdmin'] = $this->isAdmin;
        $response->getBody()->write($templateRenderer->render($view, $params));
        return $response->withStatus(200);
    }
    
    protected function renderJson($data = [])
    {
        $response = new Response();
        $response->getBody()->write(\json_encode($data));
        $response->withAddedHeader('content-type', 'application/json')->withStatus(200);
        return $response;
    }
    
    protected function redirect($url, $status = 302, $headers = [])
    {
        return new RedirectResponse($url, $status, $headers);
    }
    
    protected function flash($key, $content)
    {
        $this->flashMessages->flash($key, $content);
    }
    
    public function beforeAction(ServerRequestInterface $request)
    {
        $this->flashMessages = $request->getAttribute('flash');
        $this->session = $request->getAttribute('session');
        $this->isAdmin = $request->getAttribute('user') !== null;
        $this->flashMsg = $this->flashMessages->getFlashes();
        
    }
    
}