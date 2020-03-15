<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

// TODO:
// добавить сборщик фронта
// поискать другой роутер, копнуть глубже этот
// 

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$container = require_once __DIR__.'/../config/container.php';

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$strategy = (new League\Route\Strategy\ApplicationStrategy)->setContainer($container);
$router = (new League\Route\Router)->setStrategy($strategy);
$router->map('GET', '/', [App\Controller\JobController::class, 'indexAction']);
$router->map('GET', '/login', [App\Controller\SiteController::class, 'loginAction']);
$router->map('POST', '/login', [App\Controller\SiteController::class, 'loginAction']);
$router->map('GET', '/logout', [App\Controller\SiteController::class, 'logoutAction']);
$router->map('GET', '/create', [App\Controller\JobController::class, 'createAction']);
$router->map('POST', '/create', [App\Controller\JobController::class, 'createAction']);
$router->map('GET', '/update/{id:number}', [App\Controller\JobController::class, 'updateAction']);
$router->map('POST', '/update/{id:number}', [App\Controller\JobController::class, 'updateAction']);
$router->middleware(new Middlewares\AuraSession());

try {
    $response = $router->dispatch($request);
} catch (\League\Route\Http\Exception\NotFoundException $e) {
    $response = new \Laminas\Diactoros\Response();
    $response->getBody()->write($container->get(League\Plates\Engine::class)->render('app/404', ['e' => $e]));
    $response->withStatus(404);
} catch (\Exception $e) {
    if (getenv('DEBUG')) {
        throw $e;
    }
    $response = new \Laminas\Diactoros\Response();
    $response->getBody()->write($container->get(League\Plates\Engine::class)->render('app/error', ['e' => $e]));
    $response->withStatus((int)$e->getCode());
}

(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);




