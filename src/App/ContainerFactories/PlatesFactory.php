<?php

namespace App\ContainerFactories;

use App\AccessHelper;
use App\PlatesAccessExtension;
use App\PlatesUrlExtension;
use App\UrlHelper;
use League\Plates\Engine;
use League\Plates\Extension\Asset;

class PlatesFactory
{
    public function __invoke($container)
    {
        // TODO: регистрацию расширений вынести в приложение
        $asset = new Asset($container['assetsPath']);
        $url = new PlatesUrlExtension($container[UrlHelper::class]);
        $access = new PlatesAccessExtension($container[AccessHelper::class]);
        $template = new Engine($container['viewsPath']);
        $template->loadExtension($asset);
        $template->loadExtension($url);
        $template->loadExtension($access);
        return $template;
    }
}
