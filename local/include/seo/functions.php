<?php

use Bitrix\Main\Loader;

function blackPatternSeoSettings(): array
{
    if (Loader::includeModule('blackpattern.settings')) {
        return \BlackPattern\Settings\SiteSettings::get();
    }

    return [
        'BRAND_NAME' => 'Black Pattern',
        'LEGAL_NAME' => 'BLACK PATTERN',
        'SITE_URL' => '',
        'PHONE' => '',
        'EMAIL' => '',
        'TELEGRAM_URL' => '',
        'FOOTER_TEXT' => '',
        'LOGO_DARK_SRC' => '',
        'LOGO_LIGHT_SRC' => '',
        'SEO_DEFAULT_IMAGE_SRC' => '',
    ];
}

function blackPatternSeoSiteUrl(?array $settings = null): string
{
    $settings ??= blackPatternSeoSettings();
    $configuredUrl = rtrim(trim((string)($settings['SITE_URL'] ?? '')), '/');
    if ($configuredUrl !== '' && filter_var($configuredUrl, FILTER_VALIDATE_URL)) {
        $parts = parse_url($configuredUrl);
        if (in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true) && !empty($parts['host'])) {
            return $configuredUrl;
        }
    }

    $https = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = preg_replace('/[^a-z0-9.\-:\[\]]/i', '', (string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost'));

    return $scheme . '://' . ($host ?: 'localhost');
}

function blackPatternSeoAbsoluteUrl(string $url, ?array $settings = null): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }

    return blackPatternSeoSiteUrl($settings) . '/' . ltrim($url, '/');
}

function blackPatternSeoCanonicalUrl(?array $settings = null): string
{
    global $APPLICATION;

    $manualCanonical = trim((string)$APPLICATION->GetPageProperty('canonical'));
    if ($manualCanonical !== '') {
        return blackPatternSeoAbsoluteUrl($manualCanonical, $settings);
    }

    $path = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
    if ($path !== '/' && !preg_match('/\.[a-z0-9]{1,8}$/i', $path)) {
        $path = rtrim($path, '/') . '/';
    }
    $pagination = [];
    foreach ($_GET as $code => $value) {
        if (preg_match('/^PAGEN_\d+$/', (string)$code) && (int)$value > 1) {
            $pagination[$code] = (int)$value;
        }
    }

    return blackPatternSeoAbsoluteUrl($path, $settings) . ($pagination ? '?' . http_build_query($pagination) : '');
}

function blackPatternSeoText(string $value): string
{
    $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim((string)preg_replace('/\s+/u', ' ', $value));
}

function blackPatternSeoIsoDate(?string $value): string
{
    $timestamp = MakeTimeStamp((string)$value);
    return $timestamp ? date(DATE_ATOM, $timestamp) : '';
}

function blackPatternSeoAddSchema(array $schema): void
{
    if (!$schema) {
        return;
    }
    $GLOBALS['blackPatternSeoSchemas'] ??= [];
    $GLOBALS['blackPatternSeoSchemas'][] = $schema;
}

function blackPatternSeoSchemas(): array
{
    return array_values(array_filter((array)($GLOBALS['blackPatternSeoSchemas'] ?? [])));
}

