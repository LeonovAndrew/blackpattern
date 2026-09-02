<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/seo/functions.php';

$siteSettings = [
    'PHONE' => '+7 (495) 191-98-20',
    'PHONE_HREF' => 'tel:+74951919820',
    'SEO_HOME_TITLE' => 'Marketing OS — построение отдела маркетинга под ключ',
    'SEO_HOME_DESCRIPTION' => 'Отдел маркетинга под ключ: оргструктура, регламенты и ЦКП, система найма для HR, контур подрядчиков.',
];
if (\Bitrix\Main\Loader::includeModule('blackpattern.settings')) {
    $siteSettings = \BlackPattern\Settings\SiteSettings::get();
}
$APPLICATION->SetTitle($siteSettings['SEO_HOME_TITLE']);
$APPLICATION->SetPageProperty('title', $siteSettings['SEO_HOME_TITLE']);
$APPLICATION->SetPageProperty('description', $siteSettings['SEO_HOME_DESCRIPTION']);
$APPLICATION->SetPageProperty('og_type', 'website');

$faqIblockId = 0;
$faqHomeEnumId = 0;
$teamIblockId = 0;
$teamHomeEnumId = 0;
$marketingModulesIblockId = 0;
$marketingModulesHomeEnumId = 0;
$pricesIblockId = 0;
$pricesHomeEnumId = 0;
$journalIblockId = 0;
$journalHomeEnumId = 0;
$heroIblockId = 0;
if (\Bitrix\Main\Loader::includeModule('iblock')) {
    $heroIblock = CIBlock::GetList([], ['TYPE' => 'homepage', 'CODE' => 'hero'])->Fetch();
    $heroIblockId = (int)($heroIblock['ID'] ?? 0);
    $faqIblock = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => 'faq'])->Fetch();
    $faqIblockId = (int)($faqIblock['ID'] ?? 0);
    if ($faqIblockId > 0) {
        $faqHomeEnum = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $faqIblockId, 'CODE' => 'SHOW_ON_HOME', 'XML_ID' => 'Y'])->Fetch();
        $faqHomeEnumId = (int)($faqHomeEnum['ID'] ?? 0);
    }
    $teamIblock = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => 'team'])->Fetch();
    $teamIblockId = (int)($teamIblock['ID'] ?? 0);
    if ($teamIblockId > 0) {
        $teamHomeEnum = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $teamIblockId, 'CODE' => 'SHOW_ON_HOME', 'XML_ID' => 'Y'])->Fetch();
        $teamHomeEnumId = (int)($teamHomeEnum['ID'] ?? 0);
    }
    $marketingModulesIblock = CIBlock::GetList([], ['TYPE' => 'homepage', 'CODE' => 'marketing_os_modules'])->Fetch();
    $marketingModulesIblockId = (int)($marketingModulesIblock['ID'] ?? 0);
    if ($marketingModulesIblockId > 0) {
        $marketingModulesHomeEnum = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $marketingModulesIblockId, 'CODE' => 'SHOW_ON_HOME', 'XML_ID' => 'Y'])->Fetch();
        $marketingModulesHomeEnumId = (int)($marketingModulesHomeEnum['ID'] ?? 0);
    }
    $pricesIblock = CIBlock::GetList([], ['TYPE' => 'homepage', 'CODE' => 'marketing_os_prices'])->Fetch();
    $pricesIblockId = (int)($pricesIblock['ID'] ?? 0);
    if ($pricesIblockId > 0) {
        $pricesHomeEnum = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $pricesIblockId, 'CODE' => 'SHOW_ON_HOME', 'XML_ID' => 'Y'])->Fetch();
        $pricesHomeEnumId = (int)($pricesHomeEnum['ID'] ?? 0);
    }
    $journalIblock = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => 'journal'])->Fetch();
    $journalIblockId = (int)($journalIblock['ID'] ?? 0);
    if ($journalIblockId > 0) {
        $journalHomeEnum = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $journalIblockId, 'CODE' => 'SHOW_ON_HOME', 'XML_ID' => 'Y'])->Fetch();
        $journalHomeEnumId = (int)($journalHomeEnum['ID'] ?? 0);
    }
}

