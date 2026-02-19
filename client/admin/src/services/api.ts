/**
 * API Configuration
 * 
 * BACKEND ARCHITECTURE:
 * - Presentation Layer: React | Tailwind | TypeScript (This Frontend)
 * - Middleware Transaction Bridge: PHP
 * - The Vault: MySQL
 * - The Library: MongoDB
 * 
 * INSTRUCTIONS:
 * 1. Replace API_BASE_URL with your PHP backend API URL
 * 2. Your PHP backend should return JSON responses
 * 3. Update authentication method (Session-based or JWT)
 * 4. Implement proper error handling based on your PHP responses
 */

// ==========================================
// CONFIGURE YOUR PHP API BASE URL HERE
// ==========================================
export const API_BASE_URL = 'http://localhost/techzone-api'; // TODO: Replace with your PHP API URL
// Example production: 'https://api.techzone.com'

/**
 * Generic API request handler
 * Connects to your PHP Middleware Transaction Bridge
 */
async function apiRequest<T>(
  endpoint: string, 
  options: RequestInit = {}
): Promise<T> {
  const url = `${API_BASE_URL}${endpoint}`;
  
  // ==========================================
  // AUTHENTICATION OPTIONS FOR PHP BACKEND
  // ==========================================
  
  // Option 1: Session-based (PHP $_SESSION)
  const config: RequestInit = {
    ...options,
    credentials: 'include', // Include cookies for PHP session
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  };
  
  // Option 2: JWT Token (if your PHP backend uses JWT)
  /*
  const authToken = localStorage.getItem('admin_token');
  const config: RequestInit = {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...(authToken && { 'Authorization': `Bearer ${authToken}` }),
      ...options.headers,
    },
  };
  */

  try {
    const response = await fetch(url, config);
    
    // ==========================================
    // CUSTOMIZE ERROR HANDLING FOR PHP RESPONSES
    // ==========================================
    if (!response.ok) {
      // Handle PHP error responses
      const errorData = await response.json().catch(() => null);
      throw new Error(errorData?.message || `API Error: ${response.status} ${response.statusText}`);
    }
    
    const data = await response.json();
    
    // Handle PHP response format (adjust based on your backend)
    // Example: { success: true, data: {...} } or { error: false, data: {...} }
    if (data.success === false || data.error === true) {
      throw new Error(data.message || 'API request failed');
    }
    
    return data.data || data; // Adjust based on your PHP response structure
  } catch (error) {
    console.error('API Request failed:', error);
    throw error;
  }
}

// Generic CRUD operations
export const api = {
  get: <T>(endpoint: string) => apiRequest<T>(endpoint, { method: 'GET' }),
  
  post: <T>(endpoint: string, data: any) => 
    apiRequest<T>(endpoint, {
      method: 'POST',
      body: JSON.stringify(data),
    }),
  
  put: <T>(endpoint: string, data: any) => 
    apiRequest<T>(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data),
    }),
  
  delete: <T>(endpoint: string) => 
    apiRequest<T>(endpoint, { method: 'DELETE' }),
};