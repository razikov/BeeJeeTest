<?php

namespace App\ContainerFactories;

use App\Controllers\JobController;
use App\Services\JobService;
use League\Plates\Engine;
use Psr\EventDispatcher\EventDispatcherInterface;

class JobControllerFactory
{
    public function __invoke($container)
    {
        return new JobController(
            $container[Engine::class],
            $container[EventDispatcherInterface::class],
            $container[JobService::class],
            $container[\App\AccessHelper::class]
        );
    }
}
