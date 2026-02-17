import { useEffect, useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router';
// Import all necessary icons from lucide-react
import { ArrowLeft, Star, Plus, Minus, ShoppingCart, ChevronLeft, ChevronRight, Check, Search, Heart, User, Menu } from 'lucide-react';
import { getProductById, getAllProducts } from '../services/database';
import { Product } from '../types';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../contexts/AuthContext';
import { AuthModal } from '../components/AuthModal';
import Footer from '../imports/Footer-4-9633';

export function ProductDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { addToCart } = useCart();
  const { isAuthenticated, user } = useAuth();
  const [product, setProduct] = useState<Product | null>(null);
  const [relatedProducts, setRelatedProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);
  const [added, setAdded] = useState(false);
  const [selectedImage, setSelectedImage] = useState(0);
  const [showAuthModal, setShowAuthModal] = useState(false);

  // Fetch product and related products
  useEffect(() => {
    async function fetchData() {
      if (!id) return;
      
      try {
        setLoading(true);
        
        // Fetch the product
        const productData = await getProductById(id);
        setProduct(productData);
        
        // Fetch related products if we have the product category
        if (productData) {
          const allProducts = await getAllProducts();
          const related = allProducts
            .filter(p => p.category === productData.category && p.id !== productData.id)
            .slice(0, 4);
          setRelatedProducts(related);
        }
      } catch (error) {
        console.error('Error loading product:', error);
      } finally {
        setLoading(false);
      }
    }
    
    fetchData();
  }, [id]);

  // Loading state
  if (loading) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <header className="bg-white border-b border-zinc-100 sticky top-0 z-50">
          <div className="max-w-[1400px] mx-auto px-6 py-4 flex items-center justify-between">
            <Link to="/" className="font-bold italic text-[27px] text-black hover:opacity-80 transition-opacity">
              TECHZONE
            </Link>
            <div className="flex items-center gap-5">
              <button onClick={() => navigate('/')} className="text-zinc-500 hover:text-black transition-colors">
                <Search className="w-5 h-5" />
              </button>
              <button onClick={() => navigate('/favorites')} className="text-zinc-500 hover:text-black transition-colors">
                <Heart className="w-5 h-5" />
              </button>
              <button onClick={() => navigate('/cart')} className="text-zinc-500 hover:text-black transition-colors">
                <ShoppingCart className="w-5 h-5" />
              </button>
              <button onClick={() => setShowAuthModal(true)} className="text-zinc-500 hover:text-black transition-colors">
                <User className="w-5 h-5" />
              </button>
            </div>
          </div>
        </header>
        <div className="flex-1 flex items-center justify-center">
          <div className="text-center">
            <div className="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-cyan-600 mb-4"></div>
            <p className="text-zinc-600 text-lg">Loading product...</p>
          </div>
        </div>
        <Footer />
      </div>
    );
  }

  // Product not found
  if (!product) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <header className="bg-white border-b border-zinc-100 sticky top-0 z-50">
          <div className="max-w-[1400px] mx-auto px-6 py-4 flex items-center justify-between">
            <Link to="/" className="font-bold italic text-[27px] text-black hover:opacity-80 transition-opacity">
              TECHZONE
            </Link>
            <div className="flex items-center gap-5">
              <button onClick={() => navigate('/')} className="text-zinc-500 hover:text-black transition-colors">
                <Search className="w-5 h-5" />
              </button>
              <button onClick={() => navigate('/favorites')} className="text-zinc-500 hover:text-black transition-colors">
                <Heart className="w-5 h-5" />
              </button>
              <button onClick={() => navigate('/cart')} className="text-zinc-500 hover:text-black transition-colors">
                <ShoppingCart className="w-5 h-5" />
              </button>
              <button onClick={() => setShowAuthModal(true)} className="text-zinc-500 hover:text-black transition-colors">
                <User className="w-5 h-5" />
              </button>
            </div>
          </div>
        </header>
        <div className="flex-1 flex items-center justify-center">
          <div className="text-center">
            <h1 className="text-4xl font-bold mb-4">Product Not Found</h1>
            <Link to="/" className="text-cyan-600 hover:underline">
              Return to Shop
            </Link>
          </div>
        </div>
        <Footer />
      </div>
    );
  }

  const handleAddToCart = () => {
    if (!isAuthenticated) {
      setShowAuthModal(true);
      return;
    }
    for (let i = 0; i < quantity; i++) {
      addToCart(product);
    }
    setAdded(true);
    setTimeout(() => setAdded(false), 2000);
  };

  const handleBuyNow = () => {
    if (!isAuthenticated) {
      setShowAuthModal(true);
      return;
    }
    handleAddToCart();
    navigate('/cart');
  };

  // Create a varied image gallery - main product image plus some related category images for gallery effect
  const productImages = [
    product.image, // Main product image
    "https://images.unsplash.com/photo-1654860535404-aa828d051761?w=800", // GPU angle 1
    "https://images.unsplash.com/photo-1760539165409-46fd286dd7fa?w=800", // Processor detail
    "https://images.unsplash.com/photo-1758577675588-c5bbbbbf8e97?w=800", // RAM modules
  ];

  // Mock reviews data
  const reviews = [
    { id: 1, name: 'Francis Garcia', rating: 5, date: '07/03/2024', comment: 'Very effective in my work setup for multitasking.' },
    { id: 2, name: 'Marian Cruz', rating: 4, date: '10/19/2024', comment: 'Loved this! Managed to download huge apps right away. Good value(price).' },
    { id: 3, name: 'Ceasar Valdez', rating: 5, date: '12/15/2024', comment: 'Excellent Value' },
    { id: 4, name: 'Francis Garcia', rating: 4, date: '05/27/2024', comment: 'Great product, works exactly as advertised. Very fast drive with excellent functionality.' },
    { id: 5, name: 'Franz Castro', rating: 4, date: '09/07/2024', comment: 'Good value' },
    { id: 6, name: 'Maria Fernandez', rating: 5, date: '01/14/2025', comment: 'Reliable' },
  ];

  const averageRating = 4.5;
  const ratingDistribution = [
    { stars: 5, count: 7 },
    { stars: 4, count: 5 },
    { stars: 3, count: 0 },
    { stars: 2, count: 0 },
    { stars: 1, count: 0 },
  ];

  return (
    <div className="min-h-screen bg-white flex flex-col">
      {/* Simple Header - No Extra TECHZONE */}
      <header className="bg-white border-b border-zinc-100 sticky top-0 z-50">
        <div className="max-w-[1400px] mx-auto px-6 py-4 flex items-center justify-between">
          {/* Logo */}
          <Link to="/" className="font-bold italic text-[27px] text-black hover:opacity-80 transition-opacity">
            TECHZONE
          </Link>

          {/* Right Side Icons */}
          <div className="flex items-center gap-5">
            <button
              onClick={() => navigate('/')}
              className="text-zinc-500 hover:text-black transition-colors"
              aria-label="Search"
            >
              <Search className="w-5 h-5" />
            </button>
            <button
              onClick={() => navigate('/favorites')}
              className="text-zinc-500 hover:text-black transition-colors"
              aria-label="Favorites"
            >
              <Heart className="w-5 h-5" />
            </button>
            <button
              onClick={() => navigate('/cart')}
              className="text-zinc-500 hover:text-black transition-colors relative"
              aria-label="Shopping cart"
            >
              <ShoppingCart className="w-5 h-5" />
              {/* Cart badge could go here */}
            </button>
            <button
              onClick={() => isAuthenticated ? navigate('/my-orders') : setShowAuthModal(true)}
              className="text-zinc-500 hover:text-black transition-colors"
              aria-label="User account"
            >
              <User className="w-5 h-5" />
            </button>
          </div>
        </div>
      </header>

      <div className="flex-1 max-w-[1100px] mx-auto px-6 py-12 w-full">
        {/* Back Button */}
        <button
          onClick={() => navigate('/')}
          className="flex items-center gap-2 text-zinc-500 hover:text-black mb-12 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="font-bold uppercase tracking-wider text-xs">Back to Home</span>
        </button>

        {/* Product Main Section */}
        <div className="grid lg:grid-cols-[580px_1fr] gap-12 mb-20">
          {/* Left Column: Images */}
          <div className="flex flex-col gap-4">
            {/* Main Image */}
            <div className="bg-white border border-zinc-200 rounded-xl p-12 relative group">
              <img
                src={productImages[selectedImage]}
                alt={product.name}
                className="w-full h-auto max-h-[500px] object-contain"
              />
              
              {/* Navigation Arrows */}
              <button
                onClick={() => setSelectedImage((selectedImage - 1 + productImages.length) % productImages.length)}
                className="absolute left-4 top-1/2 -translate-y-1/2 bg-zinc-100 hover:bg-zinc-200 rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <ChevronLeft className="w-4 h-4 text-zinc-600" />
              </button>
              <button
                onClick={() => setSelectedImage((selectedImage + 1) % productImages.length)}
                className="absolute right-4 top-1/2 -translate-y-1/2 bg-zinc-100 hover:bg-zinc-200 rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <ChevronRight className="w-4 h-4 text-zinc-600" />
              </button>
            </div>

            {/* Thumbnails */}
            <div className="flex gap-4">
              {productImages.map((img, idx) => (
                <button
                  key={idx}
                  onClick={() => setSelectedImage(idx)}
                  className={`bg-white border rounded-lg p-2 flex-1 hover:border-cyan-500 transition-colors ${
                    selectedImage === idx ? 'border-cyan-500 border-2' : 'border-zinc-200'
                  }`}
                >
                  <img
                    src={img}
                    alt={`${product.name} thumbnail ${idx + 1}`}
                    className="w-full h-20 object-contain"
                  />
                </button>
              ))}
            </div>
          </div>

          {/* Right Column: Details */}
          <div className="flex flex-col">
            {/* Brand */}
            <div className="mb-2">
              <p className="font-bold text-zinc-500 text-xs uppercase tracking-wider">
                {product.brand || 'LEXAR'}
              </p>
            </div>

            {/* Product Title */}
            <h1 className="text-[27px] font-bold leading-[34px] mb-4">
              {product.name}
            </h1>

            {/* Rating */}
            <div className="flex items-center gap-2 mb-6">
              <div className="flex">
                {[1, 2, 3, 4, 5].map((star) => (
                  <Star
                    key={star}
                    className={`w-4 h-4 ${
                      star <= Math.floor(averageRating)
                        ? 'fill-green-500 text-green-500'
                        : star - averageRating < 1
                        ? 'fill-green-500 text-green-500'
                        : 'text-zinc-300'
                    }`}
                  />
                ))}
              </div>
              <span className="font-bold text-sm">{averageRating}</span>
            </div>

            {/* Availability */}
            <div className="flex items-center gap-3 mb-6">
              <span className="text-zinc-500 text-xs">Available:</span>
              <span className="font-bold text-green-600 text-xs uppercase tracking-wider">
                In Stock
              </span>
            </div>

            {/* Price */}
            <div className="mb-8">
              <span className="text-[30px] font-bold text-zinc-800">
                ₱{product.price.toLocaleString()}
              </span>
            </div>

            {/* Quantity */}
            <div className="mb-8">
              <label className="block font-bold text-zinc-500 text-xs uppercase mb-2">
                Quantity
              </label>
              <div className="flex items-center gap-4">
                <div className="flex items-center border border-zinc-300 rounded-lg">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="w-10 h-10 flex items-center justify-center hover:bg-zinc-100 transition-colors font-bold text-zinc-600"
                  >
                    <Minus className="w-4 h-4" />
                  </button>
                  <span className="w-12 h-10 flex items-center justify-center font-bold text-sm border-l border-r border-zinc-300">
                    {quantity}
                  </span>
                  <button
                    onClick={() => setQuantity(quantity + 1)}
                    className="w-10 h-10 flex items-center justify-center hover:bg-zinc-100 transition-colors font-bold text-zinc-600"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="flex gap-3">
              <button
                onClick={handleBuyNow}
                className="bg-[#232f3e] text-white font-bold text-sm px-8 py-3 rounded-full hover:bg-[#1a2332] transition-colors shadow-md"
              >
                Buy It Now
              </button>
              <button
                onClick={handleAddToCart}
                disabled={added}
                className="bg-cyan-500 text-black font-bold text-sm px-8 py-3 rounded-full hover:bg-cyan-600 transition-colors flex items-center gap-2 disabled:bg-green-500 shadow-md"
              >
                {added ? (
                  <>
                    <Check className="w-4 h-4" />
                    Added
                  </>
                ) : (
                  <>
                    <ShoppingCart className="w-4 h-4" />
                    Add to cart
                  </>
                )}
              </button>
            </div>
          </div>
        </div>

        {/* Product Description */}
        <div className="mb-16">
          <h3 className="text-[22px] font-bold border-l-4 border-cyan-500 pl-5 mb-6">
            Product Description
          </h3>
          <p className="text-zinc-600 text-sm leading-relaxed mb-8">
            {product.description || `Unleash speed and efficiency with Lexar NM610 Pro 500GB M.2 NVMe SSD. Elevate your storage performance for faster data access. Compact, powerful, and reliable—a seamless upgrade for an enhanced computing experience.`}
          </p>

          {/* Product Specifications */}
          <h4 className="text-[16px] font-bold mb-4">Product Specifications</h4>
          <ul className="space-y-2 text-zinc-600 text-sm">
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Brand:</strong> Lexar</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Model:</strong> NM610 Pro</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Capacity:</strong> 500GB</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Form Factor:</strong> M.2 2280</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Interface:</strong> PCIe Gen3x4</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Speed:</strong> 3000 MB/s read; up to 1850MB/s write, up to 170,000s IOPS</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Operating Temperature:</strong> 0°C - 70°C (32°F to 158°F)</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Storage Temperature:</strong> -40°C to 85°C (-40°F to 185°F)</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Dimensions & Weight:</strong> 80.33mm x 23.4mm x 3.18mm / 7.58g (3.16" x .92" x .13" / .27 oz)</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Weight:</strong> 7g</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Warranty:</strong> 5-Year Limited Warranty</span>
            </li>
            <li className="flex gap-3">
              <span className="text-zinc-400">•</span>
              <span><strong className="text-zinc-800">Warranty Resident:</strong> PH Warranty, Hazard, US Warranty, discountedB 1:23</span>
            </li>
          </ul>
        </div>

        {/* Customer Reviews */}
        <div className="mb-20">
          <h3 className="text-[22px] font-bold border-l-4 border-cyan-500 pl-5 mb-8">
            Customer Reviews
          </h3>

          <div className="grid md:grid-cols-[280px_1fr] gap-12 mb-12">
            {/* Overall Rating */}
            <div className="bg-zinc-50 border border-zinc-100 rounded-2xl p-6">
              <h4 className="font-bold text-xs uppercase tracking-wider text-zinc-600 mb-4">
                Overall Rating
              </h4>
              <div className="flex items-center gap-3 mb-6">
                <div className="flex">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                      key={star}
                      className={`w-5 h-5 ${
                        star <= Math.floor(averageRating)
                          ? 'fill-green-500 text-green-500'
                          : star - averageRating < 1
                          ? 'fill-green-500 text-green-500'
                          : 'text-zinc-300'
                      }`}
                    />
                  ))}
                </div>
                <span className="text-3xl font-bold">{averageRating}</span>
              </div>
              <p className="text-xs text-zinc-500 mb-6">Based on {reviews.length} reviews</p>

              {/* Star Distribution */}
              <div className="space-y-2">
                {ratingDistribution.map((item) => (
                  <div key={item.stars} className="flex items-center gap-3">
                    <span className="text-xs text-zinc-600 w-3">{item.stars}</span>
                    <div className="flex-1 h-2 bg-zinc-200 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-green-500 rounded-full"
                        style={{ width: `${(item.count / reviews.length) * 100}%` }}
                      />
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Individual Reviews */}
            <div>
              <div className="flex items-center justify-between mb-6">
                <h4 className="font-bold text-sm">Reviews</h4>
                <button className="text-cyan-600 text-xs font-bold uppercase tracking-wider hover:underline">
                  Add Review →
                </button>
              </div>

              <div className="space-y-6">
                {reviews.map((review) => (
                  <div key={review.id} className="border-b border-zinc-100 pb-6">
                    <div className="flex items-start justify-between mb-3">
                      <div>
                        <div className="flex items-center gap-2 mb-1">
                          <div className="flex">
                            {[1, 2, 3, 4, 5].map((star) => (
                              <Star
                                key={star}
                                className={`w-3 h-3 ${
                                  star <= review.rating
                                    ? 'fill-green-500 text-green-500'
                                    : 'text-zinc-300'
                                }`}
                              />
                            ))}
                          </div>
                        </div>
                        <p className="font-bold text-sm">{review.name}</p>
                      </div>
                      <span className="text-xs text-zinc-400">{review.date}</span>
                    </div>
                    <p className="text-sm text-zinc-600">{review.comment}</p>
                  </div>
                ))}
              </div>

              <button className="mt-8 w-full border-2 border-zinc-200 rounded-xl py-3 font-bold text-xs uppercase tracking-wider hover:bg-zinc-50 transition-colors">
                See More Reviews →
              </button>
            </div>
          </div>
        </div>

        {/* Related Items */}
        {relatedProducts.length > 0 && (
          <div className="mb-20">
            <h2 className="text-[22px] font-bold italic mb-8">
              <span className="text-black">RELATED </span>
              <span className="text-cyan-500">ITEMS</span>
            </h2>
            <div className="grid md:grid-cols-4 gap-6">
              {relatedProducts.map(p => (
                <Link
                  key={p.id}
                  to={`/product/${p.id}`}
                  className="bg-white border border-zinc-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow group"
                >
                  <div className="aspect-square bg-zinc-50 flex items-center justify-center p-6">
                    <img
                      src={p.image}
                      alt={p.name}
                      className="w-full h-full object-contain group-hover:scale-105 transition-transform"
                    />
                  </div>
                  <div className="p-4">
                    <h3 className="font-bold text-sm mb-2 line-clamp-2">{p.name}</h3>
                    <p className="text-lg font-bold text-cyan-600">
                      ₱{p.price.toLocaleString()}
                    </p>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Footer */}
      <Footer />

      {/* Auth Modal */}
      <AuthModal
        show={showAuthModal}
        onClose={() => setShowAuthModal(false)}
        onLogin={() => setShowAuthModal(false)}
        onRegister={() => setShowAuthModal(false)}
      />
    </div>
  );
}