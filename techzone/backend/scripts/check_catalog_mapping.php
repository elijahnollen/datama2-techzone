<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/common.php';
require_once __DIR__ . '/../app/Models/services.php';
require_once __DIR__ . '/../app/Core/helpers.php';

$env = loadEnv(__DIR__ . '/../.env');
$pdo = mysqlPdo($env);
$manager = mongoManager($env);

if (!$manager) {
    fwrite(STDERR, "[check-catalog] MongoDB manager unavailable.\n");
    exit(1);
}

$db = mongoDbName($env);
$collection = mongoCollectionName($env, 'product_catalog');
$catalogDocs = mongoFindMany(
    $manager,
    $db,
    $collection,
    [],
    ['projection' => ['product_public_id' => 1, 'model_name' => 1], 'sort' => ['product_public_id' => 1]]
);
$rows = $pdo->query('SELECT public_id, product_name FROM api_product ORDER BY public_id ASC')->fetchAll();

$mongoIds = [];
foreach ($catalogDocs as $doc) {
    $pid = strtoupper(trim(asString($doc['product_public_id'] ?? '')));
    if ($pid === '') {
        continue;
    }
    $mongoIds[$pid] = true;
}

$mysqlIds = [];
$missingInMongo = [];
foreach ($rows as $row) {
    $pid = strtoupper(trim(asString($row['public_id'] ?? '')));
    if ($pid === '') {
        continue;
    }
    $mysqlIds[$pid] = true;
    if (!isset($mongoIds[$pid])) {
        $missingInMongo[] = $pid . ' | ' . asString($row['product_name'] ?? '');
    }
}

$extraInMongo = [];
foreach (array_keys($mongoIds) as $pid) {
    if (!isset($mysqlIds[$pid])) {
        $extraInMongo[] = $pid;
    }
}

fwrite(STDOUT, '[check-catalog] MySQL products: ' . count($mysqlIds) . PHP_EOL);
fwrite(STDOUT, '[check-catalog] Mongo docs (with PID): ' . count($mongoIds) . PHP_EOL);
fwrite(STDOUT, '[check-catalog] Missing in Mongo: ' . count($missingInMongo) . PHP_EOL);
fwrite(STDOUT, '[check-catalog] Extra in Mongo: ' . count($extraInMongo) . PHP_EOL);

foreach ($missingInMongo as $entry) {
    fwrite(STDOUT, 'MISS ' . $entry . PHP_EOL);
}
foreach ($extraInMongo as $entry) {
    fwrite(STDOUT, 'EXTRA ' . $entry . PHP_EOL);
}

if ($missingInMongo !== [] || $extraInMongo !== []) {
    exit(2);
}

fwrite(STDOUT, "[check-catalog] Mapping is aligned.\n");
exit(0);
