<?php

namespace BlackPattern\Forms;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use BlackPattern\Settings\SiteSettings;

final class YandexCaptcha
{
    private const VALIDATE_URL = 'https://smartcaptcha.cloud.yandex.ru/validate';

    public static function isEnabled(?array $settings = null): bool
    {
        $settings ??= SiteSettings::get();

        return ($settings['YANDEX_CAPTCHA_ENABLED'] ?? 'N') === 'Y';
    }

    public static function validate(string $token, string $ip): void
    {
        $settings = SiteSettings::get();
        if (!self::isEnabled($settings)) {
            return;
        }

        $secret = trim((string)($settings['YANDEX_CAPTCHA_SERVER_KEY'] ?? ''));
        if ($secret === '' || trim((string)($settings['YANDEX_CAPTCHA_CLIENT_KEY'] ?? '')) === '') {
            AddMessage2Log('Yandex SmartCaptcha is enabled, but its keys are incomplete.', 'blackpattern.forms');
            throw new \RuntimeException('Проверка формы временно недоступна. Сообщите администратору сайта.');
        }
        if ($token === '') {
            throw new \RuntimeException('Подтвердите, что вы не робот.');
        }

        try {
            $client = new HttpClient(['socketTimeout' => 3, 'streamTimeout' => 3]);
            $client->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8', true);
            $response = $client->post(self::VALIDATE_URL, http_build_query([
                'secret' => $secret,
                'token' => $token,
                'ip' => $ip,
            ]));
            if ($client->getStatus() !== 200) {
                AddMessage2Log('Yandex SmartCaptcha returned HTTP ' . $client->getStatus(), 'blackpattern.forms');
                throw new \RuntimeException('Не удалось проверить защиту от спама. Попробуйте ещё раз.');
            }

            $result = Json::decode((string)$response);
            if (($result['status'] ?? '') !== 'ok') {
                throw new \RuntimeException('Проверка защиты от спама не пройдена. Обновите капчу и попробуйте ещё раз.');
            }
        } catch (\RuntimeException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            AddMessage2Log('Yandex SmartCaptcha error: ' . $exception->getMessage(), 'blackpattern.forms');
            throw new \RuntimeException('Не удалось проверить защиту от спама. Попробуйте ещё раз.');
        }
    }
}
