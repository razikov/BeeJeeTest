<?php

namespace App;

use Laminas\Diactoros\ServerRequest;
use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authentication\UserInterface;

class AccessHelper
{
    public $assertionProvider;
    protected $request;
    protected $user;
    // Для правил нужны Атрибуты ресурса(объекта), атрибуты субъекта(пользователя), атрибуты среды, атрибуты действия

    public function __construct(Rbac $rbac, $provider)
    {
        $this->assertionProvider = $provider;
        $this->rbac = $rbac;
    }
    
    public function setRequest(ServerRequest $request)
    {
        $this->request = $request;
    }
    
    public function isGranted(string $permission, string $assertionName = null, array $params = null)
    {
        $user = $this->request->getAttribute(UserInterface::class, false);
        
        if (!($user instanceof UserInterface)) {
            return false;
        }
        
        $assertion = null;
        if ($assertionName !== null) {
            $params['identity'] = $user;
            $assertion = $this->assertionProvider->get($assertionName, $params);
        }
        
        foreach ($user->getRoles() as $role) {
            // TODO: Роли: ? - гости, @ - аутентифицированные,
            if ($this->rbac->isGranted($role, $permission, $assertion)) {
                return true;
            }
        }
        return false;
    }
}
