<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/journal/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/seo/functions.php';

$iblockId = blackPatternJournalIblockId();
$roleOptions = $iblockId ? blackPatternJournalEnumOptions($iblockId, 'ROLE') : [];
$themeOptions = $iblockId ? blackPatternJournalEnumOptions($iblockId, 'THEME') : [];
$role = preg_replace('/[^a-z0-9_-]/', '', (string)($_GET['role'] ?? ''));
$theme = preg_replace('/[^a-z0-9_-]/', '', (string)($_GET['theme'] ?? ''));
if (!isset($roleOptions[$role])) { $role = ''; }
if (!isset($themeOptions[$theme])) { $theme = ''; }
$GLOBALS['blackPatternJournalFilter'] = ['ACTIVE' => 'Y'];
if ($role) { $GLOBALS['blackPatternJournalFilter']['PROPERTY_ROLE'] = $roleOptions[$role]['ID']; }
if ($theme) { $GLOBALS['blackPatternJournalFilter']['PROPERTY_THEME'] = $themeOptions[$theme]['ID']; }

$articleCode = preg_replace('/[^a-z0-9_-]/', '', (string)($_REQUEST['article'] ?? ''));
if ($articleCode === '') {
    $requestPath = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '');
    if (preg_match('#^/journal/([a-z0-9_-]+)/?$#', $requestPath, $articlePathMatch)) {
        $articleCode = $articlePathMatch[1];
    }
}
$GLOBALS['blackPatternJournalArticleCode'] = $articleCode;
$articleForMeta = $articleCode && $iblockId ? CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'CODE' => $articleCode], false, false, [
    'ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'TIMESTAMP_X', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE',
])->Fetch() : false;
$articleSeoTitle = $articleForMeta ? (string)(blackPatternJournalPropertyValues($iblockId, (int)$articleForMeta['ID'], 'SEO_TITLE')[0]['VALUE'] ?? '') : '';
$articleSeoDescription = $articleForMeta ? (string)(blackPatternJournalPropertyValues($iblockId, (int)$articleForMeta['ID'], 'SEO_DESCRIPTION')[0]['VALUE'] ?? '') : '';
$articleSeoImageId = $articleForMeta ? (int)(blackPatternJournalPropertyValues($iblockId, (int)$articleForMeta['ID'], 'SEO_OG_IMAGE')[0]['VALUE'] ?? 0) : 0;
$seoSettings = blackPatternSeoSettings();
$APPLICATION->SetTitle($articleForMeta['NAME'] ?? ($articleCode ? 'Материал не найден' : 'Журнал — Black Pattern'));
$APPLICATION->SetPageProperty('title', $articleForMeta ? ($articleSeoTitle ?: $articleForMeta['NAME'] . ' | Журнал Black Pattern') : ($articleCode ? 'Материал не найден | Black Pattern' : $seoSettings['SEO_JOURNAL_TITLE']));
$APPLICATION->SetPageProperty('description', $articleForMeta ? ($articleSeoDescription ?: $articleForMeta['PREVIEW_TEXT']) : ($articleCode ? 'Запрошенный материал журнала не найден.' : $seoSettings['SEO_JOURNAL_DESCRIPTION']));
$APPLICATION->SetPageProperty('og_type', $articleForMeta ? 'article' : 'website');
if ($articleCode && !$articleForMeta) {
    require $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    return;
}
if (!$articleForMeta && ($role || $theme)) {
    $APPLICATION->SetPageProperty('robots', 'noindex, follow');
}

blackPatternSeoAddSchema(blackPatternSeoBlogSchema($seoSettings));
if ($articleForMeta) {
    $authorIds = array_map(static fn(array $item): int => (int)$item['VALUE'], blackPatternJournalPropertyValues($iblockId, (int)$articleForMeta['ID'], 'AUTHORS'));
    $articleSeo = blackPatternJournalArticleData($articleForMeta, $iblockId, $roleOptions, $themeOptions, blackPatternJournalLoadAuthors($authorIds));
    $articleSeo['SEO_IMAGE_ID'] = $articleSeoImageId;
    $coverId = $articleSeoImageId ?: (int)($articleSeo['DETAIL_PICTURE'] ?: $articleSeo['PREVIEW_PICTURE']);
    if ($coverId > 0) {
        $APPLICATION->SetPageProperty('og_image', (string)CFile::GetPath($coverId));
    }
    blackPatternSeoAddSchema(blackPatternSeoArticleSchema($articleSeo, $seoSettings));
    blackPatternSeoAddSchema(blackPatternSeoBreadcrumbSchema([
        ['name' => 'Главная', 'url' => '/'],
        ['name' => 'Журнал', 'url' => '/journal/'],
        ['name' => $articleSeo['NAME'], 'url' => blackPatternJournalUrl($articleSeo['CODE'])],
    ], $seoSettings));
} else {
    blackPatternSeoAddSchema(blackPatternSeoBreadcrumbSchema([
        ['name' => 'Главная', 'url' => '/'],
        ['name' => 'Журнал', 'url' => '/journal/'],
    ], $seoSettings));
}
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/assets/css/journal.css');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->IncludeComponent('bitrix:news', 'journal', [
    'IBLOCK_TYPE' => 'content', 'IBLOCK_ID' => $iblockId, 'NEWS_COUNT' => '10',
    'USE_SEARCH' => 'N', 'USE_RSS' => 'N', 'USE_RATING' => 'N', 'USE_CATEGORIES' => 'N', 'USE_REVIEW' => 'N', 'USE_FILTER' => 'N',
    'SORT_BY1' => 'PROPERTY_FEATURED', 'SORT_ORDER1' => 'DESC', 'SORT_BY2' => 'ACTIVE_FROM', 'SORT_ORDER2' => 'DESC',
    'FIELD_CODE' => ['ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
    'PROPERTY_CODE' => ['ROLE', 'THEME', 'READING_TIME', 'AUTHORS', 'FEATURED', 'RELATED_ARTICLES'],
    'DETAIL_FIELD_CODE' => ['ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
    'DETAIL_PROPERTY_CODE' => ['ROLE', 'THEME', 'READING_TIME', 'AUTHORS', 'RELATED_ARTICLES'],
    'FILTER_NAME' => 'blackPatternJournalFilter', 'CHECK_DATES' => 'Y',
    'SEF_MODE' => 'Y', 'SEF_FOLDER' => '/journal/', 'SEF_URL_TEMPLATES' => ['news' => '', 'detail' => '#ELEMENT_CODE#/'],
    'ARTICLE_CODE' => $articleCode,
    'SET_TITLE' => 'N', 'SET_STATUS_404' => 'Y', 'SHOW_404' => 'Y', 'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
    'CACHE_TYPE' => 'A', 'CACHE_TIME' => '36000000', 'CACHE_FILTER' => 'Y', 'CACHE_GROUPS' => 'Y',
    'DISPLAY_TOP_PAGER' => 'N', 'DISPLAY_BOTTOM_PAGER' => 'Y', 'PAGER_TEMPLATE' => 'journal', 'PAGER_TITLE' => 'Материалы',
    'PAGER_SHOW_ALWAYS' => 'N', 'PAGER_DESC_NUMBERING' => 'N', 'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000', 'PAGER_SHOW_ALL' => 'N', 'PAGER_BASE_LINK_ENABLE' => 'N',
]);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