blackPatternSeoAddSchema(blackPatternSeoServiceSchema($pricesIblockId, $siteSettings));
blackPatternSeoAddSchema(blackPatternSeoFaqSchema($faqIblockId, $faqHomeEnumId));
foreach (blackPatternSeoPersonSchemas($teamIblockId, $teamHomeEnumId, $siteSettings) as $personSchema) {
    blackPatternSeoAddSchema($personSchema);
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
?>
<?php if ($heroIblockId > 0):
    $APPLICATION->IncludeComponent('bitrix:news.list', 'hero', [
        'IBLOCK_TYPE' => 'homepage', 'IBLOCK_ID' => $heroIblockId, 'NEWS_COUNT' => '1',
        'SORT_BY1' => 'SORT', 'SORT_ORDER1' => 'ASC', 'FIELD_CODE' => ['ID'],
        'PROPERTY_CODE' => [
            'KICKER', 'H1_LINE_1', 'H1_LINE_2', 'H1_ACCENT', 'H1_LINE_3', 'DESCRIPTION',
            'PRIMARY_TEXT', 'PRIMARY_URL', 'SECONDARY_TEXT', 'SECONDARY_URL', 'VIDEO_MP4', 'VIDEO_WEBM', 'POSTER',
            'STAT_1_LABEL', 'STAT_1_VALUE', 'STAT_1_ACCENT', 'STAT_2_LABEL', 'STAT_2_VALUE', 'STAT_2_ACCENT',
            'STAT_3_LABEL', 'STAT_3_VALUE', 'STAT_3_ACCENT', 'STAT_4_LABEL', 'STAT_4_VALUE', 'STAT_4_ACCENT',
        ],
        'CHECK_DATES' => 'Y', 'SET_TITLE' => 'N', 'SET_STATUS_404' => 'N', 'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'CACHE_TYPE' => 'A', 'CACHE_TIME' => '36000000', 'CACHE_GROUPS' => 'Y',
        'DISPLAY_TOP_PAGER' => 'N', 'DISPLAY_BOTTOM_PAGER' => 'N',
    ]);
endif; ?>

<section id="problem"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/problem.php', [], ['MODE' => 'html', 'NAME' => 'Вступление блока проблемы']); ?>
    <div class="roles rv"><div class="role"><b>Бренд</b><span>Смыслы</span></div><div class="role"><b>Performance</b><span>Трафик · CAC</span></div><div class="role"><b>Аналитика</b><span>Атрибуция · BI</span></div><div class="role"><b>Стратегия</b><span>Рынок · Цены</span></div><div class="role"><b>Продакшн</b><span>Сайт · Контент</span></div></div>
    <div class="fun rv"><div class="fun-l"></div><div class="fun-b">Один человек — «директор по маркетингу»</div><div class="fun-l"></div><div class="fun-b gh">Не существует → расставание через 6–9 месяцев</div></div>
    <p class="verdict rv">Ошибка не в человеке.<br><em>Ошибка в устройстве роли.</em></p>
</div></section>

<section class="off bord"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/cycle.php', [], ['MODE' => 'html', 'NAME' => 'Вступление цикла найма']); ?>
    <div class="tl-grid rv">
        <div class="tl"><span class="m">МЕС. 0</span><b>Найм</b><p>Завышенные, нигде не зафиксированные ожидания.</p></div>
        <div class="tl"><span class="m">МЕС. 1–3</span><b>«Адаптация»</b><p>Результата нет по определению — критериев результата не существует.</p></div>
        <div class="tl"><span class="m">МЕС. 4–6</span><b>Недоверие</b><p>Собственник перестаёт понимать, за что платит.</p></div>
        <div class="tl"><span class="m">МЕС. 7–9</span><b>Конфликт</b><p>Поиск виноватого. Увольнение.</p></div>
        <div class="tl"><span class="m">МЕС. 10–13</span><b>Простой</b><p>Поиск замены. Маркетинг стоит, конкурент растёт.</p></div>
    </div>
</div></section>

<section id="calc"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/calculator-copy.php', [], ['MODE' => 'html', 'NAME' => 'Вступление калькулятора']); ?>
    <div class="calc rv">
        <div>
            <div class="calc-mobile-total">
                <span>Итого за один цикл</span>
                <output id="mobile-total" for="salary months budget">8 601 000 ₽</output>
            </div>
            <div class="fld"><label for="salary">Оклад директора по маркетингу</label><span class="hint">Полная стоимость сотрудника в месяц, включая налоги и взносы.</span><div class="fld-r"><input type="range" id="salary" min="150000" max="1500000" step="10000" value="600000"><span class="val" id="salary-v">600 000 ₽</span></div></div>
            <div class="fld"><label for="months">Месяцев до расставания</label><span class="hint">По нашей практике решение об увольнении принимается на 6–9-м месяце.</span><div class="fld-r"><input type="range" id="months" min="3" max="18" step="1" value="9"><span class="val" id="months-v">9 мес.</span></div></div>
            <div class="fld"><label for="budget">Бюджет на непроверенные гипотезы</label><span class="hint">Часть рекламного бюджета в месяц, которая тратится без методологии проверки.</span><div class="fld-r"><input type="range" id="budget" min="0" max="1500000" step="1000" value="267000"><span class="val" id="budget-v">267 000 ₽</span></div></div>
        </div>
        <div class="out"><span class="tag">Расчёт прямых потерь</span><div style="margin-top:22px"><div class="orow"><span>Оплата труда за период</span><span id="o1">5 400 000 ₽</span></div><div class="orow"><span>Бюджет без методологии проверки</span><span id="o2">2 403 000 ₽</span></div><div class="orow"><span>Подбор замены и простой функции</span><span id="o3">798 000 ₽</span></div></div><div class="o-tot"><span class="tag">Итого за один цикл</span><div class="big" id="total">8 601 000 ₽</div><p class="cap">Простой считаем как ~1,33 оклада: 3–4 месяца поиска замены, в течение которых функция маркетинга не работает.</p></div><div class="vs"><span class="l">Marketing OS с авторским надзором — 600 000 ₽ фиксированно. Ваша ошибка дороже в</span><span class="r" id="ratio">× 14</span></div><a href="#cta" class="btn btn-inv" style="margin-top:26px;width:100%;justify-content:center">Разобрать мою ситуацию →</a></div>
    </div>
</div></section>

<section class="dark"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/inaction-copy.php', [], ['MODE' => 'html', 'NAME' => 'Вступление стоимости бездействия']); ?>
    <div class="loss rv">
        <div class="chart" aria-label="Сравнение стоимости ошибок найма и Marketing OS">
            <div class="col"><span class="cv">8,6 млн</span><div class="bar" style="--h:33%"></div><span class="lb">1 цикл</span></div>
            <div class="col"><span class="cv">17,2 млн</span><div class="bar" style="--h:67%"></div><span class="lb">2 цикла</span></div>
            <div class="col"><span class="cv">25,8 млн</span><div class="bar" style="--h:100%"></div><span class="lb">3 цикла</span></div>
            <div class="col hot"><span class="cv">0,6 млн</span><div class="bar" style="--h:2.3%"></div><span class="lb">Marketing OS<br>один раз</span></div>
        </div>
        <div class="metrics"><div class="metric"><div class="mv">× 14</div><div class="ml">Один цикл ошибки найма дороже полной стоимости Marketing OS: 8,6 млн против 600 тыс.</div></div><div class="metric"><div class="mv">+1333 %</div><div class="ml">ROI при предотвращении всего одного цикла — это +8,0 млн ₽ сохранённых прямых расходов.</div></div><div class="metric"><div class="mv">1 раз</div><div class="ml">Система описывается один раз и остаётся активом компании. Стоимость бездействия — нарастающая.</div></div></div>
    </div>
</div></section>

<section><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/why-copy.php', [], ['MODE' => 'html', 'NAME' => 'Вступление блока причин']); ?>
    <div class="g4 rv"><div class="cell"><span class="n">01</span><h3>Собственник</h3><p>Не маркетолог по профессии. Не может спроектировать структуру функции, которой не владеет.</p></div><div class="cell"><span class="n">02</span><h3>Действующий CMO</h3><p>Заинтересован в непрозрачности: описанная система делает его заменяемым.</p></div><div class="cell"><span class="n">03</span><h3>HR-директор</h3><p>Нет предметной экспертизы, чтобы формализовать профиль и проверить кандидата по существу.</p></div><div class="cell"><span class="n">04</span><h3>Внешние агентства</h3><p>Заинтересованы в том, чтобы клиент оставался зависимым, а не становился автономным.</p></div></div>
</div></section>

<?php if ($marketingModulesIblockId > 0): ?>
<section id="product" class="off bord"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/product-intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление блока продукта']); ?>
    <?php
    $GLOBALS['blackpatternMarketingModulesFilter'] = ['PROPERTY_SHOW_ON_HOME' => $marketingModulesHomeEnumId];
    $APPLICATION->IncludeComponent(
        'bitrix:news.list',
        'marketing-os-modules',
        [
            'IBLOCK_TYPE' => 'homepage',
            'IBLOCK_ID' => $marketingModulesIblockId,
            'NEWS_COUNT' => '100',
            'SORT_BY1' => 'SORT',
            'SORT_ORDER1' => 'ASC',
            'FIELD_CODE' => ['PREVIEW_TEXT'],
            'PROPERTY_CODE' => ['MODULE_NUMBER', 'INCLUSIONS', 'SHOW_ON_HOME'],
            'FILTER_NAME' => 'blackpatternMarketingModulesFilter',
            'CHECK_DATES' => 'Y',
            'SET_TITLE' => 'N',
            'SET_STATUS_404' => 'N',
            'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
            'CACHE_TYPE' => 'A',
            'CACHE_TIME' => '36000000',
            'CACHE_FILTER' => 'Y',
            'CACHE_GROUPS' => 'Y',
            'DISPLAY_TOP_PAGER' => 'N',
            'DISPLAY_BOTTOM_PAGER' => 'N',
        ]
    );
    ?>
    <?php $APPLICATION->IncludeFile('/local/include/home/org-and-ckp.php', [], ['MODE' => 'html', 'NAME' => 'Схема оргструктуры и примеры ЦКП']); ?>
</div></section>
<?php endif; ?>

<section id="audience"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/audience-intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление блока аудитории']); ?>
    <div class="tabs rv" role="tablist" aria-label="Аудитория"><button class="tab" id="tab-owner" type="button" role="tab" aria-selected="true" aria-controls="panel-owner" data-tab="owner">Собственнику</button><button class="tab" id="tab-hr" type="button" role="tab" aria-selected="false" aria-controls="panel-hr" data-tab="hr" tabindex="-1">HR-директору</button></div>
    <div class="panel" data-active="true" id="panel-owner" role="tabpanel" aria-labelledby="tab-owner" tabindex="0"><?php $APPLICATION->IncludeFile('/local/include/home/audience-owner.php', [], ['MODE' => 'html', 'NAME' => 'Аудитория: собственнику']); ?></div>
    <div class="panel" data-active="false" id="panel-hr" role="tabpanel" aria-labelledby="tab-hr" tabindex="0" hidden><?php $APPLICATION->IncludeFile('/local/include/home/audience-hr.php', [], ['MODE' => 'html', 'NAME' => 'Аудитория: HR-директору']); ?></div>
</div></section>

<section class="off bord"><div class="wrap"><?php $APPLICATION->IncludeFile('/local/include/home/alternatives-intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление блока альтернатив']); ?><?php $APPLICATION->IncludeFile('/local/include/home/alternatives.php', [], ['MODE' => 'html', 'NAME' => 'Сравнение альтернатив']); ?></div></section>

