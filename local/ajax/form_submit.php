<?php

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_bitrix_sessid()) {
        throw new RuntimeException('Недействительный запрос. Обновите страницу и попробуйте снова.');
    }
    if (!Loader::includeModule('blackpattern.settings')) {
        throw new RuntimeException('Модуль форм недоступен.');
    }

    $formCode = preg_replace('/[^a-z0-9_-]/', '', (string)($_POST['form_code'] ?? ''));
    $fields = is_array($_POST['fields'] ?? null) ? $_POST['fields'] : [];
    $result = \BlackPattern\Forms\FormSubmission::submit($formCode, $fields, [
        'privacyConsent' => (string)($_POST['privacy_consent'] ?? ''),
        'honeypot' => (string)($_POST['website'] ?? ''),
        'captchaToken' => (string)($_POST['smart-token'] ?? ''),
        'tracking' => is_array($_POST['tracking'] ?? null) ? $_POST['tracking'] : [],
        'ip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
    ]);

    echo Json::encode(['success' => true, 'message' => 'Спасибо! Заявка принята. Мы свяжемся с вами в рабочее время.', 'submission' => $result]);
} catch (Throwable $exception) {
    http_response_code(400);
    echo Json::encode(['success' => false, 'message' => $exception->getMessage()]);
}
