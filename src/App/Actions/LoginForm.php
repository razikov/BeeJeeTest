<?php

namespace App\Actions;

use App\Models\LoginForm as Form;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginForm extends BasicRenderController
{
    public function action(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $errors = $request->getAttribute('errors');
        $oldData = $request->getAttribute('oldData');
        $model = new Form($oldData !== null ? $oldData : []);
        return $this->render('app/loginForm', [
            'model' => $model,
            'errors' => $errors,
        ]);
    }
}
