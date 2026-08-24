<?php

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/seo/functions.php';

use Bitrix\Main\Loader;

header('Content-Type: application/xml; charset=UTF-8');

$settings = blackPatternSeoSettings();
$siteUrl = blackPatternSeoSiteUrl($settings);
$urls = [
    ['loc' => $siteUrl . '/', 'lastmod' => date(DATE_ATOM, (int)filemtime($_SERVER['DOCUMENT_ROOT'] . '/index.php'))],
    ['loc' => $siteUrl . '/journal/', 'lastmod' => ''],
];

if (Loader::includeModule('iblock')) {
    $iblock = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => 'journal'])->Fetch();
    $iblockId = (int)($iblock['ID'] ?? 0);
    if ($iblockId > 0) {
        $latestModified = 0;
        $result = CIBlockElement::GetList(['ACTIVE_FROM' => 'DESC', 'ID' => 'DESC'], [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ], false, false, ['ID', 'CODE', 'ACTIVE_FROM', 'TIMESTAMP_X']);
        while ($article = $result->Fetch()) {
            $modifiedTimestamp = MakeTimeStamp((string)($article['TIMESTAMP_X'] ?: $article['ACTIVE_FROM']));
            $latestModified = max($latestModified, $modifiedTimestamp ?: 0);
            $urls[] = [
                'loc' => $siteUrl . '/journal/' . rawurlencode((string)$article['CODE']) . '/',
                'lastmod' => $modifiedTimestamp ? date(DATE_ATOM, $modifiedTimestamp) : '',
            ];
        }
        if ($latestModified > 0) {
            $urls[1]['lastmod'] = date(DATE_ATOM, $latestModified);
        }
    }
}

$xml = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $url) {
    echo "  <url>\n";
    echo '    <loc>' . $xml($url['loc']) . "</loc>\n";
    if ($url['lastmod'] !== '') {
        echo '    <lastmod>' . $xml($url['lastmod']) . "</lastmod>\n";
    }
    echo "  </url>\n";
}
echo "</urlset>\n";
