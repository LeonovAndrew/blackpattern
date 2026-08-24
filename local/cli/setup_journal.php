<?php
/**
 * Creates the Journal iblock and initial demo articles.
 * Run from the project root: php local/cli/setup_journal.php
 */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$siteId = 's1';
$typeCode = 'content';
$iblockCode = 'journal';

if (!CIBlockType::GetByID($typeCode)->Fetch()) {
    $typeApi = new CIBlockType();
    if (!$typeApi->Add([
        'ID' => $typeCode,
        'SECTIONS' => 'Y',
        'IN_RSS' => 'N',
        'SORT' => 100,
        'LANG' => ['ru' => ['NAME' => 'Контент', 'SECTION_NAME' => 'Разделы', 'ELEMENT_NAME' => 'Элементы']],
    ])) {
        throw new RuntimeException($typeApi->LAST_ERROR);
    }
    echo "Created iblock type: {$typeCode}\n";
}

$iblock = CIBlock::GetList([], ['TYPE' => $typeCode, 'CODE' => $iblockCode])->Fetch();
if (!$iblock) {
    $iblockApi = new CIBlock();
    $iblockId = (int)$iblockApi->Add([
        'ACTIVE' => 'Y',
        'NAME' => 'Журнал',
        'CODE' => $iblockCode,
        'IBLOCK_TYPE_ID' => $typeCode,
        'SITE_ID' => [$siteId],
        'SORT' => 300,
        'LIST_PAGE_URL' => '#SITE_DIR#journal/',
        'DETAIL_PAGE_URL' => '#SITE_DIR#journal/#ELEMENT_CODE#/',
        'GROUP_ID' => ['2' => 'R'],
    ]);
    if ($iblockId <= 0) {
        throw new RuntimeException($iblockApi->LAST_ERROR);
    }
    echo "Created iblock: Journal (#{$iblockId})\n";
} else {
    $iblockId = (int)$iblock['ID'];
    echo "Using existing iblock: Journal (#{$iblockId})\n";
}

$team = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => 'team'])->Fetch();
$teamIblockId = (int)($team['ID'] ?? 0);

$properties = [
    'ROLE' => ['Роль читателя', 'L', 'Y', 100, [
        ['Собственнику', 'own'], ['HR-директору', 'hr'], ['Директору по маркетингу', 'cmo'],
    ]],
    'THEME' => ['Тема', 'L', 'Y', 200, [
        ['Найм', 'hiring'], ['Оргструктура', 'structure'], ['Метрики', 'metrics'],
        ['Экономика', 'economics'], ['Контроль', 'control'], ['Интервью', 'interview'],
    ]],
    'READING_TIME' => ['Время чтения, мин.', 'N', 'N', 300, []],
    'AUTHORS' => ['Авторы', 'E', 'Y', 400, []],
    'FEATURED' => ['Главный материал', 'L', 'N', 500, [['Да', 'Y']]],
    'SHOW_ON_HOME' => ['Показывать на главной', 'L', 'N', 600, [['Да', 'Y']]],
    'RELATED_ARTICLES' => ['Похожие материалы', 'E', 'Y', 700, []],
    'SEO_TITLE' => ['SEO: Title', 'S', 'N', 800, []],
    'SEO_DESCRIPTION' => ['SEO: Description', 'S', 'N', 900, []],
    'SEO_OG_IMAGE' => ['SEO: изображение для соцсетей', 'F', 'N', 1000, []],
];

