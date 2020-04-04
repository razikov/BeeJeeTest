<?php

$container = new \Pimple\Container;

// ===== PARAMS =========
$container['dbParams'] = [
    'driver'   => 'pdo_mysql',
    'host'     => getenv('DB_HOST'),
    'dbname'   => getenv('DB_NAME'),
    'user'     => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
];
$container['adminUsers'] = [
    getenv('ADMIN_LOGIN') => getenv('ADMIN_PASSWORD')
];
$container['entityPath'] = [__DIR__."/../src/App/Entity"];
$container['viewsPath'] = __DIR__.'/../src/App/Views';
$container['assetsPath'] = __DIR__.'/../public/';
$container['rules'] = [
    'login' => [
        'login' => [
            new \Symfony\Component\Validator\Constraints\NotBlank([]),
        ],
        'password' => [
            new \Symfony\Component\Validator\Constraints\NotBlank([]),
        ],
    ],
    'job' => [
        'name' => [
            new \Symfony\Component\Validator\Constraints\Length([
                'max' => 50,
                'minMessage' => 'Значение не может быть меньше, чем {{ limit }} символов',
                'maxMessage' => 'Значение не может быть больше, чем {{ limit }} символов',
                ]),
            new \Symfony\Component\Validator\Constraints\NotBlank([
                'message' => 'Поле не может быть пустым.',
                ]),
        ],
        'email' => [
            new Symfony\Component\Validator\Constraints\Email([
                'message' => 'Email "{{ value }}" не соответствует шаблону.',
                ]),
            new \Symfony\Component\Validator\Constraints\NotBlank([
                'message' => 'Поле не может быть пустым.',
                ]),
        ],
        'content' => [
            new \Symfony\Component\Validator\Constraints\Length([
                'max' => 255,
                'minMessage' => 'Значение не может быть меньше, чем {{ limit }} символов',
                'maxMessage' => 'Значение не может быть больше, чем {{ limit }} символов',
                ]),
            new \Symfony\Component\Validator\Constraints\NotBlank([
                'message' => 'Поле не может быть пустым.',
                ]),
        ],
    ]
];

