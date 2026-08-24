<?php

require_once __DIR__ . '/lib/sitesettings.php';

use Bitrix\Main\Config\Option;
use BlackPattern\Settings\SiteSettings;

if (!$USER->IsAdmin()) {
    return;
}

$moduleId = SiteSettings::MODULE_ID;
$settings = SiteSettings::get();
$textFields = [
    'BRAND_NAME' => 'Название бренда',
    'PHONE' => 'Телефон',
    'EMAIL' => 'E-mail',
    'TELEGRAM_URL' => 'Ссылка на Telegram',
    'FOOTER_TEXT' => 'Текст в подвале',
    'PRIVACY_URL' => 'URL политики конфиденциальности',
    'LEGAL_NAME' => 'Юридическое/копирайт-название',
];
$seoTextFields = [
    'SITE_URL' => 'Основной URL сайта',
    'SEO_HOME_TITLE' => 'Title главной',
    'SEO_HOME_DESCRIPTION' => 'Description главной',
    'SEO_JOURNAL_TITLE' => 'Title журнала',
    'SEO_JOURNAL_DESCRIPTION' => 'Description журнала',
];
$fileFields = [
    'SITE_LOGO_DARK' => [
        'label' => 'Тёмный логотип',
        'hint' => 'Для белого и светлого фона. Рекомендуемый формат — SVG.',
        'extensions' => ['svg', 'png', 'webp'],
        'previewBackground' => '#ffffff',
    ],
    'SITE_LOGO_LIGHT' => [
        'label' => 'Светлый логотип',
        'hint' => 'Для hero и подвала на тёмном фоне. Рекомендуемый формат — SVG.',
        'extensions' => ['svg', 'png', 'webp'],
        'previewBackground' => '#111111',
    ],
    'FAVICON_SVG' => [
        'label' => 'Favicon SVG',
        'hint' => 'Основная масштабируемая иконка браузера.',
        'extensions' => ['svg'],
        'previewBackground' => '#ffffff',
    ],
    'FAVICON_ICO' => [
        'label' => 'Favicon ICO',
        'hint' => 'Резервная иконка для старых браузеров; желательно включить размеры 16×16 и 32×32.',
        'extensions' => ['ico'],
        'previewBackground' => '#ffffff',
    ],
    'APPLE_TOUCH_ICON' => [
        'label' => 'Apple Touch Icon',
        'hint' => 'PNG размером 180×180 px.',
        'extensions' => ['png'],
        'previewBackground' => '#ffffff',
    ],
    'SEO_DEFAULT_IMAGE' => [
        'label' => 'Изображение для соцсетей',
        'hint' => 'Используется в Open Graph по умолчанию. Рекомендуемый размер — 1200×630 px.',
        'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        'previewBackground' => '#ffffff',
    ],
];
$fileErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    foreach ($textFields as $code => $label) {
        Option::set($moduleId, $code, trim((string)($_POST[$code] ?? '')));
    }
    foreach ($seoTextFields as $code => $label) {
        Option::set($moduleId, $code, trim((string)($_POST[$code] ?? '')));
    }
    Option::set($moduleId, 'WEBHOOK_URL', trim((string)($_POST['WEBHOOK_URL'] ?? '')));
    Option::set($moduleId, 'WEBHOOK_ENABLED', !empty($_POST['WEBHOOK_ENABLED']) ? 'Y' : 'N');
    Option::set($moduleId, 'YANDEX_CAPTCHA_ENABLED', !empty($_POST['YANDEX_CAPTCHA_ENABLED']) ? 'Y' : 'N');
    Option::set($moduleId, 'YANDEX_CAPTCHA_CLIENT_KEY', trim((string)($_POST['YANDEX_CAPTCHA_CLIENT_KEY'] ?? '')));
    if (!empty($_POST['DELETE_YANDEX_CAPTCHA_SERVER_KEY'])) {
        Option::set($moduleId, 'YANDEX_CAPTCHA_SERVER_KEY', '');
    } elseif (trim((string)($_POST['YANDEX_CAPTCHA_SERVER_KEY'] ?? '')) !== '') {
        Option::set($moduleId, 'YANDEX_CAPTCHA_SERVER_KEY', trim((string)$_POST['YANDEX_CAPTCHA_SERVER_KEY']));
    }

    foreach ($fileFields as $code => $fileConfig) {
        $currentFileId = (int)$settings[$code];
        if (!empty($_POST['DELETE_' . $code])) {
            if ($currentFileId > 0) {
                CFile::Delete($currentFileId);
            }
            Option::set($moduleId, $code, '0');
            $currentFileId = 0;
        }

        $upload = $_FILES[$code] ?? null;
        if (!is_array($upload) || empty($upload['tmp_name'])) {
            continue;
        }

        $extension = strtolower((string)pathinfo((string)$upload['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $fileConfig['extensions'], true)) {
            $fileErrors[] = $fileConfig['label'] . ': допустимые форматы — ' . implode(', ', $fileConfig['extensions']) . '.';
            continue;
        }
        if ((int)$upload['size'] > 2 * 1024 * 1024) {
            $fileErrors[] = $fileConfig['label'] . ': размер файла не должен превышать 2 МБ.';
            continue;
        }

        $fileId = (int)CFile::SaveFile($upload, 'blackpattern/settings');
        if ($fileId <= 0) {
            $fileErrors[] = $fileConfig['label'] . ': не удалось сохранить файл.';
            continue;
        }
        if ($currentFileId > 0 && $currentFileId !== $fileId) {
            CFile::Delete($currentFileId);
        }
        Option::set($moduleId, $code, (string)$fileId);
    }

    SiteSettings::clearCache();
    $settings = SiteSettings::get();
    if ($fileErrors) {
        CAdminMessage::ShowMessage(['MESSAGE' => implode('<br>', array_map('htmlspecialcharsbx', $fileErrors)), 'TYPE' => 'ERROR', 'HTML' => true]);
    } else {
        CAdminMessage::ShowMessage(['MESSAGE' => 'Настройки сохранены.', 'TYPE' => 'OK']);
    }
}

$tabControl = new CAdminTabControl('blackpatternSettingsTabs', [[
    'DIV' => 'main',
    'TAB' => 'Основное',
    'TITLE' => 'Глобальные настройки сайта',
]]);
$tabControl->Begin();
?>
<form method="post" action="<?= $APPLICATION->GetCurPageParam('', ['mid', 'lang']); ?>?mid=<?= urlencode($moduleId); ?>&lang=<?= LANGUAGE_ID; ?>" enctype="multipart/form-data">
    <?= bitrix_sessid_post(); ?>
    <?php $tabControl->BeginNextTab(); ?>
    <tr class="heading"><td colspan="2">Контакты и тексты</td></tr>
    <?php foreach ($textFields as $code => $label): ?>
        <tr>
            <td width="40%"><?= htmlspecialcharsbx($label); ?>:</td>
            <td width="60%"><input type="text" size="55" name="<?= $code; ?>" value="<?= htmlspecialcharsbx($settings[$code]); ?>"></td>
        </tr>
    <?php endforeach; ?>
    <tr class="heading"><td colspan="2">SEO страниц</td></tr>
    <?php foreach ($seoTextFields as $code => $label): ?>
        <tr>
            <td width="40%"><?= htmlspecialcharsbx($label); ?>:</td>
            <td width="60%">
                <?php if (str_contains($code, 'DESCRIPTION')): ?>
                    <textarea rows="3" cols="57" name="<?= $code; ?>"><?= htmlspecialcharsbx($settings[$code]); ?></textarea>
                <?php else: ?>
                    <input type="<?= $code === 'SITE_URL' ? 'url' : 'text'; ?>" size="55" name="<?= $code; ?>" value="<?= htmlspecialcharsbx($settings[$code]); ?>">
                <?php endif; ?>
                <?php if ($code === 'SITE_URL'): ?><br><small>Укажите полный боевой адрес с протоколом, например https://example.ru. Он используется в canonical, JSON-LD, robots.txt и sitemap.</small><?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <tr class="heading"><td colspan="2">Вебхук заявок</td></tr>
    <tr>
        <td>Включить отправку в вебхук:</td>
        <td><label><input type="checkbox" name="WEBHOOK_ENABLED" value="Y"<?= $settings['WEBHOOK_ENABLED'] === 'Y' ? ' checked' : ''; ?>> Отправлять новые заявки</label></td>
    </tr>
    <tr>
        <td>URL вебхука:</td>
        <td><input type="url" size="55" name="WEBHOOK_URL" value="<?= htmlspecialcharsbx($settings['WEBHOOK_URL']); ?>"><br><small>Данные отправляются JSON-запросом POST только при включённой галочке.</small></td>
    </tr>
    <tr class="heading"><td colspan="2">Yandex SmartCaptcha</td></tr>
    <tr>
        <td>Включить капчу для всех форм:</td>
        <td><label><input type="checkbox" name="YANDEX_CAPTCHA_ENABLED" value="Y"<?= $settings['YANDEX_CAPTCHA_ENABLED'] === 'Y' ? ' checked' : ''; ?>> Проверять все публичные формы</label></td>
    </tr>
    <tr>
        <td>Публичный клиентский ключ:</td>
        <td><input type="text" size="55" name="YANDEX_CAPTCHA_CLIENT_KEY" value="<?= htmlspecialcharsbx($settings['YANDEX_CAPTCHA_CLIENT_KEY']); ?>" autocomplete="off"></td>
    </tr>
    <tr>
        <td>Секретный серверный ключ:</td>
        <td>
            <input type="password" size="55" name="YANDEX_CAPTCHA_SERVER_KEY" value="" autocomplete="new-password" placeholder="<?= $settings['YANDEX_CAPTCHA_SERVER_KEY'] !== '' ? 'Ключ сохранён — оставьте пустым, чтобы не менять' : 'Введите серверный ключ'; ?>">
            <?php if ($settings['YANDEX_CAPTCHA_SERVER_KEY'] !== ''): ?><br><label><input type="checkbox" name="DELETE_YANDEX_CAPTCHA_SERVER_KEY" value="Y"> Удалить сохранённый серверный ключ</label><?php endif; ?>
            <br><small>Секретный ключ не выводится обратно в HTML и используется только для серверной проверки токена.</small>
        </td>
    </tr>
    <tr class="heading"><td colspan="2">Логотипы и иконки сайта</td></tr>
    <?php foreach ($fileFields as $code => $fileConfig):
        $sourceCode = match ($code) {
            'SITE_LOGO_DARK' => 'LOGO_DARK_SRC',
            'SITE_LOGO_LIGHT' => 'LOGO_LIGHT_SRC',
            'FAVICON_SVG' => 'FAVICON_SVG_SRC',
            'FAVICON_ICO' => 'FAVICON_ICO_SRC',
            'APPLE_TOUCH_ICON' => 'APPLE_TOUCH_ICON_SRC',
            'SEO_DEFAULT_IMAGE' => 'SEO_DEFAULT_IMAGE_SRC',
        };
    ?>
        <tr>
            <td><?= htmlspecialcharsbx($fileConfig['label']); ?>:</td>
            <td>
                <?php if ($settings[$sourceCode]): ?>
                    <span style="display:inline-flex;align-items:center;justify-content:center;min-width:72px;min-height:52px;padding:8px;margin-bottom:8px;background:<?= htmlspecialcharsbx($fileConfig['previewBackground']); ?>;border:1px solid #d5d9dc">
                        <img src="<?= htmlspecialcharsbx($settings[$sourceCode]); ?>" alt="" style="max-width:180px;max-height:60px;display:block">
                    </span><br>
                <?php endif; ?>
                <?= CFile::InputFile($code, 30, (int)$settings[$code]); ?><br>
                <small><?= htmlspecialcharsbx($fileConfig['hint']); ?> Максимальный размер — 2 МБ.</small>
                <?php if ((int)$settings[$code] > 0): ?><br><label><input type="checkbox" name="DELETE_<?= $code; ?>" value="Y"> Удалить файл</label><?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php $tabControl->Buttons(); ?>
    <input type="submit" name="save" value="Сохранить" class="adm-btn-save">
</form>
<?php $tabControl->End(); ?>
