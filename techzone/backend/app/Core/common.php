<?php

declare(strict_types=1);

function loadEnv(string $file): array
{
    $config = [];
    if (!is_file($file)) {
        return $config;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $config;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $pos = strpos($trimmed, '=');
        if ($pos === false) {
            continue;
        }

        $key = trim(substr($trimmed, 0, $pos));
        $value = trim(substr($trimmed, $pos + 1));
        $value = trim($value, "\"'");
        $config[$key] = $value;
    }

    return $config;
}

function envValue(array $env, string $key, ?string $default = null): ?string
{
    return $env[$key] ?? $default;
}

function sendJson(int $status, array $payload): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function applySecurityHeaders(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (isHttpsRequest()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function isDebugMode(array $env): bool
{
    $raw = strtolower(asString(envValue($env, 'APP_DEBUG', 'false')));
    return in_array($raw, ['1', 'true', 'yes', 'on'], true);
}

function requestJson(int $maxBytes = 1048576): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $contentType = asString($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
    if ($contentType !== '' && stripos($contentType, 'application/json') === false) {
        sendJson(415, [
            'ok' => false,
            'message' => 'Unsupported content type.',
            'errors' => ['body' => 'Use application/json content type.'],
        ]);
    }

    if (strlen($raw) > $maxBytes) {
        sendJson(413, [
            'ok' => false,
            'message' => 'Request body is too large.',
            'errors' => ['body' => 'Please submit a smaller payload.'],
        ]);
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        sendJson(400, [
            'ok' => false,
            'message' => 'Invalid JSON payload.',
            'errors' => ['body' => 'Please send valid JSON.'],
        ]);
    }

    return $decoded;
}

function validateEmail(?string $email): bool
{
    if ($email === null) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must include at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must include at least one lowercase letter.';
    }
    if (!preg_match('/\d/', $password)) {
        return 'Password must include at least one number.';
    }
    return null;
}

function nowUtc(): string
{
    return gmdate('Y-m-d H:i:s');
}

function randomPublicId(string $prefix): string
{
    return $prefix . '-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
}

function base64UrlEncode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function base64UrlDecode(string $value): string
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }
    return base64_decode(strtr($value, '-_', '+/')) ?: '';
}

function createToken(array $claims, string $secret, int $ttlSeconds): string
{
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload = $claims;
    $payload['iat'] = time();
    $payload['exp'] = time() + $ttlSeconds;

    $h = base64UrlEncode((string) json_encode($header));
    $p = base64UrlEncode((string) json_encode($payload));
    $signature = hash_hmac('sha256', $h . '.' . $p, $secret, true);

    return $h . '.' . $p . '.' . base64UrlEncode($signature);
}

function parseToken(?string $token, string $secret): ?array
{
    if ($token === null || $token === '') {
        return null;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$h, $p, $s] = $parts;
    $expected = base64UrlEncode(hash_hmac('sha256', $h . '.' . $p, $secret, true));
    if (!hash_equals($expected, $s)) {
        return null;
    }

    $payload = json_decode(base64UrlDecode($p), true);
    if (!is_array($payload) || !isset($payload['exp']) || !is_numeric($payload['exp'])) {
        return null;
    }

    if ((int) $payload['exp'] < time()) {
        return null;
    }

    return $payload;
}

