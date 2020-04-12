<?php

return [
    'services' => [
        \App\Controllers\SiteController::class => \App\ContainerFactories\SiteControllerFactory::class,
        \App\Controllers\JobController::class => \App\ContainerFactories\JobControllerFactory::class,
        'em' => \App\ContainerFactories\EntityManagerFactory::class,
        \PDO::class => \App\ContainerFactories\PdoFactory::class,
        \Mezzio\Authorization\AuthorizationInterface::class => \App\ContainerFactories\AuthorizationServiceFactory::class,
        Laminas\Permissions\Rbac\Rbac::class => App\ContainerFactories\RbacFactory::class,
        \League\Plates\Engine::class => \App\ContainerFactories\PlatesFactory::class,
        \App\Models\JobRepository::class => \App\ContainerFactories\JobRepositoryFactory::class,
        \App\Services\JobService::class => \App\ContainerFactories\JobServiceFactory::class,
        \Mezzio\Authentication\UserRepositoryInterface::class => function ($container) {
            return $container[\App\Models\UserRepository::class];
        },
        \App\Models\UserRepository::class => function ($container) {
            return new \App\Models\UserRepository($container['adminUsers']);
        },
        Mezzio\Authentication\UserInterface::class => function ($c) {
            $container = new \Pimple\Psr11\Container($c);
            $factory = new Mezzio\Authentication\DefaultUserFactory();
            return $factory($container);
        },
        Mezzio\Authentication\Session\PhpSession::class => function ($container) {
            return new Mezzio\Authentication\Session\PhpSession(
                $container[Mezzio\Authentication\UserRepositoryInterface::class],
                $container['authentication'],
                $container[Psr\Http\Message\ResponseInterface::class],
                $container[Mezzio\Authentication\UserInterface::class]
            );
        },
        Psr\Http\Message\ResponseInterface::class => function ($c) {
            return function (): Laminas\Diactoros\Response {
                return new Laminas\Diactoros\Response();
            };
        },
        Mezzio\Csrf\SessionCsrfGuardFactory::class => function ($c) {
            return new Mezzio\Csrf\SessionCsrfGuardFactory();
        },
        App\Middlewares\AuthenticationMiddleware::class => function ($c) {
            return new App\Middlewares\AuthenticationMiddleware($c[Mezzio\Authentication\Session\PhpSession::class]);
        },
        Psr\EventDispatcher\EventDispatcherInterface::class => function ($c) {
            return new App\EventDispatcher\EventDispatcher($c[Psr\EventDispatcher\ListenerProviderInterface::class]);
        },
        Psr\EventDispatcher\ListenerProviderInterface::class => function ($c) {
            $lp = new App\EventDispatcher\ListenerProvider();
            $lp->add(\App\Events\BeforeActionEvent::class, 'urlHelper.beforeAction', function ($event) use ($c) {
                $urlHelper = $c[\App\UrlHelper::class];
                $urlHelper->setRequest($event->request);
            });
            $lp->add(\App\Events\BeforeActionEvent::class, 'accessHelper.beforeAction', function ($event) use ($c) {
                $helper = $c[\App\AccessHelper::class];
                $helper->setRequest($event->request);
            });
            return $lp;
        },
        \App\UrlHelper::class => function ($c) {
            return new \App\UrlHelper($c['router']);
        },
        \Mezzio\Flash\FlashMessageMiddleware::class => function ($c) {
            return new \Mezzio\Flash\FlashMessageMiddleware();
        },
        \Mezzio\Session\SessionMiddleware::class => function ($c) {
            return new \Mezzio\Session\SessionMiddleware(new \Mezzio\Session\Ext\PhpSessionPersistence());
        },
        \App\Middlewares\CsrfMiddleware::class => function ($c) {
            return new \App\Middlewares\CsrfMiddleware($c[Mezzio\Csrf\SessionCsrfGuardFactory::class], $c['csrfAttribute']);
        },
        \App\Middlewares\AuthorizationMiddleware::class => function ($c) {
            return new App\Middlewares\AuthorizationMiddleware(
                $c[\Laminas\Permissions\Rbac\Rbac::class],
                $c[Psr\Http\Message\ResponseInterface::class]
            );
        },
        'router' => function ($c) {
            $psrContainer = new Pimple\Psr11\Container($c);
            $strategy = (new App\ApplicationStrategy())->setContainer($psrContainer);
            $router = (new App\Router())->setStrategy($strategy);
            return $router;
        },
        \Psr\Log\LoggerInterface::class => function ($c) {
            $log = new \Monolog\Logger('name');
            $log->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../runtime/application_warning.log', \Monolog\Logger::WARNING));
            return $log;
        },
        \App\AccessHelper::class => function ($c) {
            return new \App\AccessHelper($c[\Laminas\Permissions\Rbac\Rbac::class], $c[\App\Rbac\AssertionProvider::class]);
        },
        \App\Rbac\AssertionProvider::class => function ($c) {
            $provider = new \App\Rbac\AssertionProvider([
                App\Rbac\OwnerAssertion::class => function ($params) {
                    if (isset($params['identity']) && isset($params['model'])) {
                        return new App\Rbac\OwnerAssertion($params['identity'], $params['model']);
                    } else {
                        return null;
                    }
                },
            ]);
            return $provider;
        },
    ],
    'parameters' => [
        'dbParams' => [
            'driver'   => 'pdo_mysql',
            'host'     => getenv('DB_HOST'),
            'dbname'   => getenv('DB_NAME'),
            'user'     => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
        ],
        'adminUsers' => [
            getenv('ADMIN_LOGIN') => getenv('ADMIN_PASSWORD')
        ],
        'entityPath' => [__DIR__ . "/../src/App/Entity"],
        'viewsPath' => __DIR__ . '/../src/App/Views',
        'assetsPath' => __DIR__ . '/../public/',
        'authorization' => require __DIR__ . '/rbac.php',
        'csrfAttribute' => '__csrf',
        'authentication' =>  [
            'username' => 'login',
            'password' => 'password',
            'redirect' => '/login',
        ],
    ],
];
