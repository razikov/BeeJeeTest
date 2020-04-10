<?php

namespace App\ContainerFactories;

use App\Controllers\SiteController;
use App\Models\UserRepository;
use League\Plates\Engine;
use Mezzio\Authentication\Session\PhpSession;
use Psr\EventDispatcher\EventDispatcherInterface;

class SiteControllerFactory
{
    public function __invoke($container)
    {
        return new SiteController(
            $container[Engine::class],
            $container[EventDispatcherInterface::class],
            $container[UserRepository::class],
            $container[PhpSession::class],
        );
    }
}
