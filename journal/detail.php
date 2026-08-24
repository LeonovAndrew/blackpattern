<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/journal/functions.php';

$iblockId = blackPatternJournalIblockId();
$code = preg_replace('/[^a-z0-9_-]/', '', (string)($_REQUEST['article'] ?? ''));
$element = $iblockId && $code ? CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'CODE' => $code], false, false, [
    'ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_TEXT_TYPE', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE', 'PREVIEW_PICTURE', 'DETAIL_PICTURE',
])->GetNext() : false;

if (!$element) {
    CHTTP::SetStatus('404 Not Found');
    define('ERROR_404', 'Y');
    $APPLICATION->SetTitle('Материал не найден');
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
    ?><section class="journal-list"><div class="wrap"><p class="journal-empty">Материал не найден.</p></div></section><?php
    require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
    return;
}

$roleOptions = blackPatternJournalEnumOptions($iblockId, 'ROLE');
$themeOptions = blackPatternJournalEnumOptions($iblockId, 'THEME');
$authorIds = array_map(static fn(array $item): int => (int)$item['VALUE'], blackPatternJournalPropertyValues($iblockId, (int)$element['ID'], 'AUTHORS'));
$article = blackPatternJournalArticleData($element, $iblockId, $roleOptions, $themeOptions, blackPatternJournalLoadAuthors($authorIds));
$APPLICATION->SetTitle($article['NAME']);
$APPLICATION->SetPageProperty('title', $article['NAME'] . ' | Журнал Black Pattern');
$APPLICATION->SetPageProperty('description', $article['PREVIEW_TEXT']);
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/assets/css/journal.css');

