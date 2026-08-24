<?php

use Bitrix\Main\Loader;

function blackPatternJournalIblockId(): int
{
    static $iblockId = null;
    if ($iblockId !== null) {
        return $iblockId;
    }
    if (!Loader::includeModule('iblock')) {
        return $iblockId = 0;
    }
    $iblock = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => 'journal'])->Fetch();
    return $iblockId = (int)($iblock['ID'] ?? 0);
}

function blackPatternJournalEnumOptions(int $iblockId, string $propertyCode): array
{
    $property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $propertyCode])->Fetch();
    if (!$property) {
        return [];
    }
    $options = [];
    $result = CIBlockPropertyEnum::GetList(['SORT' => 'ASC'], ['PROPERTY_ID' => $property['ID']]);
    while ($item = $result->Fetch()) {
        $options[$item['XML_ID']] = ['ID' => (int)$item['ID'], 'NAME' => $item['VALUE']];
    }
    return $options;
}

function blackPatternJournalPropertyValues(int $iblockId, int $elementId, string $propertyCode): array
{
    $values = [];
    $result = CIBlockElement::GetProperty($iblockId, $elementId, ['SORT' => 'ASC'], ['CODE' => $propertyCode]);
    while ($property = $result->Fetch()) {
        if ($property['VALUE'] !== false && $property['VALUE'] !== null && $property['VALUE'] !== '') {
            $values[] = $property;
        }
    }
    return $values;
}

function blackPatternJournalUrl(string $code): string
{
    return '/journal/' . rawurlencode($code) . '/';
}

function blackPatternJournalDate(?string $date): string
{
    $timestamp = MakeTimeStamp((string)$date);
    return $timestamp ? date('d.m.Y', $timestamp) : '';
}

function blackPatternJournalLoadAuthors(array $authorIds): array
{
    $authorIds = array_values(array_unique(array_filter(array_map('intval', $authorIds))));
    if (!$authorIds) {
        return [];
    }
    $team = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => 'team'])->Fetch();
    $teamId = (int)($team['ID'] ?? 0);
    if (!$teamId) {
        return [];
    }
    $authors = [];
    $result = CIBlockElement::GetList([], ['IBLOCK_ID' => $teamId, 'ID' => $authorIds, 'ACTIVE' => 'Y'], false, false, ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']);
    while ($author = $result->GetNext()) {
        $author['POSITION'] = blackPatternJournalPropertyValues($teamId, (int)$author['ID'], 'POSITION_EN')[0]['VALUE'] ?? '';
        $authors[(int)$author['ID']] = $author;
    }
    return $authors;
}

function blackPatternJournalArticleData(array $element, int $iblockId, array $roleOptions, array $themeOptions, array $authors = []): array
{
    $roles = blackPatternJournalPropertyValues($iblockId, (int)$element['ID'], 'ROLE');
    $themes = blackPatternJournalPropertyValues($iblockId, (int)$element['ID'], 'THEME');
    $authorProperties = blackPatternJournalPropertyValues($iblockId, (int)$element['ID'], 'AUTHORS');
    $readingTime = blackPatternJournalPropertyValues($iblockId, (int)$element['ID'], 'READING_TIME')[0]['VALUE'] ?? '';
    $featured = blackPatternJournalPropertyValues($iblockId, (int)$element['ID'], 'FEATURED')[0]['VALUE_XML_ID'] ?? '';

    $element['ROLE_CODES'] = array_values(array_filter(array_map(static fn(array $value): string => (string)$value['VALUE_XML_ID'], $roles)));
    $element['THEME_CODES'] = array_values(array_filter(array_map(static fn(array $value): string => (string)$value['VALUE_XML_ID'], $themes)));
    $element['ROLE_NAMES'] = array_map(static fn(string $code): string => $roleOptions[$code]['NAME'] ?? $code, $element['ROLE_CODES']);
    $element['THEME_NAMES'] = array_map(static fn(string $code): string => $themeOptions[$code]['NAME'] ?? $code, $element['THEME_CODES']);
    $element['READING_TIME'] = (int)$readingTime;
    $element['FEATURED'] = $featured === 'Y';
    $element['DATE_FORMATTED'] = blackPatternJournalDate($element['ACTIVE_FROM'] ?? '');
    $element['AUTHORS'] = [];
    foreach ($authorProperties as $authorProperty) {
        $authorId = (int)$authorProperty['VALUE'];
        if (isset($authors[$authorId])) {
            $element['AUTHORS'][] = $authors[$authorId];
        }
    }
    return $element;
}

