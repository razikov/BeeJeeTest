<?php

chdir(dirname(__DIR__));
require_once __DIR__ . '/../vendor/autoload.php';

$whoops = new \Whoops\Run();
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
$whoops->register();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$container = require_once __DIR__ . '/../config/container.php';
$router = $container->get('router');
$request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
$response = $router->dispatch($request);

$emit = new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter();
$emit->emit($response);
