<?php

namespace App\Actions;

use App\Models\JobForm as Form;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JobCreateForm extends BasicJobController
{
    public function action(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $oldData = $request->getAttribute('oldData');
        $errors = $request->getAttribute('errors');
        $model = new Form();
        if ($oldData !== null) {
            $model->load($oldData);
        }
        return $this->render('app/jobForm', [
            'model' => $model,
            'errors' => $errors,
            'id' => null,
        ]);
    }
}
