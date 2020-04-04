<?php

namespace App\Controller;

use App\Models\LoginForm;
use App\Models\User;
use App\Controller\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SiteController extends BaseController
{
    
    public function loginAction(ServerRequestInterface $request) : ResponseInterface
    {
        parent::beforeAction($request);
        $users = $this->container->get('adminUsers');
        $isPost = $request->getMethod() === 'POST';
        
        $model = new LoginForm();
        
        if ($isPost && $model->load($request->getParsedBody()) && $model->validate($users)) {
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
        parent::beforeAction($request);
        $this->session->clear();
        
        return $this->redirect('/');
    }
}