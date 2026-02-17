import { Product } from '../types';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../contexts/AuthContext';
import { Link } from 'react-router';
import { useState } from 'react';
import { AuthModal } from './AuthModal';

interface ProductCardProps {
  product: Product;
  showDetails?: boolean;
  viewMode?: 'grid' | 'list';
}

export function ProductCard({ product, showDetails = false, viewMode = 'grid' }: ProductCardProps) {
  const { addToCart } = useCart();
  const { isAuthenticated, user } = useAuth();
  const [added, setAdded] = useState(false);
  const [showAuthModal, setShowAuthModal] = useState(false);

  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (isAuthenticated) {
      addToCart(product);
      setAdded(true);
      setTimeout(() => setAdded(false), 1500);
    } else {
      setShowAuthModal(true);
    }
  };

  if (viewMode === 'list') {
    return (
      <>
        <Link 
          to={`/product/${product.id}`}
          className="bg-white rounded-lg border border-zinc-200 overflow-hidden shadow-sm hover:shadow-lg transition-shadow flex group"
        >
          <div className="relative w-48 flex-shrink-0">
            {product.isNew && (
              <div className="absolute top-4 left-4 bg-black text-white text-[9px] font-bold uppercase tracking-[0.9px] px-2 py-1 rounded z-10">
                New Arrival
              </div>
            )}
            <div className="h-full bg-zinc-50 flex items-center justify-center p-4">
              <img
                src={product.image}
                alt={product.name}
                className="w-full h-full object-contain group-hover:scale-105 transition-transform"
              />
            </div>
          </div>
          <div className="flex-1 p-6 flex items-center">
            <div className="flex-1">
              <div className="flex items-start gap-4 mb-2">
                <h3 className="font-bold text-lg text-black flex-1">{product.name}</h3>
                {product.available && (
                  <span className="text-xs font-bold text-cyan-600 border border-cyan-600 rounded px-2 py-1 uppercase">
                    Available
                  </span>
                )}
              </div>
              {product.description && (
                <p className="text-sm text-zinc-600 mb-4 line-clamp-2">
                  {product.description}
                </p>
              )}
              <div className="flex items-center gap-4">
                <span className="text-2xl font-bold text-cyan-600">₱{product.price.toLocaleString()}</span>
                <button
                  onClick={handleAddToCart}
                  disabled={added}
                  className={`text-xs font-bold uppercase tracking-wider px-6 py-2 rounded transition-colors ${
                    added 
                      ? 'bg-green-500 text-white' 
                      : 'bg-cyan-500 text-black hover:bg-cyan-600'
                  }`}
                >
                  {added ? '✓ Added' : 'Add to Cart'}
                </button>
              </div>
            </div>
          </div>
        </Link>
        
        <AuthModal
          isOpen={showAuthModal}
          onClose={() => setShowAuthModal(false)}
        />
      </>
    );
  }

  return (
    <>
      <Link 
        to={`/product/${product.id}`}
        className="bg-white rounded-[11px] border border-zinc-200 overflow-hidden shadow-sm hover:shadow-lg transition-shadow block group"
      >
        <div className="relative">
          {product.isNew && (
            <div className="absolute top-4 left-4 bg-black text-white text-[9px] font-bold uppercase tracking-[0.9px] px-2 py-1 rounded z-10">
              New Arrival
            </div>
          )}
          <div className="aspect-[4/3] bg-zinc-50 flex items-center justify-center p-8">
            <img
              src={product.image}
              alt={product.name}
              className="w-full h-full object-contain group-hover:scale-105 transition-transform"
            />
          </div>
        </div>
        <div className="p-6">
          <div className="flex items-start justify-between mb-2">
            <h3 className="font-bold text-[16px] text-black leading-[21px] flex-1 pr-2">{product.name}</h3>
            {product.available && showDetails && (
              <span className="text-[9px] font-bold text-cyan-600 border border-cyan-600 rounded px-2 py-1 uppercase tracking-[0.5px] flex-shrink-0">
                Available
              </span>
            )}
          </div>
          {showDetails && product.description && (
            <p className="text-[10px] text-zinc-600 leading-[15px] mb-4 line-clamp-2">
              {product.description}
            </p>
          )}
          <div className="flex items-center justify-between mt-4">
            <span className="text-[19px] font-bold text-cyan-600">₱{product.price.toLocaleString()}</span>
            <button
              onClick={handleAddToCart}
              disabled={added}
              className={`text-[10px] font-bold uppercase tracking-[1px] px-4 py-2 rounded transition-colors ${
                added 
                  ? 'bg-green-500 text-white' 
                  : 'bg-cyan-500 text-black hover:bg-cyan-600'
              }`}
            >
              {added ? '✓ Added' : 'Add to Cart'}
            </button>
          </div>
        </div>
      </Link>
      
      <AuthModal
        isOpen={showAuthModal}
        onClose={() => setShowAuthModal(false)}
      />
    </>
  );
}