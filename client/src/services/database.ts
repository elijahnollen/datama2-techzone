/**
 * ═══════════════════════════════════════════════════════════════════════
 * TECHZONE DATABASE SERVICE - LAYER CAKE ARCHITECTURE
 * ═══════════════════════════════════════════════════════════════════════
 * 
 * DEMO MODE ACTIVE - Console warnings disabled
 * Last updated: 2026-02-13
 * 
 * ═══════════════════════════════════════════════════════════════════════
 */

import { Product } from '../types';

// ═══════════════════════════════════════════════════════════════════════
// CONFIGURATION - UPDATE YOUR PHP API ENDPOINT
// ═══════════════════════════════════════════════════════════════════════

/**
 * Your PHP Middleware Transaction Bridge API endpoint
 * 
 * ⚠️ IMPORTANT: Update this to your actual PHP API URL
 * 
 * Examples:
 * - Development: 'http://localhost:8000/api'
 * - Production: 'https://api.techzone.com'
 */
export const API_BASE_URL = 'http://localhost/api';

/**
 * Enable/Disable fallback mock data
 * 
 * Set to false once your backend is ready to force real API calls
 */
const USE_FALLBACK_DATA = true;

/**
 * Show console messages about API status
 * 
 * Set to true to see warnings about fallback mode
 * Set to false for clean console (recommended for demo mode)
 */
const SHOW_API_WARNINGS = false;

/**
 * Optional: Add authentication headers if your API requires it
 */
const getHeaders = () => ({
  'Content-Type': 'application/json',
  // Add authentication token if needed:
  // 'Authorization': `Bearer ${localStorage.getItem('token')}`,
});

// ═══════════════════════════════════════════════════════════════════════
// TEMPORARY MOCK DATA (Remove this once your backend is ready)
// ═══════════════════════════════════════════════════════════════════════

const MOCK_PRODUCTS: Product[] = [
  {
    id: 'gpu-1',
    name: 'NVIDIA GeForce RTX 4090 24GB',
    price: 8999,
    image: 'https://images.unsplash.com/photo-1587134160474-cd3c9a60a34a?w=500',
    category: 'Graphics',
    description: 'The ultimate graphics card for 4K gaming and content creation. Features 24GB GDDR6X memory.',
    isNew: true,
    available: true,
    brand: 'NVIDIA',
  },
  {
    id: 'gpu-2',
    name: 'AMD Radeon RX 7900 XTX',
    price: 5999,
    image: 'https://images.unsplash.com/photo-1591488320449-011701bb6704?w=500',
    category: 'Graphics',
    description: 'High-performance AMD graphics card with 24GB GDDR6 memory for exceptional gaming.',
    isNew: true,
    available: true,
    brand: 'AMD',
  },
  {
    id: 'cpu-1',
    name: 'Intel Core i9-13900K',
    price: 4599,
    image: 'https://images.unsplash.com/photo-1749006590475-4592a5dbf99f?w=500',
    category: 'Processors',
    description: '24-core processor with up to 5.8 GHz for ultimate performance.',
    isNew: false,
    available: true,
    brand: 'Intel',
  },
  {
    id: 'cpu-2',
    name: 'AMD Ryzen 9 7950X',
    price: 4299,
    image: 'https://images.unsplash.com/photo-1655457980896-75f7c8eb3c97?w=500',
    category: 'Processors',
    description: '16-core processor with Zen 4 architecture for gaming and productivity.',
    isNew: true,
    available: true,
    brand: 'AMD',
  },
  {
    id: 'ram-1',
    name: 'Corsair Vengeance DDR5 32GB',
    price: 1899,
    image: 'https://images.unsplash.com/photo-1541843592-0271bc44bae5?w=500',
    category: 'Memory',
    description: 'High-performance DDR5 memory kit with RGB lighting.',
    isNew: false,
    available: true,
    brand: 'Corsair',
  },
  {
    id: 'ram-2',
    name: 'G.SKILL Trident Z5 RGB 64GB',
    price: 3499,
    image: 'https://images.unsplash.com/photo-1625519851550-1296e5f06f6b?w=500',
    category: 'Memory',
    description: 'Premium DDR5 memory with stunning RGB and extreme speeds.',
    isNew: true,
    available: true,
    brand: 'G.SKILL',
  },
  {
    id: 'cooling-1',
    name: 'NZXT Kraken Z73 RGB',
    price: 2450,
    image: 'https://images.unsplash.com/photo-1704871132546-d1d3b845ae65?w=500',
    category: 'Cooling',
    description: '360mm AIO liquid cooler with customizable LCD display.',
    isNew: false,
    available: true,
    brand: 'NZXT',
  },
  {
    id: 'cooling-2',
    name: 'Corsair iCUE H150i Elite',
    price: 2199,
    image: 'https://images.unsplash.com/photo-1595004071727-29a81d83d0d9?w=500',
    category: 'Cooling',
    description: 'Advanced liquid cooling with magnetic levitation fans.',
    isNew: true,
    available: true,
    brand: 'Corsair',
  },
  {
    id: 'keyboard-1',
    name: 'Razer BlackWidow V4 Pro',
    price: 1599,
    image: 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=500',
    category: 'Peripherals',
    description: 'Mechanical gaming keyboard with command dial and underglow.',
    isNew: true,
    available: true,
    brand: 'Razer',
  },
  {
    id: 'mouse-1',
    name: 'Logitech G Pro X Superlight',
    price: 899,
    image: 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500',
    category: 'Peripherals',
    description: 'Ultra-lightweight wireless gaming mouse for pro players.',
    isNew: false,
    available: true,
    brand: 'Logitech',
  },
  {
    id: 'headset-1',
    name: 'SteelSeries Arctis Nova Pro',
    price: 2299,
    image: 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500',
    category: 'Peripherals',
    description: 'Premium gaming headset with active noise cancellation.',
    isNew: true,
    available: true,
    brand: 'SteelSeries',
  },
  {
    id: 'monitor-1',
    name: 'ASUS ROG Swift PG279QM',
    price: 4599,
    image: 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500',
    category: 'Peripherals',
    description: '27" 1440p gaming monitor with 240Hz refresh rate.',
    isNew: false,
    available: true,
    brand: 'ASUS',
  },
];

