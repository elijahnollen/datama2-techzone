import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router';
import { ArrowLeft, MapPin, CreditCard, Wallet, Package, Truck, Store, CheckCircle2, Banknote } from 'lucide-react';
import NavbarLoggedInState from '../imports/NavbarLoggedInState-4-8075';
import Footer from '../imports/Footer-4-8013';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../contexts/AuthContext';
import { getAllProducts } from '../services/database';
import { Product } from '../types';

type DeliveryMethod = 'delivery' | 'pickup';
type PaymentMethod = 'cod' | 'gcash' | 'card' | 'credit';

export function Checkout() {
  const navigate = useNavigate();
  const { items, getTotalPrice, clearCart } = useCart();
  const { user } = useAuth();
  const [deliveryMethod, setDeliveryMethod] = useState<DeliveryMethod>('delivery');
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cod');
  const [products, setProducts] = useState<Product[]>([]);
  const [formData, setFormData] = useState({
    fullName: user?.name || 'Pilot Zero',
    phoneNumber: '+63 912 345 6789',
    email: user?.email || 'pilot@techzone.io',
    streetAddress: 'Block 1 Lot 2, Cyber St.',
    barangay: 'San Francisco',
    city: 'General Trias',
    province: 'Cavite',
    zipCode: '4107',
    orderNotes: '',
  });

  useEffect(() => {
    const fetchProducts = async () => {
      const allProducts = await getAllProducts();
      setProducts(allProducts);
    };
    fetchProducts();
  }, []);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    setFormData(prev => ({
      ...prev,
      [e.target.name]: e.target.value,
    }));
  };

  const merchandiseSubtotal = getTotalPrice();
  const shippingFee = deliveryMethod === 'delivery' ? 150 : 0;
  const vatRate = 0.12;
  const vat = (merchandiseSubtotal + shippingFee) * vatRate;
  const orderTotal = merchandiseSubtotal + shippingFee + vat;

  const handlePlaceOrder = () => {
    // In a real app, you would send this to a backend
    console.log('Order placed:', {
      deliveryMethod,
      paymentMethod,
      formData,
      items,
      total: orderTotal,
    });
    
    // Clear cart and redirect to success page
    clearCart();
    alert('Order placed successfully!');
    navigate('/orders');
  };

  if (items.length === 0) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <NavbarLoggedInState />
        <div className="flex-1 flex items-center justify-center">
          <div className="text-center">
            <Package className="w-16 h-16 text-zinc-300 mx-auto mb-4" />
            <h2 className="text-2xl font-bold mb-2">Your cart is empty</h2>
            <p className="text-zinc-500 mb-6">Add some products to checkout</p>
            <Link
              to="/"
              className="bg-cyan-500 text-black font-bold text-sm uppercase tracking-wider px-8 py-3 rounded-full hover:bg-cyan-600 transition-colors inline-block"
            >
              Continue Shopping
            </Link>
          </div>
        </div>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <NavbarLoggedInState />

      <div className="flex-1 max-w-[1400px] mx-auto px-6 py-12 w-full">
        {/* Back to Cart */}
        <Link
          to="/cart"
          className="flex items-center gap-2 text-zinc-500 hover:text-black mb-8 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="font-bold uppercase tracking-wider text-xs">Back to Cart</span>
        </Link>

        {/* Page Header */}
        <h1 className="text-[32px] font-bold italic leading-tight mb-12">
          <span className="text-black">SECURE </span>
          <span className="text-cyan-500">CHECKOUT</span>
        </h1>

        <div className="grid lg:grid-cols-[1fr_400px] gap-12">
          {/* Left Column - Forms */}
          <div className="space-y-8">
            {/* Shipping Information */}
            <div className="bg-white border border-zinc-200 rounded-2xl p-8">
              <h3 className="flex items-center gap-2 font-bold text-cyan-600 text-xs uppercase tracking-wider mb-6">
                <CheckCircle2 className="w-4 h-4" />
                Shipping Information
              </h3>

              {/* Delivery Method */}
              <div className="mb-6">
                <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-3">
                  Delivery Method
                </label>
                <div className="grid grid-cols-2 gap-4">
                  <button
                    onClick={() => setDeliveryMethod('delivery')}
                    className={`flex items-center gap-3 p-4 rounded-xl border-2 transition-colors ${
                      deliveryMethod === 'delivery'
                        ? 'border-cyan-500 bg-cyan-50'
                        : 'border-zinc-200 hover:border-zinc-300'
                    }`}
                  >
                    <Truck className="w-5 h-5 text-zinc-600" />
                    <div className="text-left">
                      <p className="font-bold text-sm">Delivery</p>
                      <p className="text-xs text-zinc-500">Est. 3-5 days</p>
                    </div>
                  </button>
                  <button
                    onClick={() => setDeliveryMethod('pickup')}
                    className={`flex items-center gap-3 p-4 rounded-xl border-2 transition-colors ${
                      deliveryMethod === 'pickup'
                        ? 'border-cyan-500 bg-cyan-50'
                        : 'border-zinc-200 hover:border-zinc-300'
                    }`}
                  >
                    <Store className="w-5 h-5 text-zinc-600" />
                    <div className="text-left">
                      <p className="font-bold text-sm">Store Pickup</p>
                      <p className="text-xs text-zinc-500">Est. 1-2 days</p>
                    </div>
                  </button>
                </div>
              </div>

              {/* Name and Phone */}
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                    Full Name
                  </label>
                  <input
                    type="text"
                    name="fullName"
                    value={formData.fullName}
                    onChange={handleInputChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>
                <div>
                  <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                    Phone Number
                  </label>
                  <input
                    type="tel"
                    name="phoneNumber"
                    value={formData.phoneNumber}
                    onChange={handleInputChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>
              </div>

              {/* Email */}
              <div className="mb-4">
                <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                  Email Address
                </label>
                <input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleInputChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>

              {/* Street Address */}
              <div className="mb-4">
                <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                  Street Address
                </label>
                <input
                  type="text"
                  name="streetAddress"
                  value={formData.streetAddress}
                  onChange={handleInputChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>

              {/* Barangay and City */}
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                    Barangay
                  </label>
                  <input
                    type="text"
                    name="barangay"
                    value={formData.barangay}
                    onChange={handleInputChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>
                <div>
                  <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                    City / Municipality
                  </label>
                  <input
                    type="text"
                    name="city"
                    value={formData.city}
                    onChange={handleInputChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>
              </div>

              {/* Province and Zip */}
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                    Province
                  </label>
                  <input
                    type="text"
                    name="province"
                    value={formData.province}
                    onChange={handleInputChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>
                <div>
                  <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                    Zip Code
                  </label>
                  <input
                    type="text"
                    name="zipCode"
                    value={formData.zipCode}
                    onChange={handleInputChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>
              </div>

              {/* Order Notes */}
              <div>
                <label className="block font-bold text-zinc-500 text-xs uppercase tracking-wider mb-2">
                  Order Notes (Optional)
                </label>
                <textarea
                  name="orderNotes"
                  value={formData.orderNotes}
                  onChange={handleInputChange}
                  placeholder="Special instructions..."
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 resize-none h-24"
                />
              </div>
            </div>

            {/* Payment Method */}
            <div className="bg-white border border-zinc-200 rounded-2xl p-8">
              <h3 className="flex items-center gap-2 font-bold text-cyan-600 text-xs uppercase tracking-wider mb-6">
                <CheckCircle2 className="w-4 h-4" />
                Payment Method
              </h3>

              <div className="space-y-3">
                <button
                  onClick={() => setPaymentMethod('cod')}
                  className={`w-full flex items-center justify-between p-4 rounded-xl border-2 transition-colors ${
                    paymentMethod === 'cod'
                      ? 'border-cyan-500 bg-cyan-50'
                      : 'border-zinc-200 hover:border-zinc-300'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <Banknote className="w-5 h-5 text-zinc-600" />
                    <div className="text-left">
                      <p className="font-bold text-sm">Cash on Delivery (COD)</p>
                      <p className="text-xs text-zinc-500">Pay when you receive your order</p>
                    </div>
                  </div>
                  <Package className="w-5 h-5 text-zinc-400" />
                </button>

                <button
                  onClick={() => setPaymentMethod('gcash')}
                  className={`w-full flex items-center justify-between p-4 rounded-xl border-2 transition-colors ${
                    paymentMethod === 'gcash'
                      ? 'border-cyan-500 bg-cyan-50'
                      : 'border-zinc-200 hover:border-zinc-300'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <Wallet className="w-5 h-5 text-zinc-600" />
                    <div className="text-left">
                      <p className="font-bold text-sm">GCash</p>
                      <p className="text-xs text-zinc-500">Pay via GCash e-wallet</p>
                    </div>
                  </div>
                  <Wallet className="w-5 h-5 text-zinc-400" />
                </button>

                <button
                  onClick={() => setPaymentMethod('card')}
                  className={`w-full flex items-center justify-between p-4 rounded-xl border-2 transition-colors ${
                    paymentMethod === 'card'
                      ? 'border-cyan-500 bg-cyan-50'
                      : 'border-zinc-200 hover:border-zinc-300'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <CreditCard className="w-5 h-5 text-zinc-600" />
                    <div className="text-left">
                      <p className="font-bold text-sm">Credit / Debit Card</p>
                      <p className="text-xs text-zinc-500">Secure payment via PayMongo</p>
                    </div>
                  </div>
                  <CreditCard className="w-5 h-5 text-zinc-400" />
                </button>

                <button
                  onClick={() => setPaymentMethod('credit')}
                  className={`w-full flex items-center justify-between p-4 rounded-xl border-2 transition-colors ${
                    paymentMethod === 'credit'
                      ? 'border-cyan-500 bg-cyan-50'
                      : 'border-zinc-200 hover:border-zinc-300'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <CreditCard className="w-5 h-5 text-zinc-600" />
                    <div className="text-left">
                      <p className="font-bold text-sm">Store Credit</p>
                      <p className="text-xs text-zinc-500">Pay using store credit</p>
                    </div>
                  </div>
                  <CreditCard className="w-5 h-5 text-zinc-400" />
                </button>
              </div>
            </div>
          </div>

          {/* Right Column - Order Summary */}
          <div>
            <div className="bg-white border border-zinc-200 rounded-2xl p-6 sticky top-6">
              <h3 className="font-bold text-sm mb-6">
                <span className="text-black">YOUR </span>
                <span className="text-cyan-500">ORDER</span>
              </h3>

              {/* Products */}
              <div className="space-y-4 mb-6 pb-6 border-b border-zinc-100">
                {items.map((item, index) => {
                  const product = products.find(p => p.id === item.id);
                  if (!product) return null;

                  return (
                    <div key={index} className="flex items-center gap-4">
                      <div className="w-12 h-12 bg-zinc-50 rounded-lg flex items-center justify-center p-1">
                        <img
                          src={product.image}
                          alt={product.name}
                          className="w-full h-full object-contain"
                        />
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-bold text-xs line-clamp-2 mb-1">{product.name}</p>
                        <p className="font-bold text-cyan-600 text-sm">
                          ₱{product.price.toLocaleString()}
                        </p>
                      </div>
                      <div className="flex items-center gap-3">
                        <button className="w-6 h-6 flex items-center justify-center text-zinc-400 hover:text-zinc-600">
                          -
                        </button>
                        <span className="font-bold text-sm w-4 text-center">
                          {items.filter(i => i.id === item.id).length}
                        </span>
                        <button className="w-6 h-6 flex items-center justify-center text-zinc-400 hover:text-zinc-600">
                          +
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Order Summary */}
              <div className="space-y-3 mb-6 pb-6 border-b border-zinc-100">
                <div className="flex items-center justify-between text-sm">
                  <span className="text-zinc-600">Merchandise Subtotal</span>
                  <span className="font-bold">₱{merchandiseSubtotal.toLocaleString()}</span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-zinc-600">Shipping Fee</span>
                  <span className="font-bold">₱{shippingFee.toLocaleString()}</span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-zinc-600">VAT (12%)</span>
                  <span className="font-bold">₱{vat.toFixed(2)}</span>
                </div>
              </div>

              {/* Order Total */}
              <div className="flex items-center justify-between mb-6">
                <span className="font-bold text-sm uppercase tracking-wider">Order Total</span>
                <span className="font-bold text-2xl text-cyan-600">
                  ₱{orderTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </span>
              </div>

              {/* Place Order Button */}
              <button
                onClick={handlePlaceOrder}
                className="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-xl hover:bg-zinc-800 transition-colors flex items-center justify-center gap-2"
              >
                Place Order
                <CheckCircle2 className="w-4 h-4" />
              </button>

              {/* Terms */}
              <p className="text-[9px] text-zinc-400 text-center mt-4 leading-relaxed">
                By placing your order, you agree to TechZone's{' '}
                <Link to="/terms" className="text-cyan-600 font-bold hover:underline">
                  Terms of Service
                </Link>{' '}
                and{' '}
                <Link to="/privacy" className="text-cyan-600 font-bold hover:underline">
                  Privacy Policy
                </Link>
              </p>
            </div>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  );
}