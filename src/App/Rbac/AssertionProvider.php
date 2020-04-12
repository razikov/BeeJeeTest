<?php

namespace App\Rbac;

class AssertionProvider
{
    protected $assertions;
    
    public function __construct($assertions)
    {
        $this->assertions = $assertions;
    }
    
    public function has($assertion)
    {
        return isset($this->assertions[$assertion]);
    }
    
    public function set(string $assertion, $assertionFactory)
    {
        $this->assertions[$assertion] = $assertionFactory;
    }
    
    public function get(string $assertion, array $params)
    {
        if ($this->has($assertion)) {
            return $this->build($this->assertions[$assertion], $params);
        } else {
            return null;
        }
    }
    
    protected function build($factory, $params)
    {
        return $factory($params);
    }
}