<section id="process"><div class="wrap"><?php $APPLICATION->IncludeFile('/local/include/home/process-intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление блока процесса']); ?><?php $APPLICATION->IncludeFile('/local/include/home/process.php', [], ['MODE' => 'html', 'NAME' => 'Этапы процесса']); ?></div></section>

<?php if ($pricesIblockId > 0): ?>
<section id="price" class="off bord"><div class="wrap"><?php $APPLICATION->IncludeFile('/local/include/home/price-intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление блока стоимости']); ?>
<?php
$GLOBALS['blackpatternPricesFilter'] = ['PROPERTY_SHOW_ON_HOME' => $pricesHomeEnumId];
$APPLICATION->IncludeComponent('bitrix:news.list', 'marketing-os-prices', [
    'IBLOCK_TYPE' => 'homepage', 'IBLOCK_ID' => $pricesIblockId, 'NEWS_COUNT' => '100', 'SORT_BY1' => 'SORT', 'SORT_ORDER1' => 'ASC',
    'FIELD_CODE' => ['PREVIEW_TEXT'], 'PROPERTY_CODE' => ['TIER', 'PRICE', 'TERMS', 'FEATURED', 'SHOW_ON_HOME'], 'FILTER_NAME' => 'blackpatternPricesFilter',
    'CHECK_DATES' => 'Y', 'SET_TITLE' => 'N', 'SET_STATUS_404' => 'N', 'INCLUDE_IBLOCK_INTO_CHAIN' => 'N', 'CACHE_TYPE' => 'A', 'CACHE_TIME' => '36000000', 'CACHE_FILTER' => 'Y', 'CACHE_GROUPS' => 'Y', 'DISPLAY_TOP_PAGER' => 'N', 'DISPLAY_BOTTOM_PAGER' => 'N',
]);
?>
<p class="price-summary rv">Продукт окупается не тем, что вы зарабатываете. <span>А тем, что перестаёте тратить.</span></p></div></section>
<?php endif; ?>

