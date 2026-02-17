<?php
/**
 * ═══════════════════════════════════════════════════════════════════════
 * TECHZONE PHP API - MIDDLEWARE TRANSACTION BRIDGE
 * ═══════════════════════════════════════════════════════════════════════
 * 
 * This is an EXAMPLE implementation of your PHP backend API.
 * 
 * Architecture:
 * - Frontend (React) → calls this PHP API → queries MySQL + MongoDB
 * - The Vault (MySQL): Source of Truth for transactions
 * - The Library (MongoDB): Performance layer for catalog & sessions
 * 
 * Installation:
 * ```bash
 * composer require mongodb/mongodb
 * ```
 * 
 * ═══════════════════════════════════════════════════════════════════════
 */

// ═══════════════════════════════════════════════════════════════════════
// CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════

// Enable CORS for frontend
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database Configuration
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', 'your_password');
define('MYSQL_DB', 'techzone');

define('MONGO_URI', 'mongodb://localhost:27017');
define('MONGO_DB', 'techzone');

// Connect to MySQL (The Vault)
function getMySQLConnection() {
    $mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    
    if ($mysqli->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'MySQL connection failed']);
        exit();
    }
    
    return $mysqli;
}

// Connect to MongoDB (The Library)
function getMongoConnection() {
    try {
        $client = new MongoDB\Client(MONGO_URI);
        return $client->selectDatabase(MONGO_DB);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'MongoDB connection failed']);
        exit();
    }
}

// ═══════════════════════════════════════════════════════════════════════
// ROUTER
// ═══════════════════════════════════════════════════════════════════════

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $path); // Remove /api prefix

// Route the request
switch (true) {
    // Health Check
    case $path === '/health':
        handleHealthCheck();
        break;
    
    // Products Endpoints (MongoDB - The Library)
    case $path === '/products' && $method === 'GET':
        handleGetAllProducts();
        break;
    
    case preg_match('/^\/products\/search$/', $path) && $method === 'GET':
        handleSearchProducts();
        break;
    
    case preg_match('/^\/products\/(\w+)$/', $path, $matches) && $method === 'GET':
        handleGetProductById($matches[1]);
        break;
    
    // Orders Endpoints (MongoDB + MySQL)
    case $path === '/orders' && $method === 'GET':
        handleGetOrders();
        break;
    
    case $path === '/orders' && $method === 'POST':
        handleCreateOrder();
        break;
    
    case preg_match('/^\/orders\/(\w+)$/', $path, $matches) && $method === 'GET':
        handleGetOrderById($matches[1]);
        break;
    
    // Cart Endpoints (MongoDB)
    case $path === '/cart' && $method === 'GET':
        handleGetCart();
        break;
    
    case $path === '/cart' && $method === 'POST':
        handleSaveCart();
        break;
    
    // Reviews Endpoints (MongoDB)
    case $path === '/reviews' && $method === 'GET':
        handleGetReviews();
        break;
    
    case $path === '/reviews' && $method === 'POST':
        handleSubmitReview();
        break;
    
    // Return Requests (MongoDB + MySQL)
    case $path === '/returns' && $method === 'GET':
        handleGetReturnRequests();
        break;
    
    case $path === '/returns' && $method === 'POST':
        handleSubmitReturnRequest();
        break;
    
    // Inventory Check (MySQL)
    case preg_match('/^\/inventory\/(\w+)$/', $path, $matches) && $method === 'GET':
        handleCheckInventory($matches[1]);
        break;
    
    // Inquiries (MongoDB)
    case $path === '/inquiries' && $method === 'POST':
        handleSubmitInquiry();
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

// ═══════════════════════════════════════════════════════════════════════
// PRODUCTS HANDLERS (MongoDB - "The Library")
// ═══════════════════════════════════════════════════════════════════════

function handleGetAllProducts() {
    $db = getMongoConnection();
    
    // Get category filter if provided
    $category = $_GET['category'] ?? null;
    
    $filter = [];
    if ($category) {
        $filter['category'] = $category;
    }
    
    $products = $db->products->find($filter)->toArray();
    
    echo json_encode($products);
}

function handleGetProductById($id) {
    $db = getMongoConnection();
    
    $product = $db->products->findOne(['id' => $id]);
    
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        return;
    }
    
    echo json_encode($product);
}

