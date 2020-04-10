<?php

namespace App\ContainerFactories;

use App\Models\JobRepository;
use App\Services\JobService;
use Psr\EventDispatcher\EventDispatcherInterface;

class JobServiceFactory
{
    public function __invoke($container)
    {
        return new JobService(
            $container[JobRepository::class],
            $container[EventDispatcherInterface::class]
        );
    }
}
