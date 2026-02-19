/**
 * Dashboard Service
 * 
 * CONNECT YOUR DASHBOARD ANALYTICS HERE
 * 
 * This file handles dashboard statistics and analytics.
 * Replace the mock data with actual API calls to your backend.
 */

import { api } from './api';

export interface DashboardStats {
  totalRevenue: number;
  revenueChange: string;
  totalOrders: number;
  ordersChange: string;
  totalProducts: number;
  productsChange: string;
  activeUsers: number;
  usersChange: string;
}

export interface SalesData {
  month: string;
  sales: number;
  orders: number;
}

export interface RecentOrder {
  id: string;
  customer: string;
  product: string;
  amount: string;
  status: string;
}

export interface LowStockItem {
  product: string;
  stock: number;
  threshold: number;
}

/**
 * GET DASHBOARD STATISTICS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /dashboard/stats
 * Expected response: DashboardStats
 */
export async function getDashboardStats(): Promise<DashboardStats> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<DashboardStats>('/dashboard/stats');
}

/**
 * GET SALES DATA
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /dashboard/sales
 * Expected query params: ?period=6months
 * Expected response: SalesData[]
 */
export async function getSalesData(period: string = '6months'): Promise<SalesData[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<SalesData[]>(`/dashboard/sales?period=${period}`);
}

/**
 * GET RECENT ORDERS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /dashboard/recent-orders
 * Expected query params: ?limit=4
 * Expected response: RecentOrder[]
 */
export async function getRecentOrders(limit: number = 4): Promise<RecentOrder[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<RecentOrder[]>(`/dashboard/recent-orders?limit=${limit}`);
}

/**
 * GET LOW STOCK ITEMS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /dashboard/low-stock
 * Expected response: LowStockItem[]
 */
export async function getLowStockItems(): Promise<LowStockItem[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<LowStockItem[]>('/dashboard/low-stock');
}
