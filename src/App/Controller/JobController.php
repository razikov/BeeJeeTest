<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController
{
    const PER_PAGE = 3;
    
    protected $container;
    protected $templateRenderer;
    protected $jobRepository;
    protected $isAdmin = false;


    public function __construct(\League\Plates\Engine $templateRenderer, \App\Models\JobRepository $jobRepository, $container)
    {
        $this->container = $container;
        $this->templateRenderer = $templateRenderer;
        $this->jobRepository = $jobRepository;
    }
    
    public function indexAction(ServerRequestInterface $request) : ResponseInterface
    {
        $session = $request->getAttribute('session');
        $segment = $session->getSegment('jobController');
        $isAdmin = $segment->get('isAdmin');
        $successMessage = $segment->getFlash('successMessage');
        $failMessage = $segment->getFlash('failMessage');
        
        $q = $request->getQueryParams();
        $page = $q['page'] ?? 0;
        $pager = new \App\Models\Pagination(
            $this->jobRepository->countAll(),
            (int)$page ?: 1,
            self::PER_PAGE,
            $q
        );
        $order = $q['sort'] ?? '';
        
        $jobs = $this->jobRepository->all(
            $pager->getOffset(),
            $pager->getLimit(),
            $order
        );
        
        return $this->render('app/index', [
            'jobs' => $jobs,
            'pager' => $pager,
            'isAdmin' => $isAdmin,
            'successMessage' => $successMessage,
            'failMessage' => $failMessage,
            'q' => $q,
        ]);
    }
    
    public function createAction(ServerRequestInterface $request) : ResponseInterface
    {
        $isPost = $request->getMethod() === 'POST';
        $session = $request->getAttribute('session');
        $segment = $session->getSegment('jobController');
        $isAdmin = $segment->get('isAdmin');
        
        $model = new \App\Models\JobForm();
        
        if ($isPost && $model->load($request->getParsedBody()) && $model->validate()) {
            $job = new \App\Entity\Job($model->getDto());
            $job->loadForm($model, true);
            if ($this->jobRepository->save($job)) {
                $segment->setFlash('successMessage', 'Задача добавлена');
            } else {
                $segment->setFlash('failMessage', 'Произошла ошибка. Задача не создана.');
            }
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        } else {
            return $this->render('app/form', [
                    'model' => $model,
                    'isAdmin' => $isAdmin,
                    'isNew' => true,
                    'url' => '/create'
                ]);
        }
    }
    
    public function updateAction(ServerRequestInterface $request, $args) : ResponseInterface
    {
        $id = (int)$args['id'] ?? null;
        $isPost = $request->getMethod() === 'POST';
        $session = $request->getAttribute('session');
        $segment = $session->getSegment('jobController');
        $isAdmin = $segment->get('isAdmin');
        
        if (!$isAdmin) {
            $segment->setFlash('failMessage', 'Операция доступна только администратору.');
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        }
        
        $job = $this->jobRepository->find($id);
        $model = new \App\Models\JobForm($job->getDto());
        
        if (!$job) {
            $segment->setFlash('failMessage', 'Не найдена задача.');
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        }
        
        if ($isPost && $model->load($request->getParsedBody()) && $model->validate()) {
            $job->loadForm($model, false);
            if ($this->jobRepository->save($job)) {
                $segment->setFlash('successMessage', 'Задача сохранена');
            } else {
                $segment->setFlash('failMessage', 'Произошла ошибка. Задача не сохранена.');
            }
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        } else {
            return $this->render('app/form', [
                    'model' => $model,
                    'isAdmin' => $isAdmin,
                    'isNew' => false,
                    'url' => '/update/'.$id
                ]);
        }
    }
    
    public function loginAction(ServerRequestInterface $request) : ResponseInterface
    {
        $users = [
            getenv('ADMIN_LOGIN') => getenv('ADMIN_PASSWORD')
        ];
        
        $isPost = $request->getMethod() === 'POST';
        $session = $request->getAttribute('session');
        $segment = $session->getSegment('jobController');
        $isAdmin = $segment->get('isAdmin');
        
        $model = new \App\Models\LoginForm();
        
        if ($isPost && $model->load($request->getParsedBody()) && $model->validate($users)) {
            $segment->set('isAdmin', true);
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        } else {
            return $this->render('app/loginForm', [
                'model' => $model,
                'isAdmin' => $isAdmin,
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
    
    protected function render($view, $params = [])
    {
        $response = new \Laminas\Diactoros\Response();
        $response->getBody()
                ->write($this->templateRenderer->render($view, $params));
        return $response->withStatus(200);
    }
    
    protected function renderJson($data = [])
    {
        $response = new \Laminas\Diactoros\Response();
        $response->getBody()->write(json_encode($data));
        $response->withAddedHeader('content-type', 'application/json')->withStatus(200);
        return $response;
    }
    
}