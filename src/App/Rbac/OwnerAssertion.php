<?php

namespace App\Rbac;

use Laminas\Permissions\Rbac\AssertionInterface;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\RoleInterface;

class OwnerAssertion implements AssertionInterface
{
    protected $identity;
    protected $model;
    
    // Обеспечить интерфейсы, иначе возможны падения
    public function __construct($identity, $model)
    {
        $this->identity = $identity;
        $this->model = $model;
    }
    
    public function assert(Rbac $rbac, RoleInterface $role, string $permission): bool
    {
        return $this->identity->getId() === $this->model->getCreatorId();
    }
}
