/**
 * Product Service
 * 
 * CONNECT YOUR PHP PRODUCT API HERE
 * 
 * This service connects to your PHP Middleware Transaction Bridge
 * for product management operations.
 * 
 * Database: MySQL (The Vault) - for product data
 * Backend: PHP
 */

import { api } from './api';

export interface Product {
  id: string;
  name: string;
  category: string;
  price: number;
  stock: number;
  status: 'Active' | 'Inactive';
  image?: string;
  description?: string;
  sku?: string;
}

/**
 * GET ALL PRODUCTS
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: GET /products.php or GET /api/products
 * 
 * PHP Query Example:
 * SELECT * FROM products WHERE deleted_at IS NULL
 * 
 * Expected PHP Response:
 * {
 *   "success": true,
 *   "data": [
 *     { "id": "1", "name": "Product Name", ... }
 *   ]
 * }
 */
export async function getProducts(): Promise<Product[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.get<Product[]>('/products');
}

/**
 * GET SINGLE PRODUCT
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: GET /products.php?id=1 or GET /api/products/1
 * 
 * PHP Query Example:
 * SELECT * FROM products WHERE id = ? LIMIT 1
 */
export async function getProduct(id: string): Promise<Product> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.get<Product>(`/products/${id}`);
}

/**
 * CREATE PRODUCT
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: POST /products.php or POST /api/products
 * 
 * PHP Query Example:
 * INSERT INTO products (name, category, price, stock, status) VALUES (?, ?, ?, ?, ?)
 */
export async function createProduct(product: Omit<Product, 'id'>): Promise<Product> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.post<Product>('/products', product);
}

/**
 * UPDATE PRODUCT
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: PUT /products.php or PUT /api/products/1
 * 
 * PHP Query Example:
 * UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ?
 */
export async function updateProduct(id: string, product: Partial<Product>): Promise<Product> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.put<Product>(`/products/${id}`, product);
}

/**
 * DELETE PRODUCT
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: DELETE /products.php or DELETE /api/products/1
 * 
 * PHP Query Example (Soft Delete):
 * UPDATE products SET deleted_at = NOW() WHERE id = ?
 * 
 * Or Hard Delete:
 * DELETE FROM products WHERE id = ?
 */
export async function deleteProduct(id: string): Promise<void> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  await api.delete(`/products/${id}`);
}

/**
 * UPDATE PRODUCT STOCK
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: PUT /products.php or PATCH /api/products/1/stock
 * 
 * PHP Query Example:
 * UPDATE products SET stock = ? WHERE id = ?
 */
export async function updateProductStock(id: string, stock: number): Promise<Product> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.put<Product>(`/products/${id}/stock`, { stock });
}