// ═══════════════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════

/**
 * Check if API is configured and available
 */
async function isApiAvailable(): Promise<boolean> {
  // If API URL not configured, use fallback
  if (API_BASE_URL === 'YOUR_PHP_API_ENDPOINT_HERE') {
    if (SHOW_API_WARNINGS) {
      console.warn('⚠️  API_BASE_URL not configured. Using fallback mock data.');
      console.warn('📝 Update API_BASE_URL in /services/database.ts (line 21)');
    }
    return false;
  }

  // If fallback is enabled, use it
  if (USE_FALLBACK_DATA) {
    if (SHOW_API_WARNINGS) {
      console.info('ℹ️  Fallback mode enabled. Using mock data.');
      console.info('💡 Set USE_FALLBACK_DATA = false to use real API');
    }
    return false;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/health`, {
      method: 'GET',
      headers: getHeaders(),
      signal: AbortSignal.timeout(3000), // 3 second timeout
    });
    return response.ok;
  } catch (error) {
    if (SHOW_API_WARNINGS) {
      console.warn('⚠️  API not available. Using fallback mock data.');
      console.warn('🔧 Make sure your PHP backend is running');
    }
    return false;
  }
}

// ═══════════════════════════════════════════════════════════════════════
// PRODUCTS API (MongoDB - "The Library" - products collection)
// ═══════════════════════════════════════════════════════════════════════

/**
 * Get all products from MongoDB "products" collection (Catalog)
 */
export async function getAllProducts(): Promise<Product[]> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    // Use mock data silently
    return new Promise((resolve) => {
      setTimeout(() => resolve(MOCK_PRODUCTS), 500); // Simulate network delay
    });
  }

  try {
    const response = await fetch(`${API_BASE_URL}/products`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    if (SHOW_API_WARNINGS) {
      console.error('❌ Error fetching products from API:', error);
      console.log('📦 Falling back to mock data');
    }
    return MOCK_PRODUCTS;
  }
}

/**
 * Get a single product by ID from MongoDB
 */
export async function getProductById(id: string): Promise<Product | null> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    // Use mock data
    const product = MOCK_PRODUCTS.find(p => p.id === id);
    return new Promise((resolve) => {
      setTimeout(() => resolve(product || null), 300); // Simulate network delay
    });
  }

  try {
    const response = await fetch(`${API_BASE_URL}/products/${id}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (response.status === 404) {
      return null;
    }

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`❌ Error fetching product ${id}:`, error);
    // Fallback to mock data
    return MOCK_PRODUCTS.find(p => p.id === id) || null;
  }
}

/**
 * Get products by category from MongoDB
 */
