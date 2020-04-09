<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LoginForm;
use App\Models\UserRepository;
use League\Plates\Engine;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SiteController extends BaseController
{
    protected $users;
    protected $auth;

    public function __construct(Engine $engine, EventDispatcherInterface $dispatcher, UserRepository $users, AuthenticationInterface $auth)
    {
        $this->users = $users;
        $this->auth = $auth;
        parent::__construct($engine, $dispatcher);
    }
    
    public function loginAction(ServerRequestInterface $request): ResponseInterface
    {
        $model = new LoginForm($this->users);
        
        if ($model->load($request->getParsedBody()) && $model->validate()) {
            $this->auth->authenticate($request);
            $defaultRedirect = '/';
            $redirect = $this->session->get(self::REDIRECT_ATTRIBUTE, $defaultRedirect);
            if (strpos($redirect, '/login') !== false) {
                $redirect = $defaultRedirect;
            }
            $this->session->unset(self::REDIRECT_ATTRIBUTE);
            return $this->redirect($redirect);
        }
        
        return $this->render('app/loginForm', [
            'model' => $model,
        ]);
    }
    
    public function logoutAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->clear();
        
        return $this->redirect('/');
    }
}
