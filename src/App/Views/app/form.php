<?php

$this->layout('layout/main', ['isAdmin' => $isAdmin, 'flashes' => $flashes]);

$getValidCssClass = function ($model, $attribute) {
    if ($model->isLoad) {
        if ($model->getAttributeErrors($attribute)) {
            return ' is-invalid';
        } else {
            return ' is-valid';
        }
    } else {
        return '';
    }
};

$hasErors = function ($model, $attribute) {
    return $model->getAttributeErrors($attribute);
};

$getErrors = function ($model, $attribute) {
    $msg = '';
    foreach ($model->getAttributeErrors($attribute) as $error) {
        $msg .= sprintf("<li>%s</li>", $error);
    }
    return sprintf("<ul>%s</ul>", $msg);
};
?>

<form method="POST" action="<?= $id ? $this->url('update', ['id' => $id]) : $this->url('create') ?>">
    <div class="form-group row">
        <label for="name" class="col-sm-3 col-form-label text-right">Имя:</label>
        <div class="col-sm-9">
            <input type="text" 
                   class="form-control<?= $getValidCssClass($model, 'name') ?>" 
                   name="name" 
                   value="<?= $this->e($model->name) ?>">
            <?php if ($hasErors($model, 'name')) : ?>
                <div class="invalid-feedback">
                    <?= $getErrors($model, 'name') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="email" class="col-sm-3 col-form-label text-right">E-mail:</label>
        <div class="col-sm-9">
            <input type="text" 
                   class="form-control<?= $getValidCssClass($model, 'email') ?>" 
                   name="email"
                   value="<?= $this->e($model->email) ?>">
            <?php if ($hasErors($model, 'email')) : ?>
                <div class="invalid-feedback">
                    <?= $getErrors($model, 'email') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="content" class="col-sm-3 col-form-label text-right">Текст задачи:</label>
        <div class="col-sm-9">
            <textarea class="form-control<?= $getValidCssClass($model, 'content') ?>" 
                      name="content"><?= $this->e($model->content) ?></textarea>
            <?php if ($hasErors($model, 'content')) : ?>
                <div class="invalid-feedback">
                    <?= $getErrors($model, 'content') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($isAdmin) : ?>
        <div class="form-group row">
            <label for="status" class="col-sm-3 col-form-label text-right">Статус:</label>
            <div class="col-sm-9">
                <select class="form-control<?= $getValidCssClass($model, 'status') ?>" 
                        name="status"
                        value="<?= $model->status ?>">
                    <?php foreach ($model->getStatusList() as $value => $status) : ?>
                        <option value="<?= $this->e($value) ?>" <?= $model->status == $value ? ' selected' : '' ?>>
                            <?= $this->e($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($hasErors($model, 'status')) : ?>
                    <div class="invalid-feedback">
                        <?= $getErrors($model, 'status') ?>
                    </div>
                <?php endif; ?>
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