export async function getProductsByCategory(category: string): Promise<Product[]> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    // Use mock data
    const filtered = MOCK_PRODUCTS.filter(p => p.category === category);
    return new Promise((resolve) => {
      setTimeout(() => resolve(filtered), 300);
    });
  }

  try {
    const response = await fetch(`${API_BASE_URL}/products?category=${encodeURIComponent(category)}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`❌ Error fetching products by category ${category}:`, error);
    return MOCK_PRODUCTS.filter(p => p.category === category);
  }
}

/**
 * Search products by name or description
 */
export async function searchProducts(query: string): Promise<Product[]> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    // Use mock data
    const lowerQuery = query.toLowerCase();
    const results = MOCK_PRODUCTS.filter(p =>
      p.name.toLowerCase().includes(lowerQuery) ||
      p.description.toLowerCase().includes(lowerQuery)
    );
    return new Promise((resolve) => {
      setTimeout(() => resolve(results), 300);
    });
  }

  try {
    const response = await fetch(`${API_BASE_URL}/products/search?q=${encodeURIComponent(query)}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`❌ Error searching products with query "${query}":`, error);
    const lowerQuery = query.toLowerCase();
    return MOCK_PRODUCTS.filter(p =>
      p.name.toLowerCase().includes(lowerQuery) ||
      p.description.toLowerCase().includes(lowerQuery)
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════
// ORDERS API (MongoDB - "The Library" - orders collection)
// ═══════════════════════════════════════════════════════════════════════

export interface Order {
  id: string;
  userId: string;
  items: Array<{
    productId: string;
    name: string;
    price: number;
    quantity: number;
    image: string;
  }>;
  totalAmount: number;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  deliveryMethod: 'delivery' | 'pickup';
  shippingAddress?: {
    fullName: string;
    phone: string;
    address: string;
    city: string;
    postalCode: string;
  };
  paymentMethod: string;
  createdAt: string;
  updatedAt: string;
}

/**
 * Create a new order in MongoDB "orders" collection (History)
 */
export async function createOrder(orderData: Omit<Order, 'id' | 'createdAt' | 'updatedAt'>): Promise<Order> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    // Mock order creation
    const mockOrder: Order = {
      ...orderData,
      id: `ORD-${Date.now()}`,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    };
    console.log('📦 Mock order created:', mockOrder.id);
    return new Promise((resolve) => {
      setTimeout(() => resolve(mockOrder), 500);
    });
  }

  try {
    const response = await fetch(`${API_BASE_URL}/orders`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(orderData),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('❌ Error creating order:', error);
    throw error;
  }
}

/**
 * Get user's order history from MongoDB "orders" collection
 */
export async function getUserOrders(userId: string): Promise<Order[]> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    // Return empty orders for mock mode
    console.log('📦 Mock mode: No orders available');
    return [];
  }

  try {
    const response = await fetch(`${API_BASE_URL}/orders?userId=${userId}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`❌ Error fetching orders for user ${userId}:`, error);
    return [];
  }
}

/**
 * Get a single order by ID
 */
export async function getOrderById(orderId: string): Promise<Order | null> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    return null;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/orders/${orderId}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (response.status === 404) {
      return null;
    }

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`❌ Error fetching order ${orderId}:`, error);
    return null;
  }
}

// ═══════════════════════════════════════════════════════════════════════
// CART API (MongoDB - "The Library" - carts collection)
// ═══════════════════════════════════════════════════════════════════════

/**
 * Save cart to MongoDB "carts" collection (Persisted Sessions)
 */
export async function saveCart(userId: string, cartItems: any[]): Promise<void> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    console.log('📦 Mock mode: Cart saved locally only');
    return;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/cart`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ userId, items: cartItems }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
  } catch (error) {
    console.error('❌ Error saving cart:', error);
    throw error;
  }
}

/**
 * Get saved cart from MongoDB
 */
export async function getCart(userId: string): Promise<any[]> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    return [];
  }

  try {
    const response = await fetch(`${API_BASE_URL}/cart?userId=${userId}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data.items || [];
  } catch (error) {
    console.error(`❌ Error fetching cart for user ${userId}:`, error);
    return [];
  }
}

// ═══════════════════════════════════════════════════════════════════════
// REVIEWS API (MongoDB - "The Library" - reviews collection)
// ═══════════════════════════════════════════════════════════════════════

export interface Review {
  id: string;
  productId: string;
  userId: string;
  userName: string;
  rating: number;
  comment: string;
  createdAt: string;
}

/**
 * Get product reviews from MongoDB "reviews" collection (Social Proof)
 */
export async function getProductReviews(productId: string): Promise<Review[]> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    return [];
  }

  try {
    const response = await fetch(`${API_BASE_URL}/reviews?productId=${productId}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`❌ Error fetching reviews for product ${productId}:`, error);
    return [];
  }
}

/**
 * Submit a product review
 */
