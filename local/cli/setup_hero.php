<?php
/** Idempotent setup for the homepage Hero block. */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$typeCode = 'homepage';
$iblockCode = 'hero';

if (!CIBlockType::GetByID($typeCode)->Fetch()) {
    $typeApi = new CIBlockType();
    if (!$typeApi->Add([
        'ID' => $typeCode,
        'SECTIONS' => 'N',
        'IN_RSS' => 'N',
        'SORT' => 200,
        'LANG' => [
            'ru' => ['NAME' => 'Главная', 'SECTION_NAME' => 'Разделы', 'ELEMENT_NAME' => 'Элементы'],
        ],
    ])) {
        throw new RuntimeException($typeApi->LAST_ERROR);
    }
    echo "Created iblock type: Homepage\n";
}

$iblock = CIBlock::GetList([], ['TYPE' => $typeCode, 'CODE' => $iblockCode, 'CHECK_PERMISSIONS' => 'N'])->Fetch();
$iblockApi = new CIBlock();
if (!$iblock) {
    $iblockId = (int)$iblockApi->Add([
        'ACTIVE' => 'Y',
        'NAME' => 'Hero',
        'CODE' => $iblockCode,
        'IBLOCK_TYPE_ID' => $typeCode,
        'SITE_ID' => ['s1'],
        'SORT' => 50,
        'LIST_PAGE_URL' => '#SITE_DIR#/',
        'GROUP_ID' => ['1' => 'X', '2' => 'R'],
    ]);
    if ($iblockId <= 0) {
        throw new RuntimeException($iblockApi->LAST_ERROR);
    }
    echo "Created iblock: Hero (#{$iblockId})\n";
} else {
    $iblockId = (int)$iblock['ID'];
    if (!$iblockApi->Update($iblockId, [
        'ACTIVE' => 'Y',
        'NAME' => 'Hero',
        'CODE' => $iblockCode,
        'IBLOCK_TYPE_ID' => $typeCode,
        'SITE_ID' => ['s1'],
        'SORT' => 50,
    ])) {
        throw new RuntimeException($iblockApi->LAST_ERROR);
    }
    echo "Updated iblock: Hero (#{$iblockId})\n";
}
CIBlock::SetPermission($iblockId, ['1' => 'X', '2' => 'R']);

$properties = [
    'KICKER' => ['Надзаголовок', 'S'],
    'H1_LINE_1' => ['Первая строка H1', 'S'],
    'H1_LINE_2' => ['Вторая строка H1', 'S'],
    'H1_ACCENT' => ['Акцент во второй строке H1', 'S'],
    'H1_LINE_3' => ['Третья строка H1', 'S'],
    'DESCRIPTION' => ['Описание', 'S'],
    'PRIMARY_TEXT' => ['Текст основной кнопки', 'S'],
    'PRIMARY_URL' => ['Ссылка основной кнопки', 'S'],
    'SECONDARY_TEXT' => ['Текст второй кнопки', 'S'],
    'SECONDARY_URL' => ['Ссылка второй кнопки', 'S'],
    'VIDEO_MP4' => ['Фоновое видео MP4', 'F'],
    'VIDEO_WEBM' => ['Фоновое видео WebM', 'F'],
    'POSTER' => ['Постер видео', 'F'],
];
for ($number = 1; $number <= 4; $number++) {
    $properties['STAT_' . $number . '_LABEL'] = ['Показатель ' . $number . ': подпись', 'S'];
    $properties['STAT_' . $number . '_VALUE'] = ['Показатель ' . $number . ': значение', 'S'];
    $properties['STAT_' . $number . '_ACCENT'] = ['Показатель ' . $number . ': акцентный', 'L'];
}

