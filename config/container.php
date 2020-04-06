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

$container['router'] = function($c) {
    $psrContainer = new Pimple\Psr11\Container($c);
    $strategy = (new App\ApplicationStrategy)->setContainer($psrContainer);
    $router = (new League\Route\Router)->setStrategy($strategy);
//    $router->addPatternMatcher('word', '\w+');
//    $router->addPatternMatcher('sort_chars', '[\-\+]{0,1}');
    $router->get('/', [App\Controllers\JobController::class, 'indexAction']);
//    $router->get('/sort/{attribute:word}{direction:sort_chars}', [App\Controllers\JobController::class, 'indexAction']);
//    $router->get('/sort/{attribute:word}{direction:sort_chars}/page/{page:number}', [App\Controllers\JobController::class, 'indexAction']);
//    $router->get('/page/{page:number}', [App\Controllers\JobController::class, 'indexAction']);
    $router->map('GET', '/test', [App\Controllers\SiteController::class, 'testAction']);
    $router->map('GET', '/login', [App\Controllers\SiteController::class, 'loginAction']);
    $router->map('POST', '/login', [App\Controllers\SiteController::class, 'loginAction']);
    $router->map('GET', '/logout', [App\Controllers\SiteController::class, 'logoutAction']);
    $router->map('GET', '/create', [App\Controllers\JobController::class, 'createAction']);
    $router->map('POST', '/create', [App\Controllers\JobController::class, 'createAction']);
    $router->map('GET', '/update/{id:number}', [App\Controllers\JobController::class, 'updateAction'])
        ->middleware(new \App\Middlewares\Authorize('@'));
    $router->map('POST', '/update/{id:number}', [App\Controllers\JobController::class, 'updateAction'])
        ->middleware(new \App\Middlewares\Authorize('@'));
    $router->middleware(new \Mezzio\Session\SessionMiddleware(new \Mezzio\Session\Ext\PhpSessionPersistence()));
    $router->middleware(new \Mezzio\Flash\FlashMessageMiddleware());
    $router->middleware(new \App\Middlewares\SessionAuthenticate($c[App\Models\UserManager::class]));
    return $router;
};

// ===== APP =========
$container[App\Controllers\SiteController::class] = function($c) {
    return new App\Controllers\SiteController(
        $c[\League\Plates\Engine::class],
        $c[\Psr\EventDispatcher\EventDispatcherInterface::class],
        $c[\App\Models\UserManager::class]
    );
};
$container[App\Controllers\JobController::class] = function($c) {
    return new App\Controllers\JobController(
        $c[\League\Plates\Engine::class],
        $c[\Psr\EventDispatcher\EventDispatcherInterface::class],
        $c[\App\Services\JobService::class]
    );
};
$container[App\Models\JobRepository::class] = function($c) {
    return new App\Models\JobRepository($c[\PDO::class], $c['em']);
};
$container[\App\Services\JobService::class] = function($c) {
    return new \App\Services\JobService(
        $c[App\Models\JobRepository::class],
        $c[Psr\EventDispatcher\EventDispatcherInterface::class]
    );
};
$container[App\Models\UserManager::class] = function ($c) {
    return new \App\Models\UserManager($c['adminUsers']);
};
$container[Psr\EventDispatcher\EventDispatcherInterface::class] = function ($c) {
    return new App\EventDispatcher\EventDispatcher($c[Psr\EventDispatcher\ListenerProviderInterface::class]);
};
$container[Psr\EventDispatcher\ListenerProviderInterface::class] = function ($c) {
    $lp = new App\EventDispatcher\ListenerProvider();
//    $lp->add(App\Events\BeforeActionEvent::class, 0, [App\Controllers\BaseController::class, 'beforeActionForEvent']);
    return $lp;
};

return new \Pimple\Psr11\Container($container);