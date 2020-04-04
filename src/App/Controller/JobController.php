<?php

namespace App\Controller;

use App\Models\JobForm;
use App\Models\Pagination;
use App\Controller\BaseJobController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends BaseJobController
{
    
    public function indexAction(ServerRequestInterface $request) : ResponseInterface
    {
        parent::beforeAction($request);
        $q = $request->getQueryParams();
        $page = $q['page'] ?? 0;
        $order = $q['sort'] ?? '';
        
        $pager = new Pagination(
            $this->jobRepository->countAll(),
            (int)$page ?: 1,
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
            'q' => $q,
        ]);
    }
    
    public function createAction(ServerRequestInterface $request) : ResponseInterface
    {
        parent::beforeAction($request);
        $isPost = $request->getMethod() === 'POST';
        $form = new JobForm();
        
        if ($isPost && $form->load($request->getParsedBody()) && $form->validate()) {
            $this->createJob($form);
            return $this->redirect('/');
        } else {
            return $this->render('app/form', [
                'model' => $form,
                'id' => null,
            ]);
        }
    }
    
    public function updateAction(ServerRequestInterface $request) : ResponseInterface
    {
        parent::beforeAction($request);
        $id = (int)$request->getAttribute('id');
        $isPost = $request->getMethod() === 'POST';
        
        $job = $this->getJob($id);
        $form = new JobForm($job->getDto());
        
        if ($isPost && $form->load($request->getParsedBody()) && $form->validate()) {
            $this->saveJob($form, $job);
            return $this->redirect('/');
        } else {
            return $this->render('app/form', [
                'model' => $form,
                'id' => $id,
            ]);
        }
    }
}