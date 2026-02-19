/**
 * Supplier Service
 * 
 * CONNECT YOUR SUPPLIER DATABASE HERE
 * 
 * This file handles all supplier-related operations.
 * Replace the mock data with actual API calls to your backend.
 */

import { api } from './api';

export interface Supplier {
  id: string;
  name: string;
  contact: string;
  email: string;
  phone: string;
  address: string;
  products: string[];
  totalOrders: number;
  totalValue: number;
  status: 'Active' | 'Inactive';
  rating: number;
}

/**
 * GET ALL SUPPLIERS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /suppliers
 * Expected response: Supplier[]
 */
export async function getSuppliers(): Promise<Supplier[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<Supplier[]>('/suppliers');
}

/**
 * GET SINGLE SUPPLIER
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /suppliers/:id
 * Expected response: Supplier
 */
export async function getSupplier(id: string): Promise<Supplier> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<Supplier>(`/suppliers/${id}`);
}

/**
 * CREATE SUPPLIER
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: POST /suppliers
 * Expected request body: Omit<Supplier, 'id'>
 * Expected response: Supplier
 */
export async function createSupplier(supplier: Omit<Supplier, 'id'>): Promise<Supplier> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.post<Supplier>('/suppliers', supplier);
}

/**
 * UPDATE SUPPLIER
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: PUT /suppliers/:id
 * Expected request body: Partial<Supplier>
 * Expected response: Supplier
 */
export async function updateSupplier(id: string, supplier: Partial<Supplier>): Promise<Supplier> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.put<Supplier>(`/suppliers/${id}`, supplier);
}

/**
 * DELETE SUPPLIER
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: DELETE /suppliers/:id
 * Expected response: { success: boolean }
 */
export async function deleteSupplier(id: string): Promise<void> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  await api.delete(`/suppliers/${id}`);
}
