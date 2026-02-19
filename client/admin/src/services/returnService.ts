/**
 * Return/Refund Service
 * 
 * CONNECT YOUR RETURN DATABASE HERE
 * 
 * This file handles all return and refund operations.
 * Replace the mock data with actual API calls to your backend.
 */

import { api } from './api';

export interface ReturnRequest {
  id: string;
  orderId: string;
  customer: string;
  product: string;
  reason: string;
  amount: number;
  status: 'Pending' | 'Approved' | 'Rejected' | 'Refunded';
  requestDate: string;
  images?: string[];
}

/**
 * GET ALL RETURNS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /returns
 * Expected response: ReturnRequest[]
 */
export async function getReturns(): Promise<ReturnRequest[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<ReturnRequest[]>('/returns');
}

/**
 * GET SINGLE RETURN
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /returns/:id
 * Expected response: ReturnRequest
 */
export async function getReturn(id: string): Promise<ReturnRequest> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<ReturnRequest>(`/returns/${id}`);
}

/**
 * UPDATE RETURN STATUS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: PATCH /returns/:id/status
 * Expected request body: { status: string }
 * Expected response: ReturnRequest
 */
export async function updateReturnStatus(
  id: string, 
  status: ReturnRequest['status']
): Promise<ReturnRequest> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.put<ReturnRequest>(`/returns/${id}/status`, { status });
}

/**
 * APPROVE RETURN
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: POST /returns/:id/approve
 * Expected response: ReturnRequest
 */
export async function approveReturn(id: string): Promise<ReturnRequest> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.post<ReturnRequest>(`/returns/${id}/approve`, {});
}

/**
 * REJECT RETURN
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: POST /returns/:id/reject
 * Expected request body: { reason: string }
 * Expected response: ReturnRequest
 */
export async function rejectReturn(id: string, reason: string): Promise<ReturnRequest> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.post<ReturnRequest>(`/returns/${id}/reject`, { reason });
}

/**
 * PROCESS REFUND
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: POST /returns/:id/refund
 * Expected response: ReturnRequest
 */
export async function processRefund(id: string): Promise<ReturnRequest> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.post<ReturnRequest>(`/returns/${id}/refund`, {});
}