function handleSearchProducts() {
    $db = getMongoConnection();
    
    $query = $_GET['q'] ?? '';
    
    if (empty($query)) {
        echo json_encode([]);
        return;
    }
    
    // Search in name and description
    $products = $db->products->find([
        '$or' => [
            ['name' => new MongoDB\BSON\Regex($query, 'i')],
            ['description' => new MongoDB\BSON\Regex($query, 'i')]
        ]
    ])->toArray();
    
    echo json_encode($products);
}

// ═══════════════════════════════════════════════════════════════════════
// ORDERS HANDLERS (MongoDB "The Library" + MySQL "The Vault")
// ═══════════════════════════════════════════════════════════════════════

function handleCreateOrder() {
    $db = getMongoConnection();
    $mysqli = getMySQLConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Generate order ID
    $orderId = uniqid('ORD-');
    
    // Prepare order data for MongoDB
    $orderData = [
        'id' => $orderId,
        'userId' => $input['userId'],
        'items' => $input['items'],
        'totalAmount' => $input['totalAmount'],
        'status' => 'pending',
        'deliveryMethod' => $input['deliveryMethod'],
        'shippingAddress' => $input['shippingAddress'] ?? null,
        'paymentMethod' => $input['paymentMethod'],
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
        'updatedAt' => new MongoDB\BSON\UTCDateTime()
    ];
    
    // 1. Insert into MongoDB (The Library - orders collection)
    $db->orders->insertOne($orderData);
    
    // 2. Insert into MySQL (The Vault - Sale table - Source of Truth)
    $stmt = $mysqli->prepare("
        INSERT INTO Sale (order_id, customer_id, total_amount, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssd", $orderId, $input['userId'], $input['totalAmount']);
    $stmt->execute();
    
    // 3. Update MySQL Inventory (decrease stock)
    foreach ($input['items'] as $item) {
        $stmt = $mysqli->prepare("
            INSERT INTO Inventory_Transaction (product_id, quantity, type, transaction_date) 
            VALUES (?, ?, 'sale', NOW())
        ");
        $quantity = -$item['quantity']; // Negative for sales
        $stmt->bind_param("si", $item['productId'], $quantity);
        $stmt->execute();
    }
    
    echo json_encode([
        'orderId' => $orderId,
        'status' => 'success',
        'message' => 'Order created successfully'
    ]);
}

function handleGetOrders() {
    $db = getMongoConnection();
    
    $userId = $_GET['userId'] ?? null;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'userId is required']);
        return;
    }
    
    $orders = $db->orders->find(
        ['userId' => $userId],
        ['sort' => ['createdAt' => -1]]
    )->toArray();
    
    echo json_encode($orders);
}

function handleGetOrderById($orderId) {
    $db = getMongoConnection();
    
    $order = $db->orders->findOne(['id' => $orderId]);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }
    
    echo json_encode($order);
}

// ═══════════════════════════════════════════════════════════════════════
// CART HANDLERS (MongoDB - "The Library" - carts collection)
// ═══════════════════════════════════════════════════════════════════════

function handleGetCart() {
    $db = getMongoConnection();
    
    $userId = $_GET['userId'] ?? null;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'userId is required']);
        return;
    }
    
    $cart = $db->carts->findOne(['userId' => $userId]);
    
    echo json_encode([
        'items' => $cart['items'] ?? []
    ]);
}

function handleSaveCart() {
    $db = getMongoConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = $input['userId'];
    $items = $input['items'];
    
    // Upsert cart (update if exists, insert if not)
    $db->carts->updateOne(
        ['userId' => $userId],
        [
            '$set' => [
                'items' => $items,
                'updatedAt' => new MongoDB\BSON\UTCDateTime()
            ]
        ],
        ['upsert' => true]
    );
    
    echo json_encode(['status' => 'success']);
}

// ═══════════════════════════════════════════════════════════════════════
// REVIEWS HANDLERS (MongoDB - "The Library" - reviews collection)
// ═══════════════════════════════════════════════════════════════════════

function handleGetReviews() {
    $db = getMongoConnection();
    
    $productId = $_GET['productId'] ?? null;
    
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['error' => 'productId is required']);
        return;
    }
    
    $reviews = $db->reviews->find(
        ['productId' => $productId],
        ['sort' => ['createdAt' => -1]]
    )->toArray();
    
    echo json_encode($reviews);
}

