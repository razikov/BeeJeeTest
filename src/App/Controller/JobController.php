<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends BaseController
{
    const PER_PAGE = 3;
    
    public function indexAction(ServerRequestInterface $request) : ResponseInterface
    {
        $segment = $this->getSession($request);
        $flashMsg = [
            'success' => $segment->getFlash('successMessage'),
            'fail' => $segment->getFlash('failMessage'),
        ];
        $q = $request->getQueryParams();
        $page = $q['page'] ?? 0;
        $order = $q['sort'] ?? '';
        
        $pager = new \App\Models\Pagination(
            $this->jobRepository->countAll(),
            (int)$page ?: 1,
            self::PER_PAGE,
            $q
        );
        
        $jobs = $this->jobRepository->all(
            $pager->getOffset(),
            $pager->getLimit(),
            $order
        );
        
        return $this->render('app/index', [
            'jobs' => $jobs,
            'pager' => $pager,
            'isAdmin' => $this->isAdmin,
            'flashMsg' => $flashMsg,
            'q' => $q,
        ]);
    }
    
    public function createAction(ServerRequestInterface $request) : ResponseInterface
    {
        $isPost = $request->getMethod() === 'POST';
        $segment = $this->getSession($request);
        
        $model = new \App\Models\JobForm();
        
        if ($isPost && $model->load($request->getParsedBody()) && $model->validate()) {
            $job = new \App\Entity\Job($model->getDto());
            $job->loadForm($model);
            if ($this->jobRepository->save($job)) {
                $segment->setFlash('successMessage', 'Задача добавлена');
            } else {
                $segment->setFlash('failMessage', 'Произошла ошибка. Задача не создана.');
            }
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        } else {
            return $this->render('app/form', [
                'model' => $model,
                'isAdmin' => $this->isAdmin,
                'isNew' => true,
                'url' => '/create'
            ]);
        }
    }
    
    public function updateAction(ServerRequestInterface $request, $args) : ResponseInterface
    {
        $id = (int)$args['id'] ?? null;
        $isPost = $request->getMethod() === 'POST';
        $segment = $this->getSession($request);
        
        if (!$this->isAdmin) {
            $segment->setFlash('failMessage', 'Операция доступна только администратору.');
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        }
        
        $job = $this->jobRepository->find($id);
        if (!$job) {
            throw new \League\Route\Http\Exception\NotFoundException("Задача не найдена");
        }
        $model = new \App\Models\JobForm($job->getDto());
        
        if ($isPost && $model->load($request->getParsedBody()) && $model->validate()) {
            $job->loadForm($model);
            if ($this->jobRepository->save($job)) {
                $segment->setFlash('successMessage', 'Задача сохранена');
            } else {
                $segment->setFlash('failMessage', 'Произошла ошибка. Задача не сохранена.');
            }
            return new \Laminas\Diactoros\Response\RedirectResponse('/');
        } else {
            return $this->render('app/form', [
                    'model' => $model,
                    'isAdmin' => $this->isAdmin,
                    'isNew' => false,
                    'url' => '/update/'.$id
                ]);
        }
    }
}