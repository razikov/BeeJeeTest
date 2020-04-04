<?php

namespace App\Controller;

use League\Route\Http\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use App\Entity\Job;
use App\Models\JobRepository;
use App\Controller\BaseController;

class BaseJobController extends BaseController
{
    protected $jobRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->jobRepository = $container->get(JobRepository::class);
    }
    
    protected function createJob($form)
    {
        $job = new Job();
        $job->loadForm($form);
        if ($this->jobRepository->save($job)) {
            $this->flash('successMessage', 'Задача создана');
            return true;
        } else {
            $this->flash('failMessage', 'Произошла ошибка. Задача не создана.');
            return false;
        }
    }
    
    protected function saveJob($form, $job)
    {
        $job->loadForm($form);
        if ($this->jobRepository->save($job)) {
            $this->flash('successMessage', 'Задача сохранена');
            return true;
        } else {
            $this->flash('failMessage', 'Произошла ошибка. Задача не сохранена.');
            return false;
        }
    }
    
    protected function getJob($id)
    {
        $job = $this->jobRepository->find($id);
        if (!$job) {
            throw new NotFoundException("Задача не найдена");
        }
        return $job;
    }
    
}