# 🗄️ DATABASE CONNECTION GUIDE

## Overview
Your TechZone e-commerce application currently uses mock data stored in `/data/products.ts`. This guide will help you connect your own database.

---

## 📋 Current Database Structure

### Product Data Model
Your products should have this structure (defined in `/types/index.ts`):

```typescript
interface Product {
  id: string;           // Unique product ID
  name: string;         // Product name
  price: number;        // Price in your currency
  image: string;        // Image URL
  category: string;     // Category: Graphics, Processors, Memory, Cooling, Peripherals
  description: string;  // Product description
  isNew?: boolean;      // Optional: Mark as new arrival
  available?: boolean;  // Optional: Product availability
}
```

---

## 🔧 Where to Connect Your Database

### **Step 1: Configure Database Service**
Open `/services/database.ts` and update the configuration:

```typescript
// Option 1: REST API
const API_BASE_URL = 'https://your-api.com';

// Option 2: Supabase
import { createClient } from '@supabase/supabase-js';
export const supabase = createClient('YOUR_URL', 'YOUR_KEY');

// Option 3: Firebase
import { getFirestore } from 'firebase/firestore';
export const db = getFirestore(app);
```

### **Step 2: Implement Database Functions**
In `/services/database.ts`, replace the mock functions with real database calls:

#### Example with REST API:
```typescript
export async function getAllProducts(): Promise<Product[]> {
  const response = await fetch(`${API_BASE_URL}/products`);
  return await response.json();
}
```

#### Example with Supabase:
```typescript
export async function getAllProducts(): Promise<Product[]> {
  const { data, error } = await supabase
    .from('products')
    .select('*');
  if (error) throw error;
  return data || [];
}
```

---

## 📍 Files That Need Database Connection

### 1. **Home Page** (`/pages/Home.tsx`)
- **Current:** `import { products } from '../data/products'`
- **Update to:** `import { getAllProducts } from '../services/database'`
- **What it does:** Displays all products, handles filtering and search
- **Lines:** 4, 68

### 2. **Product Detail Page** (`/pages/ProductDetail.tsx`)
- **Current:** `import { products } from '../data/products'`
- **Update to:** `import { getProductById } from '../services/database'`
- **What it does:** Shows individual product details, reviews, related items
- **Line:** 4

### 3. **Cart Page** (`/pages/Cart.tsx`)
- **Current:** `import { products } from '../data/products'`
- **Update to:** `import { getAllProducts } from '../services/database'`
- **What it does:** Shows recommended products below cart
- **Line:** 6

### 4. **My Orders Page** (`/pages/MyOrders.tsx`)
- **Current:** `import { products } from '../data/products'`
- **Update to:** `import { getUserOrders } from '../services/database'`
- **What it does:** Displays user's order history
- **Line:** 6

### 5. **Checkout Page** (`/pages/Checkout.tsx`)
- **Current:** `import { products } from '../data/products'`
- **Update to:** `import { createOrder } from '../services/database'`
- **What it does:** Processes checkout and creates orders
- **Line:** 7

### 6. **Return Request Page** (`/pages/ReturnRequest.tsx`)
- **Current:** `import { products } from '../data/products'`
- **Update to:** `import { getProductById } from '../services/database'`
- **What it does:** Handles product returns
- **Line:** 5

---

## 🔄 Migration Steps

### **Step 1: Remove Mock Data**
```bash
# You can delete this file after connecting your database
/data/products.ts
```

### **Step 2: Update Each Page**
For each file listed above, change from synchronous to asynchronous data fetching:

**BEFORE:**
```typescript
import { products } from '../data/products';

export function Home() {
  const latestProducts = products.filter(p => p.isNew);
  // ...
}
```

**AFTER:**
```typescript
import { useState, useEffect } from 'react';
import { getAllProducts } from '../services/database';

export function Home() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchProducts() {
      try {
        const data = await getAllProducts();
        setProducts(data);
      } catch (error) {
        console.error('Error loading products:', error);
      } finally {
        setLoading(false);
      }
    }
    fetchProducts();
  }, []);

  if (loading) return <div>Loading...</div>;
  
  const latestProducts = products.filter(p => p.isNew);
  // ...
}
```

