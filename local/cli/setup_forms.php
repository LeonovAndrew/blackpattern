<?php
/** Creates or updates all schema-driven forms and their shared mail event. */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$typeCode = 'forms';
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

$forms = [
    'diagnostic' => [
        'NAME' => 'Диагностика',
        'SORT' => 100,
        'PROPERTIES' => [
            'NAME' => ['NAME' => 'Имя', 'TYPE' => 'S', 'REQUIRED' => 'Y'],
            'COMPANY' => ['NAME' => 'Компания', 'TYPE' => 'S', 'REQUIRED' => 'Y'],
            'ROLE' => ['NAME' => 'Ваша роль', 'TYPE' => 'L', 'REQUIRED' => 'N', 'VALUES' => [
                'owner' => 'Собственник',
                'commercial' => 'Коммерческий директор',
                'ceo' => 'Генеральный директор',
                'hrd' => 'HR-директор',
                'hr' => 'HR-менеджер',
                'cmo' => 'Директор по маркетингу',
                'other' => 'Другое',
            ]],
            'REVENUE' => ['NAME' => 'Годовая выручка', 'TYPE' => 'L', 'REQUIRED' => 'N', 'VALUES' => [
                'under_100' => 'до 100 млн ₽',
                '100_300' => '100–300 млн ₽',
                '300_700' => '300–700 млн ₽',
                '700_2000' => '700 млн – 2 млрд ₽',
                'over_2000' => 'более 2 млрд ₽',
            ]],
            'PHONE' => ['NAME' => 'Телефон', 'TYPE' => 'S', 'REQUIRED' => 'Y'],
        ],
    ],
    'callback_short' => [
        'NAME' => 'Обратный звонок',
        'SORT' => 200,
        'PROPERTIES' => [
            'PHONE' => ['NAME' => 'Телефон', 'TYPE' => 'S', 'REQUIRED' => 'Y'],
        ],
    ],
    'journal_lead' => [
        'NAME' => 'Лид-магнит журнала',
        'SORT' => 300,
        'PROPERTIES' => [
            'ROLE' => ['NAME' => 'Ваша роль', 'TYPE' => 'L', 'REQUIRED' => 'Y', 'VALUES' => [
                'own' => 'Собственник',
                'hr' => 'HR-директор',
                'cmo' => 'Директор по маркетингу',
                'other' => 'Другое',
            ]],
            'EMAIL' => ['NAME' => 'Рабочая почта', 'TYPE' => 'S', 'REQUIRED' => 'Y'],
        ],
    ],
];

$systemProperties = [
    'UTM_SOURCE' => 'UTM source',
    'UTM_MEDIUM' => 'UTM medium',
    'UTM_CAMPAIGN' => 'UTM campaign',
    'UTM_CONTENT' => 'UTM content',
    'UTM_TERM' => 'UTM term',
    'PAGE_URL' => 'Страница отправки',
    'REFERRER' => 'Источник перехода',
    'USER_IP' => 'IP-адрес',
    'CONSENT' => 'Согласие на обработку данных',
    'CAPTCHA_USED' => 'Проверка капчи',
];

