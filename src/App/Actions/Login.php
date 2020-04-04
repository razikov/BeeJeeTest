<?php

namespace App\Actions;

use App\Models\User;
use App\Models\UserManager;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Login
{
    private $_userManager;

    public function __construct(UserManager $userManager)
    {
        $this->_userManager = $userManager;
    }
    
    public function __invoke(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $session = $request->getAttribute('session');
        $args = $request->getParsedBody();
        $user = $this->_userManager->findUser($args['login']);
        if ($user !== null && $this->_userManager->validatePassword($user, $args['password'])) {
            $session->set(User::KEY, $args['login']);
            return new RedirectResponse('/');
        }
        $errors = $session->set('errors', ['login' => 'Invalid login or password']);
        $oldData = $session->set('oldData', $args);
        return new RedirectResponse('/login');
    }
}
