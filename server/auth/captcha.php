<?php
require_once __DIR__ . '/../lib/session.php';

header('Content-Type: application/json');

$a = random_int(1, 9);
$b = random_int(1, 9);

$_SESSION['captcha_answer'] = (string)($a + $b);

echo json_encode([
  'success' => true,
  'question' => "What is {$a} + {$b}?"
]);
