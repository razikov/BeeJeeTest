<?php

namespace App;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class PlatesAccessExtension implements ExtensionInterface
{
    protected $engine;
    public $template;
    
    private $accessHelper;
    
    public function __construct(AccessHelper $accessHelper)
    {
        $this->accessHelper = $accessHelper;
    }
    
    public function register(Engine $engine)
    {
        $engine->registerFunction('isGranted', [$this, 'isGranted']);
    }

    public function isGranted(string $permission, string $assertionName = null, array $params = null)
    {
        return $this->accessHelper->isGranted($permission, $assertionName, $params);
    }
}
