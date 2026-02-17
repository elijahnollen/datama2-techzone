import { useState, useMemo, useRef, useEffect } from 'react';
import { Search } from 'lucide-react';
import { ProductCard } from '../components/ProductCard';
import { getAllProducts } from '../services/database';
import { Product } from '../types';
import Footer from '../imports/Footer-4-4788';
import { DatabaseStatusBanner } from '../components/DatabaseStatusBanner';
import { Header } from '../components/Header';

const priceRanges = [
  { label: 'Sub ₱200', min: 0, max: 200 },
  { label: '₱200 - ₱600', min: 200, max: 600 },
  { label: '₱600+', min: 600, max: Infinity },
];

export function Home() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [selectedPriceRange, setSelectedPriceRange] = useState<number | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortBy, setSortBy] = useState<'newest' | 'price-asc' | 'price-desc'>('newest');
  const productsRef = useRef<HTMLElement>(null);

  // Fetch products from database
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

  // Calculate categories dynamically based on loaded products
  const categories = useMemo(() => [
    { name: 'All', count: products.length },
    { name: 'Graphics', count: products.filter(p => p.category === 'Graphics').length },
    { name: 'Processors', count: products.filter(p => p.category === 'Processors').length },
    { name: 'Memory', count: products.filter(p => p.category === 'Memory').length },
    { name: 'Cooling', count: products.filter(p => p.category === 'Cooling').length },
    { name: 'Peripherals', count: products.filter(p => p.category === 'Peripherals').length },
  ], [products]);

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
      // newest first
      filtered.sort((a, b) => (b.isNew ? 1 : 0) - (a.isNew ? 1 : 0));
    }

    return filtered;
  }, [products, selectedCategory, selectedPriceRange, searchQuery, sortBy]);

  const latestProducts = useMemo(() => 
    products.filter(p => p.isNew).slice(0, 3),
    [products]
  );

  // Loading state
  if (loading) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-cyan-600 mb-4"></div>
          <p className="text-zinc-600 text-lg">Loading products...</p>
        </div>
      </div>
    );
  }

  // Error state
  if (error) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <div className="text-center max-w-md px-6">
          <div className="text-red-500 text-5xl mb-4">⚠️</div>
          <h2 className="text-2xl font-bold mb-2">Oops!</h2>
          <p className="text-zinc-600 mb-6">{error}</p>
          <button
            onClick={() => window.location.reload()}
            className="bg-cyan-500 text-white px-6 py-3 rounded-lg hover:bg-cyan-600 transition-colors"
          >
            Retry
          </button>
        </div>
      </div>
    );
  }

return (
    <div className="min-h-screen bg-white">
      <Header />
      {/* Database Status Banner */}
      <DatabaseStatusBanner />
      
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
                {categories.map((cat) => (
                  <li key={cat.name}>
                    <button
                      onClick={() => { setSelectedCategory(cat.name); if (cat.name === 'All') { setSearchQuery(''); setSelectedPriceRange(null); } }}
                      className={`w-full flex items-center justify-between text-[13px] font-bold transition-colors ${
                        selectedCategory === cat.name
                          ? 'text-black'
                          : 'text-zinc-500 hover:text-black'
                      }`}
                    >
                      <span>{cat.name}</span>
                      <span
                        className={`px-2 py-0.5 rounded text-[9px] font-bold ${
                          selectedCategory === cat.name
                            ? 'bg-cyan-500 text-white'
                            : 'bg-zinc-100 text-zinc-500'
                        }`}
                      >
                        {cat.count}
                      </span>
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            {/* Price Filters */}
            <div>
              <h4 className="text-[9px] font-bold text-cyan-600 uppercase tracking-[2.7px] mb-6">
                Price
              </h4>
              <div className="space-y-3">
                {priceRanges.map((range, idx) => (
                  <label key={idx} className="flex items-center gap-3 cursor-pointer">
                    <input
                      type="radio"
                      name="price"
                      checked={selectedPriceRange === idx}
                      onChange={() => setSelectedPriceRange(selectedPriceRange === idx ? null : idx)}
                      className="w-[15px] h-[15px] accent-cyan-600 border border-zinc-400 rounded-full"
                    />
                    <span className="text-[12px] text-zinc-600">{range.label}</span>
                  </label>
                ))}
              </div>
            </div>
          </aside>

          {/* Main Content */}
          <main className="flex-1">
            {/* Sort & Search Controls */}
            <div className="bg-zinc-50 border border-zinc-100 rounded-[15px] p-6 mb-8 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <span className="text-[8px] font-bold text-zinc-600 uppercase tracking-[0.8px]">
                  Sort:
                </span>
                <select
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value as any)}
                  className="bg-transparent text-[12px] font-bold text-cyan-600 uppercase tracking-tight focus:outline-none cursor-pointer"
                >
                  <option value="newest">Newest</option>
                  <option value="price-asc">Price: Low to High</option>
                  <option value="price-desc">Price: High to Low</option>
                </select>
              </div>
              <div className="relative flex-1 max-w-[350px]">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-[15px] h-[15px] text-zinc-400" />
                <input
                  type="text"
                  placeholder="Search products..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-400 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
              </div>
            </div>

            {/* Latest Products */}
            {!searchQuery && selectedCategory === 'All' && selectedPriceRange === null && latestProducts.length > 0 && (
              <div className="mb-12">
                <div className="flex items-center gap-4 mb-8">
                  <h3 className="text-[22px] font-bold italic tracking-tight">LATEST</h3>
                  <span className="text-[21px] font-bold italic text-zinc-400 tracking-tight">
                    PRODUCTS
                  </span>
                  <div className="flex-1 h-[2px] bg-zinc-100" />
                </div>
                <div className="grid grid-cols-3 gap-8">
                  {latestProducts.map(product => (
                    <ProductCard key={product.id} product={product} />
                  ))}
                </div>
              </div>
            )}

            {/* All Products */}
            <div>
              <div className="flex items-center gap-4 mb-8">
                <h3 className="text-[22px] font-bold italic tracking-tight">ALL</h3>
                <span className="text-[21px] font-bold italic text-zinc-400 tracking-tight">
                  PRODUCTS
                </span>
                <div className="flex-1 h-[2px] bg-zinc-100" />
              </div>
              {filteredProducts.length > 0 ? (
                <div className="grid grid-cols-3 gap-8">
                  {filteredProducts.map(product => (
                    <ProductCard key={product.id} product={product} showDetails />
                  ))}
                </div>
              ) : (
                <div className="text-center py-16">
                  <p className="text-zinc-500">No products found matching your criteria.</p>
                </div>
              )}
            </div>

            {/* See More Button */}
            {filteredProducts.length > 6 && (
              <div className="flex justify-center mt-12">
                <button className="text-[11px] font-bold text-black uppercase tracking-[1.65px] hover:text-cyan-600 transition-colors">
                  SEE MORE PRODUCTS →
                </button>
              </div>
            )}
          </main>
        </div>
      </section>

      {/* Footer */}
      <Footer />
    </div>
  );
}