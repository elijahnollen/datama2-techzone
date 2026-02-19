/**
 * Authentication Service
 * 
 * CONNECT YOUR PHP AUTHENTICATION HERE
 * 
 * This service connects to your PHP Middleware Transaction Bridge
 * for admin authentication. The PHP backend manages sessions with MySQL.
 * 
 * Database: MySQL (The Vault)
 * Backend: PHP
 */

import { api } from './api';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface AuthResponse {
  token?: string; // If using JWT
  user: {
    id: string;
    email: string;
    name: string;
    role: string;
  };
}

/**
 * LOGIN FUNCTION
 * 
 * TODO: Connect to your PHP login endpoint
 * 
 * Expected PHP endpoint: POST /auth/login.php or POST /api/auth/login
 * Expected request body: { email: string, password: string }
 * 
 * PHP Response Example:
 * {
 *   "success": true,
 *   "data": {
 *     "token": "jwt-token-here" (if using JWT),
 *     "user": {
 *       "id": "1",
 *       "email": "admin@techzone.com",
 *       "name": "Admin User",
 *       "role": "admin"
 *     }
 *   }
 * }
 * 
 * Or if using PHP Sessions, your PHP should set session variables:
 * $_SESSION['admin_user'] = [...user data]
 */
export async function login(credentials: LoginCredentials): Promise<AuthResponse> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  
  const response = await api.post<AuthResponse>('/auth/login', credentials);
  
  // If using JWT, store token
  if (response.token) {
    localStorage.setItem('admin_token', response.token);
  }
  
  // Store user data
  localStorage.setItem('admin_user', JSON.stringify(response.user));
  
  return response;
}

/**
 * LOGOUT FUNCTION
 * 
 * TODO: Connect to your PHP logout endpoint
 * 
 * Expected PHP endpoint: POST /auth/logout.php or POST /api/auth/logout
 * 
 * PHP should destroy session:
 * session_destroy();
 */
export async function logout(): Promise<void> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  
  try {
    await api.post('/auth/logout', {});
  } catch (error) {
    console.error('Logout error:', error);
  }
  
  // Clear local storage
  localStorage.removeItem('admin_token');
  localStorage.removeItem('admin_user');
}

/**
 * CHECK AUTHENTICATION
 * 
 * TODO: Verify session with PHP backend
 * 
 * Expected PHP endpoint: GET /auth/verify.php or GET /api/auth/verify
 * 
 * PHP should check:
 * isset($_SESSION['admin_user']) or validate JWT
 */
export function isAuthenticated(): boolean {
  const token = localStorage.getItem('admin_token');
  const user = localStorage.getItem('admin_user');
  
  // TODO: Optionally verify with PHP backend
  // const response = await api.get('/auth/verify');
  
  return !!(token || user);
}

/**
 * GET CURRENT USER
 */
export function getCurrentUser() {
  const userStr = localStorage.getItem('admin_user');
  return userStr ? JSON.parse(userStr) : null;
}