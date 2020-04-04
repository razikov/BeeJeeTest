<?php

namespace App\Actions;

use App\Models\JobForm;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobUpdate extends BasicJobController
{
    public function action(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $id = intval($args['id']) ?? intval($request->getAttribute('id') ?? 0);
        $flashMessages = $request->getAttribute('flash');
        
        $job = $this->getModel($id);
        $job->loadForm(new JobForm($request->getParsedBody()));
        
        if ($this->jobRepository->save($job)) {
            $flashMessages->flash('successMessage', 'Задача сохранена');
        } else {
            $flashMessages->flash('failMessage', 'Произошла ошибка. Задача не сохранена.');
        }
        
        return new RedirectResponse('/');
    }
}