$ensureProperty = static function (int $iblockId, string $code, array $definition, int $sort, bool $active = true): int {
    $property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch();
    $fields = [
        'NAME' => $definition['NAME'],
        'ACTIVE' => $active ? 'Y' : 'N',
        'SORT' => $sort,
        'CODE' => $code,
        'PROPERTY_TYPE' => $definition['TYPE'] ?? 'S',
        'MULTIPLE' => 'N',
        'IS_REQUIRED' => $definition['REQUIRED'] ?? 'N',
    ];
    $propertyApi = new CIBlockProperty();
    if ($property) {
        $propertyId = (int)$property['ID'];
        if (!$propertyApi->Update($propertyId, $fields)) {
            throw new RuntimeException($propertyApi->LAST_ERROR);
        }
    } else {
        $fields['IBLOCK_ID'] = $iblockId;
        if (($definition['TYPE'] ?? 'S') === 'L') {
            $fields['LIST_TYPE'] = 'L';
        }
        $propertyId = (int)$propertyApi->Add($fields);
        if ($propertyId <= 0) {
            throw new RuntimeException($propertyApi->LAST_ERROR);
        }
        echo "Created property {$code} for iblock #{$iblockId}\n";
    }

    if (($definition['TYPE'] ?? 'S') === 'L') {
        $enumApi = new CIBlockPropertyEnum();
        foreach (($definition['VALUES'] ?? []) as $xmlId => $value) {
            $enum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyId, 'XML_ID' => $xmlId])->Fetch();
            if (!$enum) {
                $enum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyId, 'VALUE' => $value])->Fetch();
            }
            $enumFields = ['VALUE' => $value, 'SORT' => 100 + (array_search($xmlId, array_keys($definition['VALUES']), true) * 100), 'XML_ID' => $xmlId];
            if ($enum) {
                $enumApi->Update((int)$enum['ID'], $enumFields);
            } else {
                $enumFields['PROPERTY_ID'] = $propertyId;
                if ((int)$enumApi->Add($enumFields) <= 0) {
                    throw new RuntimeException('Unable to create enum value for ' . $code);
                }
            }
        }
    }

    return $propertyId;
};

foreach ($forms as $formCode => $formDefinition) {
    $iblock = CIBlock::GetList([], ['TYPE' => $typeCode, 'CODE' => $formCode, 'CHECK_PERMISSIONS' => 'N'])->Fetch();
    if ($iblock) {
        $iblockId = (int)$iblock['ID'];
        $iblockApi = new CIBlock();
        if (!$iblockApi->Update($iblockId, [
            'ACTIVE' => 'Y',
            'NAME' => $formDefinition['NAME'],
            'SORT' => $formDefinition['SORT'],
        ])) {
            throw new RuntimeException($iblockApi->LAST_ERROR);
        }
        echo "Updated iblock {$formCode} (#{$iblockId})\n";
    } else {
        $iblockApi = new CIBlock();
        $iblockId = (int)$iblockApi->Add([
            'ACTIVE' => 'Y',
            'NAME' => $formDefinition['NAME'],
            'CODE' => $formCode,
            'IBLOCK_TYPE_ID' => $typeCode,
            'SITE_ID' => ['s1'],
            'SORT' => $formDefinition['SORT'],
            'LIST_PAGE_URL' => '',
            'DETAIL_PAGE_URL' => '',
        ]);
        if ($iblockId <= 0) {
            throw new RuntimeException($iblockApi->LAST_ERROR);
        }
        echo "Created iblock {$formCode} (#{$iblockId})\n";
    }

    CIBlock::SetPermission($iblockId, ['1' => 'X', '2' => 'D']);

    $sort = 100;
    foreach ($formDefinition['PROPERTIES'] as $code => $definition) {
        $ensureProperty($iblockId, $code, $definition, $sort);
        $sort += 100;
    }
    foreach ($systemProperties as $code => $name) {
        $ensureProperty($iblockId, $code, ['NAME' => $name, 'TYPE' => 'S', 'REQUIRED' => 'N'], $sort, false);
        $sort += 100;
    }
}

$eventName = 'BLACKPATTERN_FORM_SUBMIT';
if (!CEventType::GetList(['TYPE_ID' => $eventName])->Fetch()) {
    $eventTypeApi = new CEventType();
    if (!$eventTypeApi->Add([
        'LID' => 'ru',
        'EVENT_NAME' => $eventName,
        'NAME' => 'Black Pattern: новая заявка с формы',
        'DESCRIPTION' => "#FORM_NAME# — название формы\n#SUBMISSION_TITLE# — название заявки\n#SUBMISSION_DATA# — поля заявки\n#RECIPIENT_EMAIL# — получатель",
    ])) {
        throw new RuntimeException($eventTypeApi->LAST_ERROR);
    }
}

$by = 'id';
$order = 'asc';
if (!CEventMessage::GetList($by, $order, ['TYPE_ID' => $eventName])->Fetch()) {
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
}

echo "Forms setup completed.\n";
