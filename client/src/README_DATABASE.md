# 🎯 DATABASE CONNECTION - YOUR LAYER CAKE ARCHITECTURE

## Welcome! 👋

Your TechZone e-commerce application is ready to connect to your **Layer Cake Architecture** with PHP middleware, MySQL (The Vault), and MongoDB (The Library).

---

## 🏗️ Your Technical Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│              PRESENTATION LAYER (This Frontend)                 │
│              React | Tailwind | TypeScript                      │
└─────────────────────────────────────────────────────────────────┘
                           ↕️ HTTP Requests
┌─────────────────────────────────────────────────────────────────┐
│         MIDDLEWARE TRANSACTION BRIDGE (Your PHP Backend)        │
│                        PHP API                                  │
└─────────────────────────────────────────────────────────────────┘
                           ↕️ Database Queries
         ┌─────────────────────────────────────────┐
         │                                         │
    ┌────▼─────┐                          ┌───────▼──────┐
    │THE VAULT │                          │ THE LIBRARY  │
    │  MySQL   │                          │   MongoDB    │
    │          │                          │              │
    │ Source   │                          │ Performance  │
    │of Truth  │                          │    Layer     │
    └──────────┘                          └──────────────┘
```

---

## 📊 Data Strategy

### **THE VAULT (MySQL)** - The Source of Truth
- ✅ Absolute precision
- ✅ Strict logic  
- ✅ Secure auth data

**Tables:**
- Suppliers
- Customers
- **Product** (Master data)
- Inventory Transaction
- Employee
- Return
- Damaged goods
- Sale

### **THE LIBRARY (MongoDB)** - The Performance Layer
- ✅ Flexible schema
- ✅ High volume reads
- ✅ Temporary states

**Collections:**
- **products** (Catalog) ← Frontend reads from here
- **orders** (History)
- **carts** (Persisted Sessions)
- **reviews** (Social Proof)
- **return_requests** (RMA)
- **inquiries** (Contact Form)
- **audit_logs** (Security)

---

## ⚡ Quick Start (3 Steps)

### **Step 1: Configure Your PHP API Endpoint** (2 minutes)

Open `/services/database.ts` and update line 21:

```typescript
// Change this:
const API_BASE_URL = 'YOUR_PHP_API_ENDPOINT_HERE';

// To your actual PHP API endpoint:
const API_BASE_URL = 'http://localhost:8000/api';  // Development
// or
const API_BASE_URL = 'https://api.techzone.com';   // Production
```

### **Step 2: Ensure Your PHP API Has These Endpoints** (30 minutes)

Your PHP middleware should expose these REST API endpoints:

| Method | Endpoint | Data Source | Description |
|--------|----------|-------------|-------------|
| GET | `/api/products` | MongoDB products | Get all products |
| GET | `/api/products/{id}` | MongoDB products | Get single product |
| GET | `/api/products?category={cat}` | MongoDB products | Get by category |
| GET | `/api/products/search?q={query}` | MongoDB products | Search products |
| POST | `/api/orders` | MongoDB orders + MySQL Sale | Create order |
| GET | `/api/orders?userId={id}` | MongoDB orders | Get user orders |
| GET | `/api/orders/{id}` | MongoDB orders | Get single order |
| POST | `/api/cart` | MongoDB carts | Save cart |
| GET | `/api/cart?userId={id}` | MongoDB carts | Get cart |
| GET | `/api/reviews?productId={id}` | MongoDB reviews | Get reviews |
| POST | `/api/reviews` | MongoDB reviews | Submit review |
| POST | `/api/returns` | MongoDB + MySQL Return | Submit return request |
| GET | `/api/returns?userId={id}` | MongoDB return_requests | Get return requests |
| GET | `/api/inventory/{id}` | MySQL Product + Inventory | Check stock |
| POST | `/api/inquiries` | MongoDB inquiries | Submit inquiry |
| GET | `/api/health` | - | Health check |

### **Step 3: Update Pages to Use Async Data** (20 minutes)

Update these 6 files from synchronous to asynchronous data fetching:

- `/pages/Home.tsx`
- `/pages/ProductDetail.tsx`
- `/pages/Cart.tsx`
- `/pages/MyOrders.tsx`
- `/pages/Checkout.tsx`
- `/pages/ReturnRequest.tsx`

See [EXAMPLE_Home_with_Database.tsx](./EXAMPLE_Home_with_Database.tsx) for the pattern.

---

## 🔧 PHP Backend Implementation Examples

### **Example 1: Get All Products** (MongoDB → Frontend)

```php
// GET /api/products
<?php
header('Content-Type: application/json');

// Connect to MongoDB (The Library)
$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$db = $mongoClient->techzone;

