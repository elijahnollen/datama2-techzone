<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/common.php';
require_once __DIR__ . '/../app/Models/services.php';

$env = loadEnv(__DIR__ . '/../.env');
applySecurityHeaders();

$allowedOrigins = array_filter(array_map('trim', explode(',', (string) envValue($env, 'CORS_ALLOW_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173,http://localhost'))));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS');
header('Access-Control-Allow-Credentials: true');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../app/Core/helpers.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = parsePath();
$pdo = null;

try {
    $pdo = mysqlPdo($env);
    if ($path !== '/health') {
        $ttlSeconds = (int) (envValue($env, 'MONGO_ORDER_SYNC_TTL_SECONDS', '0') ?? '0');
        ensureMongoOrderHistorySync($env, $pdo, $ttlSeconds);
    }

    if ($path === '/health' && $method === 'GET') {
        sendJson(200, ['ok' => true, 'message' => 'API is healthy', 'time' => nowUtc()]);
    }

    require __DIR__ . '/../app/Controllers/auth.php';
    require __DIR__ . '/../app/Controllers/catalog.php';
    require __DIR__ . '/../app/Controllers/customer.php';
    require __DIR__ . '/../app/Controllers/admin.php';

    endpointNotFound();
} catch (PDOException $e) {
    if ($pdo instanceof PDO) {
        rollbackIfInTransaction($pdo);
    }
    $detail = isDebugMode($env) ? $e->getMessage() : 'Please contact the system administrator if this issue persists.';
    sendJson(500, [
        'ok' => false,
        'message' => 'Database operation failed.',
        'errors' => ['database' => $detail],
    ]);
} catch (Throwable $e) {
    if ($pdo instanceof PDO) {
        rollbackIfInTransaction($pdo);
    }
    $detail = isDebugMode($env) ? $e->getMessage() : 'Please try again later.';
    sendJson(500, [
        'ok' => false,
        'message' => 'Unexpected server error.',
        'errors' => ['server' => $detail],
    ]);
}

