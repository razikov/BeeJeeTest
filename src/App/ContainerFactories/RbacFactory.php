<?php

namespace App\ContainerFactories;

use Mezzio\Authorization\Exception\InvalidConfigException;
use Laminas\Permissions\Rbac\Exception\ExceptionInterface as RbacExceptionInterface;
use Laminas\Permissions\Rbac\Rbac;

class RbacFactory
{
    public function __invoke($container)
    {
        $config = $container['authorization'] ?? null;
        $injectRoles = function (Rbac $rbac, array $roles): void {
            $rbac->setCreateMissingRoles(true);

            // Roles and parents
            foreach ($roles as $role => $parents) {
                try {
                    $rbac->addRole($role, $parents);
                } catch (RbacExceptionInterface $e) {
                    throw new InvalidConfigException($e->getMessage(), $e->getCode(), $e);
                }
            }
        };
        $injectPermissions = function (Rbac $rbac, array $specification): void {
            foreach ($specification as $role => $permissions) {
                foreach ($permissions as $permission) {
                    try {
                        $rbac->getRole($role)->addPermission($permission);
                    } catch (RbacExceptionInterface $e) {
                        throw new InvalidConfigException($e->getMessage(), $e->getCode(), $e);
                    }
                }
            }
        };

        $rbac = new Rbac();
        $injectRoles($rbac, $config['roles']);
        $injectPermissions($rbac, $config['permissions']);
        return $rbac;
    }
}
