<?php

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title></title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="<?=$this->asset('/css/bootstrap.min.css')?>">
        <link rel="stylesheet" href="<?=$this->asset('/css/app.css')?>">
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    </head>
    <body class="">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
            <div class="container d-flex justify-content-between">
                <a class="navbar-brand" href="/">Трекер задач</a>
                <?php if ($isAdmin) : ?>
                    <a href="/logout" class="d-flex align-right btn btn-light">Выйти</a>
                <?php else : ?>
                    <a href="/login" class="d-flex align-right btn btn-light">Войти</a>
                <?php endif; ?>
            </div>
        </nav>
        <main role="main" class="container">
            <?php if (isset($flashes['success'])) : ?>
                <div class="alert alert-success" role="alert">
                    <?= $flashes['success'] ?>
                </div>
            <?php endif; ?>
            <?php if (isset($flashes['error'])) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= $flashes['error'] ?>
                </div>
            <?php endif; ?>
            <div class="">
                <?= $this->section('content') ?>
            </div>
        </main>
        <script src="<?=$this->asset('/js/jquery.min.js')?>"></script>
        <script src="<?=$this->asset('/js/bootstrap.min.js')?>"></script>
        <script src="<?=$this->asset('/js/app.js')?>"></script>
        <?= $this->section('scripts') ?>
    </body>
</html>
