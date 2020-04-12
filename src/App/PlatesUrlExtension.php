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
        $engine->registerFunction('currentUrl', [$this, 'currentUrl']);
        $engine->registerFunction('currentAbsoluteUrl', [$this, 'currentAbsoluteUrl']);
        $engine->registerFunction('modifyCurrentUrl', [$this, 'modifyCurrentUrl']);
        $engine->registerFunction('modifyCurrentAbsoluteUrl', [$this, 'modifyCurrentAbsoluteUrl']);
    }

    public function url($name, $params = [])
    {
        return $this->urlHelper->createUrl($name, $params);
    }

    public function absoluteUrl($name, $params = [])
    {
        return $this->urlHelper->createUrl($name, $params, true);
    }

    public function currentUrl()
    {
        return $this->urlHelper->modifyCurrentUrl('');
    }

    public function currentAbsoluteUrl()
    {
        return $this->urlHelper->modifyCurrentUrl('', true);
    }

    public function modifyCurrentUrl(string $query)
    {
        return $this->urlHelper->modifyCurrentUrl($query);
    }

    public function modifyCurrentAbsoluteUrl(string $query)
    {
        return $this->urlHelper->modifyCurrentUrl($query, true);
    }
}
