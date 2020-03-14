<?php 
$this->layout('layout/main', ['isAdmin' => $isAdmin]);

$hasErors = function($model) {
    if ($model->isLoad && !$model->isValid) {
        return true;
    }
    return false;
};
?>

<div class="text-center">
    <form method="POST" action="/login" class="form-signin">
        <h1 class="h5 mb-3 font-weight-normal">Пожайлуйста авторизуйтесь</h1>
        <label for="login" class="sr-only">Логин</label>
        <input type="text" 
               name="login" 
               class="form-control" 
               placeholder="Логин" 
               required="" 
               autofocus=""
               value="<?= $model->login ?>">
        <label for="password" class="sr-only">Пароль</label>
        <input type="password" 
               name="password" 
               class="form-control" 
               placeholder="Пароль" 
               required="">
        <?php if ($hasErors($model)): ?>
            <div class="text-danger">
                Пара логин/пароль некорректна! Попробуйте снова.
            </div>
        <?php endif; ?>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
    </form>
</div>