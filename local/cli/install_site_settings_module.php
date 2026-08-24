<?php
/** Installs the Black Pattern global site settings module. */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\ModuleManager;

$moduleId = 'blackpattern.settings';
if (ModuleManager::isModuleInstalled($moduleId)) {
    echo "Module {$moduleId} is already installed.\n";
} else {
    require $_SERVER['DOCUMENT_ROOT'] . '/local/modules/blackpattern.settings/install/index.php';
    $module = new blackpattern_settings();
    $module->DoInstall();

    echo "Module {$moduleId} installed.\n";
}

if (!\Bitrix\Main\Loader::includeModule($moduleId)) {
    throw new RuntimeException("Module {$moduleId} could not be loaded.");
}

if (!class_exists(\BlackPattern\Settings\SiteSettings::class)) {
    throw new RuntimeException('The SiteSettings class could not be loaded.');
}

echo "Module {$moduleId} loaded successfully.\n";
