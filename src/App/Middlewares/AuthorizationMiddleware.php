<?php

namespace App\Middlewares;

use App\Controllers\BaseController;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authentication\UserInterface;
use App\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(Rbac $authorization, callable $responseFactory)
    {
        $this->rbac = $authorization;

        // Ensures type safety of the composed factory
        $this->responseFactory = function () use ($responseFactory): ResponseInterface {
            return $responseFactory();
        };
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $flashMessages = $request->getAttribute('flash');
        $user = $request->getAttribute(UserInterface::class, false);
        if (!$user instanceof UserInterface) {
            $flashMessages->flash('error', 'Действие доступно только авторизованым пользователям.');
            $request->getAttribute('session')->set(BaseController::REDIRECT_ATTRIBUTE, $request->getUri()->getPath());
            return new RedirectResponse('/login');
//            return ($this->responseFactory)()->withStatus(401); // unauthorized
        }

        $routeResult = $request->getAttribute(RouteResult::class, false);
        // No matching route. Everyone can access.
        if ($routeResult->isFailed()) {
            return $handler->handle($request);
        }
        foreach ($user->getRoles() as $role) {
            // TODO: Роли: ? - гости, @ - аутентифицированные,
            $routeName = $routeResult->getMatchedRouteName();
            if ($this->rbac->isGranted($role, $routeName, null)) {
                return $handler->handle($request);
            }
        }
        $flashMessages->flash('error', 'Вам запрещено это действие.');
        return new RedirectResponse('/');
//        return ($this->responseFactory)()->withStatus(403); // forbidden
    }
}
