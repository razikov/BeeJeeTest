<?php

$containerFactory = function ($config) {
    // services - глобальны, синглтоны, определяются анонимными функциями возвращающими экземпляр объекта
    // factoryServices - возвращает всегда новый объект $container->factory(...)
    // parameters - значения
    // protectedParameters - значения полученные из калбеков
    // получение определения сервиса $container->raw(...);
    $container = new \Pimple\Container();
    
    $resolve_callable = function ($callable) use ($container) {
        if (is_callable($callable)) {
            return $callable;
        }
        if (is_string($callable) && class_exists($callable) && method_exists($callable, '__invoke')) {
            return new $callable();
        }
        return false;
    };
    
    if (isset($config['services'])) {
        foreach ($config['services'] as $name => $callback) {
            $container[$name] = $resolve_callable($callback);
        }
    }
//    if (isset($config['factoryServices'])) {
//        foreach ($config['factoryServices'] as $name => $callback) {
//            $container[$name] = $container->factory($resolve_callable($callback));
//        }
//    }
    if (isset($config['parameters'])) {
        foreach ($config['parameters'] as $name => $value) {
            $container[$name] = $value;
        }
    }
//    if (isset($config['protectedParameters'])) {
//        foreach ($config['protectedParameters'] as $name => $callback) {
//            $container[$name] = $container->protect($callback());
//        }
//    }
    return $container;
};

$config  = require __DIR__ . '/config.php';
$container = $containerFactory($config);

return new \Pimple\Psr11\Container($container);
