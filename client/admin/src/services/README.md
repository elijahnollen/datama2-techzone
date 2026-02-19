# Backend Integration Guide

This folder contains all the service files where you need to connect your **PHP backend** with **MySQL and MongoDB** databases.

## 🏗️ Your Backend Architecture

```
┌─────────────────────────────────┐
│   Presentation Layer            │
│   React | Tailwind | TypeScript │  ← This Admin Dashboard
└─────────────────────────────────┘
              ↓ API Calls
┌─────────────────────────────────┐
│  Middleware Transaction Bridge  │
│            PHP                  │  ← Your Backend
└─────────────────────────────────┘
              ↓ Queries
┌──────────────────┬──────────────┐
│   The Vault      │  The Library │
│     MySQL        │   MongoDB    │  ← Your Databases
└──────────────────┴──────────────┘
```

## 📋 Quick Start

1. **Configure PHP API URL**
   - Open `/services/api.ts`
   - Replace `API_BASE_URL` with your PHP backend URL
   - Choose authentication method (Session or JWT)

2. **Set Up Database**
   - Review `/services/DATABASE_SCHEMA.md` for MySQL table schemas
   - Create your MySQL database and tables
   - Set up MongoDB for logs (optional)

3. **Create PHP Endpoints**
   - Each service file shows expected PHP endpoints
   - Use the PHP examples in `DATABASE_SCHEMA.md`
   - Implement CRUD operations in PHP

4. **Connect Services**
   - Each service file has clear `TODO` comments
   - Uncomment the API calls
   - Test each endpoint

## 📁 Service Files Overview

### Core Services

| File | Purpose | Database | What to Connect |
|------|---------|----------|-----------------|
| `api.ts` | Base API config | - | PHP API URL, auth method |
| `authService.ts` | Admin login/logout | MySQL | PHP authentication endpoints |

### Data Services

| File | Purpose | Database | PHP Endpoints Needed |
|------|---------|----------|---------------------|
| `productService.ts` | Product CRUD | MySQL | `/products.php` |
| `orderService.ts` | Order management | MySQL | `/orders.php` |
| `userService.ts` | User management | MySQL | `/users.php` |
| `inventoryService.ts` | Inventory tracking | MySQL + MongoDB | `/inventory.php`, `/inventory/logs.php` |
| `returnService.ts` | Returns & refunds | MySQL | `/returns.php` |
| `supplierService.ts` | Supplier management | MySQL | `/suppliers.php` |
| `dashboardService.ts` | Analytics | MySQL + MongoDB | `/dashboard/stats.php` |

## 🔧 Implementation Steps

### Step 1: Update API Configuration

```typescript
// In /services/api.ts
export const API_BASE_URL = 'http://localhost/techzone-api';
// or production: 'https://api.techzone.com'
```

### Step 2: Choose Authentication Method

**Option A: PHP Sessions (Recommended)**
```typescript
// In /services/api.ts - Line 29
credentials: 'include', // Already configured for PHP sessions
```

Your PHP should use:
```php
session_start();
$_SESSION['admin_id'] = $user['id'];
```

**Option B: JWT Tokens**
```typescript
// Uncomment JWT section in /services/api.ts
```

### Step 3: Create PHP Endpoints

See `/services/DATABASE_SCHEMA.md` for complete PHP examples.

Example PHP file structure:
```
your-php-backend/
├── api/
│   ├── auth/
│   │   ├── login.php
│   │   └── logout.php
│   ├── products/
│   │   └── index.php
│   ├── orders/
│   │   └── index.php
│   └── ...
├── config/
│   └── database.php
└── middleware/
    └── auth.php
```

### Step 4: Update Components to Use Services

After PHP endpoints are ready, update these components:

**Example: Product Management**
```typescript
// In /components/pages/ProductManagement.tsx
import { getProducts, createProduct, updateProduct, deleteProduct } from '../../services/productService';

// Inside component:
useEffect(() => {
  async function loadProducts() {
    try {
      const data = await getProducts();
      setProducts(data);
    } catch (error) {
      console.error('Failed to load products:', error);
    }
  }
  loadProducts();
}, []);
```

## 📡 Required PHP Endpoints

### Authentication
- `POST /api/auth/login.php` - Admin login
- `POST /api/auth/logout.php` - Admin logout
- `GET /api/auth/verify.php` - Verify session (optional)

### Products (MySQL)
- `GET /api/products/index.php` - List products
- `GET /api/products/index.php?id=1` - Get single product
- `POST /api/products/index.php` - Create product
- `PUT /api/products/index.php?id=1` - Update product
- `DELETE /api/products/index.php?id=1` - Delete product

### Orders (MySQL)
- `GET /api/orders/index.php` - List orders
- `PUT /api/orders/index.php?id=1` - Update order status

### Users (MySQL)
- `GET /api/users/index.php` - List users
- `PUT /api/users/index.php?id=1` - Update user

### Inventory (MySQL + MongoDB)
- `GET /api/inventory/index.php` - Get inventory
- `PUT /api/inventory/index.php?id=1` - Update inventory
- `GET /api/inventory/logs.php` - Get logs (MongoDB recommended)
- `POST /api/inventory/logs.php` - Add log

### Returns (MySQL)
- `GET /api/returns/index.php` - List returns
- `PUT /api/returns/index.php?id=1` - Update status
- `POST /api/returns/approve.php?id=1` - Approve return
- `POST /api/returns/refund.php?id=1` - Process refund

### Suppliers (MySQL)
- `GET /api/suppliers/index.php` - List suppliers
- `POST /api/suppliers/index.php` - Create supplier
- `PUT /api/suppliers/index.php?id=1` - Update supplier

### Dashboard (MySQL + MongoDB)
- `GET /api/dashboard/stats.php` - Get statistics
- `GET /api/dashboard/sales.php` - Get sales data

## 🗄️ Database Setup

### MySQL (The Vault)
Primary relational data storage.

See `/services/DATABASE_SCHEMA.md` for complete table schemas:
- admin_users
- products
- users (customers)
- orders
- order_items
- inventory
- return_requests
- suppliers

### MongoDB (The Library)
For logs and analytics (recommended but optional).

Collections:
- `inventory_logs` - Stock movement history
- `activity_logs` - Admin actions audit trail
- `sales_analytics` - Aggregated sales data

## 🔐 Security Best Practices

1. **Password Hashing**
```php
// When creating admin users
$hashed = password_hash($password, PASSWORD_BCRYPT);
```

2. **Prepared Statements** (Already in examples)
```php
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
```

3. **CORS Configuration**
```php
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
```

4. **Input Validation**
```php
$name = filter_var($input['name'], FILTER_SANITIZE_STRING);
```

## 🧪 Testing

1. Test PHP endpoints with Postman or curl
2. Check MySQL queries in phpMyAdmin
3. Verify MongoDB connections (if using)
4. Test authentication flow
5. Test CRUD operations from frontend

## 📚 Additional Resources

- **Complete Database Schema**: `/services/DATABASE_SCHEMA.md`
- **PHP Examples**: Included in DATABASE_SCHEMA.md
- **Service Interfaces**: Each service file has TypeScript interfaces

## 🚀 Deployment Checklist

- [ ] MySQL database created with all tables
- [ ] MongoDB set up (optional)
- [ ] PHP backend deployed
- [ ] Environment variables configured
- [ ] CORS properly configured
- [ ] All API endpoints tested
- [ ] Frontend connected and tested
- [ ] Authentication working
- [ ] Error handling implemented