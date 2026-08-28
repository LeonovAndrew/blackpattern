<?php

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Web\Json;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo Json::encode(['success' => false, 'message' => 'Метод не поддерживается.']);
    exit;
}

$sessid = bitrix_sessid();
if ($sessid === '') {
    http_response_code(500);
    echo Json::encode(['success' => false, 'message' => 'Не удалось подготовить безопасную отправку формы.']);
    exit;
}

echo Json::encode(['success' => true, 'sessid' => $sessid]);
