<?php

namespace BlackPattern\Forms;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use BlackPattern\Settings\SiteSettings;

final class FormSubmission
{
    public static function getProperties(int $iblockId): array
    {
        $properties = [];
        $result = \CIBlockProperty::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y']);
        while ($property = $result->Fetch()) {
            if (!in_array($property['PROPERTY_TYPE'], ['S', 'N', 'L'], true) || $property['MULTIPLE'] === 'Y') {
                continue;
            }
            $property['ENUMS'] = [];
            if ($property['PROPERTY_TYPE'] === 'L') {
                $enumResult = \CIBlockPropertyEnum::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], ['PROPERTY_ID' => $property['ID']]);
                while ($enum = $enumResult->Fetch()) {
                    $property['ENUMS'][(int)$enum['ID']] = $enum;
                }
            }
            $properties[$property['CODE']] = $property;
        }

        return $properties;
    }

    public static function submit(string $formCode, array $rawValues, array $context = []): array
    {
        if (!Loader::includeModule('iblock')) {
            throw new \RuntimeException('Модуль инфоблоков недоступен.');
        }

        $iblock = \CIBlock::GetList([], ['TYPE' => 'forms', 'CODE' => $formCode, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'])->Fetch();
        if (!$iblock) {
            throw new \RuntimeException('Форма не найдена.');
        }

        if (trim((string)($context['honeypot'] ?? '')) !== '') {
            throw new \RuntimeException('Не удалось отправить форму. Обновите страницу и попробуйте снова.');
        }
        if (($context['privacyConsent'] ?? '') !== 'Y') {
            throw new \RuntimeException('Подтвердите согласие на обработку персональных данных.');
        }

        $properties = self::getProperties((int)$iblock['ID']);
        if (!$properties) {
            throw new \RuntimeException('У формы нет доступных полей.');
        }

        $values = [];
        $mailRows = [];
        $webhookValues = [];
        foreach ($properties as $code => $property) {
            $rawValue = $rawValues[$code] ?? '';
            $value = is_scalar($rawValue) ? trim((string)$rawValue) : '';
            if ($property['IS_REQUIRED'] === 'Y' && $value === '') {
                throw new \RuntimeException('Заполните поле «' . $property['NAME'] . '».');
            }

            if ($value === '') {
                continue;
            }
            if ($property['PROPERTY_TYPE'] === 'N' && !is_numeric(str_replace(',', '.', $value))) {
                throw new \RuntimeException('Поле «' . $property['NAME'] . '» должно быть числом.');
            }
            if ($code === 'PHONE') {
                $digits = preg_replace('/\D+/', '', $value);
                if (strlen($digits) < 10 || strlen($digits) > 15) {
                    throw new \RuntimeException('Введите корректный номер телефона.');
                }
            }
            if ($code === 'EMAIL' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Введите корректный email.');
            }
            if (mb_strlen($value) > 3000) {
                throw new \RuntimeException('Поле «' . $property['NAME'] . '» содержит слишком много символов.');
            }

            $displayValue = $value;
            if ($property['PROPERTY_TYPE'] === 'L') {
                $enumId = (int)$value;
                if (!isset($property['ENUMS'][$enumId])) {
                    throw new \RuntimeException('Передано некорректное значение поля «' . $property['NAME'] . '».');
                }
                $values[$code] = $enumId;
                $displayValue = $property['ENUMS'][$enumId]['VALUE'];
            } else {
                $values[$code] = $value;
            }
            $mailRows[] = $property['NAME'] . ': ' . $displayValue;
            $webhookValues[$code] = $displayValue;
        }

        $ip = trim((string)($context['ip'] ?? ''));
        YandexCaptcha::validate(trim((string)($context['captchaToken'] ?? '')), $ip);
        self::assertRateLimit($formCode);

        $tracking = is_array($context['tracking'] ?? null) ? $context['tracking'] : [];
        $trackingMap = [
            'utm_source' => 'UTM_SOURCE',
            'utm_medium' => 'UTM_MEDIUM',
            'utm_campaign' => 'UTM_CAMPAIGN',
            'utm_content' => 'UTM_CONTENT',
            'utm_term' => 'UTM_TERM',
            'page_url' => 'PAGE_URL',
            'referrer' => 'REFERRER',
        ];
        $systemValues = [
            'USER_IP' => mb_substr($ip, 0, 64),
            'CONSENT' => 'Y',
            'CAPTCHA_USED' => YandexCaptcha::isEnabled() ? 'Y' : 'N',
        ];
        foreach ($trackingMap as $inputCode => $propertyCode) {
            $trackingValue = $tracking[$inputCode] ?? '';
            $systemValues[$propertyCode] = is_scalar($trackingValue) ? mb_substr(trim((string)$trackingValue), 0, 1000) : '';
        }
        foreach ($systemValues as $propertyCode => $systemValue) {
            if ($systemValue === '' || !\CIBlockProperty::GetList([], ['IBLOCK_ID' => (int)$iblock['ID'], 'CODE' => $propertyCode])->Fetch()) {
                continue;
            }
            $values[$propertyCode] = $systemValue;
        }
        foreach ($trackingMap as $inputCode => $propertyCode) {
            if (($systemValues[$propertyCode] ?? '') !== '') {
                $mailRows[] = $propertyCode . ': ' . $systemValues[$propertyCode];
                $webhookValues[$propertyCode] = $systemValues[$propertyCode];
            }
        }

        $submittedAt = date('d.m.Y H:i');
        $title = 'Заявка от формы «' . $iblock['NAME'] . '» ' . $submittedAt;
        $element = new \CIBlockElement();
        $elementId = (int)$element->Add([
            'IBLOCK_ID' => (int)$iblock['ID'],
            'ACTIVE' => 'Y',
            'NAME' => $title,
            'ACTIVE_FROM' => date('d.m.Y H:i:s'),
        ]);
        if ($elementId <= 0) {
            throw new \RuntimeException('Не удалось сохранить заявку: ' . $element->LAST_ERROR);
        }
        \CIBlockElement::SetPropertyValuesEx($elementId, (int)$iblock['ID'], $values);

        $settings = SiteSettings::get();
        \CEvent::Send('BLACKPATTERN_FORM_SUBMIT', 's1', [
            'FORM_NAME' => $iblock['NAME'],
            'SUBMISSION_TITLE' => $title,
            'SUBMISSION_DATA' => implode("\n", $mailRows),
            'RECIPIENT_EMAIL' => $settings['EMAIL'],
        ]);

        self::sendWebhook($settings, [
            'formCode' => $formCode,
            'formName' => $iblock['NAME'],
            'submissionId' => $elementId,
            'submissionTitle' => $title,
            'submittedAt' => date(DATE_ATOM),
            'values' => $webhookValues,
        ]);

        return ['id' => $elementId, 'title' => $title];
    }

    private static function assertRateLimit(string $formCode): void
    {
        $now = microtime(true);
        $lastSubmission = (float)($_SESSION['BLACKPATTERN_FORM_RATE'][$formCode] ?? 0);
        if ($lastSubmission > 0 && ($now - $lastSubmission) < 3) {
            throw new \RuntimeException('Форма уже отправляется. Подождите несколько секунд.');
        }
        $_SESSION['BLACKPATTERN_FORM_RATE'][$formCode] = $now;
    }

    private static function sendWebhook(array $settings, array $payload): void
    {
        if (($settings['WEBHOOK_ENABLED'] ?? 'N') !== 'Y' || !filter_var($settings['WEBHOOK_URL'] ?? '', FILTER_VALIDATE_URL)) {
            return;
        }

        try {
            $client = new HttpClient(['socketTimeout' => 5, 'streamTimeout' => 5]);
            $client->setHeader('Content-Type', 'application/json; charset=utf-8', true);
            $client->post($settings['WEBHOOK_URL'], json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            if ($client->getStatus() >= 400) {
                AddMessage2Log('Form webhook returned HTTP ' . $client->getStatus(), 'blackpattern.forms');
            }
        } catch (\Throwable $exception) {
            AddMessage2Log('Form webhook error: ' . $exception->getMessage(), 'blackpattern.forms');
        }
    }
}
