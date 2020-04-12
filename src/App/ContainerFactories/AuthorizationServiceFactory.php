<?php

namespace App\ContainerFactories;

use App\AuthorizationService;
use Laminas\Permissions\Rbac\Rbac;

class AuthorizationServiceFactory
{
    public function __invoke($container)
    {
        $factory = function ($container) {
            $rbac = $container[Rbac::class];
            $assertion = null;
            return new AuthorizationService($rbac, $assertion);
        };
        return $factory($container);
    }
}
