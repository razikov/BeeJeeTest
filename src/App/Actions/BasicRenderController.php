<?php

namespace App\Actions;

use Laminas\Diactoros\Response;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class BasicRenderController implements RendarableControllerInterface
{
    private $templateRenderer;
    private $user;
    
    public function __construct(Engine $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }
    
    public function __invoke(ServerRequestInterface $request, $args = []): ResponseInterface
    {
        $this->user = $request->getAttribute('user');
        return $this->action($request, $args);
    }
    
    public function render(string $view = 'index', array $data = array()): ResponseInterface
    {
        $data['isAdmin'] = $this->user !== null;
        $response = new Response();
        $response->getBody()
            ->write(
                $this->templateRenderer->render($view, $data)
            );
        return $response;
    }
}