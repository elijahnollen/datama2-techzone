<?php
require_once __DIR__ . '/../config/mongo_db.php';

class ProductContent {
    private $collection;

    public function __construct() {
        $this->collection = mongo()->product_details;
    }

    public function getProductDetails($sqlProductID) {
        return $this->collection->findOne(['sql_product_id' => (int)$sqlProductID]);
    }

    public function saveProductDetails($sqlProductID, $description, $features, $images) {
        // Updats the document if it exists, creates a new one if it doesnt
        $result = $this->collection->updateOne(
            ['sql_product_id' => (int)$sqlProductID],
            ['$set' => [
                'description' => $description,
                'features'    => $features,
                'images'      => $images,
                'updated_at'  => new MongoDB\BSON\UTCDateTime()
            ]],
            ['upsert' => true]
        );
        return $result->getModifiedCount() || $result->getUpsertedCount();
    }
}