---

## 🎯 Quick Start Examples

### Example 1: Using REST API
```typescript
// /services/database.ts
const API_BASE_URL = 'https://api.techzone.com';

export async function getAllProducts(): Promise<Product[]> {
  const response = await fetch(`${API_BASE_URL}/products`);
  if (!response.ok) throw new Error('Failed to fetch products');
  return await response.json();
}
```

### Example 2: Using Supabase
```typescript
// /services/database.ts
import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  'https://your-project.supabase.co',
  'your-anon-key'
);

export async function getAllProducts(): Promise<Product[]> {
  const { data, error } = await supabase
    .from('products')
    .select('*')
    .order('created_at', { ascending: false });
    
  if (error) throw error;
  return data || [];
}
```

### Example 3: Using Firebase Firestore
```typescript
// /services/database.ts
import { collection, getDocs } from 'firebase/firestore';
import { db } from './firebase-config';

export async function getAllProducts(): Promise<Product[]> {
  const querySnapshot = await getDocs(collection(db, 'products'));
  return querySnapshot.docs.map(doc => ({
    id: doc.id,
    ...doc.data()
  })) as Product[];
}
```

---

## 📊 Database Schema Recommendation

### Products Table
```sql
CREATE TABLE products (
  id VARCHAR(50) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  image VARCHAR(500),
  category VARCHAR(50),
  description TEXT,
  is_new BOOLEAN DEFAULT FALSE,
  available BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Indexes for better performance
CREATE INDEX idx_category ON products(category);
CREATE INDEX idx_is_new ON products(is_new);
CREATE INDEX idx_available ON products(available);
```

### Orders Table (Optional - for full e-commerce)
```sql
CREATE TABLE orders (
  id VARCHAR(50) PRIMARY KEY,
  user_id VARCHAR(50),
  total_amount DECIMAL(10, 2),
  status VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
  id VARCHAR(50) PRIMARY KEY,
  order_id VARCHAR(50),
  product_id VARCHAR(50),
  quantity INT,
  price DECIMAL(10, 2),
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);
```

---

## ✅ Checklist

- [ ] 1. Set up your database (MySQL, PostgreSQL, MongoDB, Supabase, Firebase, etc.)
- [ ] 2. Create products table with required columns
- [ ] 3. Insert your product data into the database
- [ ] 4. Update `/services/database.ts` with your database connection
- [ ] 5. Implement `getAllProducts()` function
- [ ] 6. Implement `getProductById()` function
- [ ] 7. Update `/pages/Home.tsx` to use async data fetching
- [ ] 8. Update `/pages/ProductDetail.tsx` to use async data fetching
- [ ] 9. Update `/pages/Cart.tsx` to use async data fetching
- [ ] 10. Update `/pages/MyOrders.tsx` to use async data fetching
- [ ] 11. Update `/pages/Checkout.tsx` to use async data fetching
- [ ] 12. Update `/pages/ReturnRequest.tsx` to use async data fetching
- [ ] 13. Add loading states to all pages
- [ ] 14. Add error handling for failed requests
- [ ] 15. Test all pages with real data
- [ ] 16. Delete `/data/products.ts` (optional)

---

## 🆘 Need Help?

Common issues:
- **CORS errors:** Configure your backend to allow requests from your frontend domain
- **Authentication:** Add authorization headers to your fetch requests
- **Rate limiting:** Implement caching to reduce database calls
- **Image URLs:** Make sure your image URLs are publicly accessible

---

## 📞 Summary

**Key Points:**
1. ✅ Database service file created at `/services/database.ts`
2. ✅ You need to implement the functions in that file
3. ✅ Update 6 page files to use async data fetching
4. ✅ Your Product data structure is defined in `/types/index.ts`
5. ✅ Mock data file is at `/data/products.ts` (can be deleted after migration)

**Your Next Steps:**
1. Choose your database (REST API, Supabase, Firebase, etc.)
2. Configure connection in `/services/database.ts`
3. Implement the database functions
4. Update each page file to use async/await
5. Test thoroughly!
