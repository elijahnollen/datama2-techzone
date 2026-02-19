/**
 * Password Reset Service
 * 
 * CONNECT YOUR PHP PASSWORD RESET HERE
 * 
 * This service handles forgot password and password reset functionality.
 * 
 * Database: MySQL (The Vault)
 * Backend: PHP
 */

import { api } from './api';

export interface ForgotPasswordRequest {
  email: string;
}

export interface ResetPasswordRequest {
  token: string;
  newPassword: string;
  confirmPassword: string;
}

/**
 * FORGOT PASSWORD - Send Reset Link
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: POST /auth/forgot-password.php
 * 
 * PHP should:
 * 1. Check if email exists in admin_users table
 * 2. Generate a unique reset token
 * 3. Store token with expiry (e.g., 1 hour) in password_resets table
 * 4. Send email with reset link containing token
 * 
 * Database Schema (MySQL):
 * CREATE TABLE password_resets (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   email VARCHAR(255) NOT NULL,
 *   token VARCHAR(255) UNIQUE NOT NULL,
 *   expires_at TIMESTAMP NOT NULL,
 *   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *   INDEX (email),
 *   INDEX (token)
 * );
 * 
 * PHP Example Response:
 * {
 *   "success": true,
 *   "message": "Password reset link sent to your email"
 * }
 */
export async function forgotPassword(email: string): Promise<void> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  await api.post('/auth/forgot-password', { email });
}

/**
 * RESET PASSWORD - Update Password with Token
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: POST /auth/reset-password.php
 * 
 * PHP should:
 * 1. Validate token exists and not expired
 * 2. Hash new password
 * 3. Update admin_users table with new password
 * 4. Delete used token from password_resets table
 * 
 * PHP Query Example:
 * // Verify token
 * SELECT email FROM password_resets 
 * WHERE token = ? AND expires_at > NOW()
 * 
 * // Update password
 * UPDATE admin_users 
 * SET password = ? 
 * WHERE email = ?
 * 
 * // Delete token
 * DELETE FROM password_resets WHERE token = ?
 * 
 * PHP Example Response:
 * {
 *   "success": true,
 *   "message": "Password reset successfully"
 * }
 */
export async function resetPassword(data: ResetPasswordRequest): Promise<void> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  await api.post('/auth/reset-password', data);
}

/**
 * VERIFY RESET TOKEN
 * 
 * TODO: Connect to your PHP backend
 * Expected PHP endpoint: GET /auth/verify-token.php?token=xxx
 * 
 * PHP should:
 * Check if token is valid and not expired
 * 
 * PHP Example Response:
 * {
 *   "success": true,
 *   "valid": true,
 *   "email": "admin@techzone.com"
 * }
 */
export async function verifyResetToken(token: string): Promise<{ valid: boolean; email?: string }> {
  // ==========================================
  // REPLACE THIS WITH YOUR PHP BACKEND CALL
  // ==========================================
  return api.get(`/auth/verify-token?token=${token}`);
}
