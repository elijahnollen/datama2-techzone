<?php
// server/auth/captcha.php
require_once __DIR__ . '/../lib/session.php';

function captcha_generate(): array {
    $a = random_int(1, 9);
    $b = random_int(1, 9);

    $_SESSION['captcha_answer'] = (string)($a + $b);

    return [
        'question' => "{$a} + {$b} = ?"
    ];
}

function captcha_verify(string $answer): bool {
    $expected = $_SESSION['captcha_answer'] ?? null;
    unset($_SESSION['captcha_answer']);

    if ($expected === null) return false;

    return trim($answer) === trim($expected);
}
