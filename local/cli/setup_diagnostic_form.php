<?php
/** Creates the first schema-driven form and the shared form mail event. */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$typeCode = 'forms';
$iblockCode = 'diagnostic';
if (!CIBlockType::GetByID($typeCode)->Fetch()) {
    $typeApi = new CIBlockType();
    if (!$typeApi->Add([
        'ID' => $typeCode,
        'SECTIONS' => 'N',
        'IN_RSS' => 'N',
        'SORT' => 300,
        'LANG' => ['ru' => ['NAME' => 'Формы', 'SECTION_NAME' => 'Разделы', 'ELEMENT_NAME' => 'Заявки']],
    ])) {
        throw new RuntimeException($typeApi->LAST_ERROR);
    }
    echo "Created iblock type: Forms\n";
}

$iblock = CIBlock::GetList([], ['TYPE' => $typeCode, 'CODE' => $iblockCode])->Fetch();
if (!$iblock) {
    $iblockApi = new CIBlock();
    $iblockId = (int)$iblockApi->Add([
        'ACTIVE' => 'Y',
        'NAME' => 'Диагностика',
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
    echo "Created iblock: Diagnostic form (#{$iblockId})\n";
} else {
    $iblockId = (int)$iblock['ID'];
    echo "Using existing iblock: Diagnostic form (#{$iblockId})\n";
}

$properties = [
    'NAME' => ['Имя', 'S', 'Y', []],
    'COMPANY' => ['Компания', 'S', 'Y', []],
    'ROLE' => ['Ваша роль', 'L', 'N', ['Собственник', 'Коммерческий директор', 'Генеральный директор', 'HR-директор', 'HR-менеджер', 'Директор по маркетингу', 'Другое']],
    'REVENUE' => ['Годовая выручка', 'L', 'N', ['до 100 млн ₽', '100–300 млн ₽', '300–700 млн ₽', '700 млн – 2 млрд ₽', 'более 2 млрд ₽']],
    'PHONE' => ['Телефон', 'S', 'Y', []],
];
foreach ($properties as $sort => $propertyDefinition) {
    [$code, [$name, $type, $required, $enumValues]] = [$sort, $propertyDefinition];
    $property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch();
    if (!$property) {
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $name,
            'ACTIVE' => 'Y',
            'SORT' => (array_search($code, array_keys($properties), true) + 1) * 100,
            'CODE' => $code,
            'PROPERTY_TYPE' => $type,
            'MULTIPLE' => 'N',
            'IS_REQUIRED' => $required,
        ];
        if ($type === 'L') {
            $fields['LIST_TYPE'] = 'L';
            $fields['VALUES'] = array_map(static fn ($value, $index) => ['VALUE' => $value, 'SORT' => ($index + 1) * 100, 'XML_ID' => 'value_' . ($index + 1)], $enumValues, array_keys($enumValues));
        }
        $propertyApi = new CIBlockProperty();
        if ((int)$propertyApi->Add($fields) <= 0) {
            throw new RuntimeException($propertyApi->LAST_ERROR);
        }
        echo "Created property: {$code}\n";
    }
}

$eventName = 'BLACKPATTERN_FORM_SUBMIT';
$eventType = CEventType::GetList(['TYPE_ID' => $eventName])->Fetch();
if (!$eventType) {
    $eventTypeApi = new CEventType();
    if (!$eventTypeApi->Add([
        'LID' => 'ru',
        'EVENT_NAME' => $eventName,
        'NAME' => 'Black Pattern: новая заявка с формы',
        'DESCRIPTION' => "#FORM_NAME# — название формы\n#SUBMISSION_TITLE# — название заявки\n#SUBMISSION_DATA# — поля заявки\n#RECIPIENT_EMAIL# — получатель",
    ])) {
        throw new RuntimeException($eventTypeApi->LAST_ERROR);
    }
    echo "Created mail event type: {$eventName}\n";
}

$by = 'id';
$order = 'asc';
$eventMessage = CEventMessage::GetList($by, $order, ['TYPE_ID' => $eventName])->Fetch();
if (!$eventMessage) {
    $eventMessageApi = new CEventMessage();
    if ((int)$eventMessageApi->Add([
        'ACTIVE' => 'Y',
        'EVENT_NAME' => $eventName,
        'LID' => ['s1'],
        'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO' => '#RECIPIENT_EMAIL#',
        'SUBJECT' => '#SUBMISSION_TITLE#',
        'MESSAGE' => "Новая заявка с сайта\n\nФорма: #FORM_NAME#\nЗаявка: #SUBMISSION_TITLE#\n\n#SUBMISSION_DATA#",
    ]) <= 0) {
        throw new RuntimeException($eventMessageApi->LAST_ERROR);
    }
    echo "Created mail template for {$eventName}\n";
}

echo "Diagnostic form setup completed.\n";
