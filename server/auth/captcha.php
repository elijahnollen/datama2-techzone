<?php
require_once __DIR__ . '/../lib/session.php';

function captcha_generate(): array {
  $a = random_int(1, 9);
  $b = random_int(1, 9);
  $_SESSION['captcha_answer'] = $a + $b;
  return ['question' => "What is $a + $b?"];
}

function captcha_verify(string $answer): bool {
  if (!isset($_SESSION['captcha_answer'])) return false;
  return ((int)$answer === (int)$_SESSION['captcha_answer']);
}
