import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router';
import { ArrowLeft, Package } from 'lucide-react';
import { Header } from '../components/Header';
import Footer from '../imports/Footer-4-7106';
import { getAllProducts } from '../services/database';
import { Product } from '../types';
import { ReviewCard } from '../components/ReviewCard';

type OrderStatus = 'delivered' | 'processing' | 'cancelled' | 'returned';
type FilterType = 'all' | 'unpaid' | 'processing' | 'shipped' | 'to-review' | 'returned';

interface Order {
  id: string;
  orderNumber: string;
  date: string;
  status: OrderStatus;
  productId: string;
  quantity: number;
  price: number;
}

const mockOrders: Order[] = [
  {
    id: '1',
    orderNumber: '#77-9865',
    date: 'December 20, 2024',
    status: 'delivered',
    productId: 'gpu-1',
    quantity: 1,
    price: 9890.00,
  },
  {
    id: '2',
    orderNumber: '#77-6512',
    date: 'December 18, 2024',
    status: 'returned',
    productId: 'peripheral-1',
    quantity: 1,
    price: 1185.00,
  },
  {
    id: '3',
    orderNumber: '#77-1702',
    date: 'December 10, 2024',
    status: 'delivered',
    productId: 'cooling-1',
    quantity: 1,
    price: 14050.00,
  },
  {
    id: '4',
    orderNumber: '#77-u856',
    date: 'December 6, 2024',
    status: 'cancelled',
    productId: 'cooling-2',
    quantity: 1,
    price: 2210.00,
  },
  {
    id: '5',
    orderNumber: '#77-7729',
    date: 'December 1, 2024',
    status: 'processing',
    productId: 'processor-1',
    quantity: 1,
    price: 9000.00,
  },
];

