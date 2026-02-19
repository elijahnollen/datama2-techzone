/**
 * Order Service
 * 
 * CONNECT YOUR ORDER DATABASE HERE
 * 
 * This file handles all order-related operations.
 * Replace the mock data with actual API calls to your backend.
 */

import { api } from './api';

export interface Order {
  id: string;
  customer: string;
  email: string;
  products: string;
  total: number;
  status: 'Pending' | 'Processing' | 'Shipped' | 'Delivered' | 'Cancelled';
  date: string;
}

/**
 * GET ALL ORDERS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /orders
 * Expected response: Order[]
 */
export async function getOrders(): Promise<Order[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<Order[]>('/orders');
}

/**
 * GET SINGLE ORDER
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /orders/:id
 * Expected response: Order
 */
export async function getOrder(id: string): Promise<Order> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<Order>(`/orders/${id}`);
}

/**
 * UPDATE ORDER STATUS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: PATCH /orders/:id/status
 * Expected request body: { status: string }
 * Expected response: Order
 */
export async function updateOrderStatus(
  id: string, 
  status: Order['status']
): Promise<Order> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.put<Order>(`/orders/${id}/status`, { status });
}

/**
 * GET ORDERS BY STATUS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /orders?status=:status
 * Expected response: Order[]
 */
export async function getOrdersByStatus(status: string): Promise<Order[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<Order[]>(`/orders?status=${status}`);
}

/**
 * CANCEL ORDER
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: POST /orders/:id/cancel
 * Expected response: Order
 */
export async function cancelOrder(id: string): Promise<Order> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.post<Order>(`/orders/${id}/cancel`, {});
}
