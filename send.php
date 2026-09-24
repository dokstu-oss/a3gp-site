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

$subject = '=?UTF-8?B?' . base64_encode('Заявка с сайта АЗ ГРУПП: ' . $name) . '?=';
$headers = "From: info@a3gp.ru\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit";
$ok = mail($to, $subject, $text, $headers, '-finfo@a3gp.ru');

http_response_code($ok ? 200 : 500);
echo $ok ? '{"ok":true}' : '{"ok":false}';
