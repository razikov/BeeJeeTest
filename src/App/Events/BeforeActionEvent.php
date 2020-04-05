<?php

namespace App\Events;

class BeforeActionEvent
{
    public $request;
    public $controller;
    
    public function __construct($request = null, $controller = null)
    {
        $this->request = $request;
        $this->controller = $controller;
    }
}
