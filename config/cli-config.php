<?php

$container = require_once "container.php";
$entityManager = $container->get('em');

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
