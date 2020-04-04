<?php

namespace App\Actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RendarableControllerInterface
{
//    public function __construct(\League\Plates\Engine $templateRenderer);
    public function __invoke(ServerRequestInterface $request, array $args = []): ResponseInterface;
    public function action(ServerRequestInterface $request, array $args = []): ResponseInterface;
    public function render(string $view = 'index', array $data = []): ResponseInterface;
}