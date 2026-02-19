/**
 * User Service
 * 
 * CONNECT YOUR USER DATABASE HERE
 * 
 * This file handles all user management operations.
 * Replace the mock data with actual API calls to your backend.
 */

import { api } from './api';

export interface User {
  id: string;
  name: string;
  email: string;
  phone: string;
  orders: number;
  totalSpent: number;
  status: 'Active' | 'Inactive' | 'Suspended';
  joinDate: string;
}

/**
 * GET ALL USERS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /users
 * Expected response: User[]
 */
export async function getUsers(): Promise<User[]> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<User[]>('/users');
}

/**
 * GET SINGLE USER
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: GET /users/:id
 * Expected response: User
 */
export async function getUser(id: string): Promise<User> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.get<User>(`/users/${id}`);
}

/**
 * UPDATE USER STATUS
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: PATCH /users/:id/status
 * Expected request body: { status: string }
 * Expected response: User
 */
export async function updateUserStatus(
  id: string, 
  status: User['status']
): Promise<User> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  return api.put<User>(`/users/${id}/status`, { status });
}

/**
 * DELETE USER
 * 
 * TODO: Connect to your backend
 * Expected backend endpoint: DELETE /users/:id
 * Expected response: { success: boolean }
 */
export async function deleteUser(id: string): Promise<void> {
  // ==========================================
  // REPLACE THIS WITH YOUR BACKEND CALL
  // ==========================================
  await api.delete(`/users/${id}`);
}
