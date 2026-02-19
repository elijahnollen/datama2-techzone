import { useState } from 'react';
import { Search, Eye, Check, X, Truck } from 'lucide-react';
import { ViewOrderModal } from '../modals/ViewOrderModal';

interface Order {
  id: string;
  customer: string;
  email: string;
  products: string;
  total: number;
  status: 'Pending' | 'Processing' | 'Shipped' | 'Delivered' | 'Cancelled';
  date: string;
}

const mockOrders: Order[] = [
  { id: 'ORD-001', customer: 'Juan Dela Cruz', email: 'juan@email.com', products: 'ROG Strix G16', total: 75000, status: 'Delivered', date: '2024-02-08' },
  { id: 'ORD-002', customer: 'Maria Santos', email: 'maria@email.com', products: 'RTX 4080 Graphics Card', total: 55000, status: 'Processing', date: '2024-02-09' },
  { id: 'ORD-003', customer: 'Pedro Reyes', email: 'pedro@email.com', products: 'LG UltraGear Monitor', total: 22000, status: 'Pending', date: '2024-02-10' },
  { id: 'ORD-004', customer: 'Ana Garcia', email: 'ana@email.com', products: 'Logitech G Pro', total: 8500, status: 'Shipped', date: '2024-02-10' },
  { id: 'ORD-005', customer: 'Carlos Miguel', email: 'carlos@email.com', products: 'Corsair RAM 32GB', total: 12000, status: 'Processing', date: '2024-02-11' },
];

export function OrderManagement() {
  const [orders, setOrders] = useState<Order[]>(mockOrders);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('All');
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [showViewModal, setShowViewModal] = useState(false);

  const filteredOrders = orders.filter(order => {
    const matchesSearch = order.id.toLowerCase().includes(searchTerm.toLowerCase()) ||
      order.customer.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = statusFilter === 'All' || order.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  const updateOrderStatus = (orderId: string, newStatus: Order['status']) => {
    setOrders(orders.map(order => 
      order.id === orderId ? { ...order, status: newStatus } : order
    ));
  };

  const getStatusColor = (status: Order['status']) => {
    switch (status) {
      case 'Pending': return 'bg-yellow-100 text-yellow-700';
      case 'Processing': return 'bg-blue-100 text-blue-700';
      case 'Shipped': return 'bg-purple-100 text-purple-700';
      case 'Delivered': return 'bg-green-100 text-green-700';
      case 'Cancelled': return 'bg-red-100 text-red-700';
    }
  };

  const handleViewOrder = (order: Order) => {
    setSelectedOrder(order);
    setShowViewModal(true);
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Order Management</h1>
        <p className="text-gray-600 mt-1">Track and manage customer orders</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
        {[
          { label: 'All Orders', count: orders.length, status: 'All' },
          { label: 'Pending', count: orders.filter(o => o.status === 'Pending').length, status: 'Pending' },
          { label: 'Processing', count: orders.filter(o => o.status === 'Processing').length, status: 'Processing' },
          { label: 'Shipped', count: orders.filter(o => o.status === 'Shipped').length, status: 'Shipped' },
          { label: 'Delivered', count: orders.filter(o => o.status === 'Delivered').length, status: 'Delivered' },
        ].map((stat) => (
          <button
            key={stat.status}
            onClick={() => setStatusFilter(stat.status)}
            className={`p-4 rounded-lg border-2 transition-all ${
              statusFilter === stat.status 
                ? 'border-blue-600 bg-blue-50' 
                : 'border-gray-200 bg-white hover:border-gray-300'
            }`}
          >
            <div className="text-2xl font-bold text-gray-900">{stat.count}</div>
            <div className="text-sm text-gray-600 mt-1">{stat.label}</div>
          </button>
        ))}
      </div>

      {/* View Order Modal */}
      <ViewOrderModal 
        isOpen={showViewModal}
        onClose={() => setShowViewModal(false)}
        order={selectedOrder}
      />

      <div className="bg-white rounded-lg border border-gray-200">
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search orders..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50 border-b border-gray-200">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Products</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {filteredOrders.map((order) => (
                <tr key={order.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">{order.id}</td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-gray-900">{order.customer}</div>
                    <div className="text-xs text-gray-500">{order.email}</div>
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-600">{order.products}</td>
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">₱{order.total.toLocaleString()}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{order.date}</td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(order.status)}`}>
                      {order.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-2">
                      <button 
                        onClick={() => handleViewOrder(order)}
                        className="p-1.5 hover:bg-blue-50 rounded text-blue-600" 
                        title="View Details"
                      >
                        <Eye className="w-4 h-4" />
                      </button>
                      {order.status === 'Pending' && (
                        <button 
                          onClick={() => updateOrderStatus(order.id, 'Processing')}
                          className="p-1.5 hover:bg-green-50 rounded text-green-600" 
                          title="Approve"
                        >
                          <Check className="w-4 h-4" />
                        </button>
                      )}
                      {order.status === 'Processing' && (
                        <button 
                          onClick={() => updateOrderStatus(order.id, 'Shipped')}
                          className="p-1.5 hover:bg-purple-50 rounded text-purple-600" 
                          title="Mark as Shipped"
                        >
                          <Truck className="w-4 h-4" />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="p-4 border-t border-gray-200 flex items-center justify-between">
          <div className="text-sm text-gray-600">
            Showing {filteredOrders.length} of {orders.length} orders
          </div>
        </div>
      </div>
    </div>
  );
}