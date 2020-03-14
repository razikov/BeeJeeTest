<?php
$sort = $q['sort'] ?? '';
$reverseSort = function ($attribute) {
    if (substr($attribute, 0, 1) == '-') {
        return substr($attribute, 1);
    } else {
        return '-'.$attribute;
    }
};
$getIcon = function ($getAttr, $targetAttr) {
    $dir = null;
    $attr = null;
    if (substr($getAttr, 0, 1) == '-') {
        $attr = substr($getAttr, 1); // DESC, вверх
        $dir = SORT_DESC;
    } else {
        $attr = $getAttr; // ASC, вниз
        $dir = SORT_ASC;
    }
    if ($attr == $targetAttr) {
        if ($dir == SORT_ASC) {
            return '<img src="/img/arrow-down.svg">';
        }
        if ($dir == SORT_DESC) {
            return '<img src="/img/arrow-up.svg">';
        }
    }
    return '';
};
$getSortUrl = function ($getAttr, $targetAttr) use ($reverseSort, $q) {
    $name = ($getAttr === $targetAttr) ? $reverseSort($getAttr) : $targetAttr;
    $args = ['sort' => $name];
    return sprintf('/?%s', http_build_query(array_merge($q, $args)));
};
?>

<?php $this->layout('layout/main', ['isAdmin' => $isAdmin]) ?>

<?php $this->start('flash') ?>
<?php if ($successMessage): ?>
    <div class="alert alert-success" role="alert">
        <?= $successMessage ?>
    </div>
<?php endif; ?>
<?php if ($failMessage): ?>
    <div class="alert alert-danger" role="alert">
        <?= $failMessage ?>
    </div>
<?php endif; ?>
<?php $this->end() ?>
    
<div class="d-flex p-2">
    <a href="/create" class="btn btn-primary js-form-modal">Добавить</a>
</div>
<table class="table">
    <thead>
        <tr>
            <th scope="col" width="20%"><a href="<?= $getSortUrl($sort, 'name') ?>">имя пользователя <?= $getIcon($sort, 'name') ?></a></th>
            <th scope="col" width="20%"><a href="<?= $getSortUrl($sort, 'email') ?>">e-mail <?= $getIcon($sort, 'email') ?></th>
            <th scope="col" width="40%">текст задачи</a></th>
            <th scope="col" width="10%"><a href="<?= $getSortUrl($sort, 'status') ?>">статус <?= $getIcon($sort, 'status') ?></a></th>
            <th scope="col" width="5%"></th>
            <th scope="col" width="5%"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($jobs as $i => $job): ?>
        <tr>
            <td scope="col"><?= $this->e($job['name']) ?></td>
            <td scope="col"><?= $this->e($job['email']) ?></td>
            <td scope="col"><?= $this->e($job['content']) ?></td>
            <td scope="col"><?= $job['status'] ? '<img src="/img/check.svg" alt="Выполнена">' : '' ?></td>
            <td scope="col"><?= $job['edited_by_admin'] ? '<img src="/img/edit-3.svg" alt="Редактировалось администратором">' : '' ?></td>
            <td scope="col">
                <?php if($isAdmin): ?>
                <a href="/update/<?=$job['id']?>" title="Редактировать"><img src="/img/edit.svg"></a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php $this->insert('widget/pager', ['pager' => $pager, 'main_route' => '/', 'route' => '/']) ?>
