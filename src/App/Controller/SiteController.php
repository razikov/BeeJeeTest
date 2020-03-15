<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SiteController extends BaseController
{
    
    public function loginAction(ServerRequestInterface $request) : ResponseInterface
    {
        $users = $this->container->get('adminUsers');
        $isPost = $request->getMethod() === 'POST';
        $segment = $this->getSession($request);
        
        $model = new \App\Models\LoginForm();
        
        if ($isPost && $model->load($request->getParsedBody()) && $model->validate($users)) {
            $segment->set('isAdmin', true);
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        } else {
            return $this->render('app/loginForm', [
                'model' => $model,
                'isAdmin' => $this->isAdmin,
            ]);
        }
        return new \Laminas\Diactoros\Response\RedirectResponse('/');
    }
    
    public function logoutAction(ServerRequestInterface $request) : ResponseInterface
    {
        $session = $request->getAttribute('session');
        $segment = $session->getSegment('jobController');
        $segment->set('isAdmin', null);
        
        return new \Laminas\Diactoros\Response\RedirectResponse('/');
    }
}