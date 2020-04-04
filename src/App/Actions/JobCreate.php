<?php

namespace App\Actions;

use App\Entity\Job;
use App\Models\JobForm;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobCreate extends BasicJobController
{
    public function action(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $model = new JobForm();
        $flashMessages = $request->getAttribute('flash');
        
        $model->load($request->getParsedBody());
        $job = new Job($model->getDto());
        $job->loadForm($model);
        if ($this->jobRepository->save($job)) {
            $flashMessages->flash('successMessage', 'Задача добавлена');
        } else {
            $flashMessages->flash('failMessage', 'Произошла ошибка. Задача не создана.');
        }
        return new RedirectResponse('/');
    }
}