function handleSubmitReview() {
    $db = getMongoConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $reviewData = [
        'id' => uniqid('REV-'),
        'productId' => $input['productId'],
        'userId' => $input['userId'],
        'userName' => $input['userName'],
        'rating' => $input['rating'],
        'comment' => $input['comment'],
        'createdAt' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $db->reviews->insertOne($reviewData);
    
    echo json_encode($reviewData);
}

// ═══════════════════════════════════════════════════════════════════════
// RETURN REQUESTS HANDLERS (MongoDB + MySQL)
// ═══════════════════════════════════════════════════════════════════════

function handleSubmitReturnRequest() {
    $db = getMongoConnection();
    $mysqli = getMySQLConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $returnId = uniqid('RET-');
    
    // 1. Insert into MongoDB (The Library - return_requests collection)
    $returnData = [
        'id' => $returnId,
        'orderId' => $input['orderId'],
        'userId' => $input['userId'],
        'productId' => $input['productId'],
        'reason' => $input['reason'],
        'description' => $input['description'],
        'images' => $input['images'] ?? [],
        'status' => 'pending',
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
        'updatedAt' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $db->return_requests->insertOne($returnData);
    
    // 2. Insert into MySQL (The Vault - Return table)
    $stmt = $mysqli->prepare("
        INSERT INTO `Return` (return_id, order_id, product_id, reason, status, created_at) 
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("ssss", 
        $returnId, 
        $input['orderId'], 
        $input['productId'], 
        $input['reason']
    );
    $stmt->execute();
    
    echo json_encode($returnData);
}

function handleGetReturnRequests() {
    $db = getMongoConnection();
    
    $userId = $_GET['userId'] ?? null;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'userId is required']);
        return;
    }
    
    $returns = $db->return_requests->find(
        ['userId' => $userId],
        ['sort' => ['createdAt' => -1]]
    )->toArray();
    
    echo json_encode($returns);
}

// ═══════════════════════════════════════════════════════════════════════
// INVENTORY HANDLERS (MySQL - "The Vault")
// ═══════════════════════════════════════════════════════════════════════

function handleCheckInventory($productId) {
    $mysqli = getMySQLConnection();
    
    $stmt = $mysqli->prepare("
        SELECT 
            p.id,
            p.name,
            COALESCE(SUM(it.quantity), 0) as stock,
            CASE 
                WHEN COALESCE(SUM(it.quantity), 0) > 0 THEN 1 
                ELSE 0 
            END as available
        FROM Product p
        LEFT JOIN Inventory_Transaction it ON p.id = it.product_id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    
    $stmt->bind_param("s", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if (!$data) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        return;
    }
    
    echo json_encode([
        'available' => (bool) $data['available'],
        'quantity' => (int) $data['stock']
    ]);
}

// ═══════════════════════════════════════════════════════════════════════
// INQUIRIES HANDLERS (MongoDB - "The Library" - inquiries collection)
// ═══════════════════════════════════════════════════════════════════════

function handleSubmitInquiry() {
    $db = getMongoConnection();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $inquiryData = [
        'id' => uniqid('INQ-'),
        'name' => $input['name'],
        'email' => $input['email'],
        'phone' => $input['phone'] ?? null,
        'subject' => $input['subject'],
        'message' => $input['message'],
        'status' => 'new',
        'createdAt' => new MongoDB\BSON\UTCDateTime()
    ];
    
    $db->inquiries->insertOne($inquiryData);
    
    echo json_encode($inquiryData);
}

// ═══════════════════════════════════════════════════════════════════════
// UTILITY HANDLERS
// ═══════════════════════════════════════════════════════════════════════

function handleHealthCheck() {
    $mysqli = getMySQLConnection();
    $db = getMongoConnection();
    
    $mysqlConnected = $mysqli->ping();
    $mongoConnected = true; // If we got here, MongoDB is connected
    
    echo json_encode([
        'status' => 'ok',
        'mysql' => $mysqlConnected ? 'connected' : 'disconnected',
        'mongodb' => $mongoConnected ? 'connected' : 'disconnected'
    ]);
}

?>
