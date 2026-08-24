<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }
$roleOptions = blackPatternJournalEnumOptions((int)$arParams['IBLOCK_ID'], 'ROLE');
$themeOptions = blackPatternJournalEnumOptions((int)$arParams['IBLOCK_ID'], 'THEME');
$role = preg_replace('/[^a-z0-9_-]/', '', (string)($_GET['role'] ?? ''));
$theme = preg_replace('/[^a-z0-9_-]/', '', (string)($_GET['theme'] ?? ''));
if (!isset($roleOptions[$role])) { $role = ''; }
if (!isset($themeOptions[$theme])) { $theme = ''; }
$makeFilterUrl = static function (string $nextRole, string $nextTheme): string { $params = array_filter(['role' => $nextRole, 'theme' => $nextTheme]); return '/journal/' . ($params ? '?' . http_build_query($params) : ''); };
$availability = blackPatternJournalFilterAvailability((int)$arParams['IBLOCK_ID'], $roleOptions, $themeOptions, $role, $theme);
?>
<section class="journal-hero"><div class="wrap"><?php $APPLICATION->IncludeFile('/local/include/journal/intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление журнала']); ?><div class="journal-meta"><span><b><?= count($roleOptions); ?></b> роли читателя</span><span><b><?= count($themeOptions); ?></b> тематических рубрик</span><span>Обновляется <b>регулярно</b></span></div></div></section>
<nav class="journal-filter" aria-label="Фильтры журнала"><div class="wrap journal-filter-row"><span class="journal-filter-label">Роль</span><div class="journal-filter-list"><a data-journal-role="" aria-pressed="<?= $role === '' ? 'true' : 'false'; ?>" class="journal-chip<?= $role === '' ? ' is-active' : ''; ?>" href="<?= $makeFilterUrl('', $theme); ?>">Все</a><?php foreach ($roleOptions as $code => $option): $available = $availability['roles'][$code]; ?><a data-journal-role="<?= htmlspecialcharsbx($code); ?>" aria-pressed="<?= $role === $code ? 'true' : 'false'; ?>" class="journal-chip<?= $role === $code ? ' is-active' : ''; ?><?= !$available ? ' is-disabled' : ''; ?>"<?= $available ? ' href="' . $makeFilterUrl($code, $theme) . '"' : ''; ?><?= !$available ? ' aria-disabled="true" tabindex="-1"' : ''; ?>><span class="journal-dot dot-role-<?= htmlspecialcharsbx($code); ?>"></span><?= htmlspecialcharsbx($option['NAME']); ?></a><?php endforeach; ?></div><span class="journal-filter-divider" aria-hidden="true"></span><span class="journal-filter-label">Тема</span><div class="journal-filter-list"><a data-journal-theme="" aria-pressed="<?= $theme === '' ? 'true' : 'false'; ?>" class="journal-chip journal-theme<?= $theme === '' ? ' is-active' : ''; ?>" href="<?= $makeFilterUrl($role, ''); ?>">Все темы</a><?php foreach ($themeOptions as $code => $option): $available = $availability['themes'][$code]; ?><a data-journal-theme="<?= htmlspecialcharsbx($code); ?>" aria-pressed="<?= $theme === $code ? 'true' : 'false'; ?>" class="journal-chip journal-theme<?= $theme === $code ? ' is-active' : ''; ?><?= !$available ? ' is-disabled' : ''; ?>"<?= $available ? ' href="' . $makeFilterUrl($role, $code) . '"' : ''; ?><?= !$available ? ' aria-disabled="true" tabindex="-1"' : ''; ?>><?= htmlspecialcharsbx($option['NAME']); ?></a><?php endforeach; ?></div></div></nav>
<?php $APPLICATION->IncludeComponent('bitrix:news.list', 'journal', [
    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'], 'IBLOCK_ID' => $arParams['IBLOCK_ID'], 'NEWS_COUNT' => $arParams['NEWS_COUNT'],
    'SORT_BY1' => $arParams['SORT_BY1'], 'SORT_ORDER1' => $arParams['SORT_ORDER1'], 'SORT_BY2' => $arParams['SORT_BY2'], 'SORT_ORDER2' => $arParams['SORT_ORDER2'],
    'FIELD_CODE' => $arParams['FIELD_CODE'], 'PROPERTY_CODE' => $arParams['PROPERTY_CODE'], 'DETAIL_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['detail'],
    'FILTER_NAME' => 'blackPatternJournalFilter', 'CHECK_DATES' => $arParams['CHECK_DATES'], 'SET_TITLE' => 'N', 'SET_STATUS_404' => 'N', 'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
    'CACHE_TYPE' => $arParams['CACHE_TYPE'], 'CACHE_TIME' => $arParams['CACHE_TIME'], 'CACHE_FILTER' => $arParams['CACHE_FILTER'], 'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
    'DISPLAY_TOP_PAGER' => $arParams['DISPLAY_TOP_PAGER'], 'DISPLAY_BOTTOM_PAGER' => $arParams['DISPLAY_BOTTOM_PAGER'], 'PAGER_TEMPLATE' => $arParams['PAGER_TEMPLATE'], 'PAGER_TITLE' => $arParams['PAGER_TITLE'],
    'PAGER_SHOW_ALWAYS' => $arParams['PAGER_SHOW_ALWAYS'], 'PAGER_DESC_NUMBERING' => $arParams['PAGER_DESC_NUMBERING'], 'PAGER_DESC_NUMBERING_CACHE_TIME' => $arParams['PAGER_DESC_NUMBERING_CACHE_TIME'], 'PAGER_SHOW_ALL' => $arParams['PAGER_SHOW_ALL'], 'PAGER_BASE_LINK_ENABLE' => $arParams['PAGER_BASE_LINK_ENABLE'],
]); ?>

<section class="lead-cta">
    <div class="wrap lead-cta-grid">
        <div class="lead-cta-copy">
            <span class="tag">Материал для руководителей</span>
            <h2>Шаблон профиля должности маркетолога</h2>
            <p>Шесть обязательных блоков, стоп-факторы и балльная шкала оценки. Укажите свою роль и рабочую почту — отправим материал.</p>
        </div>
        <div class="lead-magnet-card">
            <span class="lead-magnet-kicker">Материал журнала</span>
            <b>PDF · шаблон профиля должности</b>
            <?php if (\Bitrix\Main\Loader::includeModule('blackpattern.settings')): ?>
                <?= \BlackPattern\Forms\FormRenderer::render('journal_lead', [
                    'SUBMIT_TEXT' => 'Получить шаблон',
                    'CLASS' => 'journal-lead-form',
                    'COMPACT' => 'Y',
                ]); ?>
            <?php else: ?>
                <p>Форма временно недоступна.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
