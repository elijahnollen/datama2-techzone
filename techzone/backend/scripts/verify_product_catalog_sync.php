<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/common.php';
require_once __DIR__ . '/../app/Models/services.php';

$env = loadEnv(__DIR__ . '/../.env');
$pdo = mysqlPdo($env);
$manager = mongoManager($env);

if (!$manager) {
    fwrite(STDERR, "[verify-catalog] MongoDB manager unavailable.\n");
    exit(1);
}

$db = mongoDbName($env);
$collection = mongoCollectionName($env, 'product_catalog');

$rows = $pdo->query(
    'SELECT public_id
     FROM api_product
     WHERE public_id IS NOT NULL
       AND public_id <> ""
     ORDER BY productID ASC'
)->fetchAll();

$mysqlPublicIds = [];
foreach ($rows as $row) {
    $publicId = strtoupper(asString($row['public_id'] ?? ''));
    if ($publicId === '' || !isValidPublicId($publicId)) {
        continue;
    }
    $mysqlPublicIds[$publicId] = true;
}

$catalogDocs = mongoFindMany(
    $manager,
    $db,
    $collection,
    [],
    ['projection' => ['_id' => 1, 'product_public_id' => 1], 'sort' => ['product_public_id' => 1]]
);

$mongoPublicIds = [];
$duplicates = [];
$invalidDocs = 0;
foreach ($catalogDocs as $doc) {
    if (!is_array($doc)) {
        continue;
    }
    $publicId = strtoupper(asString($doc['product_public_id'] ?? ''));
    if ($publicId === '' || !isValidPublicId($publicId)) {
        $invalidDocs++;
        continue;
    }
    if (!isset($mongoPublicIds[$publicId])) {
        $mongoPublicIds[$publicId] = 0;
    }
    $mongoPublicIds[$publicId]++;
    if ($mongoPublicIds[$publicId] > 1) {
        $duplicates[$publicId] = $mongoPublicIds[$publicId];
    }
}

$missingInMongo = [];
foreach (array_keys($mysqlPublicIds) as $publicId) {
    if (!isset($mongoPublicIds[$publicId])) {
        $missingInMongo[] = $publicId;
    }
}

$extraInMongo = [];
foreach (array_keys($mongoPublicIds) as $publicId) {
    if (!isset($mysqlPublicIds[$publicId])) {
        $extraInMongo[] = $publicId;
    }
}

$hasUniqueIndex = false;
try {
    $cursor = $manager->executeCommand($db, new MongoDB\Driver\Command(['listIndexes' => $collection]));
    foreach ($cursor as $indexDoc) {
        $index = toArrayDocument($indexDoc);
        $key = is_array($index['key'] ?? null) ? $index['key'] : [];
        $isUnique = (bool) ($index['unique'] ?? false);
        if ($isUnique && (int) ($key['product_public_id'] ?? 0) === 1) {
            $hasUniqueIndex = true;
            break;
        }
    }
} catch (Throwable) {
    $hasUniqueIndex = false;
}

fwrite(STDOUT, '[verify-catalog] MySQL IDs: ' . count($mysqlPublicIds) . PHP_EOL);
fwrite(STDOUT, '[verify-catalog] Mongo IDs: ' . count($mongoPublicIds) . PHP_EOL);
fwrite(STDOUT, '[verify-catalog] Missing in Mongo: ' . count($missingInMongo) . PHP_EOL);
fwrite(STDOUT, '[verify-catalog] Extra in Mongo: ' . count($extraInMongo) . PHP_EOL);
fwrite(STDOUT, '[verify-catalog] Duplicate Mongo IDs: ' . count($duplicates) . PHP_EOL);
fwrite(STDOUT, '[verify-catalog] Invalid Mongo docs: ' . $invalidDocs . PHP_EOL);
fwrite(STDOUT, '[verify-catalog] Unique index product_public_id: ' . ($hasUniqueIndex ? 'YES' : 'NO') . PHP_EOL);

if ($missingInMongo !== [] || $extraInMongo !== [] || $duplicates !== [] || $invalidDocs > 0 || !$hasUniqueIndex) {
    foreach ($missingInMongo as $publicId) {
        fwrite(STDOUT, 'MISS ' . $publicId . PHP_EOL);
    }
    foreach ($extraInMongo as $publicId) {
        fwrite(STDOUT, 'EXTRA ' . $publicId . PHP_EOL);
    }
    foreach ($duplicates as $publicId => $count) {
        fwrite(STDOUT, 'DUP ' . $publicId . ' (' . $count . ')' . PHP_EOL);
    }
    exit(2);
}

fwrite(STDOUT, "[verify-catalog] Product catalog mapping is healthy.\n");
exit(0);
