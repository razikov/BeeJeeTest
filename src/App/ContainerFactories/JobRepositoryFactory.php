<?php

namespace App\ContainerFactories;

use App\Models\JobRepository;
use PDO;

class JobRepositoryFactory
{
    public function __invoke($container)
    {
        $repository = new JobRepository(
            $container[PDO::class],
            $container['em']
        );
        return $repository;
    }
}
