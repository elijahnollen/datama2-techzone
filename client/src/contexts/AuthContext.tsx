import React, { createContext, useContext, useState, useEffect } from 'react';

// ═══════════════════════════════════════════════════════════════════════
// AUTHENTICATION CONTEXT - Easy Backend Connection Points
// ═══════════════════════════════════════════════════════════════════════

interface User {
  id: string;
  email: string;
  name: string;
  wallet: number;
}

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  signup: (email: string, password: string, name: string) => Promise<void>;
  logout: () => void;
  updateWallet: (amount: number) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);

  // Load user from localStorage on mount
  useEffect(() => {
    const savedUser = localStorage.getItem('techzone_user');
    if (savedUser) {
      setUser(JSON.parse(savedUser));
    }
  }, []);

  // 🔌 BACKEND CONNECTION POINT #1: Login
  const login = async (email: string, password: string) => {
    try {
      // TODO: Replace with actual API call
      // const response = await fetch('YOUR_API_URL/auth/login', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ email, password }),
      // });
      // const data = await response.json();
      // if (!response.ok) throw new Error(data.message);
      
      // Mock login - Remove this when connecting to backend
      const mockUser: User = {
        id: 'user-123',
        email,
        name: email.split('@')[0],
        wallet: 1250.0,
      };
      
      setUser(mockUser);
      localStorage.setItem('techzone_user', JSON.stringify(mockUser));
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  };

  // 🔌 BACKEND CONNECTION POINT #2: Signup
  const signup = async (email: string, password: string, name: string) => {
    try {
      // TODO: Replace with actual API call
      // const response = await fetch('YOUR_API_URL/auth/signup', {
      //   method: 'POST',
      //   headers: { 'Content-Type': 'application/json' },
      //   body: JSON.stringify({ email, password, name }),
      // });
      // const data = await response.json();
      // if (!response.ok) throw new Error(data.message);
      
      // Mock signup - Remove this when connecting to backend
      const mockUser: User = {
        id: `user-${Date.now()}`,
        email,
        name,
        wallet: 0,
      };
      
      setUser(mockUser);
      localStorage.setItem('techzone_user', JSON.stringify(mockUser));
    } catch (error) {
      console.error('Signup error:', error);
      throw error;
    }
  };

  // 🔌 BACKEND CONNECTION POINT #3: Logout
  const logout = () => {
    // TODO: Call backend logout if using sessions/tokens
    // await fetch('YOUR_API_URL/auth/logout', { method: 'POST' });
    
    setUser(null);
    localStorage.removeItem('techzone_user');
  };

  // Update wallet balance
  const updateWallet = (amount: number) => {
    if (user) {
      const updatedUser = { ...user, wallet: amount };
      setUser(updatedUser);
      localStorage.setItem('techzone_user', JSON.stringify(updatedUser));
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated: !!user,
        login,
        signup,
        logout,
        updateWallet,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
