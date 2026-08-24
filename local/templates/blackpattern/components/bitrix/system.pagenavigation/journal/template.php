<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true || $arResult['NavPageCount'] <= 1) {
    return;
}
$query = trim((string)$arResult['NavQueryString'], '&');
$queryParams = [];
parse_str($query, $queryParams);
foreach (['role', 'theme'] as $filterParam) {
    if (isset($_GET[$filterParam]) && is_scalar($_GET[$filterParam])) {
        $queryParams[$filterParam] = preg_replace('/[^a-z0-9_-]/', '', (string)$_GET[$filterParam]);
    }
}
$query = http_build_query($queryParams);
$pageUrl = static function (int $page) use ($arResult, $query): string {
    $params = $query ? $query . '&' : '';
    $params .= 'PAGEN_' . $arResult['NavNum'] . '=' . $page;
    return $arResult['sUrlPath'] . '?' . $params;
};
?>
<nav class="journal-pagination" aria-label="Страницы журнала">
    <div class="modern-page-navigation">
        <?php if ($arResult['NavPageNomer'] > 1): ?><a href="<?= htmlspecialcharsbx($pageUrl($arResult['NavPageNomer'] - 1)); ?>" aria-label="Предыдущая страница">←</a><?php endif; ?>
        <?php for ($page = 1; $page <= $arResult['NavPageCount']; $page++): ?>
            <?php if ($page === (int)$arResult['NavPageNomer']): ?><span class="modern-page-current"><?= $page; ?></span><?php else: ?><a href="<?= htmlspecialcharsbx($pageUrl($page)); ?>"><?= $page; ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($arResult['NavPageNomer'] < $arResult['NavPageCount']): ?><a href="<?= htmlspecialcharsbx($pageUrl($arResult['NavPageNomer'] + 1)); ?>" aria-label="Следующая страница">→</a><?php endif; ?>
    </div>
</nav>