<section class="pcta">
    <div class="wrap in">
        <div class="pcta-l">
            <b>Не хотите заполнять форму? Оставьте телефон — перезвоним и обсудим задачу.</b>
            <span>Перезвоним в течение рабочего дня. Без продаж — звонит эксперт.</span>
        </div>
        <?php if (\Bitrix\Main\Loader::includeModule('blackpattern.settings')): ?>
            <?= \BlackPattern\Forms\FormRenderer::render('callback_short', [
                'SUBMIT_TEXT' => 'Перезвоните мне',
                'CLASS' => 'pcta-f bp-form-compact',
                'COMPACT' => 'Y',
            ]); ?>
        <?php else: ?>
            <a class="btn btn-red" href="<?= htmlspecialcharsbx($siteSettings['PHONE_HREF']); ?>"><?= htmlspecialcharsbx($siteSettings['PHONE']); ?></a>
        <?php endif; ?>
    </div>
</section>

<section class="proof"><div class="wrap"><?php $APPLICATION->IncludeFile('/local/include/home/proof.php', [], ['MODE' => 'html', 'NAME' => 'Цифры и доказательства']); ?></div></section>

<section id="experts" class="off bord"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/experts-intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление блока экспертов']); ?>
    <?php if ($teamIblockId > 0):
        $GLOBALS['blackpatternTeamFilter'] = ['PROPERTY_SHOW_ON_HOME' => $teamHomeEnumId];
        $APPLICATION->IncludeComponent(
            'bitrix:news.list',
            'team',
            [
                'IBLOCK_TYPE' => 'content',
                'IBLOCK_ID' => $teamIblockId,
                'NEWS_COUNT' => '100',
                'SORT_BY1' => 'SORT',
                'SORT_ORDER1' => 'ASC',
                'FIELD_CODE' => ['PREVIEW_PICTURE'],
                'PROPERTY_CODE' => ['POSITION_EN', 'RESPONSIBILITIES', 'REGALIA', 'SHOW_ON_HOME'],
                'FILTER_NAME' => 'blackpatternTeamFilter',
                'CHECK_DATES' => 'Y',
                'SET_TITLE' => 'N',
                'SET_STATUS_404' => 'N',
                'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                'CACHE_TYPE' => 'A',
                'CACHE_TIME' => '36000000',
                'CACHE_FILTER' => 'Y',
                'CACHE_GROUPS' => 'Y',
                'DISPLAY_TOP_PAGER' => 'N',
                'DISPLAY_BOTTOM_PAGER' => 'N',
            ]
        );
    endif; ?>