export async function submitReview(reviewData: Omit<Review, 'id' | 'createdAt'>): Promise<Review> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    const mockReview: Review = {
      ...reviewData,
      id: `REV-${Date.now()}`,
      createdAt: new Date().toISOString(),
    };
    console.log('📦 Mock review created:', mockReview.id);
    return mockReview;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/reviews`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(reviewData),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('❌ Error submitting review:', error);
    throw error;
  }
}

// ═══════════════════════════════════════════════════════════════════════
// RETURN REQUESTS API (MongoDB - "The Library" - return_requests collection)
// ═══════════════════════════════════════════════════════════════════════

export interface ReturnRequest {
  id: string;
  orderId: string;
  userId: string;
  productId: string;
  reason: string;
  description: string;
  images?: string[];
  status: 'pending' | 'approved' | 'rejected' | 'completed';
  createdAt: string;
  updatedAt: string;
}

/**
 * Submit a return request to MongoDB "return_requests" collection (RMA)
 */
export async function submitReturnRequest(returnData: Omit<ReturnRequest, 'id' | 'createdAt' | 'updatedAt'>): Promise<ReturnRequest> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    const mockReturn: ReturnRequest = {
      ...returnData,
      id: `RET-${Date.now()}`,
      status: 'pending',
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    };
    console.log('📦 Mock return request created:', mockReturn.id);
    return mockReturn;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/returns`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(returnData),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('❌ Error submitting return request:', error);
    throw error;
  }
}

/**
 * Get user's return requests
 */
export async function getUserReturnRequests(userId: string): Promise<ReturnRequest[]> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    return [];
  }

  try {
    const response = await fetch(`${API_BASE_URL}/returns?userId=${userId}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`❌ Error fetching return requests for user ${userId}:`, error);
    return [];
  }
}

// ═══════════════════════════════════════════════════════════════════════
// INVENTORY API (MySQL - "The Vault" - Inventory Transaction table)
// ═══════════════════════════════════════════════════════════════════════

/**
 * Check product availability from MySQL "Product" and "Inventory_Transaction" tables
 */
export async function checkProductStock(productId: string): Promise<{ available: boolean; quantity: number }> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    // Mock stock check
    return { available: true, quantity: 10 };
  }

  try {
    const response = await fetch(`${API_BASE_URL}/inventory/${productId}`, {
      method: 'GET',
      headers: getHeaders(),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(`❌ Error checking stock for product ${productId}:`, error);
    return { available: false, quantity: 0 };
  }
}

// ═══════════════════════════════════════════════════════════════════════
// AUDIT LOGS API (MongoDB - "The Library" - audit_logs collection)
// ═══════════════════════════════════════════════════════════════════════

/**
 * Log user actions to MongoDB "audit_logs" collection (Security)
 */
export async function logAuditEvent(event: {
  userId: string;
  action: string;
  details: any;
}): Promise<void> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    return;
  }

  try {
    await fetch(`${API_BASE_URL}/audit-logs`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(event),
    });
  } catch (error) {
    // Don't throw - audit logging shouldn't break the app
    console.error('❌ Error logging audit event:', error);
  }
}

// ═══════════════════════════════════════════════════════════════════════
// CONTACT/INQUIRY API (MongoDB - "The Library" - inquiries collection)
// ═══════════════════════════════════════════════════════════════════════

export interface Inquiry {
  id: string;
  name: string;
  email: string;
  phone?: string;
  subject: string;
  message: string;
  status: 'new' | 'in-progress' | 'resolved';
  createdAt: string;
}

/**
 * Submit a contact inquiry to MongoDB "inquiries" collection (Contact Form)
 */
export async function submitInquiry(inquiryData: Omit<Inquiry, 'id' | 'status' | 'createdAt'>): Promise<Inquiry> {
  const apiAvailable = await isApiAvailable();
  
  if (!apiAvailable) {
    const mockInquiry: Inquiry = {
      ...inquiryData,
      id: `INQ-${Date.now()}`,
      status: 'new',
      createdAt: new Date().toISOString(),
    };
    console.log('📦 Mock inquiry created:', mockInquiry.id);
    return mockInquiry;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/inquiries`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(inquiryData),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('❌ Error submitting inquiry:', error);
    throw error;
  }
}

// ═══════════════════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════

/**
 * Test API connection
 */
export async function testConnection(): Promise<boolean> {
  if (API_BASE_URL === 'YOUR_PHP_API_ENDPOINT_HERE') {
    return false;
  }

  try {
    const response = await fetch(`${API_BASE_URL}/health.php`, {
      method: 'GET',
      headers: getHeaders(),
      signal: AbortSignal.timeout(3000),
    });
    return response.ok;
  } catch (error) {
    console.error('❌ API connection test failed:', error);
    return false;
  }
}