<?php

namespace App\Services;

use Psr\EventDispatcher\EventDispatcherInterface;
use App\Models\JobRepository;
use App\Models\JobForm;
use App\Entity\Job;

class JobService
{
    private $jobs;
    private $dispatcher;
 
    public function __construct(JobRepository $jobs, EventDispatcherInterface $dispatcher)
    {
        $this->jobs = $jobs;
        $this->dispatcher = $dispatcher;
    }
    
    public function countAll()
    {
        return $this->jobs->countAll();
    }
    
    public function getAll($pager, $sorter)
    {
        return $this->jobs->all(
            $pager->getOffset(),
            $pager->getLimit(),
            $sorter
        );
    }
    
    public function getJob(int $id)
    {
        $job = $this->jobs->find($id);
        if (!$job) {
            throw new \DomainException("Задача не найдена");
        }
        return $job;
    }
 
    public function create(JobForm $form)
    {
        $job = new Job();
        $job->setName($form->name);
        $job->setEmail($form->email);
        $job->setContent($form->content);
        $job->setStatus($form->status);
        
        $result = $this->jobs->save($job);
//        $this->dispatcher->dispatch(new CreateJobEvent($job));
        return $result;
    }
 
    public function update(Job $job, JobForm $form)
    {
        $job->setName($form->name);
        $job->setEmail($form->email);
        $job->setContent($form->content);
        $job->setStatus($form->status);
        
        $result = $this->jobs->save($job);
//        $this->dispatcher->dispatch(new UpdateJobEvent($job));
        return $result;
    }
}
