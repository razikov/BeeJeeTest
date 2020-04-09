<?php

namespace App\Middlewares;

use App\Controllers\BaseController;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Authorization\AuthorizationInterface;

class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(AuthorizationInterface $authorization, callable $responseFactory)
    {
        $this->authorization = $authorization;

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

        foreach ($user->getRoles() as $role) {
            // TODO: Роли: ? - гости, @ - аутентифицированные,
            if ($this->authorization->isGranted($role, $request)) {
                return $handler->handle($request);
            }
        }
        $flashMessages->flash('error', 'Вам запрещено это действие.');
        return new RedirectResponse('/');
//        return ($this->responseFactory)()->withStatus(403); // forbidden
    }
}
