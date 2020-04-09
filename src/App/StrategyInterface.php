<?php

namespace App;

use League\Route\Strategy\StrategyInterface as DefaultStrategyInterface;

interface StrategyInterface extends DefaultStrategyInterface
{
    public function isPrependThrowableDecorator(): bool;
}
