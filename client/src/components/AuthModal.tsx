import { X, Mail, Lock } from 'lucide-react';
import { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { Link } from 'react-router'; 

interface AuthModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export function AuthModal({ isOpen, onClose }: AuthModalProps) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();

  if (!isOpen) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await login(email, password);
      onClose();
      setEmail('');
      setPassword('');
    } catch (err: any) {
      setError(err.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
      <div className="bg-white rounded-[24px] shadow-2xl max-w-md w-full overflow-hidden flex flex-col border border-zinc-100">
        
        {/* Header - Strictly Login */}
        <div className="flex items-center justify-between p-8 border-b border-zinc-50">
          <h2 className="text-2xl font-bold italic text-black">Welcome Back</h2>
          <button 
            onClick={onClose} 
            className="text-zinc-400 hover:text-zinc-600 transition-colors"
          >
            <X className="w-6 h-6" />
          </button>
        </div>

        {/* Login Form */}
        <form onSubmit={handleSubmit} className="p-8 space-y-5">
          {error && (
            <div className="bg-red-50 text-red-600 px-4 py-3 rounded-xl text-[11px] font-bold border border-red-100 text-center">
              {error}
            </div>
          )}

          <div className="space-y-1">
            <label className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">
              Email Address
            </label>
            <div className="flex items-center bg-zinc-50 border border-zinc-200 rounded-xl focus-within:ring-2 focus-within:ring-cyan-500 transition-all px-4">
              <Mail className="w-4 h-4 text-zinc-400 shrink-0" />
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full bg-transparent py-3.5 pl-3 text-sm outline-none text-zinc-700 placeholder:text-zinc-300"
                placeholder="john@example.com"
                required
              />
            </div>
          </div>

          <div className="space-y-1">
            <label className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">
              Password
            </label>
            <div className="flex items-center bg-zinc-50 border border-zinc-200 rounded-xl focus-within:ring-2 focus-within:ring-cyan-500 transition-all px-4">
              <Lock className="w-4 h-4 text-zinc-400 shrink-0" />
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full bg-transparent py-3.5 pl-3 text-sm outline-none text-zinc-700 placeholder:text-zinc-300"
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-black text-white py-4 rounded-xl font-bold hover:bg-zinc-800 transition-all disabled:bg-zinc-400 uppercase tracking-widest text-[12px] shadow-lg mt-2"
          >
            {loading ? 'Verifying...' : 'Log In'}
          </button>
        </form>

        {/* Footer - Link to the Full Page */}
        <div className="px-8 pb-10 text-center">
          <p className="text-sm text-zinc-500 font-medium">
            Don't have an account?{' '}
            <Link
              to="/signup"
              onClick={onClose}
              className="text-cyan-600 hover:text-cyan-700 font-bold underline-offset-4 hover:underline"
            >
              Sign Up
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}