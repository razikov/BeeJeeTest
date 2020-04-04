<?php

namespace App\Actions;

use App\Models\JobRepository;
use League\Plates\Engine;
use League\Route\Http\Exception\NotFoundException;

abstract class BasicJobController extends BasicRenderController
{
    /**
     * @var JobRepository
     */
    protected $jobRepository;
    
    public function __construct(Engine $templateRenderer, JobRepository $jobRepository)
    {
        parent::__construct($templateRenderer);
        $this->jobRepository = $jobRepository;
    }
    
    /**
     * Get model by ID
     * @param int $id
     * @return JobForm
     * @throws NotFoundException
     */
    protected function getModel(int $id)
    {
        $job = $this->jobRepository->find($id);
        if (!$job) {
            throw new NotFoundException("Задача не найдена");
        }
        return $job;
    }
}