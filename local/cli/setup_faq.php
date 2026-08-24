<?php
/**
 * Idempotent initial setup for the FAQ content block.
 * Run from the project root: php local/cli/setup_faq.php
 */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$siteId = 's1';
$typeCode = 'content';
$iblockCode = 'faq';

$type = CIBlockType::GetByID($typeCode)->Fetch();
if (!$type) {
    $typeApi = new CIBlockType();
    if (!$typeApi->Add([
        'ID' => $typeCode,
        'SECTIONS' => 'Y',
        'IN_RSS' => 'N',
        'SORT' => 100,
        'LANG' => [
            'ru' => [
                'NAME' => 'Контент',
                'SECTION_NAME' => 'Разделы',
                'ELEMENT_NAME' => 'Элементы',
            ],
        ],
    ])) {
        throw new RuntimeException($typeApi->LAST_ERROR);
    }
    echo "Created iblock type: {$typeCode}\n";
}

$iblock = CIBlock::GetList([], ['=TYPE' => $typeCode, '=CODE' => $iblockCode])->Fetch();
if (!$iblock) {
    $iblockApi = new CIBlock();
    $iblockId = (int)$iblockApi->Add([
        'ACTIVE' => 'Y',
        'NAME' => 'FAQ',
        'CODE' => $iblockCode,
        'IBLOCK_TYPE_ID' => $typeCode,
        'SITE_ID' => [$siteId],
        'SORT' => 100,
        'LIST_PAGE_URL' => '#SITE_DIR#faq/',
        'DETAIL_PAGE_URL' => '#SITE_DIR#faq/#ELEMENT_CODE#/',
        'GROUP_ID' => ['2' => 'R'],
    ]);
    if ($iblockId <= 0) {
        throw new RuntimeException($iblockApi->LAST_ERROR);
    }
    echo "Created iblock: FAQ (#{$iblockId})\n";
} else {
    $iblockId = (int)$iblock['ID'];
    echo "Using existing iblock: FAQ (#{$iblockId})\n";
}

$property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, '=CODE' => 'SHOW_ON_HOME'])->Fetch();
if (!$property) {
    $propertyApi = new CIBlockProperty();
    $propertyId = $propertyApi->Add([
        'IBLOCK_ID' => $iblockId,
        'NAME' => 'Показывать на главной',
        'ACTIVE' => 'Y',
        'SORT' => 100,
        'CODE' => 'SHOW_ON_HOME',
        'PROPERTY_TYPE' => 'L',
        'LIST_TYPE' => 'C',
        'MULTIPLE' => 'N',
        'VALUES' => [['VALUE' => 'Да', 'DEF' => 'Y', 'SORT' => 100, 'XML_ID' => 'Y']],
    ]);
    if ($propertyId <= 0) {
        throw new RuntimeException($propertyApi->LAST_ERROR);
    }
    echo "Created property: SHOW_ON_HOME\n";
} else {
    $propertyId = (int)$property['ID'];
}

$homeEnum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyId, '=XML_ID' => 'Y'])->Fetch();
if (!$homeEnum) {
    throw new RuntimeException('The SHOW_ON_HOME=Y value is missing.');
}
$homeEnumId = (int)$homeEnum['ID'];

$items = [
    ['consultant-or-interim', 'Чем это отличается от найма консультанта или интерим-директора?', 'Консультант и интерим — снова ставка на человека: пока он рядом, работает; ушёл — всё вернулось. Мы отдаём систему: документы, по которым функция воспроизводится и проверяется без нас.'],
    ['strong-marketer', 'У нас уже есть сильный маркетолог. Продукт обесценит его?', 'Наоборот. Сильному специалисту описанная система даёт рамку и прозрачные критерии, а собственнику — язык для диалога с ним. Сопротивляется описанию обычно тот, кому выгодна непрозрачность.'],
    ['large-staff', 'Вы навяжете нам раздутый штат?', 'Нет. Состав ролей проектируется под вашу бизнес-модель и бюджет, часть контура сознательно выводится на аутстафф — это не создаёт постоянных расходов.'],
    ['hiring-after-delivery', 'А если после передачи мы не сможем нанять людей по профилю?', 'Ровно для этого существует авторский надзор. Первый месяц мы сопровождаем старт найма вместе с вашим HRD и корректируем требования по фактическому состоянию рынка труда.'],
    ['template-or-custom', 'Мы получим шаблон или индивидуальную разработку?', 'Индивидуальную. Структура, регламенты и профили собираются под ваш рынок, продукт и объём по итогам диагностики. Шаблонная оргструктура не работает — именно поэтому мы начинаем с интервью, а не с выдачи документов.'],
    ['candidate-search', 'Вы занимаетесь самим наймом — ищете кандидатов?', 'Нет, поиском и закрытием вакансий занимается ваш HR. Мы даём ему методологию, профили, сценарии интервью, тестовые с эталонами и шкалу оценки, а в период надзора разбираем вместе с ним конкретных кандидатов.'],
    ['owner-time', 'Сколько времени это займёт у собственника?', '3–4 сессии по 90 минут плюс участие в защите продукта. Основную работу по разработке системы берёт на себя наша команда.'],
    ['specific-niche', 'Что если у нас сложная или узкая ниша?', 'Мы работаем с компаниями, которые продают через маркетинг: розница, e-commerce, торгово-промышленные. Если ваша модель принципиально другая, мы скажем об этом на диагностике — и не возьмёмся.'],
];

$elementApi = new CIBlockElement();
foreach ($items as $sort => [$code, $name, $answer]) {
    $existing = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, '=CODE' => $code], false, false, ['ID'])->Fetch();
    $fields = [
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'NAME' => $name,
        'CODE' => $code,
        'SORT' => ($sort + 1) * 100,
        'PREVIEW_TEXT' => $answer,
        'PREVIEW_TEXT_TYPE' => 'html',
        'PROPERTY_VALUES' => ['SHOW_ON_HOME' => $homeEnumId],
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
    CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, ['SHOW_ON_HOME' => $homeEnumId]);
}

echo "FAQ setup completed.\n";
