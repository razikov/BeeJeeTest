<?php

$getValidCssClass = function($model, $attribute) {
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

$hasErors = function($model, $attribute) {
    return $model->getAttributeErrors($attribute);
};

$getErrors = function($model, $attribute) {
    $msg = '';
    foreach ($model->getAttributeErrors($attribute) as $error) {
        $msg .= sprintf("<li>%s</li>", $error);
    }
    return sprintf("<ul>%s</ul>", $msg);
};
?>

<div class="modal fade" id="form-modal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formModalLabel"><?= ($isNew ? 'Создать' : 'Изменить задачу') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= $url ?>">
                    <div class="form-group row">
                        <label for="name" class="col-sm-3 col-form-label text-right">Имя:</label>
                        <div class="col-sm-9">
                            <input type="text" 
                                   class="form-control<?= $getValidCssClass($model, 'name') ?>" 
                                   name="name" 
                                   value="<?=$model->name?>">
                            <?php if($hasErors($model, 'name')): ?>
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
                                   value="<?=$model->email?>">
                            <?php if($hasErors($model, 'email')): ?>
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
                                      name="content"
                                      value="<?=$model->content?>"></textarea>
                            <?php if($hasErors($model, 'content')): ?>
                            <div class="invalid-feedback">
                                <?= $getErrors($model, 'content') ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($isAdmin): ?>
                        <div class="form-group row">
                            <label for="status" class="col-sm-3 col-form-label text-right">Статус:</label>
                            <div class="col-sm-9">
                                <select class="form-control<?= $getValidCssClass($model, 'status') ?>"
                                        name="status"
                                        value="<?=$model->status?>">
                                    <option value="0">В работе</option>
                                    <option value="1">Выполнено</option>
                                </select>
                            </div>
                            <?php if($hasErors($model, 'status')): ?>
                            <div class="invalid-feedback">
                                <?= $getErrors($model, 'status') ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary js-submit"><?= ($isNew ? 'Добавить' : 'Сохранить') ?></button>
            </div>
        </div>
    </div>
</div>
