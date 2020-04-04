<?php
chdir(dirname(__DIR__));
require_once __DIR__.'/../vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

require_once __DIR__.'/../src/Application.php';
$container = require_once __DIR__.'/../config/container.php';

(new app\Application($container))->run();
