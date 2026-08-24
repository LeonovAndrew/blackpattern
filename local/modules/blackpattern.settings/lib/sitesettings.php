<?php

namespace BlackPattern\Settings;

use Bitrix\Main\Config\Option;

final class SiteSettings
{
    public const MODULE_ID = 'blackpattern.settings';

    private const DEFAULTS = [
        'BRAND_NAME' => 'Black Pattern',
        'PHONE' => '+7 (495) 191-98-20',
        'EMAIL' => 'hello@whitepattern.ru',
        'TELEGRAM_URL' => '',
        'FOOTER_TEXT' => 'Строим управляемые отделы маркетинга внутри компаний.',
        'PRIVACY_URL' => '/privacy/',
        'LEGAL_NAME' => 'BLACK PATTERN',
        'SITE_URL' => '',
        'SEO_HOME_TITLE' => 'Marketing OS — построение отдела маркетинга под ключ',
        'SEO_HOME_DESCRIPTION' => 'Отдел маркетинга под ключ: оргструктура, регламенты и ЦКП, система найма для HR, контур подрядчиков.',
        'SEO_JOURNAL_TITLE' => 'Журнал — как построить управляемый маркетинг | Black Pattern',
        'SEO_JOURNAL_DESCRIPTION' => 'Разборы по устройству отдела маркетинга, найму, метрикам и управлению для собственников, HR-директоров и директоров по маркетингу.',
        'SITE_LOGO' => '0',
        'SITE_LOGO_DARK' => '0',
        'SITE_LOGO_LIGHT' => '0',
        'FAVICON_SVG' => '0',
        'FAVICON_ICO' => '0',
        'APPLE_TOUCH_ICON' => '0',
        'SEO_DEFAULT_IMAGE' => '0',
        'WEBHOOK_ENABLED' => 'N',
        'WEBHOOK_URL' => '',
        'YANDEX_CAPTCHA_ENABLED' => 'N',
        'YANDEX_CAPTCHA_CLIENT_KEY' => '',
        'YANDEX_CAPTCHA_SERVER_KEY' => '',
    ];

    private static ?array $settings = null;

    public static function get(): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }

        $settings = self::DEFAULTS;
        foreach (self::DEFAULTS as $code => $defaultValue) {
            $settings[$code] = Option::get(self::MODULE_ID, $code, $defaultValue);
        }

        $settings['PHONE_HREF'] = 'tel:' . preg_replace('/[^+0-9]/', '', $settings['PHONE']);

        $legacyLogoId = (int)$settings['SITE_LOGO'];
        $darkLogoId = (int)$settings['SITE_LOGO_DARK'] ?: $legacyLogoId;
        $lightLogoId = (int)$settings['SITE_LOGO_LIGHT'] ?: $legacyLogoId;
        $settings['LOGO_SRC'] = $legacyLogoId > 0 ? (string)\CFile::GetPath($legacyLogoId) : '';
        $settings['LOGO_DARK_SRC'] = $darkLogoId > 0 ? (string)\CFile::GetPath($darkLogoId) : '';
        $settings['LOGO_LIGHT_SRC'] = $lightLogoId > 0 ? (string)\CFile::GetPath($lightLogoId) : '';
        $settings['FAVICON_SVG_SRC'] = self::getFilePath((int)$settings['FAVICON_SVG']);
        $settings['FAVICON_ICO_SRC'] = self::getFilePath((int)$settings['FAVICON_ICO']);
        $settings['APPLE_TOUCH_ICON_SRC'] = self::getFilePath((int)$settings['APPLE_TOUCH_ICON']);
        $settings['SEO_DEFAULT_IMAGE_SRC'] = self::getFilePath((int)$settings['SEO_DEFAULT_IMAGE']);

        self::$settings = $settings;

        return self::$settings;
    }

    public static function clearCache(): void
    {
        self::$settings = null;
    }

    private static function getFilePath(int $fileId): string
    {
        return $fileId > 0 ? (string)\CFile::GetPath($fileId) : '';
    }
}