$propertyIds = [];
foreach ($properties as $code => [$name, $type, $multiple, $sort, $values]) {
    $property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch();
    if (!$property) {
        $fields = [
            'IBLOCK_ID' => $iblockId,
            'NAME' => $name,
            'ACTIVE' => 'Y',
            'SORT' => $sort,
            'CODE' => $code,
            'PROPERTY_TYPE' => $type,
            'MULTIPLE' => $multiple,
        ];
        if ($type === 'L') {
            $fields['LIST_TYPE'] = 'C';
            $fields['VALUES'] = array_map(static fn(array $value, int $index): array => [
                'VALUE' => $value[0], 'XML_ID' => $value[1], 'SORT' => ($index + 1) * 100,
            ], $values, array_keys($values));
        }
        if ($type === 'E') {
            $fields['LINK_IBLOCK_ID'] = $code === 'AUTHORS' ? $teamIblockId : $iblockId;
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
    if ($type === 'L') {
        foreach ($values as $index => [$value, $xmlId]) {
            $enum = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyId, 'XML_ID' => $xmlId])->Fetch();
            if (!$enum) {
                $enumApi = new CIBlockPropertyEnum();
                $enumId = (int)$enumApi->Add([
                    'PROPERTY_ID' => $propertyId,
                    'VALUE' => $value,
                    'XML_ID' => $xmlId,
                    'SORT' => ($index + 1) * 100,
                ]);
                if ($enumId <= 0) {
                    throw new RuntimeException($enumApi->LAST_ERROR);
                }
                echo "Created enum: {$code}/{$xmlId}\n";
            }
        }
    }
}

$enumIds = [];
foreach (['ROLE', 'THEME', 'FEATURED', 'SHOW_ON_HOME'] as $code) {
    $enumIds[$code] = [];
    $result = CIBlockPropertyEnum::GetList(['SORT' => 'ASC'], ['PROPERTY_ID' => $propertyIds[$code]]);
    while ($enum = $result->Fetch()) {
        $enumIds[$code][$enum['XML_ID']] = (int)$enum['ID'];
    }
}

$author = CIBlockElement::GetList([], ['IBLOCK_ID' => $teamIblockId, 'CODE' => 'dmitry-mayer'], false, false, ['ID'])->Fetch();
$authorId = (int)($author['ID'] ?? 0);

$articles = [
    ['cost-of-bad-marketing-hire', 'Сколько на самом деле стоит неудачный найм директора по маркетингу', '8,6 млн ₽ за цикл в прямых расходах. Раскладываем сумму по статьям и показываем, почему её платят по два-три раза.', ['own'], ['economics', 'hiring'], 7, true, '2026-07-12 10:00:00', '<p>Вы наняли директора по маркетингу. Через восемь месяцев расстались. Взяли следующего — и всё повторилось. Прежде чем искать третьего, посчитайте стоимость уже пройденного круга.</p><h2>Из чего складывается один цикл</h2><p>Мы считаем только прямые, наблюдаемые расходы: оплату труда, бюджет на непроверенные гипотезы и подбор замены. Даже без упущенной выручки один цикл обходится компании в миллионы рублей.</p><h2>Что делать</h2><p>Начните не с поиска следующего человека, а с проектирования роли: опишите измеримый результат, разделите несовместимые функции и дайте HR проверяемый профиль кандидата.</p>'],
    ['staff-or-outsource', 'Маркетолог в штат или на аутсорс: как считать, а не гадать', 'Модель выбора ролей: что держать внутри компании, а что выводить наружу без потери контроля.', ['own'], ['economics'], 6, false, '2026-07-05 10:00:00', '<p>Решение о найме начинается с того, какую функцию вы хотите контролировать каждый день. Сравните стоимость, скорость и управляемость каждой роли, а не выбирайте формат по привычке.</p>'],
    ['check-marketing-work', 'Как проверить работу маркетолога, если вы не маркетолог', 'Пять вопросов, по которым видно, управляем ли маркетинг — даже без погружения в его инструменты.', ['own'], ['control'], 8, false, '2026-06-28 10:00:00', '<p>Собственнику не нужно знать все рекламные кабинеты. Нужны прозрачные цели, метрики, ритм отчётности и понятная связь действий с результатом.</p>'],
    ['marketing-job-profile', 'Как составить профиль должности маркетолога: шесть блоков', 'Что включить в профиль, чтобы сравнивать кандидатов по одной линейке, а не по впечатлению.', ['hr'], ['hiring'], 9, false, '2026-06-21 10:00:00', '<p>Профиль должности превращает расплывчатое «нужен сильный маркетолог» в проверяемую задачу для рекрутера и руководителя.</p>'],
    ['marketing-interview-questions', '12 вопросов для собеседования маркетолога и признаки сильного ответа', 'Что спрашивать, чтобы отличить сильного кандидата от красноречивого.', ['hr'], ['interview', 'hiring'], 10, false, '2026-06-14 10:00:00', '<p>Вопросы должны проверять опыт решения задач, а не владение терминологией. Заранее зафиксируйте признаки сильного и слабого ответа.</p>'],
    ['marketing-test-task', 'Тестовое задание для маркетолога: как составить и как оценить', 'Как подготовить задание и шкалу оценки, чтобы решение не принималось «на глаз».', ['hr'], ['hiring'], 7, false, '2026-06-07 10:00:00', '<p>Хорошее тестовое задание короткое, похоже на реальную рабочую ситуацию и проверяется по заранее известным критериям.</p>'],
    ['five-marketing-roles', 'Пять ролей, которые нельзя совмещать в одном человеке', 'Почему бренд, performance, аналитика, стратегия и продакшн — разные профессии, а не один универсал.', ['cmo'], ['structure'], 6, false, '2026-05-31 10:00:00', '<p>Разделение ролей — не раздувание штата. Это способ ясно определить, что вы покупаете в штат, а что закрываете внешним контуром.</p>'],
    ['marketing-kpi', 'ЦКП маркетолога: как привязать должность к выручке компании', 'Как сформулировать ценный конечный продукт так, чтобы по нему было видно: сделано или нет.', ['cmo'], ['metrics'], 8, false, '2026-05-24 10:00:00', '<p>ЦКП превращает должность из набора обязанностей в измеримый результат, за который можно отвечать и которым можно управлять.</p>'],
    ['marketing-department-structure', 'Как собрать оргструктуру маркетинга под бизнес-модель компании', 'Практический порядок проектирования команды: от выручки и воронки к ролям, подрядчикам и метрикам.', ['cmo', 'own'], ['structure', 'metrics'], 11, false, '2026-05-17 10:00:00', '<p>Оргструктура начинается не со списка должностей, а с бизнес-модели, ограничений и задач, которые маркетинг должен решить в ближайший период.</p>'],
];

