<?php
$this->layout('layout/main', ['isAdmin' => $isAdmin]);

$hasErrors = function (string $attribute) use ($errors) {
    return isset($errors[$attribute]);
};


$getValidCssClass = function (string $attribute) use ($hasErrors, $model) {
    if ($model->isLoad != true) {
        return '';
    }
    return $hasErrors($attribute) ? 'is-invalid' : 'is-valid';
};

$getErrors = function (string $attribute) use ($errors, $hasErrors) {
    $msg = '';
    if (!$hasErrors($attribute)) {
        return $msg;
    }
    foreach ($errors[$attribute] as $error) {
        $msg .= sprintf("<li>%s</li>", $error);
    }
    return sprintf('<div class="invalid-feedback"><ul>%s</ul></div>', $msg);
}
?>

<form method="POST" action="<?= $id ? "/update/$id" : '/create' ?>">
    <div class="form-group row">
        <label for="name" class="col-sm-3 col-form-label text-right">Имя:</label>
        <div class="col-sm-9">
            <input type="text" 
                class="form-control <?= $getValidCssClass('name') ?>" 
                name="name" 
                value="<?= $model->name ?>">
            <?= $getErrors('name') ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="email" class="col-sm-3 col-form-label text-right">E-mail:</label>
        <div class="col-sm-9">
            <input type="text" 
                class="form-control <?= $getValidCssClass('email') ?>" 
                name="email"
                value="<?= $model->email ?>">
            <?= $getErrors('email') ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="content" class="col-sm-3 col-form-label text-right">Текст задачи:</label>
        <div class="col-sm-9">
            <textarea class="form-control <?= $getValidCssClass('content') ?>" 
                name="content"><?= $model->content ?></textarea>
            <?= $getErrors('content') ?>
        </div>
    </div>
    <?php if ($isAdmin) : ?>
        <div class="form-group row">
            <label for="status" class="col-sm-3 col-form-label text-right">Статус:</label>
            <div class="col-sm-9">
                <select class="form-control <?= $getValidCssClass('status') ?>" 
                    name="status"
                    value="<?= $model->status ?>">
                    <option value="0"<?= $model->status == false ? ' selected' : '' ?>>В работе</option>
                    <option value="1"<?= $model->status == true ? ' selected' : '' ?>>Выполнена</option>
                </select>
                <?= $getErrors('status') ?>
            </div>
            
        </div>
    <?php endif; ?>
    <div class="form-group row">
        <div class="col-sm-3 col-form-label text-right"></div>
        <div class="col-sm-9">
            <button type="submit" class="btn btn-primary"><?= (!$id ? 'Добавить' : 'Сохранить') ?></button>
        </div>
    </div>
</form>