function blackPatternSeoHeadData($application, array $settings): array
{
    $title = blackPatternSeoText((string)($application->GetPageProperty('title') ?: $application->GetTitle()));
    $description = blackPatternSeoText((string)$application->GetPageProperty('description'));
    $robots = strtolower(trim((string)$application->GetPageProperty('robots')));
    $canonical = str_contains($robots, 'noindex') ? '' : blackPatternSeoCanonicalUrl($settings);
    $pageUrl = $canonical ?: blackPatternSeoAbsoluteUrl((string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/'), $settings);
    $image = trim((string)$application->GetPageProperty('og_image')) ?: (string)($settings['SEO_DEFAULT_IMAGE_SRC'] ?? '');

    return [
        'title' => $title,
        'description' => $description,
        'canonical' => $canonical,
        'url' => $pageUrl,
        'type' => trim((string)$application->GetPageProperty('og_type')) ?: 'website',
        'image' => blackPatternSeoAbsoluteUrl($image, $settings),
        'siteName' => (string)($settings['BRAND_NAME'] ?? 'Black Pattern'),
        'robots' => $robots,
    ];
}

function blackPatternSeoOrganizationSchema(array $settings): array
{
    $siteUrl = blackPatternSeoSiteUrl($settings);
    $schema = [
        '@type' => 'Organization',
        '@id' => $siteUrl . '/#organization',
        'name' => (string)($settings['LEGAL_NAME'] ?: $settings['BRAND_NAME']),
        'alternateName' => (string)$settings['BRAND_NAME'],
        'url' => $siteUrl . '/',
    ];
    $logo = (string)($settings['LOGO_DARK_SRC'] ?: $settings['LOGO_LIGHT_SRC']);
    if ($logo !== '') {
        $schema['logo'] = blackPatternSeoAbsoluteUrl($logo, $settings);
    }
    if (!empty($settings['PHONE'])) {
        $schema['telephone'] = (string)$settings['PHONE'];
    }
    if (!empty($settings['EMAIL'])) {
        $schema['email'] = (string)$settings['EMAIL'];
    }
    if (!empty($settings['TELEGRAM_URL'])) {
        $schema['sameAs'] = [(string)$settings['TELEGRAM_URL']];
    }

    return $schema;
}

function blackPatternSeoWebsiteSchema(array $settings): array
{
    $siteUrl = blackPatternSeoSiteUrl($settings);
    return [
        '@type' => 'WebSite',
        '@id' => $siteUrl . '/#website',
        'url' => $siteUrl . '/',
        'name' => (string)$settings['BRAND_NAME'],
        'publisher' => ['@id' => $siteUrl . '/#organization'],
        'inLanguage' => 'ru-RU',
    ];
}

function blackPatternSeoBreadcrumbSchema(array $items, array $settings): array
{
    $elements = [];
    foreach ($items as $index => $item) {
        $elements[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => (string)$item['name'],
            'item' => blackPatternSeoAbsoluteUrl((string)$item['url'], $settings),
        ];
    }

    return ['@type' => 'BreadcrumbList', 'itemListElement' => $elements];
}

function blackPatternSeoFaqSchema(int $iblockId, int $homeEnumId): array
{
    if ($iblockId <= 0 || $homeEnumId <= 0) {
        return [];
    }
    $questions = [];
    $result = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], [
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'PROPERTY_SHOW_ON_HOME' => $homeEnumId,
    ], false, false, ['ID', 'NAME', 'PREVIEW_TEXT']);
    while ($item = $result->Fetch()) {
        $answer = blackPatternSeoText((string)$item['PREVIEW_TEXT']);
        if ($answer === '') {
            continue;
        }
        $questions[] = [
            '@type' => 'Question',
            'name' => blackPatternSeoText((string)$item['NAME']),
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
        ];
    }

    return $questions ? ['@type' => 'FAQPage', 'mainEntity' => $questions] : [];
}

function blackPatternSeoPersonSchemas(int $iblockId, int $homeEnumId, array $settings): array
{
    if ($iblockId <= 0 || $homeEnumId <= 0) {
        return [];
    }
    $siteUrl = blackPatternSeoSiteUrl($settings);
    $persons = [];
    $result = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], [
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        'PROPERTY_SHOW_ON_HOME' => $homeEnumId,
    ], false, false, ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE']);
    while ($item = $result->GetNext()) {
        $person = [
            '@type' => 'Person',
            '@id' => $siteUrl . '/#person-' . (int)$item['ID'],
            'name' => blackPatternSeoText((string)$item['NAME']),
            'description' => blackPatternSeoText((string)$item['PREVIEW_TEXT']),
            'worksFor' => ['@id' => $siteUrl . '/#organization'],
        ];
        $position = CIBlockElement::GetProperty($iblockId, (int)$item['ID'], [], ['CODE' => 'POSITION_EN'])->Fetch();
        if (!empty($position['VALUE'])) {
            $person['jobTitle'] = blackPatternSeoText((string)$position['VALUE']);
        }
        $photo = CFile::GetPath((int)$item['PREVIEW_PICTURE']);
        if ($photo) {
            $person['image'] = blackPatternSeoAbsoluteUrl((string)$photo, $settings);
        }
        $persons[] = array_filter($person, static fn($value): bool => $value !== '');
    }

    return $persons;
}