// Fetch from products collection
$products = $db->products->find()->toArray();

echo json_encode($products);
```

### **Example 2: Create Order** (Frontend → MongoDB + MySQL)

```php
// POST /api/orders
<?php
header('Content-Type: application/json');

$orderData = json_decode(file_get_contents('php://input'), true);

// 1. Save to MongoDB (The Library - orders collection)
$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$db = $mongoClient->techzone;

$orderData['createdAt'] = new MongoDB\BSON\UTCDateTime();
$orderData['status'] = 'pending';

$result = $db->orders->insertOne($orderData);
$orderId = (string) $result->getInsertedId();

// 2. Save to MySQL (The Vault - Sale table - Source of Truth)
$mysqli = new mysqli("localhost", "user", "pass", "techzone");

$stmt = $mysqli->prepare("
    INSERT INTO Sale (order_id, customer_id, total_amount, created_at) 
    VALUES (?, ?, ?, NOW())
");
$stmt->bind_param("ssd", 
    $orderId, 
    $orderData['userId'], 
    $orderData['totalAmount']
);
$stmt->execute();

// 3. Update inventory (MySQL)
foreach ($orderData['items'] as $item) {
    $stmt = $mysqli->prepare("
        INSERT INTO Inventory_Transaction (product_id, quantity, type) 
        VALUES (?, ?, 'sale')
    ");
    $qty = -$item['quantity']; // Negative for sales
    $stmt->bind_param("si", $item['productId'], $qty);
    $stmt->execute();
}

echo json_encode(['orderId' => $orderId, 'status' => 'success']);
```

### **Example 3: Search Products** (MongoDB)

```php
// GET /api/products/search?q={query}
<?php
header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$db = $mongoClient->techzone;

// Text search in MongoDB
$products = $db->products->find([
    '$or' => [
        ['name' => new MongoDB\BSON\Regex($query, 'i')],
        ['description' => new MongoDB\BSON\Regex($query, 'i')]
    ]
])->toArray();

echo json_encode($products);
```

### **Example 4: Check Inventory** (MySQL)

```php
// GET /api/inventory/{productId}
<?php
header('Content-Type: application/json');

$productId = $_GET['productId'];

$mysqli = new mysqli("localhost", "user", "pass", "techzone");

$result = $mysqli->query("
    SELECT p.id, p.name,
           COALESCE(SUM(it.quantity), 0) as stock,
           CASE WHEN COALESCE(SUM(it.quantity), 0) > 0 
                THEN true ELSE false END as available
    FROM Product p
    LEFT JOIN Inventory_Transaction it ON p.id = it.product_id
    WHERE p.id = '$productId'
    GROUP BY p.id
");

$data = $result->fetch_assoc();

echo json_encode([
    'available' => (bool) $data['available'],
    'quantity' => (int) $data['stock']
]);
```

---

## 📋 MongoDB Collections Schema

### **products** (Catalog)
```json
{
  "_id": "ObjectId",
  "id": "string",
  "name": "string",
  "price": "number",
  "image": "string",
  "category": "Graphics|Processors|Memory|Cooling|Peripherals",
  "description": "string",
  "isNew": "boolean",
  "available": "boolean"
}
```

### **orders** (History)
```json
{
  "_id": "ObjectId",
  "id": "string",
  "userId": "string",
  "items": [
    {
      "productId": "string",
      "name": "string",
      "price": "number",
      "quantity": "number",
      "image": "string"
    }
  ],
  "totalAmount": "number",
  "status": "pending|processing|shipped|delivered|cancelled",
  "deliveryMethod": "delivery|pickup",
  "shippingAddress": { ... },
  "paymentMethod": "string",
  "createdAt": "ISODate",
  "updatedAt": "ISODate"
}
```

### **carts** (Persisted Sessions)
```json
{
  "_id": "ObjectId",
  "userId": "string",
  "items": [ ... ],
  "updatedAt": "ISODate"
}
```

### **reviews** (Social Proof)
```json
{
  "_id": "ObjectId",
  "id": "string",
  "productId": "string",
  "userId": "string",
  "userName": "string",
  "rating": "number (1-5)",
  "comment": "string",
  "createdAt": "ISODate"
}
```

### **return_requests** (RMA)
```json
{
  "_id": "ObjectId",
  "id": "string",
  "orderId": "string",
  "userId": "string",
  "productId": "string",
  "reason": "string",
  "description": "string",
  "images": ["string"],
  "status": "pending|approved|rejected|completed",
  "createdAt": "ISODate",
  "updatedAt": "ISODate"
}
```

### **inquiries** (Contact Form)
```json
{
  "_id": "ObjectId",
  "id": "string",
  "name": "string",
  "email": "string",
  "phone": "string",
  "subject": "string",
  "message": "string",
  "status": "new|in-progress|resolved",
  "createdAt": "ISODate"
}
```

### **audit_logs** (Security)
```json
{
  "_id": "ObjectId",
  "userId": "string",
  "action": "string",
  "details": { ... },
  "timestamp": "ISODate"
}
```

---

## 🗄️ MySQL Tables Schema

### **Product** (Master Data)
```sql
CREATE TABLE Product (
  id VARCHAR(50) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  category VARCHAR(50),
  sku VARCHAR(100),
  supplier_id VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **Inventory_Transaction**
```sql
CREATE TABLE Inventory_Transaction (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id VARCHAR(50),
  quantity INT,
  type ENUM('purchase', 'sale', 'return', 'damaged'),
  transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES Product(id)
);
```

### **Sale**
```sql
CREATE TABLE Sale (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50),
  customer_id VARCHAR(50),
  total_amount DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### **Return**
```sql
CREATE TABLE Return (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50),
  product_id VARCHAR(50),
  reason TEXT,
  status ENUM('pending', 'approved', 'rejected', 'completed'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## ✅ Integration Checklist

### Backend (PHP)
- [ ] Set up MySQL database (The Vault)
- [ ] Set up MongoDB database (The Library)
- [ ] Create MySQL tables (Product, Sale, Inventory_Transaction, Return, etc.)
- [ ] Create MongoDB collections (products, orders, carts, reviews, etc.)
- [ ] Implement PHP API endpoints (see table above)
- [ ] Add CORS headers to allow frontend requests
- [ ] Test API with Postman or similar tool

### Frontend (React)
- [ ] Update `API_BASE_URL` in `/services/database.ts`
- [ ] Update `/pages/Home.tsx` to use `getAllProducts()`
- [ ] Update `/pages/ProductDetail.tsx` to use `getProductById()`
- [ ] Update `/pages/Cart.tsx` to use `saveCart()` / `getCart()`
- [ ] Update `/pages/MyOrders.tsx` to use `getUserOrders()`
- [ ] Update `/pages/Checkout.tsx` to use `createOrder()`
- [ ] Update `/pages/ReturnRequest.tsx` to use `submitReturnRequest()`
- [ ] Add loading states to all pages
- [ ] Add error handling for API failures
- [ ] Test all functionality end-to-end

---

## 🆘 Common Issues & Solutions

### Issue 1: CORS Error
**Error:** `Access-Control-Allow-Origin` blocked

**Solution:** Add CORS headers to your PHP API:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### Issue 2: MongoDB Connection Failed
**Error:** Cannot connect to MongoDB

**Solution:** Check MongoDB is running:
```bash
# Check if MongoDB is running
sudo systemctl status mongod

# Start MongoDB
sudo systemctl start mongod
```

### Issue 3: MySQL Authentication Error
**Error:** Access denied for user

**Solution:** Check MySQL credentials and permissions:
```sql
GRANT ALL PRIVILEGES ON techzone.* TO 'user'@'localhost';
FLUSH PRIVILEGES;
```

### Issue 4: Empty Product Data
**Error:** Products array is empty

**Solution:** Verify data exists in MongoDB:
```javascript
// In MongoDB shell
use techzone
db.products.find().pretty()
```

---

## 📂 File Structure

```
/
├── README_DATABASE.md ← You are here
├── WHERE_TO_CONNECT_DATABASE.md
├── DATABASE_GUIDE.md
├── EXAMPLE_Home_with_Database.tsx
├── DATABASE_FLOW_DIAGRAM.ts
│
├── services/
│   └── database.ts ← Configure API_BASE_URL here
│
├── types/
│   └── index.ts ← Type definitions
│
└── pages/
    ├── Home.tsx ← Update to async
    ├── ProductDetail.tsx ← Update to async
    ├── Cart.tsx ← Update to async
    ├── MyOrders.tsx ← Update to async
    ├── Checkout.tsx ← Update to async
    └── ReturnRequest.tsx ← Update to async
```

---

## 🎯 Summary

**Your Architecture:**
- **Frontend:** React + TypeScript (this app)
- **Middleware:** PHP API (you need to implement)
- **The Vault:** MySQL (source of truth)
- **The Library:** MongoDB (performance layer)

**Your Next Steps:**
1. Configure `API_BASE_URL` in `/services/database.ts` (2 min)
2. Implement PHP API endpoints (30 min)
3. Update React pages to use async data (20 min)
4. Test everything! (10 min)

**Total Time:** ~1 hour to fully integrate!

---

**Good luck with your Layer Cake! 🍰**
