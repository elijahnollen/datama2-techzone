# Database Schema Reference

This document provides recommended database schemas for your PHP backend.

## Architecture Overview

```
Frontend (React/TypeScript) 
    ↓ API Calls
Middleware Transaction Bridge (PHP)
    ↓ Queries
MySQL (The Vault) + MongoDB (The Library)
```

---

## MySQL Schema (The Vault)

### 1. Admin Users Table
```sql
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- hashed
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. Products Table
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(100),
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### 3. Customers/Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL, -- hashed
    status ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
    total_spent DECIMAL(10, 2) DEFAULT 0,
    total_orders INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 4. Orders Table
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    payment_status ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 5. Order Items Table
```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255),
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

### 6. Inventory Table
```sql
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNIQUE NOT NULL,
    sku VARCHAR(100) NOT NULL,
    stock INT DEFAULT 0,
    reserved INT DEFAULT 0,
    damaged INT DEFAULT 0,
    available INT AS (stock - reserved - damaged) STORED,
    reorder_point INT DEFAULT 10,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### 7. Return Requests Table
```sql
CREATE TABLE return_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    return_number VARCHAR(50) UNIQUE NOT NULL,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255),
    reason TEXT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Refunded') DEFAULT 'Pending',
    images TEXT, -- JSON array of image URLs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

### 8. Suppliers Table
```sql
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    products TEXT, -- JSON array of product categories
    total_orders INT DEFAULT 0,
    total_value DECIMAL(12, 2) DEFAULT 0,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    rating DECIMAL(2, 1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## MongoDB Collections (The Library)

MongoDB is ideal for logs, analytics, and historical data.

### 1. Inventory Logs Collection
```javascript
{
  _id: ObjectId("..."),
  product_id: "123",
  product_name: "ROG Strix G16",
  type: "Stock In", // Stock In, Stock Out, Damaged, Returned
  quantity: 10,
  previous_stock: 5,
  new_stock: 15,
  notes: "New shipment from supplier",
  admin_id: "1",
  admin_name: "Admin User",
  created_at: ISODate("2024-02-10T10:30:00Z")
}
```

### 2. Activity Logs Collection
```javascript
{
  _id: ObjectId("..."),
  admin_id: "1",
  admin_email: "admin@techzone.com",
  action: "UPDATE_ORDER_STATUS",
  resource: "orders",
  resource_id: "ORD-001",
  details: {
    old_status: "Pending",
    new_status: "Processing"
  },
  ip_address: "192.168.1.1",
  user_agent: "Mozilla/5.0...",
  created_at: ISODate("2024-02-10T10:30:00Z")
}
```

### 3. Sales Analytics Collection
```javascript
{
  _id: ObjectId("..."),
  period: "2024-02", // YYYY-MM format
  total_revenue: 1234500,
  total_orders: 854,
  total_products_sold: 1245,
  average_order_value: 1446.37,
  top_products: [
    { product_id: "1", product_name: "ROG Strix G16", quantity_sold: 45, revenue: 3375000 }
  ],
  daily_breakdown: [
    { date: "2024-02-01", revenue: 45000, orders: 12 }
  ],
  created_at: ISODate("2024-02-10T10:30:00Z")
}
```

---

## PHP Backend Examples

### Example: Get Products Endpoint

**File:** `/api/products/index.php`

```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173'); // Your frontend URL
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Check authentication
verifyAdminAuth();

try {
    $db = getDBConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get all products
        $stmt = $db->prepare("
            SELECT * FROM products 
            WHERE deleted_at IS NULL 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $products
        ]);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create new product
        $input = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $db->prepare("
            INSERT INTO products (name, sku, category, price, stock, status, image)
            VALUES (:name, :sku, :category, :price, :stock, :status, :image)
        ");
        
        $stmt->execute([
            ':name' => $input['name'],
            ':sku' => $input['sku'],
            ':category' => $input['category'],
            ':price' => $input['price'],
            ':stock' => $input['stock'],
            ':status' => $input['status'] ?? 'Active',
            ':image' => $input['image'] ?? null
        ]);
        
        $productId = $db->lastInsertId();
        
        // Get created product
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $product
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
```

### Example: Login Endpoint

**File:** `/api/auth/login.php`

```php
<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');

require_once '../../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Create session
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_role'] = $user['role'];
        
        // Don't send password to frontend
        unset($user['password']);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
```

---

## Environment Setup

### Database Connection (config/database.php)

```php
<?php
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'techzone_db';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

function getMongoConnection() {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $client = new MongoDB\Client("mongodb://localhost:27017");
    return $client->techzone_logs;
}
```

---

## Next Steps

1. ✅ Create MySQL database and run the schema scripts
2. ✅ Set up MongoDB for logs (optional but recommended)
3. ✅ Create PHP endpoints following the examples above
4. ✅ Update `/services/api.ts` with your PHP backend URL
5. ✅ Test each endpoint with the frontend
6. ✅ Implement authentication middleware
7. ✅ Add input validation and security measures
