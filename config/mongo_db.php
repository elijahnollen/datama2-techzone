<?php
require_once __DIR__ . '/../vendor/autoload.php';

function mongo() {
    static $db = null;
    
    if ($db !== null) {
        return $db;
    }
    
    try {
        $client = new MongoDB\Client("mongodb://localhost:27017");
        $db = $client->techzone;
        return $db;
    } catch (Exception $e) {
        die("MongoDB Connection Error: " . $e->getMessage());
    }
}
