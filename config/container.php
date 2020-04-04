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
//$container['rules'] = [
//    'login' => [
//        'login' => [
//            new \Symfony\Component\Validator\Constraints\NotBlank([]),
//        ],
//        'password' => [
//            new \Symfony\Component\Validator\Constraints\NotBlank([]),
//        ],
//    ],
//    'job' => [
//        'name' => [
//            new \Symfony\Component\Validator\Constraints\Length([
//                'max' => 50,
//                'minMessage' => 'Значение не может быть меньше, чем {{ limit }} символов',
//                'maxMessage' => 'Значение не может быть больше, чем {{ limit }} символов',
//                ]),
//            new \Symfony\Component\Validator\Constraints\NotBlank([
//                'message' => 'Поле не может быть пустым.',
//                ]),
//        ],
//        'email' => [
//            new Symfony\Component\Validator\Constraints\Email([
//                'message' => 'Email "{{ value }}" не соответствует шаблону.',
//                ]),
//            new \Symfony\Component\Validator\Constraints\NotBlank([
//                'message' => 'Поле не может быть пустым.',
//                ]),
//        ],
//        'content' => [
//            new \Symfony\Component\Validator\Constraints\Length([
//                'max' => 255,
//                'minMessage' => 'Значение не может быть меньше, чем {{ limit }} символов',
//                'maxMessage' => 'Значение не может быть больше, чем {{ limit }} символов',
//                ]),
//            new \Symfony\Component\Validator\Constraints\NotBlank([
//                'message' => 'Поле не может быть пустым.',
//                ]),
//        ],
//    ]
//];

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

$container['router'] = function($c) {
    $psrContainer = new \Pimple\Psr11\Container($c);
    $strategy = (new App\ApplicationStrategy)->setContainer($psrContainer);
    $router = (new League\Route\Router)->setStrategy($strategy);
    $router->map('GET', '/', [App\Controller\JobController::class, 'indexAction']);
    $router->map('GET', '/login', [App\Controller\SiteController::class, 'loginAction']);
    $router->map('POST', '/login', [App\Controller\SiteController::class, 'loginAction']);
    $router->map('GET', '/logout', [App\Controller\SiteController::class, 'logoutAction']);
    $router->map('GET', '/create', [App\Controller\JobController::class, 'createAction']);
    $router->map('POST', '/create', [App\Controller\JobController::class, 'createAction']);
    $router->map('GET', '/update/{id:number}', [App\Controller\JobController::class, 'updateAction'])
        ->middleware(new \App\Middleware\Authorize('@'));
    $router->map('POST', '/update/{id:number}', [App\Controller\JobController::class, 'updateAction'])
        ->middleware(new \App\Middleware\Authorize('@'));
    $router->middleware(new \Mezzio\Session\SessionMiddleware(new \Mezzio\Session\Ext\PhpSessionPersistence()));
    $router->middleware(new \Mezzio\Flash\FlashMessageMiddleware());
    $router->middleware(new \App\Middleware\SessionAuthenticate($c['userManager']));
    return $router;
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
$container['userManager'] = function ($c) {
    return new \App\Models\UserManager($c['adminUsers']);
};

return new \Pimple\Psr11\Container($container);