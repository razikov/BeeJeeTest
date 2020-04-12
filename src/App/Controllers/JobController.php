<?php

namespace App\Controllers;

use App\Models\JobForm;
use App\Models\Pagination;
use App\Models\Sort;
use App\Services\JobService;
use App\Controllers\BaseController;
use DomainException;
use League\Plates\Engine;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobController extends BaseController
{
    protected $jobServices;
    protected $access;

    public function __construct(Engine $engine, EventDispatcherInterface $dispatcher, JobService $jobService, \App\AccessHelper $access)
    {
        $this->jobServices = $jobService;
        $this->access = $access;
        parent::__construct($engine, $dispatcher);
    }
    
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $getParams = $request->getQueryParams();
        $pager = new Pagination(
            $this->jobServices->countAll(),
            $getParams,
            'jobList'
        );
        $sorter = new Sort($getParams);
        $jobs = $this->jobServices->getAll($pager, $sorter);
                
        return $this->render('app/index', [
            'jobs' => $jobs,
            'pager' => $pager,
            'sorter' => $sorter,
        ]);
    }
    
    public function createAction(ServerRequestInterface $request): ResponseInterface
    {
        $csrfGuard = $request->getAttribute('__csrf');
        $form = new JobForm();
        
        if ($form->load($request->getParsedBody()) && $form->validate()) {
            try {
                $this->jobServices->create($form);
                $this->setFlash('success', 'Задача создана.');
                return $this->redirect('/');
            } catch (DomainException $e) {
//                $this->logException($e);
                $this->setFlash('error', $e->getMessage());
            }
        }
        
        return $this->render('app/form', [
            'model' => $form,
            '__csrf' => $csrfGuard->generateToken(),
            'id' => null,
        ]);
    }
    
    public function updateAction(ServerRequestInterface $request): ResponseInterface
    {
        $csrfGuard = $request->getAttribute('__csrf');
        $id = (int)$request->getAttribute('id');
        $job = $this->jobServices->getJob($id);
        $form = new JobForm($job->getDto());
        
        if ($form->load($request->getParsedBody()) && $form->validate()) {
            try {
                $this->jobServices->update($job, $form);
                $this->setFlash('success', 'Задача сохранена.');
                return $this->redirect('/');
            } catch (DomainException $e) {
//                $this->logException($e);
                $this->setFlash('error', $e->getMessage());
            }
        }
        
        return $this->render('app/form', [
            'model' => $form,
            '__csrf' => $csrfGuard->generateToken(),
            'id' => $id,
        ]);
    }
}
