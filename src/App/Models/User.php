<?php

namespace App\Models;

class User
{
    public const KEY = 'username';
    
    private $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
}