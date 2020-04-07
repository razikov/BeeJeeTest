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

    public function __construct(Engine $engine, EventDispatcherInterface $dispatcher, JobService $jobService)
    {
        $this->jobServices = $jobService;
        parent::__construct($engine, $dispatcher);
    }
    
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $getParams = $request->getQueryParams();
        $pager = new Pagination(
            $this->jobServices->countAll(),
            $getParams
        );
        $sorter = new Sort($getParams);
        $jobs = $this->jobServices->getAll($pager, $sorter);
        
        return $this->render('app/index', [
            'jobs' => $jobs,
            'pager' => $pager,
            'q' => $getParams,
        ]);
    }
    
    public function createAction(ServerRequestInterface $request): ResponseInterface
    {
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
            'id' => null,
        ]);
    }
    
    public function updateAction(ServerRequestInterface $request): ResponseInterface
    {
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
            'id' => $id,
        ]);
    }
}
