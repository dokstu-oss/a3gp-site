<?php
// Приём заявок с формы сайта: письмо на info@a3gp.ru
header('Content-Type: application/json; charset=utf-8');
$to = 'info@a3gp.ru';
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$text = trim($_POST['text'] ?? '');

// website — скрытое поле-ловушка для ботов
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($_POST['website']) || $name === '' || strlen($name) > 200
    || !preg_match('/^[0-9+ ()\-]{10,20}$/', $phone) || $text === '' || strlen($text) > 20000) {
  http_response_code(400);
  exit('{"ok":false}');
}

// не больше 5 заявок в час с одного IP, чтобы боты не завалили ящик; сверх лимита форма сама откроет почтовое приложение
$ip = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$log = sys_get_temp_dir() . '/a3gp_lead_' . md5($ip);
$hits = array_filter(explode(',', (string)@file_get_contents($log)), fn($t) => (int)$t > time() - 3600);
if (count($hits) >= 5) {
  http_response_code(429);
  exit('{"ok":false}');
}
$hits[] = time();
@file_put_contents($log, implode(',', $hits), LOCK_EX);

$subject = '=?UTF-8?B?' . base64_encode('Заявка с сайта АЗ ГРУПП: ' . $name) . '?=';
$headers = "From: info@a3gp.ru\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit";
$ok = mail($to, $subject, $text, $headers, '-finfo@a3gp.ru');

http_response_code($ok ? 200 : 500);
echo $ok ? '{"ok":true}' : '{"ok":false}';
