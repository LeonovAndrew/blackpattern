<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

CHTTP::SetStatus('404 Not Found');
define('ERROR_404', 'Y');

$APPLICATION->SetTitle('Страница не найдена');
$APPLICATION->SetPageProperty('title', 'Страница не найдена — 404 | Black Pattern');
$APPLICATION->SetPageProperty('description', 'Запрошенная страница не найдена.');
$APPLICATION->SetPageProperty('robots', 'noindex, nofollow');
$APPLICATION->SetPageProperty('body_class', 'service-404');
$APPLICATION->SetPageProperty('service_js', 'Y');
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/assets/css/service.css');

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
?>
<section class="error-page">
    <div class="error-scene" aria-hidden="true">
        <div class="error-sky"></div>
        <svg class="error-city" viewBox="0 0 1440 400" preserveAspectRatio="xMidYMax slice"><g id="service-city"></g></svg>
        <div class="error-streak service-streak-one"></div>
        <div class="error-streak is-red service-streak-two"></div>
        <div class="error-streak service-streak-three"></div>
        <div class="error-gradient"></div>
    </div>
    <div class="wrap">
        <div class="error-content">
            <div class="error-code" aria-hidden="true">404</div>
            <span class="tag">Ошибка 404</span>
            <h1>Эта страница — как маркетолог-универсал.<br>
                <em>Её не существует.</em></h1>
            <p class="error-sub">Ссылка устарела или адрес набран с опечаткой. Зато у нас есть то, что существует и работает.</p>
            <div class="error-actions">
                <a href="/" class="btn btn-red">На главную →</a>
                <a href="/journal/" class="btn btn-glass">В журнал</a>
            </div>
            <nav class="error-links" aria-label="Полезные разделы">
                <a href="/#product">Marketing OS — что входит</a>
                <a href="/#price">Стоимость</a>
                <a href="/journal/">Журнал</a>
                <a href="/#cta">Обсудить задачу</a>
            </nav>
        </div>
    </div>
</section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
