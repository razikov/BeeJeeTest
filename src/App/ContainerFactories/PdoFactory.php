<?php

namespace App\ContainerFactories;

use PDO;

use function sprintf;

class PdoFactory
{
    public function __invoke($container)
    {
        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s', $container['dbParams']['host'], $container['dbParams']['dbname']),
            $container['dbParams']['user'],
            $container['dbParams']['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
        return $pdo;
    }
}
