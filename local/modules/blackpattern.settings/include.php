<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('blackpattern.settings', [
    'BlackPattern\\Settings\\SiteSettings' => 'lib/sitesettings.php',
    'BlackPattern\\Forms\\FormRenderer' => 'lib/forms/formrenderer.php',
    'BlackPattern\\Forms\\FormSubmission' => 'lib/forms/formsubmission.php',
    'BlackPattern\\Forms\\YandexCaptcha' => 'lib/forms/yandexcaptcha.php',
]);
