<?php
/** Idempotent initial setup for the Team content block. */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

set_exception_handler(static function (Throwable $exception): void {
    fwrite(STDERR, $exception->__toString() . PHP_EOL);
    exit(1);
});

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$typeCode = 'content';
$iblockCode = 'team';
$siteId = 's1';

if (!CIBlockType::GetByID($typeCode)->Fetch()) {
    throw new RuntimeException('The content iblock type is missing. Run setup_faq.php first.');
}

$iblock = CIBlock::GetList([], ['=TYPE' => $typeCode, '=CODE' => $iblockCode])->Fetch();
if (!$iblock) {
    $iblockApi = new CIBlock();
    $iblockId = (int)$iblockApi->Add([
        'ACTIVE' => 'Y',
        'NAME' => 'Сотрудники',
        'CODE' => $iblockCode,
        'IBLOCK_TYPE_ID' => $typeCode,
        'SITE_ID' => [$siteId],
        'SORT' => 200,
        'LIST_PAGE_URL' => '#SITE_DIR#team/',
        'DETAIL_PAGE_URL' => '#SITE_DIR#team/#ELEMENT_CODE#/',
        'GROUP_ID' => ['2' => 'R'],
    ]);
    if ($iblockId <= 0) {
        throw new RuntimeException($iblockApi->LAST_ERROR);
    }
    echo "Created iblock: Team (#{$iblockId})\n";
} else {
    $iblockId = (int)$iblock['ID'];
    echo "Using existing iblock: Team (#{$iblockId})\n";
}

$properties = [
    'POSITION_EN' => ['Должность (EN)', 'S', 'N', ''],
    'RESPONSIBILITIES' => ['Зоны ответственности', 'S', 'Y', ''],
    'REGALIA' => ['Регалии', 'S', 'Y', ''],
    'SHOW_ON_HOME' => ['Показывать на главной', 'L', 'N', 'Y'],
    'SHOW_AS_AUTHOR' => ['Доступен как автор статьи', 'L', 'N', 'Y'],
];

$propertyIds = [];
foreach ($properties as $code => [$name, $type, $multiple, $xmlId]) {
    $property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch();
    if (!$property) {
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $name,
            'ACTIVE' => 'Y',
            'SORT' => count($propertyIds) * 100 + 100,
            'CODE' => $code,
            'PROPERTY_TYPE' => $type,
            'MULTIPLE' => $multiple,
        ];
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

$enumIds = [];
foreach (['SHOW_ON_HOME', 'SHOW_AS_AUTHOR'] as $code) {
    $enum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyIds[$code], 'XML_ID' => 'Y'])->Fetch();
    if (!$enum) {
        throw new RuntimeException("The {$code}=Y enum value is missing.");
    }
    $enumIds[$code] = (int)$enum['ID'];
}

$team = [
    [
        'dmitry-mayer', 'Дмитрий Майер', 'Founder & CEO, Black Pattern',
        'Основатель маркетингового агентства, 9+ лет управления маркетинговой группой. Отвечает за архитектуру оргструктуры и экономику решения в Marketing OS.',
        ['Основатель агентства, 9+ лет управления маркетинговой группой', 'Отвечает за архитектуру оргструктуры и экономику решения'],
    ],
    [
        'vera-kotova', 'Вера Котова', 'Co-founder & COO',
        'Операционное управление агентством: процессы, регламенты и ЦКП должностей. Отвечает за инструменты найма и внедрение системы у заказчика.',
        ['Операционное управление агентством: процессы, регламенты, ЦКП должностей', 'Отвечает за инструменты найма и внедрение системы у заказчика'],
    ],
    [
        'nikita-baranov', 'Никита Баранов', 'Marketing Strategist',
        'Стратегия и метрики: связывает ЦКП ролей с выручкой компании. Отвечает за диагностику и проектирование под бизнес-модель.',
        ['Стратегия и метрики: связывает ЦКП ролей с выручкой компании', 'Отвечает за диагностику и проектирование под бизнес-модель'],
    ],
];

$elementApi = new CIBlockElement();
foreach ($team as $sort => [$code, $name, $position, $bio, $responsibilities]) {
    $existing = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, false, ['ID'])->Fetch();
    $fields = [
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'NAME' => $name,
        'CODE' => $code,
        'SORT' => ($sort + 1) * 100,
        'PREVIEW_TEXT' => $bio,
        'PREVIEW_TEXT_TYPE' => 'text',
    ];
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
        'POSITION_EN' => $position,
        'RESPONSIBILITIES' => $responsibilities,
        'SHOW_ON_HOME' => $enumIds['SHOW_ON_HOME'],
        'SHOW_AS_AUTHOR' => $enumIds['SHOW_AS_AUTHOR'],
    ]);

    $regalia = CIBlockElement::GetProperty($iblockId, $elementId, [], ['CODE' => 'REGALIA'])->Fetch();
    if (($regalia['VALUE'] ?? '') === 'Заполнить реальную регалию') {
        CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, ['REGALIA' => false]);
    }
}

echo "Team setup completed.\n";