function blackPatternJournalRoleClass(array $article): string
{
    return 'dot-role-' . ($article['ROLE_CODES'][0] ?? 'own');
}

function blackPatternJournalRelatedArticles(array $article, int $iblockId, array $roleOptions, array $themeOptions, array $manualIds = [], int $limit = 3): array
{
    $limit = max(1, $limit);
    $currentId = (int)($article['ID'] ?? 0);
    $manualIds = array_values(array_unique(array_filter(array_map('intval', $manualIds), static fn(int $id): bool => $id > 0 && $id !== $currentId)));
    $select = ['ID', 'NAME', 'CODE', 'ACTIVE_FROM', 'PREVIEW_TEXT', 'PREVIEW_PICTURE'];
    $related = [];

    if ($manualIds) {
        $manualById = [];
        $result = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'ID' => $manualIds], false, false, $select);
        while ($item = $result->GetNext()) {
            $manualById[(int)$item['ID']] = blackPatternJournalArticleData($item, $iblockId, $roleOptions, $themeOptions);
        }
        foreach ($manualIds as $manualId) {
            if (isset($manualById[$manualId])) {
                $related[] = $manualById[$manualId];
                if (count($related) >= $limit) {
                    return $related;
                }
            }
        }
    }

    $excludedIds = array_merge([$currentId], array_map(static fn(array $item): int => (int)$item['ID'], $related));
    $candidates = [];
    $result = CIBlockElement::GetList(
        ['ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'],
        ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', '!ID' => array_values(array_filter($excludedIds))],
        false,
        ['nTopCount' => 100],
        $select
    );
    while ($item = $result->GetNext()) {
        $candidate = blackPatternJournalArticleData($item, $iblockId, $roleOptions, $themeOptions);
        $candidate['_RELATED_SCORE'] = count(array_intersect($article['THEME_CODES'], $candidate['THEME_CODES'])) * 3
            + count(array_intersect($article['ROLE_CODES'], $candidate['ROLE_CODES'])) * 2;
        if ($candidate['_RELATED_SCORE'] > 0) {
            $candidates[] = $candidate;
        }
    }

    usort($candidates, static function (array $left, array $right): int {
        $scoreCompare = $right['_RELATED_SCORE'] <=> $left['_RELATED_SCORE'];
        if ($scoreCompare !== 0) {
            return $scoreCompare;
        }
        return MakeTimeStamp((string)($right['ACTIVE_FROM'] ?? '')) <=> MakeTimeStamp((string)($left['ACTIVE_FROM'] ?? ''));
    });
    foreach ($candidates as $candidate) {
        unset($candidate['_RELATED_SCORE']);
        $related[] = $candidate;
        if (count($related) >= $limit) {
            break;
        }
    }

    return $related;
}

function blackPatternJournalFilterAvailability(int $iblockId, array $roleOptions, array $themeOptions, string $role, string $theme): array
{
    $hasArticles = static function (array $filter): bool {
        return (bool)CIBlockElement::GetList([], $filter, false, ['nTopCount' => 1], ['ID'])->Fetch();
    };
    $availability = ['roles' => [], 'themes' => []];
    foreach ($roleOptions as $code => $option) {
        $filter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'PROPERTY_ROLE' => $option['ID']];
        if ($theme) {
            $filter['PROPERTY_THEME'] = $themeOptions[$theme]['ID'];
        }
        $availability['roles'][$code] = $hasArticles($filter);
    }
    foreach ($themeOptions as $code => $option) {
        $filter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'PROPERTY_THEME' => $option['ID']];
        if ($role) {
            $filter['PROPERTY_ROLE'] = $roleOptions[$role]['ID'];
        }
        $availability['themes'][$code] = $hasArticles($filter);
    }
    return $availability;
}
