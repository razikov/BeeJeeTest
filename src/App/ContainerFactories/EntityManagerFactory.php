<?php

namespace App\ContainerFactories;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class EntityManagerFactory
{
    public function __invoke($container)
    {
        $isDevMode = true;
        $proxyDir = null;
        $cache = null;
        $useSimpleAnnotationReader = false;
        $paths = $container['entityPath'];
        $annotationConfig = Setup::createAnnotationMetadataConfiguration(
            $paths,
            $isDevMode,
            $proxyDir,
            $cache,
            $useSimpleAnnotationReader
        );
        return EntityManager::create($container['dbParams'], $annotationConfig);
    }
}
