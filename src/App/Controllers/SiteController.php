<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LoginForm;
use App\Models\User;
use App\Models\UserManager;
use League\Plates\Engine;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SiteController extends BaseController
{
    protected $users;

    public function __construct(Engine $engine, EventDispatcherInterface $dispatcher, UserManager $users)
    {
        $this->users = $users;
        parent::__construct($engine, $dispatcher);
    }
    
    public function loginAction(ServerRequestInterface $request) : ResponseInterface
    {
        $model = new LoginForm($this->users);
        
        if ($model->load($request->getParsedBody()) && $model->validate()) {
            $this->session->set(User::KEY, $model->login);
            return $this->redirect('/');
        } else {
            return $this->render('app/loginForm', [
                'model' => $model,
                'isAdmin' => $this->isAdmin,
            ]);
        }
    }
    
    public function logoutAction(ServerRequestInterface $request) : ResponseInterface
    {
        $this->session->clear();
        
        return $this->redirect('/');
    }
}