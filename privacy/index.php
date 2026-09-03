<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$APPLICATION->SetTitle('Политика в отношении обработки персональных данных');
$APPLICATION->SetPageProperty('title', 'Политика в отношении обработки персональных данных | Black Pattern');
$APPLICATION->SetPageProperty('description', 'Порядок обработки персональных данных пользователей сайта Black Pattern.');
$APPLICATION->SetPageProperty('robots', 'index, follow');
$APPLICATION->SetPageProperty('body_class', 'service-legal');
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/assets/css/service.css');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
?>
<section class="privacy-page">
    <div class="wrap">
        <header class="privacy-head">
            <span class="tag">Правовая информация</span>
            <h1>Политика в отношении обработки персональных данных</h1>
        </header>
        <div class="privacy-content">
            <?php $APPLICATION->IncludeFile('/local/include/legal/privacy.php', [], ['MODE' => 'html', 'NAME' => 'Текст политики обработки персональных данных']); ?>
        </div>
    </div>
</section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