function isHttpsRequest(): bool
{
    $https = asString($_SERVER['HTTPS'] ?? '');
    if ($https !== '' && strtolower($https) !== 'off') {
        return true;
    }
    $proto = strtolower(asString($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    return $proto === 'https';
}

function bearerToken(): ?string
{
    $candidates = [];

    $serverAuth = asString($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if ($serverAuth !== '') {
        $candidates[] = $serverAuth;
    }

    $redirectAuth = asString($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if ($redirectAuth !== '') {
        $candidates[] = $redirectAuth;
    }

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (is_array($headers)) {
            $direct = asString($headers['Authorization'] ?? '');
            if ($direct !== '') {
                $candidates[] = $direct;
            }
            $lower = asString($headers['authorization'] ?? '');
            if ($lower !== '') {
                $candidates[] = $lower;
            }
        }
    }

    foreach ($candidates as $header) {
        if (preg_match('/^\s*Bearer\s+(.+)\s*$/i', $header, $matches) === 1) {
            $token = trim((string) ($matches[1] ?? ''));
            if ($token !== '') {
                return $token;
            }
        }
    }

    return null;
}

function parsePath(): string
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH);
    if (!is_string($path)) {
        return '/';
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($scriptName !== '' && str_starts_with($path, $scriptName)) {
        $path = substr($path, strlen($scriptName));
    } else {
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        $scriptDir = rtrim($scriptDir, '/');
        if ($scriptDir !== '' && str_starts_with($path, $scriptDir . '/')) {
            $path = substr($path, strlen($scriptDir));
        }
    }

    if ($path === '' || $path === false) {
        return '/';
    }

    return '/' . ltrim($path, '/');
}

function asString(mixed $value): string
{
    return is_string($value) ? trim($value) : '';
}

function validateHumanName(string $value, string $fieldLabel, int $min = 2, int $max = 60): ?string
{
    $len = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    if ($len < $min) {
        return $fieldLabel . ' must be at least ' . $min . ' characters.';
    }
    if ($len > $max) {
        return $fieldLabel . ' must not exceed ' . $max . ' characters.';
    }
    if (!preg_match('/^[A-Za-z][A-Za-z\s\.\'-]*$/', $value)) {
        return $fieldLabel . ' contains invalid characters.';
    }
    return null;
}

function validatePhone(?string $value): bool
{
    if ($value === null || trim($value) === '') {
        return true;
    }
    return normalizePhoneNumber($value) !== null;
}

function normalizePhoneNumber(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $clean = preg_replace('/[^0-9]/', '', trim($value)) ?? '';
    if ($clean === '') {
        return null;
    }

    if (str_starts_with($clean, '0') && strlen($clean) === 11) {
        $clean = '63' . substr($clean, 1);
    }

    if (preg_match('/^63\\d{10}$/', $clean) !== 1) {
        return null;
    }

    return $clean;
}

function validateZipCode(?string $value): bool
{
    if ($value === null || trim($value) === '') {
        return true;
    }
    return preg_match('/^\d{4}$/', $value) === 1;
}

function validateTextLength(string $value, string $fieldLabel, int $min, int $max): ?string
{
    $len = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    if ($len < $min) {
        return $fieldLabel . ' must be at least ' . $min . ' characters.';
    }
    if ($len > $max) {
        return $fieldLabel . ' must not exceed ' . $max . ' characters.';
    }
    return null;
}

function parseBoolean(mixed $value): ?bool
{
    if (is_bool($value)) {
        return $value;
    }
    if (is_int($value)) {
        if ($value === 1) return true;
        if ($value === 0) return false;
    }
    if (is_string($value)) {
        $normalized = strtolower(trim($value));
        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) return true;
        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) return false;
    }
    return null;
}

function configuredDemoOtp(array $env): string
{
    $otp = asString(envValue($env, 'DEMO_OTP_CODE', '123456'));
    return $otp !== '' ? $otp : '123456';
}

function configuredOtpWindowSeconds(array $env): int
{
    $raw = (int) (envValue($env, 'DEMO_OTP_WINDOW_SECONDS', '300') ?? '300');
    return $raw > 0 ? $raw : 300;
}

function verifyDemoOtp(array $env, string $otpCode, ?int $issuedAtEpoch = null): bool
{
    if (!hash_equals(configuredDemoOtp($env), trim($otpCode))) {
        return false;
    }

    if ($issuedAtEpoch === null || $issuedAtEpoch <= 0) {
        return true;
    }

    return (time() - $issuedAtEpoch) <= configuredOtpWindowSeconds($env);
}

function ensureAllowedValue(string $value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}

function clientIp(): string
{
    $forwarded = asString($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    if ($forwarded !== '') {
        $parts = array_map('trim', explode(',', $forwarded));
        if ($parts !== []) {
            return (string) $parts[0];
        }
    }
    return asString($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function sanitizeRateId(string $value): string
{
    $normalized = strtolower(trim($value));
    return preg_replace('/[^a-z0-9\-\_\.@]/', '', $normalized) ?? 'unknown';
}

function isRateLimited(string $scope, string $subject, int $maxAttempts, int $windowSeconds): bool
{
    $root = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'techzone_rate_limit';
    if (!is_dir($root)) {
        @mkdir($root, 0777, true);
    }

    $safeScope = preg_replace('/[^a-zA-Z0-9_\-]/', '', $scope) ?? 'default';
    $safeSubject = sanitizeRateId($subject);
    $key = hash('sha256', $safeScope . '|' . $safeSubject);
    $path = $root . DIRECTORY_SEPARATOR . $key . '.json';
    $now = time();
    $windowStart = $now - $windowSeconds;

    $attempts = [];
    if (is_file($path)) {
        $raw = file_get_contents($path);
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $attempt) {
                    if (is_int($attempt) && $attempt >= $windowStart) {
                        $attempts[] = $attempt;
                    }
                }
            }
        }
    }

    if (count($attempts) >= $maxAttempts) {
        file_put_contents($path, json_encode($attempts));
        return true;
    }

    $attempts[] = $now;
    file_put_contents($path, json_encode($attempts));
    return false;
}

function isValidPublicId(string $value): bool
{
    return preg_match('/^[A-Z]{2,3}-[A-Z0-9]{6,12}$/', strtoupper($value)) === 1;
}

function toArrayDocument(mixed $document): array
{
    if ($document instanceof MongoDB\BSON\Document) {
        /** @var array<string,mixed> $doc */
        $doc = $document->toPHP(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        return $doc;
    }

    if ($document instanceof stdClass) {
        /** @var array<string,mixed> $doc */
        $doc = json_decode(json_encode($document, JSON_UNESCAPED_UNICODE), true) ?? [];
        return $doc;
    }

    if (is_array($document)) {
        return $document;
    }

    return [];
}

