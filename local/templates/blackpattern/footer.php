<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

$siteSettings = [
    'BRAND_NAME' => 'Black Pattern',
    'LOGO_DARK_SRC' => '',
    'FOOTER_TEXT' => 'Строим управляемые отделы маркетинга внутри компаний.',
    'PHONE' => '+7 (495) 191-98-20',
    'PHONE_HREF' => 'tel:+74951919820',
    'EMAIL' => 'hello@whitepattern.ru',
    'TELEGRAM_URL' => '',
    'PRIVACY_URL' => '/privacy/',
    'LEGAL_NAME' => 'BLACK PATTERN',
];
$settingsModuleLoaded = Loader::includeModule('blackpattern.settings');
if ($settingsModuleLoaded) {
    $siteSettings = \BlackPattern\Settings\SiteSettings::get();
}
?>
</main>
<footer class="site-footer">
    <div class="wrap">
        <div class="ft">
            <div>
                <a href="/" class="logo" aria-label="<?= htmlspecialcharsbx($siteSettings['BRAND_NAME']); ?> — главная">
                    <?php if ($siteSettings['LOGO_DARK_SRC']): ?>
                        <img class="logo-img" src="<?= htmlspecialcharsbx($siteSettings['LOGO_DARK_SRC']); ?>" alt="">
                    <?php else: ?>
                        <span class="sq"></span><?= htmlspecialcharsbx($siteSettings['BRAND_NAME']); ?>
                    <?php endif; ?>
                </a>
                <p><?= htmlspecialcharsbx($siteSettings['FOOTER_TEXT']); ?></p>
            </div>
            <div><h4>Marketing OS</h4><ul><li><a href="/#product">Что входит</a></li><li><a href="/#process">Процесс</a></li><li><a href="/#price">Стоимость</a></li></ul></div>
            <div><h4>Журнал</h4><ul><li><a href="/journal/">Все материалы</a></li><li><a href="/journal/?role=own">Собственнику</a></li><li><a href="/journal/?role=hr">HR-директору</a></li></ul></div>
            <div><h4>Контакты</h4><ul><li><a href="<?= htmlspecialcharsbx($siteSettings['PHONE_HREF']); ?>"><?= htmlspecialcharsbx($siteSettings['PHONE']); ?></a></li><li><a href="mailto:<?= htmlspecialcharsbx($siteSettings['EMAIL']); ?>"><?= htmlspecialcharsbx($siteSettings['EMAIL']); ?></a></li><?php if ($siteSettings['TELEGRAM_URL']): ?><li><a href="<?= htmlspecialcharsbx($siteSettings['TELEGRAM_URL']); ?>" target="_blank" rel="noopener">Telegram</a></li><?php endif; ?></ul></div>
        </div>
        <div class="fb"><span>© <?= htmlspecialcharsbx($siteSettings['LEGAL_NAME']); ?>, <?= date('Y'); ?></span><a href="<?= htmlspecialcharsbx($siteSettings['PRIVACY_URL']); ?>">Политика конфиденциальности</a></div>
    </div>
</footer>
<dialog class="bp-modal" id="header-task-modal" data-bp-modal aria-labelledby="header-task-modal-title">
    <div class="bp-modal-card">
        <button class="bp-modal-close" type="button" data-bp-modal-close aria-label="Закрыть форму"><span aria-hidden="true"></span></button>
        <h2 id="header-task-modal-title">Обсудить задачу</h2>
        <?php if ($settingsModuleLoaded): ?>
            <?= \BlackPattern\Forms\FormRenderer::render('callback_short', [
                'SUBMIT_TEXT' => 'Обсудить задачу',
                'CLASS' => 'bp-popup-form bp-form-compact',
                'COMPACT' => 'Y',
            ]); ?>
        <?php else: ?>
            <p class="bp-modal-fallback">Форма временно недоступна.</p>
        <?php endif; ?>
    </div>
</dialog>
<div class="cookie-notice" data-cookie-notice role="region" aria-label="Уведомление об использовании cookie" hidden>
    <p>Оставаясь на сайте, вы соглашаетесь с <a target="_blank" rel="noopener" href="/privacy/">нашей Политикой обработки персональных данных</a> и использованием cookie.</p>
    <button class="btn btn-red cookie-notice-button" type="button" data-cookie-notice-accept>Понятно</button>
</div>
<button class="totop" id="totop" type="button" aria-label="Наверх">↑</button>
<?php
$commonJs = SITE_TEMPLATE_PATH . '/assets/js/common.js';
$commonJsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . $commonJs);
?>
<script src="<?= $commonJs; ?>?v=<?= $commonJsVersion; ?>"></script>
<?php if ($APPLICATION->GetCurPage(false) === '/'):
    $homeJs = SITE_TEMPLATE_PATH . '/assets/js/home.js';
    $homeJsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . $homeJs);
?>
<script src="<?= $homeJs; ?>?v=<?= $homeJsVersion; ?>"></script>
<?php endif; ?>
<?php if (str_starts_with($APPLICATION->GetCurPage(false), '/journal/')):
    $journalJs = SITE_TEMPLATE_PATH . '/assets/js/journal.js';
    $journalJsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . $journalJs);
?>
<script src="<?= $journalJs; ?>?v=<?= $journalJsVersion; ?>"></script>
<?php endif; ?>
<?php if ($APPLICATION->GetPageProperty('service_js') === 'Y'):
    $serviceJs = SITE_TEMPLATE_PATH . '/assets/js/service.js';
    $serviceJsVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . $serviceJs);
?>
<script src="<?= $serviceJs; ?>?v=<?= $serviceJsVersion; ?>"></script>
<?php endif; ?>
</body>
</html>
