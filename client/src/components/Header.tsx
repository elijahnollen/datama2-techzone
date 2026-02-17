import { ShoppingCart, Wallet, MessageSquare, User, Package, LogOut } from 'lucide-react';
import { Link, useNavigate } from 'react-router';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import { useState } from 'react';
import { AuthModal } from './AuthModal';

export function Header() {
  const navigate = useNavigate();
  const { user, isAuthenticated, logout } = useAuth();
  const { getTotalItems } = useCart();
  const [showAuthModal, setShowAuthModal] = useState(false);
  const cartItemsCount = getTotalItems();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  return (
    <>
      <header className="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div className="max-w-[1400px] mx-auto px-6 py-4">
          <div className="flex items-center justify-between">
            {/* Logo */}
            <Link to="/" className="flex items-center gap-2">
              <h1 className="text-2xl font-bold">TECHZONE</h1>
            </Link>

            {/* Right Side - Icons and Login */}
            <div className="flex items-center gap-6">
              {isAuthenticated && user ? (
                <>
                  {/* Wallet - Clickable */}
                  <button
                    onClick={() => navigate('/wallet')}
                    className="flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                    title="Wallet"
                  >
                    <Wallet className="w-5 h-5 text-gray-700" />
                    <span className="font-semibold">₱{user.wallet.toFixed(2)}</span>
                  </button>

                  {/* Chat Icon - Clickable */}
                  <button
                    onClick={() => navigate('/messages')}
                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors relative"
                    title="Messages"
                  >
                    <MessageSquare className="w-6 h-6 text-gray-700" />
                  </button>

                  {/* User Profile Icon - Clickable */}
                  <button
                    onClick={() => navigate('/profile')}
                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                    title="Profile"
                  >
                    <User className="w-6 h-6 text-gray-700" />
                  </button>

                  {/* Orders Icon - Clickable */}
                  <button
                    onClick={() => navigate('/my-orders')}
                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                    title="My Orders"
                  >
                    <Package className="w-6 h-6 text-gray-700" />
                  </button>

                  {/* Cart Icon - Clickable with Badge */}
                  <button
                    onClick={() => navigate('/cart')}
                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors relative"
                    title="Shopping Cart"
                  >
                    <ShoppingCart className="w-6 h-6 text-gray-700" />
                    {cartItemsCount > 0 && (
                      <span className="absolute -top-1 -right-1 bg-blue-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                        {cartItemsCount}
                      </span>
                    )}
                  </button>

                  {/* Logout Icon - Clickable */}
                  <button
                    onClick={handleLogout}
                    className="p-2 hover:bg-red-50 rounded-lg transition-colors"
                    title="Log Out"
                  >
                    <LogOut className="w-6 h-6 text-gray-700" />
                  </button>
                </>
              ) : (
                <>
                  {/* Cart Icon (View Only when not logged in) */}
                  <button
                    onClick={() => navigate('/cart')}
                    className="p-2 hover:bg-gray-100 rounded-lg transition-colors relative"
                    title="Shopping Cart"
                  >
                    <ShoppingCart className="w-6 h-6 text-gray-700" />
                    {cartItemsCount > 0 && (
                      <span className="absolute -top-1 -right-1 bg-blue-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                        {cartItemsCount}
                      </span>
                    )}
                  </button>

                  {/* Login Button */}
                  <button
                    onClick={() => setShowAuthModal(true)}
                    className="px-8 py-2 bg-black text-white rounded-full hover:bg-gray-800 transition-colors"
                  >
                    LOG IN
                  </button>
                </>
              )}
            </div>
          </div>
        </div>
      </header>

      {/* Auth Modal */}
      <AuthModal
        isOpen={showAuthModal}
        onClose={() => setShowAuthModal(false)}
      />
    </>
  );
}