$propertyIds = [];
$sort = 100;
foreach ($properties as $code => [$name, $type]) {
    $property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch();
    $fields = [
        'IBLOCK_ID' => $iblockId,
        'NAME' => $name,
        'ACTIVE' => 'Y',
        'SORT' => $sort,
        'CODE' => $code,
        'PROPERTY_TYPE' => $type,
        'MULTIPLE' => 'N',
    ];
    if ($code === 'DESCRIPTION') {
        $fields['ROW_COUNT'] = 3;
    }
    if ($type === 'F') {
        $fields['FILE_TYPE'] = $code === 'POSTER' ? 'jpg,jpeg,png,webp,avif' : 'mp4,webm';
    }
    if ($type === 'L') {
        $fields['LIST_TYPE'] = 'C';
    }

    $propertyApi = new CIBlockProperty();
    if ($property) {
        $propertyId = (int)$property['ID'];
        if (!$propertyApi->Update($propertyId, $fields)) {
            throw new RuntimeException($propertyApi->LAST_ERROR);
        }
    } else {
        if ($type === 'L') {
            $fields['VALUES'] = [['VALUE' => 'Да', 'DEF' => 'N', 'SORT' => 100, 'XML_ID' => 'Y']];
        }
        $propertyId = (int)$propertyApi->Add($fields);
        if ($propertyId <= 0) {
            throw new RuntimeException($propertyApi->LAST_ERROR);
        }
        echo "Created property: {$code}\n";
    }
    $propertyIds[$code] = $propertyId;
    $sort += 100;
}

$existing = CIBlockElement::GetList([], [
    'IBLOCK_ID' => $iblockId,
    'CODE' => 'hero-main',
    'CHECK_PERMISSIONS' => 'N',
], false, false, ['ID'])->Fetch();
if ($existing) {
    $elementId = (int)$existing['ID'];
    echo "Using existing Hero element (#{$elementId}); content preserved.\n";
} else {
    $elementApi = new CIBlockElement();
    $elementId = (int)$elementApi->Add([
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'NAME' => 'Главный экран',
        'CODE' => 'hero-main',
        'SORT' => 100,
    ]);
    if ($elementId <= 0) {
        throw new RuntimeException($elementApi->LAST_ERROR);
    }

    $accentValues = [];
    for ($number = 1; $number <= 4; $number++) {
        $enum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyIds['STAT_' . $number . '_ACCENT'], 'XML_ID' => 'Y'])->Fetch();
        $accentValues[$number] = (int)($enum['ID'] ?? 0);
    }
    $videoPath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/blackpattern/assets/video/hero.mp4';
    $values = [
        'KICKER' => 'Построение отдела маркетинга под ключ',
        'H1_LINE_1' => 'Marketing OS.',
        'H1_LINE_2' => 'Операционная',
        'H1_ACCENT' => 'система',
        'H1_LINE_3' => 'отдела маркетинга.',
        'DESCRIPTION' => 'Проектируем структуру, роли и метрики так, чтобы маркетинг не зависел от одного человека.',
        'PRIMARY_TEXT' => 'Записаться на диагностику →',
        'PRIMARY_URL' => '#cta',
        'SECONDARY_TEXT' => 'Посчитать ошибку найма',
        'SECONDARY_URL' => '#calc',
        'STAT_1_LABEL' => 'Стоимость под ключ',
        'STAT_1_VALUE' => '600 000 ₽',
        'STAT_2_LABEL' => 'Срок разработки',
        'STAT_2_VALUE' => '6 недель',
        'STAT_3_LABEL' => 'Модулей в поставке',
        'STAT_3_VALUE' => '4',
        'STAT_4_LABEL' => 'Дешевле ошибки найма',
        'STAT_4_VALUE' => '× 14',
        'STAT_4_ACCENT' => $accentValues[4],
    ];
    if (is_file($videoPath)) {
        $values['VIDEO_MP4'] = CFile::MakeFileArray($videoPath);
    }
    CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $values);
    echo "Created Hero element (#{$elementId}) with the current homepage content.\n";
}

echo "Hero setup completed. iblock={$iblockId}, element={$elementId}\n";
