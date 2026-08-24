<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/journal/functions.php';

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    CHTTP::SetStatus('404 Not Found');
    die();
}

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

$APPLICATION->RestartBuffer();
ob_start();
$APPLICATION->IncludeComponent('bitrix:news.list', 'journal', [
    'IBLOCK_TYPE' => 'content', 'IBLOCK_ID' => $iblockId, 'NEWS_COUNT' => '3',
    'SORT_BY1' => 'PROPERTY_FEATURED', 'SORT_ORDER1' => 'DESC', 'SORT_BY2' => 'ACTIVE_FROM', 'SORT_ORDER2' => 'DESC',
    'FIELD_CODE' => ['ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
    'PROPERTY_CODE' => ['ROLE', 'THEME', 'READING_TIME', 'AUTHORS', 'FEATURED', 'RELATED_ARTICLES'],
    'DETAIL_URL' => '/journal/#ELEMENT_CODE#/', 'FILTER_NAME' => 'blackPatternJournalFilter', 'CHECK_DATES' => 'Y',
    'SET_TITLE' => 'N', 'SET_STATUS_404' => 'N', 'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
    'CACHE_TYPE' => 'N', 'CACHE_TIME' => '36000000', 'CACHE_FILTER' => 'Y', 'CACHE_GROUPS' => 'Y',
    'DISPLAY_TOP_PAGER' => 'N', 'DISPLAY_BOTTOM_PAGER' => 'Y', 'PAGER_TEMPLATE' => 'journal', 'PAGER_TITLE' => 'Материалы',
    'PAGER_SHOW_ALWAYS' => 'N', 'PAGER_DESC_NUMBERING' => 'N', 'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000', 'PAGER_SHOW_ALL' => 'N', 'PAGER_BASE_LINK_ENABLE' => 'Y', 'PAGER_BASE_LINK' => '/journal/',
]);
$html = ob_get_clean();
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'html' => $html,
    'availability' => blackPatternJournalFilterAvailability($iblockId, $roleOptions, $themeOptions, $role, $theme),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
die();
