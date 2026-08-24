<?php

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/seo/functions.php';

header('Content-Type: text/plain; charset=UTF-8');
$siteUrl = blackPatternSeoSiteUrl();

echo "User-agent: *\n";
echo "Allow: /bitrix/cache/css/\n";
echo "Allow: /bitrix/cache/js/\n";
echo "Allow: /bitrix/css/\n";
echo "Allow: /bitrix/js/\n";
echo "Disallow: /bitrix/admin/\n";
echo "Disallow: /bitrix/services/\n";
echo "Disallow: /bitrix/tools/\n";
echo "Disallow: /local/ajax/\n";
echo "Disallow: /local/cli/\n";
echo "Disallow: /local/modules/\n";
echo "Disallow: /auth/\n";
echo "Disallow: /*?back_url_admin=\n";
echo "Disallow: /*?bxajaxid=\n";
echo "Disallow: /*?clear_cache=\n";
echo "Clean-param: utm_source&utm_medium&utm_campaign&utm_content&utm_term\n";
echo 'Sitemap: ' . $siteUrl . "/sitemap.xml\n";
