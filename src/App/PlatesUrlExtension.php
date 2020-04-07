<?php

namespace App;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PlatesUrlExtension implements ExtensionInterface
{
    protected $engine;
    public $template;
    
    private $urlHelper;
    
    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }
    
    public function register(Engine $engine)
    {
        $engine->registerFunction('url', [$this, 'url']);
        $engine->registerFunction('absoluteUrl', [$this, 'absoluteUrl']);
    }

    public function url($name, $params = [])
    {
        return $this->urlHelper->createUrl($name, $params);
    }
}
