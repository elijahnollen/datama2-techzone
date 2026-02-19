/**
 * Inventory Service
 * 
 * CONNECT YOUR PHP INVENTORY API HERE
 * 
 * This service connects to your PHP Middleware Transaction Bridge
 * for inventory management operations.
 * 
 * Database: 
 * - MySQL (The Vault) - for inventory data
 * - MongoDB (The Library) - for inventory logs/history (optional)
 * 
 * Backend: PHP
 */

import { api } from './api';

export interface InventoryItem {
  id: string;
  product: string;
  sku: string;
  stock: number;
  damaged: number;
  reserved: number;
  available: number;
  reorderPoint: number;
  lastUpdated: string;
}

export interface InventoryLog {
  id: string;
  product: string;
  type: 'Stock In' | 'Stock Out' | 'Damaged' | 'Returned';
  quantity: number;
  date: string;
  notes: string;
}

/**
 * GET INVENTORY
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: GET /inventory.php or GET /api/inventory
 * 
 * Database: MySQL (The Vault)
 * PHP Query Example:
 * SELECT i.*, p.name as product, p.sku 
 * FROM inventory i 
 * JOIN products p ON i.product_id = p.id
 */
export async function getInventory(): Promise<InventoryItem[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.get<InventoryItem[]>('/inventory');
}

/**
 * UPDATE INVENTORY ITEM
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: PUT /inventory.php or PUT /api/inventory/1
 * 
 * Database: MySQL (The Vault)
 * PHP Query Example:
 * UPDATE inventory 
 * SET stock = ?, damaged = ?, reserved = ?, available = ? 
 * WHERE id = ?
 */
export async function updateInventory(
  id: string, 
  data: Partial<InventoryItem>
): Promise<InventoryItem> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.put<InventoryItem>(`/inventory/${id}`, data);
}

/**
 * GET INVENTORY LOGS
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: GET /inventory/logs.php or GET /api/inventory/logs
 * 
 * Database: MongoDB (The Library) - for log history
 * Or MySQL (The Vault) - if storing in relational DB
 * 
 * MongoDB Query Example (if using):
 * db.inventory_logs.find().sort({ date: -1 })
 * 
 * MySQL Query Example:
 * SELECT * FROM inventory_logs ORDER BY date DESC
 */
export async function getInventoryLogs(): Promise<InventoryLog[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.get<InventoryLog[]>('/inventory/logs');
}

/**
 * ADD INVENTORY LOG
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: POST /inventory/logs.php or POST /api/inventory/logs
 * 
 * Database: MongoDB (The Library) - recommended for logs
 * 
 * MongoDB Insert Example:
 * db.inventory_logs.insertOne({
 *   product: "Product Name",
 *   type: "Stock In",
 *   quantity: 10,
 *   date: new Date(),
 *   notes: "New shipment"
 * })
 */
export async function addInventoryLog(
  log: Omit<InventoryLog, 'id'>
): Promise<InventoryLog> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.post<InventoryLog>('/inventory/logs', log);
}

/**
 * MARK ITEM AS DAMAGED
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: POST /inventory/damaged.php or POST /api/inventory/1/damaged
 * 
 * Database: MySQL (The Vault)
 * PHP should:
 * 1. UPDATE inventory SET damaged = damaged + ?
 * 2. INSERT into inventory_logs (for audit trail)
 */
export async function markAsDamaged(
  id: string, 
  quantity: number, 
  notes: string
): Promise<InventoryItem> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.post<InventoryItem>(`/inventory/${id}/damaged`, { quantity, notes });
}