</div></section>

<?php if ($journalIblockId > 0 && $journalHomeEnumId > 0): ?>
<section id="journal" class="off bord"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/journal-intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление журнала на главной']); ?>
    <?php
    $GLOBALS['blackpatternJournalHomeFilter'] = ['PROPERTY_SHOW_ON_HOME' => $journalHomeEnumId];
    $APPLICATION->IncludeComponent('bitrix:news.list', 'journal-home', [
        'IBLOCK_TYPE' => 'content', 'IBLOCK_ID' => $journalIblockId, 'NEWS_COUNT' => '100',
        'SORT_BY1' => 'PROPERTY_FEATURED', 'SORT_ORDER1' => 'DESC', 'SORT_BY2' => 'ACTIVE_FROM', 'SORT_ORDER2' => 'DESC',
        'FIELD_CODE' => ['PREVIEW_TEXT', 'ACTIVE_FROM'], 'PROPERTY_CODE' => ['ROLE', 'THEME', 'READING_TIME', 'SHOW_ON_HOME'],
        'FILTER_NAME' => 'blackpatternJournalHomeFilter', 'CHECK_DATES' => 'Y', 'SET_TITLE' => 'N', 'SET_STATUS_404' => 'N', 'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'CACHE_TYPE' => 'A', 'CACHE_TIME' => '36000000', 'CACHE_FILTER' => 'Y', 'CACHE_GROUPS' => 'Y', 'DISPLAY_TOP_PAGER' => 'N', 'DISPLAY_BOTTOM_PAGER' => 'N',
    ]);
    ?>
    <div class="journal-home-more rv"><a class="btn btn-red" href="/journal/">Все материалы журнала →</a></div>