$relatedIds = array_map(static fn(array $item): int => (int)$item['VALUE'], blackPatternJournalPropertyValues($iblockId, (int)$article['ID'], 'RELATED_ARTICLES'));
if (!$relatedIds && $article['THEME_CODES']) {
    $relatedIds = [];
    $sameTheme = CIBlockElement::GetList(['ACTIVE_FROM' => 'DESC'], [
        'IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', '!ID' => $article['ID'], 'PROPERTY_THEME' => $themeOptions[$article['THEME_CODES'][0]]['ID'],
    ], false, ['nTopCount' => 3], ['ID'])->Fetch();
    if ($sameTheme) {
        $relatedIds[] = (int)$sameTheme['ID'];
    }
}
$related = [];
if ($relatedIds) {
    $result = CIBlockElement::GetList(['ACTIVE_FROM' => 'DESC'], ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'ID' => $relatedIds], false, false, ['ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'PREVIEW_TEXT']);
    $relatedElements = [];
    $relatedAuthorIds = [];
    while ($relatedElement = $result->GetNext()) {
        $relatedElements[] = $relatedElement;
        foreach (blackPatternJournalPropertyValues($iblockId, (int)$relatedElement['ID'], 'AUTHORS') as $relatedAuthor) {
            $relatedAuthorIds[] = (int)$relatedAuthor['VALUE'];
        }
    }
    $relatedAuthors = blackPatternJournalLoadAuthors($relatedAuthorIds);
    foreach ($relatedElements as $relatedElement) {
        $related[] = blackPatternJournalArticleData($relatedElement, $iblockId, $roleOptions, $themeOptions, $relatedAuthors);
    }
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$author = $article['AUTHORS'][0] ?? null;
$authorPhoto = $author ? CFile::GetPath((int)$author['PREVIEW_PICTURE']) : '';
$cover = CFile::GetPath((int)($article['DETAIL_PICTURE'] ?: $article['PREVIEW_PICTURE']));
?>
<header class="article-header"><div class="wrap article-wrap">
    <nav class="article-crumbs" aria-label="Хлебные крошки"><a href="/journal/">Журнал</a><span>/</span><span><?= htmlspecialcharsbx($article['ROLE_NAMES'][0] ?? 'Материал'); ?></span></nav>
    <div class="article-tags"><span class="journal-who"><span class="journal-dot <?= blackPatternJournalRoleClass($article); ?>"></span><?= htmlspecialcharsbx(implode(', ', $article['ROLE_NAMES'])); ?></span><?php foreach ($article['THEME_NAMES'] as $theme): ?><span class="article-tag"><?= htmlspecialcharsbx($theme); ?></span><?php endforeach; ?></div>
    <h1><?= htmlspecialcharsbx($article['NAME']); ?></h1>
    <p class="article-dek"><?= htmlspecialcharsbx($article['PREVIEW_TEXT']); ?></p>
    <div class="article-byline">
        <?php if ($authorPhoto): ?><img class="article-avatar" src="<?= htmlspecialcharsbx($authorPhoto); ?>" alt="<?= htmlspecialcharsbx($author['NAME']); ?>"><?php else: ?><span class="article-avatar" aria-hidden="true"></span><?php endif; ?>
        <div><?php if ($author): ?><div class="article-author-name"><?= htmlspecialcharsbx($author['NAME']); ?></div><div class="article-author-role"><?= htmlspecialcharsbx($author['POSITION']); ?></div><?php else: ?><div class="article-author-name">Black Pattern</div><?php endif; ?></div>
        <div class="article-reading"><?= htmlspecialcharsbx($article['DATE_FORMATTED']); ?><?= $article['READING_TIME'] ? ' · ' . $article['READING_TIME'] . ' мин' : ''; ?></div>
    </div>
</div><div class="wrap"><?php if ($cover): ?><img class="article-cover" src="<?= htmlspecialcharsbx($cover); ?>" alt="<?= htmlspecialcharsbx($article['NAME']); ?>"><?php else: ?><div class="journal-cover-placeholder article-cover-placeholder">ОБЛОЖКА МАТЕРИАЛА</div><?php endif; ?></div></header>

<article class="article-body"><div class="wrap article-wrap">
    <?= $article['DETAIL_TEXT'] ?: '<p>' . htmlspecialcharsbx($article['PREVIEW_TEXT']) . '</p>'; ?>
</div></article>

<?php if ($author): ?><aside class="article-author"><div><?php if ($authorPhoto): ?><img class="article-avatar" src="<?= htmlspecialcharsbx($authorPhoto); ?>" alt="<?= htmlspecialcharsbx($author['NAME']); ?>"><?php else: ?><span class="article-avatar" aria-hidden="true"></span><?php endif; ?></div><div><div class="article-author-name"><?= htmlspecialcharsbx($author['NAME']); ?></div><div class="article-author-role"><?= htmlspecialcharsbx($author['POSITION']); ?></div><p><?= htmlspecialcharsbx($author['PREVIEW_TEXT']); ?></p></div></aside><?php endif; ?>

<?php if ($related): ?><section class="journal-related"><div class="wrap"><h2>Читать дальше</h2><div class="journal-cards">
    <?php foreach ($related as $relatedArticle): ?><a class="journal-card" href="<?= blackPatternJournalUrl($relatedArticle['CODE']); ?>"><div class="journal-card-meta"><span class="journal-who"><span class="journal-dot <?= blackPatternJournalRoleClass($relatedArticle); ?>"></span><?= htmlspecialcharsbx(implode(', ', $relatedArticle['ROLE_NAMES'])); ?></span><?php if ($relatedArticle['THEME_NAMES']): ?> · <?= htmlspecialcharsbx(implode(', ', $relatedArticle['THEME_NAMES'])); ?><?php endif; ?></div><h2><?= htmlspecialcharsbx($relatedArticle['NAME']); ?></h2><p><?= htmlspecialcharsbx($relatedArticle['PREVIEW_TEXT']); ?></p><span class="journal-read">Читать<?= $relatedArticle['READING_TIME'] ? ' · ' . $relatedArticle['READING_TIME'] . ' мин' : ''; ?> →</span></a><?php endforeach; ?>
</div></div></section><?php endif; ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
