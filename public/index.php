<?php
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

// TODO:
// добавить обработчик ошибок
// добавить орм
// добавить сборщик фронта
// найти url helper к роутеру
// 

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$container = new League\Container\Container;
$container->add(App\Controller\JobController::class)
        ->addArgument(League\Plates\Engine::class)
        ->addArgument(App\Models\JobRepository::class)
        ->addArgument($container);
$container->add(League\Plates\Engine::class)->addArgument('src/App/Views')->addMethodCall('loadExtension', [League\Plates\Extension\Asset::class]);
$container->add(League\Plates\Extension\Asset::class)->addArguments([__DIR__.'/../assets/']);
$container->add(\PDO::class)->addArguments([
    'dsn' => sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_NAME')),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
]);
$container->add(App\Models\JobRepository::class)->addArgument(\PDO::class);

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$strategy = (new League\Route\Strategy\ApplicationStrategy)->setContainer($container);
$router = (new League\Route\Router)->setStrategy($strategy);
$router->middleware(new Middlewares\AuraSession());
$router->map('GET', '/', [App\Controller\JobController::class, 'indexAction']);
$router->map('GET', '/login', [App\Controller\JobController::class, 'loginAction']);
$router->map('POST', '/login', [App\Controller\JobController::class, 'loginAction']);
$router->map('GET', '/logout', [App\Controller\JobController::class, 'logoutAction']);
$router->map('GET', '/create', [App\Controller\JobController::class, 'createAction']);
$router->map('POST', '/create', [App\Controller\JobController::class, 'createAction']);
$router->map('GET', '/update/{id:number}', [App\Controller\JobController::class, 'updateAction']);
$router->map('POST', '/update/{id:number}', [App\Controller\JobController::class, 'updateAction']);
$router->middleware(new Middlewares\ErrorHandler());

$response = $router->dispatch($request);
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);




