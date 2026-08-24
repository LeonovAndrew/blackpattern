<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

$siteSettings = ['LEGAL_NAME' => 'BLACK PATTERN', 'EMAIL' => 'hello@whitepattern.ru'];
if (Loader::includeModule('blackpattern.settings')) {
    $siteSettings = \BlackPattern\Settings\SiteSettings::get();
}

$APPLICATION->SetTitle('Политика обработки персональных данных');
$APPLICATION->SetPageProperty('title', 'Политика обработки персональных данных | Black Pattern');
$APPLICATION->SetPageProperty('description', 'Порядок обработки персональных данных пользователей сайта Black Pattern.');
$APPLICATION->SetPageProperty('robots', 'noindex, nofollow');
$APPLICATION->SetPageProperty('body_class', 'service-legal');
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/assets/css/service.css');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
?>
<section class="privacy-page">
    <div class="wrap">
        <header class="privacy-head">
            <span class="tag">Правовая информация</span>
            <h1>Политика обработки персональных данных</h1>
            <p class="lead">Техническая редакция документа для форм сайта.</p>
        </header>
        <div class="privacy-draft"><strong>До публикации:</strong> необходимо указать полное юридическое наименование, адрес оператора, сроки хранения данных и утвердить текст с ответственным за персональные данные. До утверждения страница закрыта от индексации.</div>
        <div class="privacy-content">
            <?php $APPLICATION->IncludeFile('/local/include/legal/privacy.php', [], ['MODE' => 'html', 'NAME' => 'Текст политики обработки персональных данных']); ?>
            <div class="privacy-contact"><b>Контакт оператора</b><span><?= htmlspecialcharsbx($siteSettings['LEGAL_NAME']); ?></span><br><a href="mailto:<?= htmlspecialcharsbx($siteSettings['EMAIL']); ?>"><?= htmlspecialcharsbx($siteSettings['EMAIL']); ?></a></div>
        </div>
    </div>
</section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