export function MyOrders() {
  const [activeFilter, setActiveFilter] = useState<FilterType>('all');
  const [reviewingOrder, setReviewingOrder] = useState<Order | null>(null);
  const navigate = useNavigate();
  const [products, setProducts] = useState<Product[]>([]);

  useEffect(() => {
    const fetchProducts = async () => {
      const fetchedProducts = await getAllProducts();
      setProducts(fetchedProducts);
    };

    fetchProducts();
  }, []);

  const getStatusColor = (status: OrderStatus) => {
    switch (status) {
      case 'delivered':
        return 'bg-green-50 text-green-600 border-green-200';
      case 'returned':
        return 'bg-cyan-50 text-cyan-600 border-cyan-200';
      case 'cancelled':
        return 'bg-red-50 text-red-600 border-red-200';
      case 'processing':
        return 'bg-yellow-50 text-yellow-600 border-yellow-200';
      default:
        return 'bg-zinc-50 text-zinc-600 border-zinc-200';
    }
  };

  const getStatusText = (status: OrderStatus) => {
    return status.toUpperCase();
  };

  const handleWriteReview = (order: Order) => {
    setReviewingOrder(order);
  };

  const handleReturnItem = () => {
    navigate('/return-request');
  };

  const handleSubmitReview = (rating: number, review: string) => {
    console.log('Review submitted:', { rating, review, orderId: reviewingOrder?.id });
    // Here you would typically send the review to your backend
  };

  const getActionButtons = (status: OrderStatus, order: Order) => {
    switch (status) {
      case 'delivered':
        return (
          <>
            <button
              onClick={() => handleWriteReview(order)}
              className="bg-black text-white font-bold text-[10px] uppercase tracking-wider px-5 py-2.5 rounded-full hover:bg-zinc-800 transition-colors"
            >
              Write Review
            </button>
            <button
              onClick={handleReturnItem}
              className="border border-zinc-300 text-zinc-600 font-bold text-[10px] uppercase tracking-wider px-5 py-2.5 rounded-full hover:bg-zinc-50 transition-colors"
            >
              Return Item
            </button>
          </>
        );
      case 'returned':
      case 'cancelled':
        return (
          <button className="bg-cyan-500 text-black font-bold text-[10px] uppercase tracking-wider px-6 py-2.5 rounded-full hover:bg-cyan-600 transition-colors">
            Reorder
          </button>
        );
      case 'processing':
        return (
          <button className="bg-cyan-500 text-black font-bold text-[10px] uppercase tracking-wider px-6 py-2.5 rounded-full hover:bg-cyan-600 transition-colors">
            Track Order
          </button>
        );
      default:
        return null;
    }
  };

  const filters: { label: string; value: FilterType }[] = [
    { label: 'All Orders', value: 'all' },
    { label: 'Unpaid', value: 'unpaid' },
    { label: 'Processing', value: 'processing' },
    { label: 'Shipped', value: 'shipped' },
    { label: 'To Review', value: 'to-review' },
    { label: 'Returned', value: 'returned' },
  ];

  const filteredOrders = mockOrders.filter(order => {
    if (activeFilter === 'all') return true;
    if (activeFilter === 'processing') return order.status === 'processing';
    if (activeFilter === 'returned') return order.status === 'returned';
    return true;
  });

  const recommendedProducts = products.slice(0, 6);

  return (
    <div className="min-h-screen bg-white flex flex-col">
      {/* Navbar */}
      <Header />

      {/* Main Content */}
      <div className="flex-1 max-w-[1400px] mx-auto px-6 py-12 w-full">
        {/* Back Link */}
        <Link
          to="/"
          className="flex items-center gap-2 text-zinc-500 hover:text-black mb-8 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="font-bold uppercase tracking-wider text-xs">Back to Home</span>
        </Link>

        {/* Page Header */}
        <div className="mb-8">
          <h1 className="text-[40px] font-bold italic leading-tight mb-2">
            <span className="text-black">MY </span>
            <span className="text-cyan-500">ORDERS</span>
          </h1>
          <p className="text-zinc-500 text-sm">
            Track, review, and manage your TechZone purchases
          </p>
        </div>

        {/* Filter Tabs */}
        <div className="flex gap-3 mb-8 border-b border-zinc-200 pb-4">
          {filters.map((filter) => (
            <button
              key={filter.value}
              onClick={() => setActiveFilter(filter.value)}
              className={`font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-full transition-colors ${
                activeFilter === filter.value
                  ? 'bg-black text-white'
                  : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
              }`}
            >
              {filter.label}
            </button>
          ))}
        </div>

        {/* Orders List */}
        <div className="space-y-6 mb-16">
          {filteredOrders.map((order) => {
            const product = products.find(p => p.id === order.productId);
            if (!product) return null;

            return (
              <div
                key={order.id}
                className="bg-white border border-zinc-200 rounded-2xl p-6"
              >
                {/* Order Header */}
                <div className="flex items-center justify-between mb-4 pb-4 border-b border-zinc-100">
                  <div className="flex items-center gap-3">
                    <span className="font-bold text-sm text-zinc-800">
                      ORDER {order.orderNumber}
                    </span>
                    <span className="text-xs text-zinc-400">
                      {order.date}
                    </span>
                  </div>
                  <span
                    className={`font-bold text-[10px] uppercase tracking-wider px-4 py-1.5 rounded-full border ${getStatusColor(
                      order.status
                    )}`}
                  >
                    {getStatusText(order.status)}
                  </span>
                </div>

                {/* Order Content */}
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-6">
                    {/* Product Image */}
                    <div className="w-24 h-24 bg-zinc-50 rounded-lg flex items-center justify-center p-3">
                      <img
                        src={product.image}
                        alt={product.name}
                        className="w-full h-full object-contain"
                      />
                    </div>

                    {/* Product Info */}
                    <div>
                      <h3 className="font-bold text-base mb-1">{product.name}</h3>
                      <p className="text-xs text-zinc-500 mb-2">
                        Qty: {order.quantity} {order.quantity > 1 ? 'pieces' : 'piece'}
                      </p>
                      {order.status === 'returned' && (
                        <span className="inline-flex items-center text-[10px] font-bold text-cyan-600 uppercase tracking-wider">
                          <Package className="w-3 h-3 mr-1" />
                          In Transit
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Price and Actions */}
                  <div className="flex items-center gap-6">
                    <div className="text-right">
                      <p className="text-2xl font-bold text-black">
                        ₱{order.price.toLocaleString()}
                      </p>
                    </div>

                    <div className="flex gap-3">
                      {getActionButtons(order.status, order)}
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        {/* View Order History */}
        <div className="text-center mb-16">
          <button className="inline-flex items-center gap-2 text-black font-bold text-sm uppercase tracking-wider hover:text-cyan-600 transition-colors">
            <span>View Order History</span>
            <span className="text-lg">⟳</span>
          </button>
        </div>

        {/* You May Also Like */}
        <div>
          <h2 className="text-2xl font-bold mb-2">
            <span className="text-black">YOU MAY ALSO </span>
            <span className="text-cyan-500">LIKE</span>
          </h2>
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mt-8">
            {recommendedProducts.map((product) => (
              <Link
                key={product.id}
                to={`/product/${product.id}`}
                className="bg-white border border-zinc-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow group"
              >
                <div className="aspect-square bg-zinc-50 flex items-center justify-center p-4">
                  <img
                    src={product.image}
                    alt={product.name}
                    className="w-full h-full object-contain group-hover:scale-105 transition-transform"
                  />
                </div>
                <div className="p-3">
                  <h3 className="font-bold text-xs mb-1 line-clamp-2 min-h-[2.5rem]">
                    {product.name}
                  </h3>
                  <p className="text-sm font-bold text-cyan-600">
                    ₱{product.price.toLocaleString()}
                  </p>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </div>

      {/* Footer */}
      <Footer />

      {/* Review Modal */}
      {reviewingOrder && (
        <ReviewCard
          productName={products.find(p => p.id === reviewingOrder.productId)?.name || ''}
          productImage={products.find(p => p.id === reviewingOrder.productId)?.image || ''}
          onClose={() => setReviewingOrder(null)}
          onSubmit={handleSubmitReview}
        />
      )}
    </div>
  );
}