$elementApi = new CIBlockElement();
$articleIds = [];
foreach ($articles as $sort => [$code, $name, $preview, $roles, $themes, $readingTime, $featured, $activeFrom, $detail]) {
    $existing = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code], false, false, ['ID'])->Fetch();
    $fields = [
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'NAME' => $name,
        'CODE' => $code,
        'SORT' => ($sort + 1) * 100,
        'ACTIVE_FROM' => date('d.m.Y H:i:s', strtotime($activeFrom)),
        'PREVIEW_TEXT' => $preview,
        'PREVIEW_TEXT_TYPE' => 'text',
        'DETAIL_TEXT' => $detail,
        'DETAIL_TEXT_TYPE' => 'html',
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
    $articleIds[$code] = $elementId;
    CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [
        'ROLE' => array_map(static fn(string $value): int => $enumIds['ROLE'][$value], $roles),
        'THEME' => array_map(static fn(string $value): int => $enumIds['THEME'][$value], $themes),
        'READING_TIME' => $readingTime,
        'AUTHORS' => $authorId ? [$authorId] : false,
        'FEATURED' => $featured ? $enumIds['FEATURED']['Y'] : false,
        'SHOW_ON_HOME' => $enumIds['SHOW_ON_HOME']['Y'],
    ]);
}

if (!empty($articleIds['cost-of-bad-marketing-hire'])) {
    CIBlockElement::SetPropertyValuesEx($articleIds['cost-of-bad-marketing-hire'], $iblockId, [
        'RELATED_ARTICLES' => array_values(array_filter([
            $articleIds['staff-or-outsource'] ?? 0,
            $articleIds['check-marketing-work'] ?? 0,
            $articleIds['marketing-job-profile'] ?? 0,
        ])),
    ]);
}

echo "Journal setup completed.\n";
