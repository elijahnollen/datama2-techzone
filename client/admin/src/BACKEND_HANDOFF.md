# Backend Integration Package

## 📦 What to Send to Your Backend Developer

Hi! This document contains everything your backend developer needs to implement the PHP API for the TechZone Admin Dashboard.

---

## 🎯 Overview

**Frontend:** React + TypeScript (Already done)  
**Backend Needed:** PHP with MySQL and MongoDB  
**What Backend Needs to Build:** REST API endpoints that return JSON

---

## 📂 Files to Share with Backend Team

### **1. Service Files (API Contracts)**
These files show exactly what data format the frontend expects:

```
/services/
├── api.ts                    ← API configuration & base URL
├── authService.ts            ← Login/logout interfaces
├── passwordService.ts        ← Password reset interfaces
├── productService.ts         ← Product CRUD interfaces
├── orderService.ts           ← Order management interfaces
├── userService.ts            ← User management interfaces
├── inventoryService.ts       ← Inventory interfaces
├── returnService.ts          ← Return/refund interfaces
├── supplierService.ts        ← Supplier interfaces
├── dashboardService.ts       ← Dashboard analytics interfaces
├── DATABASE_SCHEMA.md        ← Complete database schemas & PHP examples
└── README.md                 ← Integration guide
```

**Send the entire `/services/` folder to your backend developer.**

---

## 🔗 API Endpoints Needed

### **Authentication**

#### 1. Admin Login
- **Endpoint:** `POST /api/auth/login.php`
- **Request Body:**
```json
{
  "email": "admin@techzone.com",
  "password": "password123"
}
```
- **Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "1",
      "email": "admin@techzone.com",
      "name": "Admin User",
      "role": "admin"
    }
  }
}
```
- **PHP Action:** Verify credentials, create session `$_SESSION['admin_id']`

---

#### 2. Forgot Password
- **Endpoint:** `POST /api/auth/forgot-password.php`
- **Request Body:**
```json
{
  "email": "admin@techzone.com"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```
- **PHP Action:** 
  1. Check if email exists
  2. Generate unique token
  3. Store in `password_resets` table with expiry
  4. Send email with reset link

**Database Table:**
```sql
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  token VARCHAR(255) UNIQUE NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

#### 3. Admin Logout
- **Endpoint:** `POST /api/auth/logout.php`
- **Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```
- **PHP Action:** Destroy session `session_destroy()`

---

### **Products**

#### Get All Products
- **Endpoint:** `GET /api/products/index.php`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "ROG Strix G16",
      "sku": "LAP-ROG-G16",
      "category": "Laptops",
      "price": 75000,
      "stock": 12,
      "status": "Active",
      "image": "product.jpg"
    }
  ]
}
```

#### Create Product
- **Endpoint:** `POST /api/products/index.php`
- **Request Body:**
```json
{
  "name": "Product Name",
  "sku": "SKU-123",
  "category": "Category",
  "price": 10000,
  "stock": 50,
  "status": "Active"
}
```

---

### **Orders**

#### Get All Orders
- **Endpoint:** `GET /api/orders/index.php`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "order_number": "ORD-001",
      "customer": "Juan Dela Cruz",
      "email": "juan@email.com",
      "total": 75000,
      "status": "Pending",
      "date": "2024-02-10"
    }
  ]
}
```

#### Update Order Status
- **Endpoint:** `PUT /api/orders/index.php?id=1`
- **Request Body:**
```json
{
  "status": "Processing"
}
```

---

### **Users (Customers)**

#### Get All Users
- **Endpoint:** `GET /api/users/index.php`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "Juan Dela Cruz",
      "email": "juan@email.com",
      "phone": "+63 912 345 6789",
      "orders": 12,
      "totalSpent": 245000,
      "status": "Active",
      "joinDate": "2024-01-15"
    }
  ]
}
```

---

### **Inventory**

#### Get Inventory
- **Endpoint:** `GET /api/inventory/index.php`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "product": "ROG Strix G16",
      "sku": "LAP-ROG-G16",
      "stock": 12,
      "damaged": 1,
      "reserved": 2,
      "available": 9,
      "reorderPoint": 10,
      "lastUpdated": "2024-02-10"
    }
  ]
}
```

#### Get Inventory Logs
- **Endpoint:** `GET /api/inventory/logs.php`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "product": "ROG Strix G16",
      "type": "Stock In",
      "quantity": 10,
      "date": "2024-02-10 10:30",
      "notes": "New shipment from supplier"
    }
  ]
}
```

---

### **Returns & Refunds**

