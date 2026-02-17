import { useState } from 'react';
import { Link, useNavigate } from 'react-router';
import { ArrowLeft } from 'lucide-react';
import Footer from '../imports/Footer-4-4788';

export function Login() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // Handle login logic here
    console.log('Login attempt:', { email, password });
  };

  return (
    <div className="min-h-screen bg-white flex flex-col">
      {/* Main Content */}
      <div className="flex-1 flex items-center justify-center px-6 py-32">
        <div className="bg-white rounded-[45px] border border-zinc-200 shadow-[0px_35px_70px_-17px_rgba(0,0,0,0.25)] w-full max-w-[540px] p-14 relative">
          {/* Back Button */}
          <button
            onClick={() => navigate(-1)}
            className="absolute top-12 left-12 p-2 hover:bg-zinc-50 rounded-lg transition-colors"
          >
            <ArrowLeft className="w-7 h-7 text-zinc-400" />
          </button>

          <div className="flex flex-col items-center gap-8">
            {/* Logo Icon */}
            <div className="w-[90px] h-[90px] bg-cyan-500 rounded-[22px] flex items-center justify-center">
              <span className="text-[42px] font-bold italic text-black leading-none">T</span>
            </div>

            {/* Header Text */}
            <div className="text-center space-y-3">
              <h2 className="text-[32px] font-bold italic text-black leading-tight">
                Welcome back!
              </h2>
              <p className="text-zinc-500 text-[17px] font-bold uppercase tracking-[1.7px]">
                Sign in to your account
              </p>
            </div>

            {/* Form */}
            <form onSubmit={handleSubmit} className="w-full space-y-6">
              {/* Email Input */}
              <div>
                <input
                  type="email"
                  placeholder="Email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[17px] px-5 py-5 text-[22px] text-zinc-400 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>

              {/* Password Input */}
              <div>
                <input
                  type="password"
                  placeholder="Password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[17px] px-5 py-5 text-[21px] text-zinc-400 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>

              {/* Login Button */}
              <button
                type="submit"
                className="w-full bg-cyan-500 hover:bg-cyan-600 text-black font-bold text-[20px] uppercase tracking-[2px] py-4 rounded-[17px] shadow-lg transition-colors"
              >
                Log In
              </button>
            </form>

            {/* Bottom Links */}
            <div className="text-center space-y-6 pt-3">
              <div className="flex items-center justify-center gap-1">
                <span className="text-zinc-500 text-[16.5px]">New Customer?</span>
                <Link
                  to="/signup"
                  className="text-black font-bold text-[16px] hover:text-cyan-600 transition-colors"
                >
                  Create your account
                </Link>
              </div>

              <div>
                <Link
                  to="/forgot-password"
                  className="text-zinc-400 font-bold text-[14px] uppercase tracking-[1.4px] hover:text-zinc-600 transition-colors"
                >
                  Forgot your password?
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <Footer />
    </div>
  );
}
