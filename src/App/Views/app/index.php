<?php

$sort = $sorter->getSort();

$reverseSort = function ($attribute, $direction) use ($sorter) {
    if ($direction == SORT_ASC) {
        return $sorter->build($attribute, SORT_DESC);
    } elseif ($direction == SORT_DESC) {
        return $sorter->build($attribute, SORT_ASC);
    }
};
$getIcon = function ($getAttr, $targetAttr) use ($sorter) {
    [$dir, $attr] = $sorter->parse($getAttr);
    if ($attr == $targetAttr) {
        if ($dir == SORT_ASC) {
            return '<img src="/img/arrow-down.svg">';
        } elseif ($dir == SORT_DESC) {
            return '<img src="/img/arrow-up.svg">';
        }
    }
    return '';
};
$getSortQuery = function ($getAttr, $targetAttr) use ($reverseSort, $sorter) {
    $name = ($getAttr === $targetAttr) ? $reverseSort($targetAttr) : $targetAttr;
    $sortParam = $sorter->getParam();
    return "{$sortParam}={$name}";
};

?>

<?php $this->layout('layout/main', ['isAdmin' => $isAdmin, 'flashes' => $flashes]) ?>
    
<div class="d-flex p-2">
    <a href="<?= $this->url('job.create') ?>" class="btn btn-primary js-form-modal">Добавить</a>
</div>
<table class="table">
    <thead>
        <tr>
            <th scope="col" width="5%">#</th>
            <th scope="col" width="20%">
                <a href="<?= $this->modifyCurrentUrl($getSortQuery($sort, 'name')) ?>">
                    имя пользователя <?= $getIcon($sort, 'name') ?>
                </a>
            </th>
            <th scope="col" width="15%">
                <a href="<?= $this->modifyCurrentUrl($getSortQuery($sort, 'email')) ?>">
                    e-mail <?= $getIcon($sort, 'email') ?>
                </a>
            </th>
            <th scope="col" width="40%">текст задачи</th>
            <th scope="col" width="10%">
                <a href="<?= $this->modifyCurrentUrl($getSortQuery($sort, 'status')) ?>">
                    статус <?= $getIcon($sort, 'status') ?>
                </a>
            </th>
            <th scope="col" width="5%"></th>
            <th scope="col" width="5%"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($jobs as $i => $job) : ?>
        <tr>
            <td scope="col"><?= $this->e($job['id']) ?></td>
            <td scope="col"><?= $this->e($job['name']) ?></td>
            <td scope="col"><?= $this->e($job['email']) ?></td>
            <td scope="col"><?= $this->e($job['content']) ?></td>
            <td scope="col"><?= $job['status'] ? '<img src="/img/check.svg" alt="Выполнена">' : '' ?></td>
            <td scope="col">
                <?= $job['edited_by_admin'] ? '<img src="/img/edit-3.svg" alt="Редактировалось администратором">' : '' ?>
            </td>
            <td scope="col">
                <?php if ($isAdmin) : ?>
                <a href="<?= $this->url('job.update', ['id' => $job['id']]) ?>" title="Редактировать">
                    <img src="/img/edit.svg">
                </a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php $this->insert('widget/pager', ['pager' => $pager]) ?>
