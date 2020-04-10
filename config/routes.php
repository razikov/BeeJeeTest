<?php

return function ($router, $c) {
    $router
        ->get('/', [App\Controllers\JobController::class, 'indexAction'])
        ->setName('jobList');
    $router
        ->map('GET', '/login', [App\Controllers\SiteController::class, 'loginAction'])
        ->setName('login');
    $router
        ->map('POST', '/login', [App\Controllers\SiteController::class, 'loginAction'])
        ->setName('loginForm')
        ->middleware($c->get(\App\Middlewares\CsrfMiddleware::class));
    $router
        ->map('GET', '/logout', [App\Controllers\SiteController::class, 'logoutAction'])
        ->setName('logout');
    $router
        ->group('/job', function ($route) use ($c) {
            $route
                ->map('GET', '/create', [App\Controllers\JobController::class, 'createAction'])
                ->setName('job.create');
            $route
                ->map('POST', '/create', [App\Controllers\JobController::class, 'createAction'])
                ->setName('job.createForm');
            $route
                ->map('GET', '/update/{id:number}', [App\Controllers\JobController::class, 'updateAction'])
                ->setName('job.update')
                ->middleware($c->get(\App\Middlewares\AuthorizationMiddleware::class));
            $route
                ->map('POST', '/update/{id:number}', [App\Controllers\JobController::class, 'updateAction'])
                ->setName('job.updateForm')
                ->middleware($c->get(\App\Middlewares\AuthorizationMiddleware::class));
        })
        ->middleware($c->get(\App\Middlewares\CsrfMiddleware::class));
    $router
        ->middlewares([
            $c->get(\Mezzio\Session\SessionMiddleware::class),
            $c->get(App\Middlewares\AuthenticationMiddleware::class),
            $c->get(\Mezzio\Flash\FlashMessageMiddleware::class),
        ]);
};
