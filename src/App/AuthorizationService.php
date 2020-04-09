<?php

namespace App;

use Laminas\Permissions\Rbac\AssertionInterface;
use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Exception;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationService implements AuthorizationInterface
{
    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * @var null|AssertionInterface
     */
    private $assertion;

    public function __construct(Rbac $rbac, LaminasRbacAssertionInterface $assertion = null)
    {
        $this->rbac = $rbac;
        $this->assertion = $assertion;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function isGranted(string $role, ServerRequestInterface $request): bool
    {
        $routeResult = $request->getAttribute(RouteResult::class, false);

        // No matching route. Everyone can access.
        if (!$routeResult->isSuccess()) {
            return true;
        }

        $routeName = $routeResult->getMatchedRouteName();
        if (null !== $this->assertion) {
            $this->assertion->setRequest($request);
        }
        return $this->rbac->isGranted($role, $routeName, $this->assertion);
    }
}
