<?php
/** Upload the optimized Hero video and poster to the existing Hero element. */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock')) {
    throw new RuntimeException('The iblock module is not available.');
}

$iblock = CIBlock::GetList([], [
    'TYPE' => 'homepage',
    'CODE' => 'hero',
    'CHECK_PERMISSIONS' => 'N',
])->Fetch();
$iblockId = (int)($iblock['ID'] ?? 0);
if ($iblockId <= 0) {
    throw new RuntimeException('Hero iblock was not found.');
}

$element = CIBlockElement::GetList([], [
    'IBLOCK_ID' => $iblockId,
    'CODE' => 'hero-main',
    'CHECK_PERMISSIONS' => 'N',
], false, false, ['ID'])->Fetch();
$elementId = (int)($element['ID'] ?? 0);
if ($elementId <= 0) {
    throw new RuntimeException('Hero element was not found.');
}

$mediaFiles = [
    'VIDEO_MP4' => $_SERVER['DOCUMENT_ROOT'] . '/local/templates/blackpattern/assets/video/hero.mp4',
    'POSTER' => $_SERVER['DOCUMENT_ROOT'] . '/local/templates/blackpattern/assets/video/hero-poster.jpg',
];
$propertyValues = [];
foreach ($mediaFiles as $propertyCode => $filePath) {
    if (!is_file($filePath)) {
        throw new RuntimeException("Media file was not found: {$filePath}");
    }
    $file = CFile::MakeFileArray($filePath);
    if (!$file) {
        throw new RuntimeException("Media file could not be prepared: {$filePath}");
    }
    $propertyValues[$propertyCode] = $file;
}

CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $propertyValues);

$uploaded = [];
foreach (array_keys($mediaFiles) as $propertyCode) {
    $property = CIBlockElement::GetProperty($iblockId, $elementId, [], ['CODE' => $propertyCode])->Fetch();
    $fileId = (int)($property['VALUE'] ?? 0);
    $filePath = $fileId > 0 ? (string)CFile::GetPath($fileId) : '';
    if ($filePath === '') {
        throw new RuntimeException("Property {$propertyCode} was not updated.");
    }
    $uploaded[$propertyCode] = $filePath;
}

global $CACHE_MANAGER;
if (is_object($CACHE_MANAGER)) {
    $CACHE_MANAGER->ClearByTag('iblock_id_' . $iblockId);
}

echo json_encode([
    'status' => 'OK',
    'iblockId' => $iblockId,
    'elementId' => $elementId,
    'files' => $uploaded,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
