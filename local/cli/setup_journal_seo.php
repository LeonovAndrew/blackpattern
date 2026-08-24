<?php
/**
 * Adds SEO properties to the existing Journal iblock without changing article content.
 * Run from the project root: php local/cli/setup_journal_seo.php
 */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$iblock = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => 'journal'])->Fetch();
$iblockId = (int)($iblock['ID'] ?? 0);
if ($iblockId <= 0) {
    throw new RuntimeException('The Journal iblock is missing.');
}

$properties = [
    'SEO_TITLE' => ['SEO: Title', 'S', 800],
    'SEO_DESCRIPTION' => ['SEO: Description', 'S', 900],
    'SEO_OG_IMAGE' => ['SEO: изображение для соцсетей', 'F', 1000],
];

foreach ($properties as $code => [$name, $type, $sort]) {
    $property = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code])->Fetch();
    if ($property) {
        echo "Using existing property: {$code}\n";
        continue;
    }

    $propertyApi = new CIBlockProperty();
    $propertyId = (int)$propertyApi->Add([
        'IBLOCK_ID' => $iblockId,
        'NAME' => $name,
        'ACTIVE' => 'Y',
        'SORT' => $sort,
        'CODE' => $code,
        'PROPERTY_TYPE' => $type,
        'MULTIPLE' => 'N',
        'ROW_COUNT' => $code === 'SEO_DESCRIPTION' ? 3 : 1,
    ]);
    if ($propertyId <= 0) {
        throw new RuntimeException($propertyApi->LAST_ERROR);
    }
    echo "Created property: {$code}\n";
}

echo "Journal SEO properties setup completed.\n";
