/**
 * EXAMPLE: How to update Home.tsx to use your database
 * 
 * This file shows you how to convert from mock data to real database calls.
 * Copy this pattern to other pages that need product data.
 */

import { useState, useMemo, useRef, useEffect } from 'react';
import { Search } from 'lucide-react';
import { ProductCard } from '../components/ProductCard';
// STEP 1: Replace this import
// OLD: import { products } from '../data/products';
import { getAllProducts } from '../services/database';
import { Product } from '../types';
import Footer from '../imports/Footer-4-4788';

const categories = [
  { name: 'All', count: 0 }, // Count will be calculated after loading
  { name: 'Graphics', count: 0 },
  { name: 'Processors', count: 0 },
  { name: 'Memory', count: 0 },
  { name: 'Cooling', count: 0 },
  { name: 'Peripherals', count: 0 },
];

const priceRanges = [
  { label: 'Sub ₱200', min: 0, max: 200 },
  { label: '₱200 - ₱600', min: 200, max: 600 },
  { label: '₱600+', min: 600, max: Infinity },
];

export function Home() {
  // STEP 2: Add state for products and loading
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [selectedPriceRange, setSelectedPriceRange] = useState<number | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortBy, setSortBy] = useState<'newest' | 'price-asc' | 'price-desc'>('newest');
  const productsRef = useRef<HTMLElement>(null);

  // STEP 3: Fetch products from database when component mounts
  useEffect(() => {
    async function fetchProducts() {
      try {
        setLoading(true);
        setError(null);
        const data = await getAllProducts();
        setProducts(data);
      } catch (err) {
        console.error('Error loading products:', err);
        setError('Failed to load products. Please try again later.');
      } finally {
        setLoading(false);
      }
    }
    fetchProducts();
  }, []);

  // STEP 4: Update category counts dynamically
  const categoriesWithCounts = useMemo(() => {
    return categories.map(cat => ({
      name: cat.name,
      count: cat.name === 'All' 
        ? products.length 
        : products.filter(p => p.category === cat.name).length
    }));
  }, [products]);

  const scrollToProducts = () => {
    productsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  const handleBrandClick = (brand: string) => {
    setSearchQuery(brand);
    setSelectedCategory('All');
    setSelectedPriceRange(null);
    scrollToProducts();
  };

  const filteredProducts = useMemo(() => {
    let filtered = [...products];

    // Filter by category
    if (selectedCategory !== 'All') {
      filtered = filtered.filter(p => p.category === selectedCategory);
    }

    // Filter by price range
    if (selectedPriceRange !== null) {
      const range = priceRanges[selectedPriceRange];
      filtered = filtered.filter(p => p.price >= range.min && p.price < range.max);
    }

    // Filter by search query
    if (searchQuery.trim()) {
      filtered = filtered.filter(p =>
        p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.description.toLowerCase().includes(searchQuery.toLowerCase())
      );
    }

    // Sort
    if (sortBy === 'price-asc') {
      filtered.sort((a, b) => a.price - b.price);
    } else if (sortBy === 'price-desc') {
      filtered.sort((a, b) => b.price - a.price);
    } else {
      filtered.sort((a, b) => (b.isNew ? 1 : 0) - (a.isNew ? 1 : 0));
    }

    return filtered;
  }, [products, selectedCategory, selectedPriceRange, searchQuery, sortBy]);

  const latestProducts = products.filter(p => p.isNew).slice(0, 3);

  // STEP 5: Add loading state UI
  if (loading) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-600 mb-4"></div>
          <p className="text-zinc-600">Loading products...</p>
        </div>
      </div>
    );
  }

  // STEP 6: Add error state UI
  if (error) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <div className="text-center max-w-md">
          <p className="text-red-600 mb-4">{error}</p>
          <button
            onClick={() => window.location.reload()}
            className="bg-cyan-500 text-white px-6 py-2 rounded hover:bg-cyan-600"
          >
            Retry
          </button>
        </div>
      </div>
    );
  }

  // Rest of your component remains the same...
  return (
    <div className="min-h-screen bg-white">
      {/* Hero Section */}
      <section className="bg-[#e0f2f7] py-32 px-6">
        <div className="max-w-[820px] mx-auto text-center">
          <h1 className="text-[64px] font-bold leading-[66px] tracking-tight mb-6">
            Premium Tech at Your
            <br />
            Fingertips
          </h1>
          <p className="text-zinc-600 text-[17px] leading-[26px] mb-10">
            Discover the latest laptops, components, and peripherals from trusted
            <br />
            brands.
          </p>
          <button 
            onClick={scrollToProducts}
            className="bg-[#8da4ef] text-white font-bold px-10 py-4 rounded-[11px] shadow-lg hover:bg-[#7a93e5] transition-colors"
          >
            Shop Now
          </button>
        </div>
      </section>

      {/* Brand Slider */}
      <section className="bg-zinc-400 border-t border-b border-zinc-500 py-10 overflow-hidden">
        <div className="flex justify-center items-center gap-20 flex-wrap px-6">
          {['ROCCAT', 'MSI', 'RAZER', 'THERMALTAKE', 'ADATA', 'HP', 'GIGABYTE', 'ROCCAT', 'MSI'].map((brand, idx) => (
            <button
              key={idx}
              onClick={() => handleBrandClick(brand)}
              className="flex-shrink-0 transition-all hover:scale-110"
            >
              <span className="text-2xl font-bold text-white/40 tracking-tight whitespace-nowrap hover:text-white/70 transition-colors cursor-pointer">
                {brand}
              </span>
            </button>
          ))}
        </div>
      </section>

      {/* Products Section */}
      <section ref={productsRef} className="max-w-[1400px] mx-auto px-6 py-16">
        <div className="flex gap-12">
          {/* Sidebar Filters */}
          <aside className="w-[260px] flex-shrink-0 space-y-10">
            {/* Categories */}
            <div>
              <h4 className="text-[9px] font-bold text-cyan-600 uppercase tracking-[2.7px] mb-6">
                Categories
              </h4>
              <ul className="space-y-3">
                {categoriesWithCounts.map((cat) => (
                  <li key={cat.name}>
                    <button
                      onClick={() => setSelectedCategory(cat.name)}
                      className={`w-full flex items-center justify-between text-left transition-colors ${
                        selectedCategory === cat.name
                          ? 'text-cyan-600 font-bold'
                          : 'text-zinc-600 hover:text-cyan-600'
                      }`}
                    >
                      <span className="text-[13px]">{cat.name}</span>
                      <span className="text-[11px] text-zinc-400">({cat.count})</span>
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            {/* Price Range */}
            <div>
              <h4 className="text-[9px] font-bold text-cyan-600 uppercase tracking-[2.7px] mb-6">
                Price Range
              </h4>
              <ul className="space-y-3">
                {priceRanges.map((range, idx) => (
                  <li key={idx}>
                    <button
                      onClick={() => setSelectedPriceRange(selectedPriceRange === idx ? null : idx)}
                      className={`text-left text-[13px] transition-colors ${
                        selectedPriceRange === idx
                          ? 'text-cyan-600 font-bold'
                          : 'text-zinc-600 hover:text-cyan-600'
                      }`}
                    >
                      {range.label}
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          </aside>

          {/* Main Content */}
          <div className="flex-1">
            {/* Search and Sort */}
            <div className="flex items-center justify-between mb-8">
              <div className="relative flex-1 max-w-[400px]">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
                <input
                  type="text"
                  placeholder="Search products..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-zinc-300 rounded-[8px] text-[13px] focus:outline-none focus:border-cyan-500"
                />
              </div>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value as any)}
                className="px-4 py-2 border border-zinc-300 rounded-[8px] text-[13px] focus:outline-none focus:border-cyan-500"
              >
                <option value="newest">Newest First</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
              </select>
            </div>

            {/* Latest Products */}
            {latestProducts.length > 0 && (
              <div className="mb-16">
                <h2 className="text-[28px] font-bold mb-8">Latest Products</h2>
                <div className="grid grid-cols-3 gap-6">
                  {latestProducts.map((product) => (
                    <ProductCard key={product.id} product={product} />
                  ))}
                </div>
              </div>
            )}

            {/* All Products */}
            <div>
              <div className="flex items-center justify-between mb-8">
                <h2 className="text-[28px] font-bold">All Products</h2>
                <span className="text-[13px] text-zinc-600">
                  {filteredProducts.length} {filteredProducts.length === 1 ? 'product' : 'products'}
                </span>
              </div>
              
              {filteredProducts.length === 0 ? (
                <div className="text-center py-16">
                  <p className="text-zinc-600">No products found matching your criteria.</p>
                </div>
              ) : (
                <div className="grid grid-cols-3 gap-6">
                  {filteredProducts.map((product) => (
                    <ProductCard key={product.id} product={product} showDetails />
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
}