function blackPatternSeoServiceSchema(int $pricesIblockId, array $settings): array
{
    $siteUrl = blackPatternSeoSiteUrl($settings);
    $schema = [
        '@type' => 'Service',
        '@id' => $siteUrl . '/#marketing-os',
        'name' => 'Marketing OS — построение отдела маркетинга под ключ',
        'serviceType' => 'Проектирование и внедрение операционной системы отдела маркетинга',
        'description' => 'Оргструктура, регламенты и ЦКП, система найма, контур подрядчиков и авторский надзор.',
        'url' => $siteUrl . '/#product',
        'provider' => ['@id' => $siteUrl . '/#organization'],
    ];
    if ($pricesIblockId > 0) {
        $priceElement = CIBlockElement::GetList(['SORT' => 'DESC'], [
            'IBLOCK_ID' => $pricesIblockId,
            'ACTIVE' => 'Y',
            'CODE' => 'full-contour',
        ], false, ['nTopCount' => 1], ['ID', 'NAME'])->Fetch();
        if ($priceElement) {
            $priceProperty = CIBlockElement::GetProperty($pricesIblockId, (int)$priceElement['ID'], [], ['CODE' => 'PRICE'])->Fetch();
            $price = (int)preg_replace('/\D+/u', '', (string)($priceProperty['VALUE'] ?? ''));
            if ($price > 0) {
                $schema['offers'] = [
                    '@type' => 'Offer',
                    'name' => blackPatternSeoText((string)$priceElement['NAME']),
                    'price' => (string)$price,
                    'priceCurrency' => 'RUB',
                    'url' => $siteUrl . '/#price',
                    'availability' => 'https://schema.org/InStock',
                ];
            }
        }
    }

    return $schema;
}

function blackPatternSeoBlogSchema(array $settings): array
{
    $siteUrl = blackPatternSeoSiteUrl($settings);
    return [
        '@type' => 'Blog',
        '@id' => $siteUrl . '/journal/#blog',
        'url' => $siteUrl . '/journal/',
        'name' => 'Журнал Black Pattern',
        'description' => 'Материалы об устройстве отдела маркетинга, найме, метриках и управлении.',
        'publisher' => ['@id' => $siteUrl . '/#organization'],
        'inLanguage' => 'ru-RU',
    ];
}

function blackPatternSeoArticleSchema(array $article, array $settings): array
{
    $siteUrl = blackPatternSeoSiteUrl($settings);
    $articleUrl = blackPatternSeoAbsoluteUrl('/journal/' . rawurlencode((string)$article['CODE']) . '/', $settings);
    $schema = [
        '@type' => 'BlogPosting',
        '@id' => $articleUrl . '#article',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $articleUrl],
        'headline' => blackPatternSeoText((string)$article['NAME']),
        'description' => blackPatternSeoText((string)$article['PREVIEW_TEXT']),
        'url' => $articleUrl,
        'datePublished' => blackPatternSeoIsoDate((string)($article['ACTIVE_FROM'] ?? '')),
        'dateModified' => blackPatternSeoIsoDate((string)($article['TIMESTAMP_X'] ?? $article['ACTIVE_FROM'] ?? '')),
        'publisher' => ['@id' => $siteUrl . '/#organization'],
        'isPartOf' => ['@id' => $siteUrl . '/journal/#blog'],
        'inLanguage' => 'ru-RU',
    ];
    $imageId = (int)($article['SEO_IMAGE_ID'] ?? 0)
        ?: (int)($article['DETAIL_PICTURE'] ?? 0)
        ?: (int)($article['PREVIEW_PICTURE'] ?? 0);
    $image = $imageId > 0 ? CFile::GetPath($imageId) : '';
    if (!$image) {
        $image = (string)($settings['SEO_DEFAULT_IMAGE_SRC'] ?? '');
    }
    if ($image) {
        $schema['image'] = [blackPatternSeoAbsoluteUrl((string)$image, $settings)];
    }
    $keywords = array_values(array_unique(array_filter(array_merge((array)($article['ROLE_NAMES'] ?? []), (array)($article['THEME_NAMES'] ?? [])))));
    if ($keywords) {
        $schema['keywords'] = implode(', ', $keywords);
    }
    if (!empty($article['READING_TIME'])) {
        $schema['timeRequired'] = 'PT' . (int)$article['READING_TIME'] . 'M';
    }
    $authors = [];
    foreach ((array)($article['AUTHORS'] ?? []) as $author) {
        $person = [
            '@type' => 'Person',
            '@id' => $siteUrl . '/#person-' . (int)$author['ID'],
            'name' => blackPatternSeoText((string)$author['NAME']),
        ];
        if (!empty($author['POSITION'])) {
            $person['jobTitle'] = blackPatternSeoText((string)$author['POSITION']);
        }
        $photo = CFile::GetPath((int)($author['PREVIEW_PICTURE'] ?? 0));
        if ($photo) {
            $person['image'] = blackPatternSeoAbsoluteUrl((string)$photo, $settings);
        }
        $authors[] = $person;
    }
    $schema['author'] = $authors ?: [['@id' => $siteUrl . '/#organization']];

    return array_filter($schema, static fn($value): bool => $value !== '');
}
