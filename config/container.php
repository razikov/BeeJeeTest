<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

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
$container['assetsPath'] = __DIR__.'/../assets/';

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
};

return new \Pimple\Psr11\Container($container);