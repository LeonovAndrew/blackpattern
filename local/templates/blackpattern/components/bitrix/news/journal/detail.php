<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }
$elementCode = trim((string)($arResult['VARIABLES']['ELEMENT_CODE'] ?? ''));
if ($elementCode === '') {
    $elementCode = trim((string)($arParams['ARTICLE_CODE'] ?? $GLOBALS['blackPatternJournalArticleCode'] ?? ''));
}
$APPLICATION->IncludeComponent('bitrix:news.detail', 'journal', [
    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'], 'IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ELEMENT_CODE' => $elementCode,
    'FIELD_CODE' => $arParams['DETAIL_FIELD_CODE'], 'PROPERTY_CODE' => $arParams['DETAIL_PROPERTY_CODE'], 'CHECK_DATES' => $arParams['CHECK_DATES'],
    'SET_TITLE' => 'N', 'SET_STATUS_404' => 'Y', 'INCLUDE_IBLOCK_INTO_CHAIN' => 'N', 'CACHE_TYPE' => $arParams['CACHE_TYPE'], 'CACHE_TIME' => $arParams['CACHE_TIME'], 'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
]);
