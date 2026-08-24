<?php
/** Idempotent setup for the Marketing OS modules on the homepage. */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$typeCode = 'homepage';
$iblockCode = 'marketing_os_modules';

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

$iblock = CIBlock::GetList([], ['TYPE' => $typeCode, 'CODE' => $iblockCode])->Fetch();
if (!$iblock) {
    $iblockApi = new CIBlock();
    $iblockId = (int)$iblockApi->Add([
        'ACTIVE' => 'Y',
        'NAME' => 'Модули Marketing OS',
        'CODE' => $iblockCode,
        'IBLOCK_TYPE_ID' => $typeCode,
        'SITE_ID' => ['s1'],
        'SORT' => 100,
        'LIST_PAGE_URL' => '#SITE_DIR#/',
        'GROUP_ID' => ['2' => 'R'],
    ]);
    if ($iblockId <= 0) {
        throw new RuntimeException($iblockApi->LAST_ERROR);
    }
    echo "Created iblock: Marketing OS modules (#{$iblockId})\n";
} else {
    $iblockId = (int)$iblock['ID'];
    echo "Using existing iblock: Marketing OS modules (#{$iblockId})\n";
}

$properties = [
    'MODULE_NUMBER' => ['Номер модуля', 'S', 'N'],
    'INCLUSIONS' => ['Состав модуля', 'S', 'Y'],
    'SHOW_ON_HOME' => ['Показывать на главной', 'L', 'N'],
];
$propertyIds = [];
foreach ($properties as $code => [$name, $type, $multiple]) {
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

$showOnHome = CIBlockPropertyEnum::GetList([], [
    'PROPERTY_ID' => $propertyIds['SHOW_ON_HOME'],
    'XML_ID' => 'Y',
])->Fetch();
if (!$showOnHome) {
    throw new RuntimeException('The SHOW_ON_HOME enum value is missing.');
}

$modules = [
    ['org-structure', 'Оргструктура отдела', 'Схема под вашу бизнес-модель, а не шаблон. Директор управляет ролями, а не задачами.', 'М-01', [
        'Состав ролей: какие должности нужны, а какие избыточны',
        'Уровни подчинения и зоны ответственности',
        'Разделение: что в штат, что на аутстафф',
        'Карта взаимодействий — кто от кого принимает вход',
        'План поэтапного укомплектования под бюджет',
    ]],
    ['regulations-and-ckp', 'Регламенты и ЦКП должностей', 'Шесть обязательных блоков на каждую роль. Размытая должность становится проверяемой единицей.', 'М-02', [
        'Ценный конечный продукт — результат, за который платят',
        '3–5 метрик оценки, привязанных к деньгам',
        'Границы полномочий: что решает сам, что согласовывает',
        'Регламент процессов и формат сдачи работы',
        'Карта входов, выходов и точек конфликта',
    ]],
    ['hiring-system', 'Система найма для HR-директора', 'Рабочий инструмент вместо задачи «найди нам хорошего маркетолога». Готовый комплект, а не рекомендации.', 'М-03', [
        'Профили должностей: требования, опыт, стоп-факторы',
        'Структура интервью: вопросы, сильные ответы, красные флаги',
        'Тестовые задания с эталонами решений',
        'Балльная шкала оценки и проходной порог',
        'Чек-листы 30 / 60 / 90 дней испытательного срока',
    ]],
    ['contractor-network', 'Контур подрядчиков', 'Часть функций сознательно выводится наружу. Это не раздувает постоянные расходы компании.', 'М-04', [
        'Критерии отбора по типам подрядчиков',
        'Что смотреть в портфолио и спрашивать до подписания',
        'Шаблон ТЗ, при котором работу можно объективно принять',
        'Процедура приёмки и критерии соответствия',
        'Расчёт эффективности и стоп-факторы',
    ]],
];

$elementApi = new CIBlockElement();
foreach ($modules as $sort => [$code, $name, $description, $number, $inclusions]) {
    $existing = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, false, ['ID'])->Fetch();
    $fields = [
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'NAME' => $name,
        'CODE' => $code,
        'SORT' => ($sort + 1) * 100,
        'PREVIEW_TEXT' => $description,
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
        'MODULE_NUMBER' => $number,
        'INCLUSIONS' => $inclusions,
        'SHOW_ON_HOME' => (int)$showOnHome['ID'],
    ]);
}

echo "Marketing OS modules setup completed.\n";
