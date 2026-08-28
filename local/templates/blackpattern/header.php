<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/seo/functions.php';

$isHomePage = $APPLICATION->GetCurPage(false) === '/';
$pageBodyClass = trim((string)$APPLICATION->GetPageProperty('body_class'));
$siteSettings = [
    'BRAND_NAME' => 'Black Pattern',
    'LOGO_DARK_SRC' => '',
    'LOGO_LIGHT_SRC' => '',
    'FAVICON_SVG_SRC' => '',
    'FAVICON_ICO_SRC' => '',
    'APPLE_TOUCH_ICON_SRC' => '',
    'SEO_DEFAULT_IMAGE_SRC' => '',
];
if (Loader::includeModule('blackpattern.settings')) {
    $siteSettings = \BlackPattern\Settings\SiteSettings::get();
}
$seoHead = blackPatternSeoHeadData($APPLICATION, $siteSettings);
$seoSchemas = blackPatternSeoSchemas();
if (str_contains($seoHead['robots'], 'noindex')) {
    $seoSchemas = [];
} else {
    array_unshift($seoSchemas, blackPatternSeoWebsiteSchema($siteSettings));
    array_unshift($seoSchemas, blackPatternSeoOrganizationSchema($siteSettings));
}
$asset = Asset::getInstance();
$asset->addCss(SITE_TEMPLATE_PATH . '/template_styles.css');
$asset->addCss(SITE_TEMPLATE_PATH . '/assets/css/forms.css');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($siteSettings['FAVICON_SVG_SRC']): ?><link rel="icon" href="<?= htmlspecialcharsbx($siteSettings['FAVICON_SVG_SRC']); ?>" type="image/svg+xml"><?php endif; ?>
    <?php if ($siteSettings['FAVICON_ICO_SRC']): ?><link rel="icon" href="<?= htmlspecialcharsbx($siteSettings['FAVICON_ICO_SRC']); ?>" sizes="any"><?php elseif (!$siteSettings['FAVICON_SVG_SRC'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/favicon.ico')): ?><link rel="icon" href="/favicon.ico" sizes="any"><?php endif; ?>
    <?php if ($siteSettings['APPLE_TOUCH_ICON_SRC']): ?><link rel="apple-touch-icon" href="<?= htmlspecialcharsbx($siteSettings['APPLE_TOUCH_ICON_SRC']); ?>" sizes="180x180"><?php endif; ?>
    <?php if ($seoHead['canonical']): ?><link rel="canonical" href="<?= htmlspecialcharsbx($seoHead['canonical']); ?>"><?php endif; ?>
    <meta property="og:locale" content="ru_RU">
    <meta property="og:type" content="<?= htmlspecialcharsbx($seoHead['type']); ?>">
    <meta property="og:site_name" content="<?= htmlspecialcharsbx($seoHead['siteName']); ?>">
    <meta property="og:title" content="<?= htmlspecialcharsbx($seoHead['title']); ?>">
    <?php if ($seoHead['description']): ?><meta property="og:description" content="<?= htmlspecialcharsbx($seoHead['description']); ?>"><?php endif; ?>
    <meta property="og:url" content="<?= htmlspecialcharsbx($seoHead['url']); ?>">
    <?php if ($seoHead['image']): ?><meta property="og:image" content="<?= htmlspecialcharsbx($seoHead['image']); ?>"><meta property="og:image:alt" content="<?= htmlspecialcharsbx($seoHead['title']); ?>"><?php endif; ?>
    <meta name="twitter:card" content="<?= $seoHead['image'] ? 'summary_large_image' : 'summary'; ?>">
    <meta name="twitter:title" content="<?= htmlspecialcharsbx($seoHead['title']); ?>">
    <?php if ($seoHead['description']): ?><meta name="twitter:description" content="<?= htmlspecialcharsbx($seoHead['description']); ?>"><?php endif; ?>
    <?php if ($seoHead['image']): ?><meta name="twitter:image" content="<?= htmlspecialcharsbx($seoHead['image']); ?>"><meta name="twitter:image:alt" content="<?= htmlspecialcharsbx($seoHead['title']); ?>"><?php endif; ?>
    <?php if ($seoSchemas): ?><script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $seoSchemas], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?></script><?php endif; ?>
    <link rel="preload" href="<?= SITE_TEMPLATE_PATH; ?>/assets/fonts/montserrat/montserrat-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
    <script>document.documentElement.classList.add('js');</script>
    <?php $APPLICATION->ShowHead(); ?>
    <?php if ($isHomePage):
        $homeCss = SITE_TEMPLATE_PATH . '/assets/css/home.css';
        $homeCssVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . $homeCss);
        $heroVideoCss = SITE_TEMPLATE_PATH . '/assets/css/hero-video.css';
        $heroVideoCssVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . $heroVideoCss);
    ?>
    <link rel="stylesheet" href="<?= $homeCss; ?>?v=<?= $homeCssVersion; ?>">
    <link rel="stylesheet" href="<?= $heroVideoCss; ?>?v=<?= $heroVideoCssVersion; ?>">
    <?php endif; ?>
    <title><?php $APPLICATION->ShowTitle(); ?></title>
</head>
<body class="<?= htmlspecialcharsbx(trim(($isHomePage ? 'is-home ' : '') . $pageBodyClass)); ?>">
<?php $APPLICATION->ShowPanel(); ?>
<a class="skip-link" href="#main-content">Перейти к содержанию</a>
<div class="prog" id="prog" aria-hidden="true"></div>
<header class="site-header<?= $isHomePage ? ' on-hero' : ''; ?>" id="hdr">
    <div class="wrap nav">
        <a class="logo" href="/" aria-label="<?= htmlspecialcharsbx($siteSettings['BRAND_NAME']); ?> — главная">
            <span class="logo-variant logo-variant-dark">
                <?php if ($siteSettings['LOGO_DARK_SRC']): ?><img class="logo-img" src="<?= htmlspecialcharsbx($siteSettings['LOGO_DARK_SRC']); ?>" alt=""><?php else: ?><span class="sq" aria-hidden="true"></span><?= htmlspecialcharsbx($siteSettings['BRAND_NAME']); ?><?php endif; ?>
            </span>
            <span class="logo-variant logo-variant-light">
                <?php if ($siteSettings['LOGO_LIGHT_SRC']): ?><img class="logo-img" src="<?= htmlspecialcharsbx($siteSettings['LOGO_LIGHT_SRC']); ?>" alt=""><?php else: ?><span class="sq" aria-hidden="true"></span><?= htmlspecialcharsbx($siteSettings['BRAND_NAME']); ?><?php endif; ?>
            </span>
        </a>
        <?php
        $APPLICATION->IncludeComponent(
            'bitrix:menu',
            'top',
            [
                'ROOT_MENU_TYPE' => 'top',
                'MAX_LEVEL' => '1',
                'CHILD_MENU_TYPE' => 'left',
                'USE_EXT' => 'N',
                'ALLOW_MULTI_SELECT' => 'N',
                'MENU_CACHE_TYPE' => 'A',
                'MENU_CACHE_TIME' => '36000000',
            ]
        );
        ?>
        <div class="nav-r">
            <a class="btn btn-red header-cta" href="<?= $isHomePage ? '#cta' : '/#cta'; ?>" data-bp-modal-open="header-task-modal">Обсудить задачу</a>
            <button class="burger" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="main-navigation"><span class="burger-icon" aria-hidden="true"></span></button>
        </div>
    </div>
</header>
<main id="main-content" tabindex="-1">
