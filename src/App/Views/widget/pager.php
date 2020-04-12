<?php

$current = $pager->getPage() ?? 1;
$pagesCount = $pager->getPagesCount() ?? [];
$requestParams = $pager->getRequestParams();
$routeName = $pager->getRouteName();

if ($current == 2) {
    $prevParams = $requestParams;
} else {
    $prevParams = array_merge($requestParams, ['page' => $current - 1]);
}

$getCurrentParams = function ($page) use ($requestParams) {
    if ($page == 1) {
        $urlParams = $requestParams;
    } else {
        $urlParams = array_merge($requestParams, ['page' => $page]);
    }
    return $urlParams;
};

$nextParams = array_merge($requestParams, ['page' => $current + 1]);
?>

<?php if ($pagesCount > 1) : ?>
    <ul class="pagination">
        <?php if ($current > 1) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $this->url($routeName, $prevParams) ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </span>
            </li>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $pagesCount; $page++) : ?>
            <li class="page-item<?= ($page == $current) ? ' active' : ''?>">
                <a class="page-link" href="<?= $this->url($routeName, $getCurrentParams($page)) ?>"><?= $page ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($current < $pagesCount) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $this->url($routeName, $nextParams) ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </span>
            </li>
        <?php endif; ?>
    </ul>
<?php endif; ?>
