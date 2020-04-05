<?php

namespace App\Events;

class AfterActionEvent
{
    public $response;
    public $controller;
    
    public function __construct($response = null, $controller = null)
    {
        $this->response = $response;
        $this->controller = $controller;
    }
}
