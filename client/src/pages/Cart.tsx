import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router';
import { ArrowLeft, Trash2, Lock } from 'lucide-react';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../contexts/AuthContext';
import { AuthModal } from '../components/AuthModal';
import Footer from '../imports/Footer-4-8839';
import { getAllProducts } from '../services/database';
import { Product } from '../types';

export function Cart() {
  const navigate = useNavigate();
  const { items, removeFromCart, updateQuantity, getTotalPrice } = useCart();
  const { isAuthenticated, user } = useAuth();
  const [selectedItems, setSelectedItems] = useState<string[]>([]);
  const [selectAll, setSelectAll] = useState(false);
  const [recommendedProducts, setRecommendedProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAuthModal, setShowAuthModal] = useState(false);

  // Fetch recommended products
  useEffect(() => {
    async function fetchRecommended() {
      try {
        setLoading(true);
        const allProducts = await getAllProducts();
        // Get random 4 products for recommendations
        const shuffled = allProducts.sort(() => 0.5 - Math.random());
        setRecommendedProducts(shuffled.slice(0, 4));
      } catch (error) {
        console.error('Error loading recommended products:', error);
      } finally {
        setLoading(false);
      }
    }
    fetchRecommended();
  }, []);

  // Get unique cart items with quantities
  const cartItems = items.reduce((acc, item) => {
    const existing = acc.find(i => i.id === item.id);
    if (existing) {
      existing.quantity += 1;
    } else {
      acc.push({ ...item, quantity: 1 });
    }
    return acc;
  }, [] as Array<typeof items[0] & { quantity: number }>);

  // Mock unavailable items (could be fetched from database in real implementation)
  const unavailableItems = [
    {
      id: 'cooling-3',
      name: 'Hydro Zen 360 Cooler',
      image: 'https://images.unsplash.com/photo-1704871132546-d1d3b845ae65?w=300',
      price: 2450,
    }
  ];

  const handleSelectAll = () => {
    if (selectAll) {
      setSelectedItems([]);
    } else {
      setSelectedItems(cartItems.map(item => item.id));
    }
    setSelectAll(!selectAll);
  };

  const handleSelectItem = (id: string) => {
    if (selectedItems.includes(id)) {
      setSelectedItems(selectedItems.filter(itemId => itemId !== id));
      setSelectAll(false);
    } else {
      const newSelected = [...selectedItems, id];
      setSelectedItems(newSelected);
      if (newSelected.length === cartItems.length) {
        setSelectAll(true);
      }
    }
  };

  const handleCheckout = () => {
    if (selectedItems.length === 0) {
      alert('Please select at least one item to checkout');
      return;
    }
    if (!isAuthenticated) {
      setShowAuthModal(true);
      return;
    }
    navigate('/checkout');
  };

  const selectedTotal = cartItems
    .filter(item => selectedItems.includes(item.id))
    .reduce((sum, item) => sum + item.price * item.quantity, 0);

  const shippingFee = selectedItems.length > 0 ? 150 : 0;
  const discount = 0;
  const orderTotal = selectedTotal + shippingFee - discount;

  if (items.length === 0) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <div className="flex-1 flex items-center justify-center">
          <div className="text-center">
            <div className="w-24 h-24 bg-zinc-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <span className="text-4xl">🛒</span>
            </div>
            <h1 className="text-4xl font-bold mb-4">Your Cart is Empty</h1>
            <p className="text-zinc-600 mb-8">
              Add some awesome tech products to get started!
            </p>
            <Link
              to="/"
              className="inline-block bg-cyan-500 text-black font-bold text-sm uppercase tracking-wider px-8 py-3 rounded-full hover:bg-cyan-600 transition-colors"
            >
              Start Shopping
            </Link>
          </div>
        </div>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <div className="flex-1 max-w-[1400px] mx-auto px-6 py-12 w-full">
        {/* Back to Home */}
        <Link
          to="/"
          className="flex items-center gap-2 text-zinc-500 hover:text-black mb-8 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="font-bold uppercase tracking-wider text-xs">Back to Home</span>
        </Link>

        {/* Page Header */}
        <h1 className="text-[32px] font-bold italic leading-tight mb-8">
          <span className="text-black">SHOPPING </span>
          <span className="text-cyan-500">CART</span>
        </h1>

        <div className="grid lg:grid-cols-[1fr_400px] gap-12">
          {/* Left Column - Cart Items */}
          <div>
            {/* Select All */}
            <div className="bg-white border border-zinc-200 rounded-xl p-4 mb-4">
              <label className="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  checked={selectAll}
                  onChange={handleSelectAll}
                  className="w-5 h-5 accent-cyan-500"
                />
                <span className="font-bold text-sm uppercase tracking-wider">
                  Select All ({cartItems.length} {cartItems.length === 1 ? 'item' : 'items'})
                </span>
              </label>
            </div>

            {/* Cart Items List */}
            <div className="space-y-4 mb-8">
              {cartItems.map((item) => (
                <div
                  key={item.id}
                  className="bg-white border border-zinc-200 rounded-xl p-6"
                >
                  <div className="flex items-start gap-4">
                    {/* Checkbox */}
                    <input
                      type="checkbox"
                      checked={selectedItems.includes(item.id)}
                      onChange={() => handleSelectItem(item.id)}
                      className="mt-1 w-5 h-5 accent-cyan-500 cursor-pointer"
                    />

                    {/* Product Image */}
                    <div className="w-20 h-20 bg-zinc-50 rounded-lg flex items-center justify-center p-2">
                      <img
                        src={item.image}
                        alt={item.name}
                        className="w-full h-full object-contain"
                      />
                    </div>

                    {/* Product Details */}
                    <div className="flex-1">
                      <h3 className="font-bold text-base mb-1">{item.name}</h3>
                      <p className="text-xs text-zinc-500 mb-3">
                        Unit Price: <span className="font-bold text-black">₱{item.price.toLocaleString()}</span>
                      </p>

                      {/* Quantity Controls */}
                      <div className="flex items-center gap-3">
                        <div className="flex items-center border border-zinc-300 rounded-lg">
                          <button
                            onClick={() => {
                              const newQty = item.quantity - 1;
                              if (newQty <= 0) {
                                removeFromCart(item.id);
                                setSelectedItems(selectedItems.filter(id => id !== item.id));
                              } else {
                                updateQuantity(item.id, newQty);
                              }
                            }}
                            className="w-8 h-8 flex items-center justify-center hover:bg-zinc-100 transition-colors text-zinc-600 font-bold"
                          >
                            -
                          </button>
                          <span className="w-10 h-8 flex items-center justify-center font-bold text-sm border-l border-r border-zinc-300">
                            {item.quantity}
                          </span>
                          <button
                            onClick={() => updateQuantity(item.id, item.quantity + 1)}
                            className="w-8 h-8 flex items-center justify-center hover:bg-zinc-100 transition-colors text-zinc-600 font-bold"
                          >
                            +
                          </button>
                        </div>
                      </div>
                    </div>

                    {/* Price and Delete */}
                    <div className="flex flex-col items-end gap-2">
                      <button
                        onClick={() => {
                          removeFromCart(item.id);
                          setSelectedItems(selectedItems.filter(id => id !== item.id));
                        }}
                        className="p-1 hover:bg-red-50 rounded transition-colors"
                      >
                        <Trash2 className="w-4 h-4 text-zinc-400 hover:text-red-600" />
                      </button>
                      <p className="font-bold text-xl text-cyan-600">
                        ₱{(item.price * item.quantity).toLocaleString()}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* Unavailable Items */}
            {unavailableItems.length > 0 && (
              <div>
                <div className="flex items-center gap-2 mb-4">
                  <div className="w-4 h-4 border-2 border-red-500 rounded-full flex items-center justify-center">
                    <span className="text-red-500 text-xs">!</span>
                  </div>
                  <span className="font-bold text-sm uppercase tracking-wider text-red-600">
                    Unavailable / Out of Stock
                  </span>
                </div>

                <div className="space-y-4">
                  {unavailableItems.map((item) => (
                    <div
                      key={item.id}
                      className="bg-zinc-50 border border-zinc-200 rounded-xl p-6 opacity-60"
                    >
                      <div className="flex items-start gap-4">
                        {/* Checkbox - disabled */}
                        <input
                          type="checkbox"
                          disabled
                          className="mt-1 w-5 h-5 cursor-not-allowed"
                        />

                        {/* Product Image */}
                        <div className="w-20 h-20 bg-white rounded-lg flex items-center justify-center p-2">
                          <img
                            src={item.image}
                            alt={item.name}
                            className="w-full h-full object-contain"
                          />
                        </div>

                        {/* Product Details */}
                        <div className="flex-1">
                          <h3 className="font-bold text-base mb-1 text-zinc-500">{item.name}</h3>
                          <p className="text-xs text-zinc-400 mb-3">
                            Unit Price: <span className="font-bold">₱{item.price.toLocaleString()}</span>
                          </p>
                        </div>

                        {/* Delete */}
                        <button className="p-1 hover:bg-red-50 rounded transition-colors">
                          <Trash2 className="w-4 h-4 text-zinc-400 hover:text-red-600" />
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Right Column - Order Summary */}
          <div>
            <div className="bg-white border border-zinc-200 rounded-2xl p-6 sticky top-6">
              <h3 className="font-bold text-sm mb-6">
                <span className="text-black">ORDER </span>
                <span className="text-cyan-500">SUMMARY</span>
              </h3>

              {/* Summary Details */}
              <div className="space-y-3 mb-6 pb-6 border-b border-zinc-100">
                <div className="flex items-center justify-between text-sm">
                  <span className="text-zinc-600">
                    Subtotal ({selectedItems.length} {selectedItems.length === 1 ? 'item' : 'items'})
                  </span>
                  <span className="font-bold">₱{selectedTotal.toLocaleString()}</span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-zinc-600">Shipping Fee</span>
                  <span className="font-bold">₱{shippingFee.toLocaleString()}</span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-zinc-600">Discount</span>
                  <span className="font-bold text-green-600">-₱{discount.toLocaleString()}</span>
                </div>
              </div>

              {/* Total */}
              <div className="flex items-center justify-between mb-6">
                <span className="font-bold text-sm uppercase tracking-wider">Total</span>
                <span className="font-bold text-2xl text-cyan-600">
                  ₱{orderTotal.toLocaleString()}
                </span>
              </div>

              {/* Checkout Button */}
              <button
                onClick={handleCheckout}
                disabled={selectedItems.length === 0}
                className="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-xl hover:bg-zinc-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-black flex items-center justify-center gap-2 mb-3"
              >
                Checkout
                <span>→</span>
              </button>

              {/* Terms */}
              <p className="text-[9px] text-zinc-400 text-center leading-relaxed">
                Shipping & taxes calculated at checkout
              </p>
            </div>
          </div>
        </div>
      </div>

      <Footer />
      
      <AuthModal
        isOpen={showAuthModal}
        onClose={() => setShowAuthModal(false)}
      />
    </div>
  );
}