</div></section>
<?php endif; ?>

<section class="off bord" id="faq"><div class="wrap">
    <?php $APPLICATION->IncludeFile('/local/include/home/faq-intro.php', [], ['MODE' => 'html', 'NAME' => 'Вступление FAQ']); ?>
    <?php if ($faqIblockId > 0):
        $GLOBALS['blackpatternFaqFilter'] = ['PROPERTY_SHOW_ON_HOME' => $faqHomeEnumId];
        $APPLICATION->IncludeComponent(
            'bitrix:news.list',
            'faq',
            [
                'IBLOCK_TYPE' => 'content',
                'IBLOCK_ID' => $faqIblockId,
                'NEWS_COUNT' => '100',
                'SORT_BY1' => 'SORT',
                'SORT_ORDER1' => 'ASC',
                'FIELD_CODE' => ['PREVIEW_TEXT'],
                'PROPERTY_CODE' => ['SHOW_ON_HOME'],
                'FILTER_NAME' => 'blackpatternFaqFilter',
                'CHECK_DATES' => 'Y',
                'SET_TITLE' => 'N',
                'SET_STATUS_404' => 'N',
                'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                'CACHE_TYPE' => 'A',
                'CACHE_TIME' => '36000000',
                'CACHE_FILTER' => 'Y',
                'CACHE_GROUPS' => 'Y',
                'DISPLAY_TOP_PAGER' => 'N',
                'DISPLAY_BOTTOM_PAGER' => 'N',
            ]
        );
    else: ?>
        <p>Инфоблок FAQ пока не создан.</p>
    <?php endif; ?>
</div></section>

<section class="final" id="cta"><div class="wrap fg"><div><?php $APPLICATION->IncludeFile('/local/include/home/final-cta-copy.php', [], ['MODE' => 'html', 'NAME' => 'Текст финального призыва']); ?><a class="btn btn-glass final-phone" href="<?= htmlspecialcharsbx($siteSettings['PHONE_HREF']); ?>">Или позвоните <?= htmlspecialcharsbx($siteSettings['PHONE']); ?></a></div><div class="final-form"><h3>Записаться на диагностику</h3><p>Оставьте контакты — эксперт перезвонит в течение рабочего дня.</p><?php if (\Bitrix\Main\Loader::includeModule('blackpattern.settings')): ?><?= \BlackPattern\Forms\FormRenderer::render('diagnostic', ['SUBMIT_TEXT' => 'Записаться на диагностику']); ?><?php else: ?><p>Форма временно недоступна. Позвоните: <a href="<?= htmlspecialcharsbx($siteSettings['PHONE_HREF']); ?>"><?= htmlspecialcharsbx($siteSettings['PHONE']); ?></a>.</p><?php endif; ?></div></div></section>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
