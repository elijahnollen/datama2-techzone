# ⚡ QUICK REFERENCE CARD - TECHZONE DATABASE INTEGRATION

## 🎯 One-Minute Summary

**What You Have:** TechZone e-commerce frontend (React)
**What You Need:** Connect to your Layer Cake architecture (PHP + MySQL + MongoDB)
**Time Required:** ~1.5 hours
**Difficulty:** ⭐⭐⭐ Intermediate

---

## 📍 THE ONE FILE YOU MUST EDIT

```typescript
// File: /services/database.ts (Line 21)

const API_BASE_URL = 'YOUR_PHP_API_ENDPOINT_HERE';

// Change to:
const API_BASE_URL = 'http://localhost/api';  // Dev
// or
const API_BASE_URL = 'https://api.techzone.com';  // Prod
```

That's it for configuration! Everything else is already set up.

---

## 🏗️ Your Architecture (The Layer Cake)

```
React (Frontend) ← You are here
    ↕️ HTTP API calls
PHP (Middleware) ← You need to deploy this
    ↕️ Database queries  
MySQL + MongoDB  ← You need to set these up
```

---

## 📝 3-Step Setup

### **Step 1: Databases** (30 min)

```bash
# MySQL
mysql -u root -p
CREATE DATABASE techzone;
# Run SQL from README_DATABASE.md

# MongoDB
mongosh
use techzone
# Collections auto-created on first insert
```

### **Step 2: PHP Backend** (20 min)

```bash
# Copy example file
cp PHP_API_EXAMPLE.php /var/www/html/api/index.php

# Install MongoDB driver
composer require mongodb/mongodb

# Edit credentials in file
nano /var/www/html/api/index.php

# Test
curl http://localhost/api/health
```

### **Step 3: Frontend** (20 min)

```bash
# Edit /services/database.ts (line 21)
# Update API_BASE_URL

# Then update these 6 files to use async:
# - /pages/Home.tsx
# - /pages/ProductDetail.tsx
# - /pages/Cart.tsx
# - /pages/MyOrders.tsx
# - /pages/Checkout.tsx
# - /pages/ReturnRequest.tsx

# See EXAMPLE_Home_with_Database.tsx for pattern
```

---

## 🔌 API Endpoints (PHP Backend)

| Endpoint | Method | Database | Purpose |
|----------|--------|----------|---------|
| `/api/products` | GET | MongoDB | Get all products |
| `/api/products/{id}` | GET | MongoDB | Get one product |
| `/api/products/search?q=` | GET | MongoDB | Search products |
| `/api/orders` | POST | Both | Create order |
| `/api/orders?userId=` | GET | MongoDB | Get user orders |
| `/api/cart` | POST | MongoDB | Save cart |
| `/api/reviews` | POST | MongoDB | Submit review |
| `/api/returns` | POST | Both | Submit return |
| `/api/inventory/{id}` | GET | MySQL | Check stock |

All implemented in `PHP_API_EXAMPLE.php`

---

## 📊 Database Collections/Tables

### MongoDB (The Library) - Read Performance
```javascript
products          // Product catalog (frontend reads from here)
orders            // Order history
carts             // Shopping cart sessions
reviews           // Product reviews
return_requests   // Return requests
inquiries         // Contact form
audit_logs        // Security logs
```

### MySQL (The Vault) - Transaction Truth
```sql
Product              -- Master product data
Sale                 -- Transaction records
Inventory_Transaction -- Stock movements
Return               -- Return records
Customers, Suppliers, Employee, Damaged_goods
```

---

## 🎨 Frontend Pattern (Update Pages)

**Before (Broken):**
```typescript
import { products } from '../data/products';  // ❌ Deleted
```

**After (Working):**
```typescript
import { useState, useEffect } from 'react';
import { getAllProducts } from '../services/database';  // ✅

export function Home() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetch() {
      const data = await getAllProducts();
      setProducts(data);
      setLoading(false);
    }
    fetch();
  }, []);

  if (loading) return <div>Loading...</div>;
  // Use products...
}
```

---

## 🔍 Quick Checks

**Is MySQL working?**
```bash
mysql -u root -p -e "SELECT COUNT(*) FROM techzone.Product;"
```

**Is MongoDB working?**
```bash
mongosh --eval "use techzone; db.products.countDocuments()"
```

**Is PHP API working?**
```bash
curl http://localhost/api/health
# Should return: {"status":"ok","mysql":"connected","mongodb":"connected"}
```

**Is Frontend connected?**
```bash
# Open browser console on your React app
# Should see products loading, no 404 errors
```

---

## 🆘 Common Errors

**Error:** `Access-Control-Allow-Origin`
**Fix:** Add CORS headers in PHP (already in example)

**Error:** `Connection refused`
**Fix:** Check MySQL/MongoDB are running
```bash
sudo systemctl start mysql
sudo systemctl start mongod
```

**Error:** `Empty products array`
**Fix:** Insert test data in MongoDB
```javascript
db.products.insertOne({
  id: "1",
  name: "Test Product",
  price: 100,
  category: "Graphics",
  image: "https://via.placeholder.com/300",
  description: "Test",
  isNew: true,
  available: true
})
```

**Error:** `Endpoint not found`
**Fix:** Check PHP file is at `/var/www/html/api/index.php`

---

## 📚 Documentation Files

| File | Purpose | Time |
|------|---------|------|
| `WHERE_TO_CONNECT_DATABASE.md` | Start here | 5 min |
| `PHP_API_EXAMPLE.php` | Copy this | 20 min |
| `EXAMPLE_Home_with_Database.tsx` | Copy pattern | 10 min |
| `README_DATABASE.md` | Full guide | 15 min |
| `DATABASE_FLOW_DIAGRAM.ts` | Visual guide | 5 min |

---

## ✅ Checklist

```
Backend:
[ ] MySQL installed and running
[ ] MongoDB installed and running
[ ] Created techzone database in MySQL
[ ] Created tables in MySQL
[ ] Inserted test products in MongoDB
[ ] Deployed PHP API file
[ ] Updated database credentials in PHP
[ ] Tested /api/health endpoint

Frontend:
[ ] Updated API_BASE_URL in /services/database.ts
[ ] Updated Home.tsx
[ ] Updated ProductDetail.tsx
[ ] Updated Cart.tsx
[ ] Updated MyOrders.tsx
[ ] Updated Checkout.tsx
[ ] Updated ReturnRequest.tsx

Testing:
[ ] Products load on home page
[ ] Product detail page works
[ ] Add to cart works
[ ] Checkout creates order
[ ] Orders show in My Orders
[ ] Stock check works
```

---

## 🎯 Summary

**You Have:**
- ✅ Complete React frontend (ready)
- ✅ Complete PHP backend example (ready to deploy)
- ✅ Database schemas (ready to create)
- ✅ TypeScript types (ready)
- ✅ Documentation (complete)

**You Need To Do:**
1. Set up databases (30 min)
2. Deploy PHP backend (20 min)
3. Update API_BASE_URL (2 min)
4. Update 6 React files (20 min)

**Total: ~75 minutes** ⏱️

---

## 🚀 Start Here

1. Open `WHERE_TO_CONNECT_DATABASE.md`
2. Follow Step 1 (Set up databases)
3. Follow Step 2 (Deploy PHP backend)
4. Follow Step 3 (Connect frontend)
5. Test and celebrate! 🎉

---

**Need help?** All documentation is in the root folder.
**Ready to code?** Start with `PHP_API_EXAMPLE.php`

**Good luck! 🍰**
