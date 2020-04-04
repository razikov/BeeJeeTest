<?php

namespace App\Actions;

use App\Models\JobForm as Form;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobUpdateForm extends BasicJobController
{
    public function action(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $id = intval($args['id']) ?? intval($request->getAttribute('id') ?? 0);
        $job = $this->getModel($id);
        $oldData = $request->getAttribute('oldData');
        $errors = $request->getAttribute('errors');
        $formModel = new Form();
        $formModel->load($oldData ?? $job->getDto());
        return $this->render('app/jobForm', [
            'model' => $formModel,
            'errors' => $errors,
            'id' => $id,
        ]);
    }
}
