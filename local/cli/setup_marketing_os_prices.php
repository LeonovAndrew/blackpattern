<?php
/** Idempotent setup for Marketing OS prices on the homepage. */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$typeCode = 'homepage';
$iblockCode = 'marketing_os_prices';
if (!CIBlockType::GetByID($typeCode)->Fetch()) {
    throw new RuntimeException('The homepage iblock type is missing. Run setup_marketing_os_modules.php first.');
}

$iblock = CIBlock::GetList([], ['TYPE' => $typeCode, 'CODE' => $iblockCode])->Fetch();
if (!$iblock) {
    $iblockApi = new CIBlock();
    $iblockId = (int)$iblockApi->Add([
        'ACTIVE' => 'Y',
        'NAME' => 'Тарифы Marketing OS',
        'CODE' => $iblockCode,
        'IBLOCK_TYPE_ID' => $typeCode,
        'SITE_ID' => ['s1'],
        'SORT' => 200,
        'LIST_PAGE_URL' => '#SITE_DIR#/',
        'GROUP_ID' => ['2' => 'R'],
    ]);
    if ($iblockId <= 0) {
        throw new RuntimeException($iblockApi->LAST_ERROR);
    }
    echo "Created iblock: Marketing OS prices (#{$iblockId})\n";
} else {
    $iblockId = (int)$iblock['ID'];
    echo "Using existing iblock: Marketing OS prices (#{$iblockId})\n";
}

$properties = [
    'TIER' => ['Маркер тарифа', 'S', 'N'],
    'PRICE' => ['Стоимость', 'S', 'N'],
    'TERMS' => ['Срок и условия оплаты', 'S', 'N'],
    'FEATURED' => ['Выделить тариф', 'L', 'N'],
    'SHOW_ON_HOME' => ['Показывать на главной', 'L', 'N'],
];
$propertyIds = [];
foreach ($properties as $code => [$name, $type, $multiple]) {
    $property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch();
    if (!$property) {
        $fields = ['IBLOCK_ID' => $iblockId, 'NAME' => $name, 'ACTIVE' => 'Y', 'SORT' => count($propertyIds) * 100 + 100, 'CODE' => $code, 'PROPERTY_TYPE' => $type, 'MULTIPLE' => $multiple];
        if ($type === 'L') {
            $fields['LIST_TYPE'] = 'C';
            $fields['VALUES'] = [['VALUE' => 'Да', 'DEF' => 'Y', 'SORT' => 100, 'XML_ID' => 'Y']];
        }
        $propertyApi = new CIBlockProperty();
        $propertyId = (int)$propertyApi->Add($fields);
        if ($propertyId <= 0) {
            throw new RuntimeException($propertyApi->LAST_ERROR);
        }
        echo "Created property: {$code}\n";
    } else {
        $propertyId = (int)$property['ID'];
    }
    $propertyIds[$code] = $propertyId;
}

$enums = [];
foreach (['FEATURED', 'SHOW_ON_HOME'] as $code) {
    $enum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyIds[$code], 'XML_ID' => 'Y'])->Fetch();
    if (!$enum) {
        throw new RuntimeException("The {$code}=Y enum value is missing.");
    }
    $enums[$code] = (int)$enum['ID'];
}

$prices = [
    ['marketing-os-base', 'Marketing OS — база', 'Оргструктура, регламенты и ЦКП, инструменты найма для HRD, контур подрядчиков. Защита и передача.', 'СТУПЕНЬ 1', '400 000 ₽', '6 недель · 70% при старте, 30% при передаче', false],
    ['author-supervision', 'Авторский надзор', 'Контроль применения, разбор найма, корректировка регламентов, письменные заключения с зонами отклонения.', 'СТУПЕНЬ 2', '200 000 ₽', '2 месяца · по 100 000 ₽ ежемесячно', false],
    ['full-contour', 'База + надзор', 'Рекомендуемый вариант. Без надзора документ рискует остаться файлом на диске — надзор удерживает внедрение.', 'ПОЛНЫЙ КОНТУР', '600 000 ₽', '≈3,5 месяца от старта до автономности', true],
];
$elementApi = new CIBlockElement();
foreach ($prices as $sort => [$code, $name, $description, $tier, $price, $terms, $featured]) {
    $existing = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, false, ['ID'])->Fetch();
    $fields = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'NAME' => $name, 'CODE' => $code, 'SORT' => ($sort + 1) * 100, 'PREVIEW_TEXT' => $description, 'PREVIEW_TEXT_TYPE' => 'text'];
    if ($existing) {
        $elementId = (int)$existing['ID'];
        if (!$elementApi->Update($elementId, $fields)) {
            throw new RuntimeException($elementApi->LAST_ERROR);
        }
        echo "Updated: {$code}\n";
    } else {
        $elementId = (int)$elementApi->Add($fields);
        if ($elementId <= 0) {
            throw new RuntimeException($elementApi->LAST_ERROR);
        }
        echo "Created: {$code}\n";
    }
    CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [
        'TIER' => $tier,
        'PRICE' => $price,
        'TERMS' => $terms,
        'FEATURED' => $featured ? $enums['FEATURED'] : false,
        'SHOW_ON_HOME' => $enums['SHOW_ON_HOME'],
    ]);
}

echo "Marketing OS prices setup completed.\n";