// ===== SERVICES =========
$container['annotationConfig'] = function($c) {
    $isDevMode = true;
    $proxyDir = null;
    $cache = null;
    $useSimpleAnnotationReader = false;
    $paths = $c['entityPath'];
    return Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
};
$container['em'] = function($c) {
    return Doctrine\ORM\EntityManager::create($c['dbParams'], $c['annotationConfig']);
};
$container[\PDO::class] = function($c) {
    $pdo = new \PDO(
        sprintf('mysql:host=%s;dbname=%s', $c['dbParams']['host'], $c['dbParams']['dbname']),
        $c['dbParams']['user'],
        $c['dbParams']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
    return $pdo;
};
$container[League\Plates\Engine::class] = function($c) {
    $asset = new League\Plates\Extension\Asset($c['assetsPath']);
    $template = new League\Plates\Engine($c['viewsPath']);
    $template->loadExtension($asset);
    return $template;
};
$container['templateRenderer'] = function ($c) {
    $asset = new League\Plates\Extension\Asset($c['assetsPath']);
    $template = new League\Plates\Engine($c['viewsPath']);
    $template->loadExtension($asset);
    return $template;
};
//$container['router'] = function($c) {
//    $router = new Aura\Router\RouterContainer();
//    $map = $router->getMap();
//    $map->get('task.list', '/', [\App\Controller\JobController::class, 'indexAction']);
//    $map->get('task.loginForm', '/login', [\App\Controller\SiteController::class, 'loginAction']);
//    $map->post('task.login', '/login', [\App\Controller\SiteController::class, 'loginAction']);
//    $map->get('task.logout', '/logout', [\App\Controller\SiteController::class, 'logoutAction']);
//    $map->get('task.createForm', '/create', [\App\Controller\JobController::class, 'createAction']);
//    $map->post('task.create', '/create', [\App\Controller\JobController::class, 'createAction']);
//    $map->get('task.updateForm', '/update/{id}', [\App\Controller\JobController::class, 'updateAction']);
//    $map->post('task.update', '/update/{id}', [\App\Controller\JobController::class, 'updateAction']);
//    return $router;
//};
$container['router'] = function($container) {
    $psrContainer = new \Pimple\Psr11\Container($container);
    $strategy = (new \League\Route\Strategy\ApplicationStrategy())->setContainer($psrContainer);
    $router = (new \League\Route\Router())->setStrategy($strategy);
    $router->get('/', \App\Actions\JobList::class);
    $router->get('/login', \App\Actions\LoginForm::class);
    $router->post('/login', \App\Actions\Login::class)
        ->middleware(new \App\Middleware\Validate($psrContainer->get('rules')['login']));
    $router->get('/logout', function (
        \Psr\Http\Message\ServerRequestInterface $request
    ): \Psr\Http\Message\ResponseInterface {
        $session = $request->getAttribute('session');
        if ($session !== null) {
            $session->clear();
        }
        return new \Laminas\Diactoros\Response\RedirectResponse('/');
    });
    $router->get('/create', \App\Actions\JobCreateForm::class);
    $router->post('/create', \App\Actions\JobCreate::class)
        ->middleware(new \App\Middleware\Validate($psrContainer->get('rules')['job']));
    $router->get('/update/{id:number}', \App\Actions\JobUpdateForm::class)
        ->middleware(new \App\Middleware\Authorize('@'));
    $router->post('/update/{id:number}', \App\Actions\JobUpdate::class)
        ->middleware(new \App\Middleware\Authorize('@'))
        ->middleware(new \App\Middleware\Validate($psrContainer->get('rules')['job']));
    
    return $router;
};
$container['pipeline'] = function($c) {
    $pipe = new \Laminas\Stratigility\MiddlewarePipe();
    $pipe->pipe(new \Mezzio\Session\SessionMiddleware(new \Mezzio\Session\Ext\PhpSessionPersistence()));
    $pipe->pipe(new \Mezzio\Flash\FlashMessageMiddleware());
    $pipe->pipe(new \App\Middleware\SessionAuthenticate($c['userManager']));
//    $pipe->pipe(new \App\Middleware\DispatchRoute($c));
    $pipe->pipe(new \App\Middleware\DispatchLeagueRoute($c));
    $pipe->pipe(\Laminas\Stratigility\middleware(function ($request, $handler) use ($c) {
        $response = (new \Laminas\Diactoros\ResponseFactory())->createResponse(404);
        $response->getBody()->write($c[\League\Plates\Engine::class]->render('app/404'));
        return $response;
    }));
    return $pipe;
};

// ===== APP =========
$container[App\Controller\SiteController::class] = function($c) {
    $psrContainer = new \Pimple\Psr11\Container($c);
    return new App\Controller\SiteController($psrContainer);
};
$container[App\Controller\JobController::class] = function($c) {
    $psrContainer = new \Pimple\Psr11\Container($c);
    return new App\Controller\JobController($psrContainer);
};
$container[App\Models\JobRepository::class] = function($c) {
    return new App\Models\JobRepository($c[\PDO::class], $c['em']);
};// дубль

$container[App\Actions\LoginForm::class] = function ($c) {
    $templateRenderer = $c['templateRenderer'];
    return new App\Actions\LoginForm($templateRenderer);
};
$container[\App\Actions\Login::class] = function ($c) {
    $userManager = $c['userManager'];
    return new \App\Actions\Login($userManager);
};
$container[\App\Actions\JobList::class] = function ($c) {
    $templateRenderer = $c['templateRenderer'];
    $jobRepository = $c['jobRepository'];
    return new \App\Actions\JobList($templateRenderer, $jobRepository);
};
$container[\App\Actions\JobCreateForm::class] = function ($c) {
    return new \App\Actions\JobCreateForm($c['templateRenderer'], $c['jobRepository']);
};
$container[\App\Actions\JobUpdateForm::class] = function ($c) {
    return new \App\Actions\JobUpdateForm($c['templateRenderer'], $c['jobRepository']);
};
$container[\App\Actions\JobCreate::class] = function ($c) {
    return new \App\Actions\JobCreate($c['templateRenderer'], $c['jobRepository']);
};
$container[\App\Actions\JobUpdate::class] = function ($c) {
    return new \App\Actions\JobUpdate($c['templateRenderer'], $c['jobRepository']);
};
$container['jobRepository'] = function ($c) {
    return new \App\Models\JobRepository($c[\PDO::class], $c['em']);
};
$container['userManager'] = function ($c) {
    return new \App\Models\UserManager($c['adminUsers']);
};


return new \Pimple\Psr11\Container($container);