<?php

$container = new \Pimple\Container();

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
$container['entityPath'] = [__DIR__ . "/../src/App/Entity"];
$container['viewsPath'] = __DIR__ . '/../src/App/Views';
$container['assetsPath'] = __DIR__ . '/../public/';
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
$container['annotationConfig'] = function ($c) {
    $isDevMode = true;
    $proxyDir = null;
    $cache = null;
    $useSimpleAnnotationReader = false;
    $paths = $c['entityPath'];
    return Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
};
$container['em'] = function ($c) {
    return Doctrine\ORM\EntityManager::create($c['dbParams'], $c['annotationConfig']);
};
$container[\PDO::class] = function ($c) {
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
$container[League\Plates\Engine::class] = function ($c) {
    $asset = new League\Plates\Extension\Asset($c['assetsPath']);
    $url = new \App\PlatesUrlExtension($c[\App\UrlHelper::class]);
    $template = new League\Plates\Engine($c['viewsPath']);
    $template->loadExtension($asset);
    $template->loadExtension($url);
    return $template;
};

$container['router'] = function ($c) {
    $psrContainer = new Pimple\Psr11\Container($c);
    $strategy = (new App\ApplicationStrategy())->setContainer($psrContainer);
    $router = (new App\Router())->setStrategy($strategy);
    $router->get('/', [App\Controllers\JobController::class, 'indexAction'])->setName('jobList');
    $router->map('GET', '/login', [App\Controllers\SiteController::class, 'loginAction'])->setName('login');
    $router->map('POST', '/login', [App\Controllers\SiteController::class, 'loginAction'])->setName('loginForm');
    $router->map('GET', '/logout', [App\Controllers\SiteController::class, 'logoutAction'])->setName('logout');
    $router->group('/job', function (\League\Route\RouteGroup $route) use ($c) {
        $route->map('GET', '/create', [App\Controllers\JobController::class, 'createAction'])->setName('job.create');
        $route->map('POST', '/create', [App\Controllers\JobController::class, 'createAction'])->setName('job.createForm');
        $route->map('GET', '/update/{id:number}', [App\Controllers\JobController::class, 'updateAction'])->setName('job.update')
            ->middleware(new App\Middlewares\AuthorizationMiddleware(
                $c[Mezzio\Authorization\AuthorizationInterface::class],
                $c[Psr\Http\Message\ResponseInterface::class]
            ));
        $route->map('POST', '/update/{id:number}', [App\Controllers\JobController::class, 'updateAction'])->setName('job.updateForm')
            ->middleware(new App\Middlewares\AuthorizationMiddleware(
                $c[Mezzio\Authorization\AuthorizationInterface::class],
                $c[Psr\Http\Message\ResponseInterface::class]
            ));
    })->middleware(new \App\Middlewares\CsrfMiddleware($c[Mezzio\Csrf\SessionCsrfGuardFactory::class], $c['csrfAttribute']));
    $router->middleware(new \Mezzio\Session\SessionMiddleware(new \Mezzio\Session\Ext\PhpSessionPersistence()));
    $router->middleware($c[App\Middlewares\AuthenticationMiddleware::class]);
    $router->middleware(new \Mezzio\Flash\FlashMessageMiddleware());
    return $router;
};

// ===== APP =========
$container[App\Controllers\SiteController::class] = function ($c) {
    return new App\Controllers\SiteController(
        $c[\League\Plates\Engine::class],
        $c[\Psr\EventDispatcher\EventDispatcherInterface::class],
        $c[\App\Models\UserRepository::class],
        $c[Mezzio\Authentication\Session\PhpSession::class],
    );
};
$container[App\Controllers\JobController::class] = function ($c) {
    return new App\Controllers\JobController(
        $c[\League\Plates\Engine::class],
        $c[\Psr\EventDispatcher\EventDispatcherInterface::class],
        $c[\App\Services\JobService::class]
    );
};
$container[App\Models\JobRepository::class] = function ($c) {
    return new App\Models\JobRepository($c[\PDO::class], $c['em']);
};
$container[\App\Services\JobService::class] = function ($c) {
    return new \App\Services\JobService(
        $c[App\Models\JobRepository::class],
        $c[Psr\EventDispatcher\EventDispatcherInterface::class]
    );
};

$container[App\Models\UserRepository::class] = function ($c) {
    return new \App\Models\UserRepository($c['adminUsers']);
};
$container[Mezzio\Authentication\UserRepositoryInterface::class] = function ($c) {
    return $c[App\Models\UserRepository::class];
};
$container[Mezzio\Authentication\UserInterface::class] = function ($c) {
    $container = new \Pimple\Psr11\Container($c);
    $factory = new Mezzio\Authentication\DefaultUserFactory();
    return $factory($container);
};
$container[Mezzio\Authentication\Session\PhpSession::class] = function ($container) {
    return new Mezzio\Authentication\Session\PhpSession(
        $container[Mezzio\Authentication\UserRepositoryInterface::class],
        $container['authentication'],
        $container[Psr\Http\Message\ResponseInterface::class],
        $container[Mezzio\Authentication\UserInterface::class]
    );
};
$container[Psr\Http\Message\ResponseInterface::class] = function ($c) {
    return function (): Laminas\Diactoros\Response {
        return new Laminas\Diactoros\Response();
    };
};
$container[Mezzio\Csrf\SessionCsrfGuardFactory::class] = function ($c) {
    return new Mezzio\Csrf\SessionCsrfGuardFactory();
};
$container['csrfAttribute'] = '__csrf';


$container[App\Middlewares\AuthenticationMiddleware::class] = function ($c) {
    return new App\Middlewares\AuthenticationMiddleware($c[Mezzio\Authentication\Session\PhpSession::class]);
};
// Для названия полей формы логина и редиректа
$container['authentication'] = [
    'username' => 'login',
    'password' => 'password',
    'redirect' => '/login',
];

$container[Mezzio\Authorization\AuthorizationInterface::class] = function ($c) {
    $container = new \Pimple\Psr11\Container($c);
    $factory = function ($container) {
        $config = $container->get('authorization') ?? null;
        $injectRoles = function (Laminas\Permissions\Rbac\Rbac $rbac, array $roles): void {
            $rbac->setCreateMissingRoles(true);

            // Roles and parents
            foreach ($roles as $role => $parents) {
                try {
                    $rbac->addRole($role, $parents);
                } catch (RbacExceptionInterface $e) {
                    throw new Exception\InvalidConfigException($e->getMessage(), $e->getCode(), $e);
                }
            }
        };
        $injectPermissions = function (Laminas\Permissions\Rbac\Rbac $rbac, array $specification): void {
            foreach ($specification as $role => $permissions) {
                foreach ($permissions as $permission) {
                    try {
                        $rbac->getRole($role)->addPermission($permission);
                    } catch (RbacExceptionInterface $e) {
                        throw new Exception\InvalidConfigException($e->getMessage(), $e->getCode(), $e);
                    }
                }
            }
        };

        $rbac = new Laminas\Permissions\Rbac\Rbac();
        $injectRoles($rbac, $config['roles']);
        $injectPermissions($rbac, $config['permissions']);
        
        $assertion = $container->has(LaminasRbacAssertionInterface::class)
            ? $container->get(LaminasRbacAssertionInterface::class)
            : null;

        return new \App\AuthorizationService($rbac, $assertion);
    };
    return $factory($container);
};
$container['authorization'] = [
    'roles' => [
        'administrator' => [],
        'user' => ['administrator'],
    ],
    'permissions' => [
        'user' => [
            'job.update',
        ],
        'administrator' => [
            'job.updateForm',
        ],
    ],
];

$container[Psr\EventDispatcher\EventDispatcherInterface::class] = function ($c) {
    return new App\EventDispatcher\EventDispatcher($c[Psr\EventDispatcher\ListenerProviderInterface::class]);
};
$container[Psr\EventDispatcher\ListenerProviderInterface::class] = function ($c) {
    $lp = new App\EventDispatcher\ListenerProvider();
    $lp->add(\App\Events\BeforeActionEvent::class, 'urlHelper.beforeAction', function ($event) use ($c) {
        $urlHelper = $c[\App\UrlHelper::class];
        $urlHelper->setRequest($event->request);
    });
    return $lp;
};
$container[\App\UrlHelper::class] = function ($c) {
    return new \App\UrlHelper($c['router']);
};

return new \Pimple\Psr11\Container($container);
