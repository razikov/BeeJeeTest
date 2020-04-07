<?php

$current = $pager->getPage() ?? 1;
$pagesCount = $pager->getPagesCount() ?? [];
$q = $pager->getArgs();

if ($current == 2) {
    $urlPrev = $q ? sprintf($main_route . '?%s', http_build_query($q)) : sprintf($main_route);
} else {
    $urlPrev = sprintf($route . '?%s', http_build_query(array_merge($q, ['page' => $current - 1])));
}

$getCurrentUrl = function ($page) use ($main_route, $route, $q) {
    if ($page == 1) {
        $urlCurrent = $q ? sprintf($main_route . '?%s', http_build_query($q)) : sprintf($main_route);
    } else {
        $urlCurrent = sprintf($route . '?%s', http_build_query(array_merge($q, ['page' => $page])));
    }
    return $urlCurrent;
};

$urlNext = sprintf($route . '?%s', http_build_query(array_merge($q, ['page' => $current + 1])));
?>

<?php if ($pagesCount > 1) : ?>
    <ul class="pagination">
        <?php if ($current > 1) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $urlPrev ?>" aria-label="Previous">
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
                <a class="page-link" href="<?= $getCurrentUrl($page) ?>"><?= $page ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($current < $pagesCount) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $urlNext ?>" aria-label="Next">
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
