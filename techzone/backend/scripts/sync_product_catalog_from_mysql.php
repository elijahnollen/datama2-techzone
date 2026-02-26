<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/common.php';
require_once __DIR__ . '/../app/Models/services.php';
require_once __DIR__ . '/../app/Core/helpers.php';

$env = loadEnv(__DIR__ . '/../.env');
$pdo = mysqlPdo($env);
$manager = mongoManager($env);
$pruneOrphans = !in_array('--keep-orphans', $argv, true);

if (!$manager) {
    fwrite(STDERR, "[sync-catalog] MongoDB manager unavailable.\n");
    exit(1);
}

$db = mongoDbName($env);
$collection = mongoCollectionName($env, 'product_catalog');

$beforeDocs = mongoFindMany($manager, $db, $collection, [], ['projection' => ['_id' => 1]]);
$beforeCount = count($beforeDocs);

$rows = $pdo->query('SELECT public_id FROM api_product WHERE public_id IS NOT NULL AND public_id <> "" ORDER BY productID ASC')->fetchAll();
$processed = 0;
$mysqlPublicIds = [];

foreach ($rows as $row) {
    $publicId = strtoupper(trim(asString($row['public_id'] ?? '')));
    if ($publicId === '') {
        continue;
    }
    $mysqlPublicIds[$publicId] = true;
    syncMongoProductCatalogFromMysql($env, $pdo, $publicId);
    $processed++;
}

$pruned = 0;
if ($pruneOrphans) {
    $catalogDocs = mongoFindMany(
        $manager,
        $db,
        $collection,
        [],
        ['projection' => ['_id' => 1, 'product_public_id' => 1]]
    );
    foreach ($catalogDocs as $catalogDoc) {
        if (!is_array($catalogDoc)) {
            continue;
        }
        $catalogPublicId = strtoupper(trim(asString($catalogDoc['product_public_id'] ?? '')));
        if ($catalogPublicId === '' || isset($mysqlPublicIds[$catalogPublicId])) {
            continue;
        }
        $objectId = mongoObjectIdFromValue($catalogDoc['_id'] ?? null);
        if ($objectId === null) {
            continue;
        }
        mongoDeleteOne($manager, $db, $collection, ['_id' => $objectId]);
        $pruned++;
    }
}

$afterDocs = mongoFindMany($manager, $db, $collection, [], ['projection' => ['_id' => 1]]);
$afterCount = count($afterDocs);

fwrite(STDOUT, '[sync-catalog] MySQL products processed: ' . $processed . PHP_EOL);
fwrite(STDOUT, '[sync-catalog] Mongo docs before: ' . $beforeCount . PHP_EOL);
fwrite(STDOUT, '[sync-catalog] Mongo docs after: ' . $afterCount . PHP_EOL);
fwrite(STDOUT, '[sync-catalog] Pruned orphan docs: ' . $pruned . PHP_EOL);

exit(0);
