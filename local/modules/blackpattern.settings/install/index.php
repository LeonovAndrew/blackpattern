<?php

use Bitrix\Main\ModuleManager;

class blackpattern_settings extends CModule
{
    public $MODULE_ID = 'blackpattern.settings';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME = 'Black Pattern: настройки сайта';
    public $MODULE_DESCRIPTION = 'Общие контакты, логотип и реквизиты сайта.';
    public $PARTNER_NAME = 'Black Pattern';
    public $PARTNER_URI = '';

    public function __construct()
    {
        $version = [];
        require __DIR__ . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}
