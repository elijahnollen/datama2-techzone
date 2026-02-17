<?php
// server/config/mongo.php
// MongoDB connection using PHP extension (no composer library needed)

function mongo_manager(): MongoDB\Driver\Manager {
    static $mgr = null;
    if ($mgr !== null) return $mgr;

    // Local MongoDB
    $uri = "mongodb://localhost:27017";

    $mgr = new MongoDB\Driver\Manager($uri);
    return $mgr;
}

// Database + collection names used by cart
define('MONGO_DB', 'techzone');
define('MONGO_CART_COLLECTION', 'carts');
