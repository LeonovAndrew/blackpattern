<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

$siteSettings = ['PHONE' => '+7 (495) 191-98-20', 'PHONE_HREF' => 'tel:+74951919820', 'TELEGRAM_URL' => ''];
if (Loader::includeModule('blackpattern.settings')) {
    $siteSettings = \BlackPattern\Settings\SiteSettings::get();
}

$APPLICATION->SetTitle('Спасибо за заявку');
$APPLICATION->SetPageProperty('title', 'Заявка принята | Black Pattern');
$APPLICATION->SetPageProperty('description', 'Спасибо, ваша заявка принята. Эксперт Black Pattern свяжется с вами в течение рабочего дня.');
$APPLICATION->SetPageProperty('robots', 'noindex, nofollow');
$APPLICATION->SetPageProperty('body_class', 'service-thanks');
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/assets/css/service.css');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
?>
<section class="thanks-page">
    <div class="wrap">
        <div class="thanks-check" aria-hidden="true"><svg viewBox="0 0 24 24"><polyline points="4 12.5 10 18 20 6"/></svg></div>
        <span class="tag">Заявка принята</span>
        <h1>Готово. Дальше — за нами.</h1>
        <p class="thanks-sub">Эксперт, который проведёт разбор, свяжется с вами по указанному телефону, чтобы согласовать удобное время.<br>
            Обычно — в течение рабочего дня.</p>
        <div class="thanks-steps">
            <div class="thanks-step"><span class="n">ШАГ 1</span><b>Звонок и согласование</b><p>Свяжемся по телефону и подберём удобное время. Звонит эксперт, а не продавец.</p></div>
            <div class="thanks-step"><span class="n">ШАГ 2</span><b>Разбор, 90 минут</b><p>Покажем контур вашей оргструктуры и посчитаем стоимость текущего найма в деньгах.</p></div>
            <div class="thanks-step"><span class="n">ШАГ 3</span><b>Решение за вами</b><p>Честно скажем, подходит ли вам Marketing OS. Если нет — так и скажем, без уговоров.</p></div>
        </div>
        <div class="thanks-wait">
            <span class="lab">Пока ждёте звонка</span>
            <div class="thanks-links">
                <a class="thanks-card" href="/journal/"><span class="k">Журнал</span><b>Материалы об устройстве отдела маркетинга</b><span class="rd">Перейти →</span></a>
                <a class="thanks-card" href="/journal/staff-or-outsource/"><span class="k">Собственнику</span><b>Маркетолог в штат или на аутсорс: как считать</b><span class="rd">Читать →</span></a>
                <a class="thanks-card" href="/#product"><span class="k">Продукт</span><b>Что входит в Marketing OS: четыре модуля</b><span class="rd">Посмотреть →</span></a>
            </div>
        </div>
        <div class="thanks-tail">
            <a href="/" class="btn btn-red">На главную</a>
            <div class="thanks-contacts"><span>Удобнее самому?</span><a href="<?= htmlspecialcharsbx($siteSettings['PHONE_HREF']); ?>"><?= htmlspecialcharsbx($siteSettings['PHONE']); ?></a><?php if ($siteSettings['TELEGRAM_URL']): ?><a href="<?= htmlspecialcharsbx($siteSettings['TELEGRAM_URL']); ?>" target="_blank" rel="noopener">Telegram</a><?php endif; ?></div>
        </div>
    </div>
</section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
