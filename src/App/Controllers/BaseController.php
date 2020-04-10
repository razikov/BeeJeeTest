<?php

namespace App\Controllers;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Plates\Engine;
use Mezzio\Authentication\UserInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

class BaseController
{
    public const REDIRECT_ATTRIBUTE = 'authentication:redirect';
    
    protected $templateEngine;
    protected $dispatcher;
    
    public $action;
    protected $flashMessages;
    protected $session;
    protected $isAdmin = false;

    public function __construct(Engine $engine, EventDispatcherInterface $dispatcher)
    {
        $this->templateEngine = $engine;
        $this->dispatcher = $dispatcher;
    }

    protected function render($view, $params = []): ResponseInterface
    {
        $response = new Response();
        $params['flashes'] = $this->flashMessages->getFlashes();
        $params['isAdmin'] = $this->isAdmin;
        
        $response->getBody()->write($this->templateEngine->render($view, $params));
        $response->withStatus(200);
        
        return $response;
    }
    
    protected function renderJson($data = [])
    {
        $response = new Response();
        $response->getBody()->write(\json_encode($data));
        $response->withAddedHeader('content-type', 'application/json');
        $response->withStatus(200);
        return $response;
    }
    
    protected function redirect($url, $status = 302, $headers = [])
    {
        return new RedirectResponse($url, $status, $headers);
    }
    
    protected function setFlash($key, $content)
    {
        $this->flashMessages->flash($key, $content);
    }
    
    public function beforeAction(\App\Events\BeforeActionEvent $event)
    {
        $request = $event->request;
        $this->flashMessages = $request->getAttribute('flash');
        $this->session = $request->getAttribute('session');
        $this->isAdmin = $request->getAttribute(UserInterface::class) !== null;
    }
    
    public function afterAction(\App\Events\AfterActionEvent $event)
    {
    }
}
