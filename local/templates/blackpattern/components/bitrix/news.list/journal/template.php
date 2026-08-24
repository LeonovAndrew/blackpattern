<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }
$roleOptions = blackPatternJournalEnumOptions((int)$arParams['IBLOCK_ID'], 'ROLE');
$themeOptions = blackPatternJournalEnumOptions((int)$arParams['IBLOCK_ID'], 'THEME');
$authorIds = [];
foreach ($arResult['ITEMS'] as $item) {
    $authorIds = array_merge($authorIds, (array)($item['PROPERTIES']['AUTHORS']['VALUE'] ?? []));
}
$authors = blackPatternJournalLoadAuthors($authorIds);
$items = array_map(static fn(array $item): array => blackPatternJournalArticleData($item, (int)$arParams['IBLOCK_ID'], $roleOptions, $themeOptions, $authors), $arResult['ITEMS']);
$featured = null;
foreach ($items as $item) {
    if ($item['FEATURED']) { $featured = $item; break; }
}
$recordCount = $arResult['NAV_RESULT'] ? (int)$arResult['NAV_RESULT']->NavRecordCount : count($items);
?>
<div data-journal-results>
<?php if ($featured): $featuredImage = CFile::GetPath((int)($featured['PREVIEW_PICTURE'] ?: $featured['DETAIL_PICTURE'])); ?>
<section class="journal-featured"><div class="wrap"><a class="journal-featured-link" href="<?= htmlspecialcharsbx($featured['DETAIL_PAGE_URL']); ?>">
    <?php if ($featuredImage): ?><img class="journal-cover" src="<?= htmlspecialcharsbx($featuredImage); ?>" alt="<?= htmlspecialcharsbx($featured['NAME']); ?>"><?php else: ?><div class="journal-cover-placeholder">ОБЛОЖКА МАТЕРИАЛА</div><?php endif; ?>
    <div class="journal-featured-body"><div class="journal-card-meta"><span class="journal-who"><span class="journal-dot <?= blackPatternJournalRoleClass($featured); ?>"></span><?= htmlspecialcharsbx(implode(', ', $featured['ROLE_NAMES'])); ?></span><?php if ($featured['THEME_NAMES']): ?> · <?= htmlspecialcharsbx(implode(', ', $featured['THEME_NAMES'])); ?><?php endif; ?><?php if ($featured['READING_TIME']): ?> · <?= $featured['READING_TIME']; ?> мин<?php endif; ?></div><h2><?= htmlspecialcharsbx($featured['NAME']); ?></h2><p class="journal-featured-text"><?= htmlspecialcharsbx($featured['PREVIEW_TEXT']); ?></p><span class="journal-read">Читать разбор →</span></div>
</a></div></section>
<?php endif; ?>
<section class="journal-list"><div class="wrap"><div class="journal-count"><b><?= $recordCount; ?></b> <?= $recordCount === 1 ? 'материал' : 'материалов'; ?></div>
<?php $cards = array_values(array_filter($items, static fn(array $item): bool => !$featured || $item['ID'] !== $featured['ID'])); ?>
<?php if ($cards): ?><div class="journal-cards"><?php foreach ($cards as $item): ?><a class="journal-card" href="<?= htmlspecialcharsbx($item['DETAIL_PAGE_URL']); ?>"><div class="journal-card-meta"><span class="journal-who"><span class="journal-dot <?= blackPatternJournalRoleClass($item); ?>"></span><?= htmlspecialcharsbx(implode(', ', $item['ROLE_NAMES'])); ?></span><?php if ($item['THEME_NAMES']): ?> · <?= htmlspecialcharsbx(implode(', ', $item['THEME_NAMES'])); ?><?php endif; ?></div><h2><?= htmlspecialcharsbx($item['NAME']); ?></h2><p><?= htmlspecialcharsbx($item['PREVIEW_TEXT']); ?></p><span class="journal-read">Читать<?= $item['READING_TIME'] ? ' · ' . $item['READING_TIME'] . ' мин' : ''; ?> →</span></a><?php endforeach; ?></div><?php elseif (!$featured): ?><p class="journal-empty">По выбранным фильтрам материалов пока нет.</p><?php endif; ?>
<?= $arResult['NAV_STRING']; ?></div></section>
</div>