#### Get All Returns
- **Endpoint:** `GET /api/returns/index.php`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "return_number": "RET-001",
      "orderId": "ORD-045",
      "customer": "Juan Dela Cruz",
      "product": "ROG Strix G16",
      "reason": "Defective - Screen flickering",
      "amount": 75000,
      "status": "Pending",
      "requestDate": "2024-02-10"
    }
  ]
}
```

#### Update Return Status
- **Endpoint:** `PUT /api/returns/index.php?id=1`
- **Request Body:**
```json
{
  "status": "Approved"
}
```

---

### **Suppliers**

#### Get All Suppliers
- **Endpoint:** `GET /api/suppliers/index.php`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "ASUS Philippines Inc.",
      "contact": "Michael Chen",
      "email": "michael@asus.com.ph",
      "phone": "+63 2 8888 4788",
      "address": "Makati City",
      "products": ["Laptops", "Motherboards"],
      "totalOrders": 45,
      "totalValue": 5400000,
      "status": "Active",
      "rating": 4.8
    }
  ]
}
```

---

### **Dashboard Analytics**

#### Get Dashboard Stats
- **Endpoint:** `GET /api/dashboard/stats.php`
- **Response:**
```json
{
  "success": true,
  "data": {
    "totalRevenue": 1234500,
    "revenueChange": "+12.5%",
    "totalOrders": 854,
    "ordersChange": "+8.2%",
    "totalProducts": 342,
    "productsChange": "+5.1%",
    "activeUsers": 2341,
    "usersChange": "+15.3%"
  }
}
```

#### Get Sales Data
- **Endpoint:** `GET /api/dashboard/sales.php?period=6months`
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "month": "Jan",
      "sales": 45000,
      "orders": 120
    },
    {
      "month": "Feb",
      "sales": 52000,
      "orders": 145
    }
  ]
}
```

---

## 🗄️ Database Schema

**Complete MySQL schemas are in `/services/DATABASE_SCHEMA.md`**

Main tables needed:
- `admin_users` - Admin accounts
- `password_resets` - Password reset tokens
- `products` - Product catalog
- `users` - Customer accounts
- `orders` - Customer orders
- `order_items` - Order line items
- `inventory` - Stock tracking
- `return_requests` - Return/refund requests
- `suppliers` - Supplier information

**MongoDB Collections (Optional):**
- `inventory_logs` - Stock movement history
- `activity_logs` - Admin action audit trail
- `sales_analytics` - Aggregated sales data

---

## 🔐 Authentication

**Method:** PHP Sessions (Default)

**Login Flow:**
1. Frontend sends email/password to `/auth/login.php`
2. PHP verifies credentials
3. PHP creates session: `$_SESSION['admin_id'] = $user['id']`
4. PHP returns user data
5. Frontend stores user data in localStorage

**All subsequent requests include session cookie automatically.**

---

## 📧 Email Configuration

For forgot password feature, PHP needs to send emails.

**PHP Mail Example:**
```php
$to = $email;
$subject = "TechZone Admin - Password Reset";
$resetLink = "https://admin.techzone.com/reset-password?token=" . $token;
$message = "Click this link to reset your password: " . $resetLink;
$headers = "From: noreply@techzone.com";

mail($to, $subject, $message, $headers);
```

Or use PHPMailer/SMTP for better delivery.

---

## 🚀 Testing

**Backend can test endpoints using:**
- Postman
- curl
- Thunder Client (VS Code)

**Example curl test:**
```bash
curl -X POST http://localhost/techzone-api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@techzone.com","password":"password123"}'
```

---

## ⚙️ CORS Configuration

PHP needs to allow frontend to make requests:

```php
header('Access-Control-Allow-Origin: http://localhost:5173'); // Dev
// header('Access-Control-Allow-Origin: https://admin.techzone.com'); // Production
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
```

---

## 📋 Checklist for Backend Developer

- [ ] Set up MySQL database
- [ ] Create all required tables (use DATABASE_SCHEMA.md)
- [ ] Set up MongoDB (optional, for logs)
- [ ] Create `/api/` folder structure
- [ ] Implement authentication endpoints (login, logout, forgot password)
- [ ] Implement product CRUD endpoints
- [ ] Implement order management endpoints
- [ ] Implement user management endpoints
- [ ] Implement inventory endpoints
- [ ] Implement return/refund endpoints
- [ ] Implement supplier endpoints
- [ ] Implement dashboard analytics endpoints
- [ ] Configure CORS headers
- [ ] Set up email sending (for password reset)
- [ ] Test all endpoints with Postman
- [ ] Share API base URL with frontend team

---

## 📞 Communication

**What frontend team needs from backend:**
1. ✅ API base URL (e.g., `http://localhost/techzone-api` or `https://api.techzone.com`)
2. ✅ Confirmation when each endpoint is ready for testing
3. ✅ Any changes to response format
4. ✅ Error codes and messages used

**Frontend will update `/services/api.ts` with the API URL once provided.**

---

## 📚 Additional Resources

All detailed examples, schemas, and PHP code samples are in:
- `/services/DATABASE_SCHEMA.md` - Complete database schemas + PHP examples
- `/services/README.md` - Integration guide
- Each service file (`.ts`) - TypeScript interfaces showing exact data structure

---

**Good luck! 🚀**
