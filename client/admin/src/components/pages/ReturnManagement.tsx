import { useState } from 'react';
import { Search, Check, X, Eye, AlertCircle } from 'lucide-react';

interface ReturnRequest {
  id: string;
  orderId: string;
  customer: string;
  product: string;
  reason: string;
  amount: number;
  status: 'Pending' | 'Approved' | 'Rejected' | 'Refunded';
  requestDate: string;
  images?: string[];
}

const mockReturns: ReturnRequest[] = [
  { 
    id: 'RET-001', 
    orderId: 'ORD-045', 
    customer: 'Juan Dela Cruz', 
    product: 'ROG Strix G16', 
    reason: 'Defective - Screen flickering issue', 
    amount: 75000, 
    status: 'Pending', 
    requestDate: '2024-02-10' 
  },
  { 
    id: 'RET-002', 
    orderId: 'ORD-038', 
    customer: 'Maria Santos', 
    product: 'Logitech G Pro', 
    reason: 'Not as described - Wrong color received', 
    amount: 8500, 
    status: 'Approved', 
    requestDate: '2024-02-09' 
  },
  { 
    id: 'RET-003', 
    orderId: 'ORD-042', 
    customer: 'Pedro Reyes', 
    product: 'RTX 4080', 
    reason: 'Changed mind - Found better price elsewhere', 
    amount: 55000, 
    status: 'Rejected', 
    requestDate: '2024-02-08' 
  },
  { 
    id: 'RET-004', 
    orderId: 'ORD-031', 
    customer: 'Ana Garcia', 
    product: 'LG Monitor', 
    reason: 'Damaged in shipping - Cracked screen', 
    amount: 22000, 
    status: 'Refunded', 
    requestDate: '2024-02-07' 
  },
];

export function ReturnManagement() {
  const [returns, setReturns] = useState<ReturnRequest[]>(mockReturns);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('All');

  const filteredReturns = returns.filter(ret => {
    const matchesSearch = ret.id.toLowerCase().includes(searchTerm.toLowerCase()) ||
      ret.customer.toLowerCase().includes(searchTerm.toLowerCase()) ||
      ret.orderId.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesStatus = statusFilter === 'All' || ret.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  const updateReturnStatus = (returnId: string, newStatus: ReturnRequest['status']) => {
    setReturns(returns.map(ret => 
      ret.id === returnId ? { ...ret, status: newStatus } : ret
    ));
  };

  const getStatusColor = (status: ReturnRequest['status']) => {
    switch (status) {
      case 'Pending': return 'bg-yellow-100 text-yellow-700';
      case 'Approved': return 'bg-blue-100 text-blue-700';
      case 'Rejected': return 'bg-red-100 text-red-700';
      case 'Refunded': return 'bg-green-100 text-green-700';
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Return & Refund Management</h1>
        <p className="text-gray-600 mt-1">Process customer return requests and manage refunds</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        {[
          { label: 'Pending', count: returns.filter(r => r.status === 'Pending').length, status: 'Pending', color: 'yellow' },
          { label: 'Approved', count: returns.filter(r => r.status === 'Approved').length, status: 'Approved', color: 'blue' },
          { label: 'Rejected', count: returns.filter(r => r.status === 'Rejected').length, status: 'Rejected', color: 'red' },
          { label: 'Refunded', count: returns.filter(r => r.status === 'Refunded').length, status: 'Refunded', color: 'green' },
        ].map((stat) => (
          <button
            key={stat.status}
            onClick={() => setStatusFilter(stat.status)}
            className={`p-4 rounded-lg border-2 transition-all text-left ${
              statusFilter === stat.status 
                ? 'border-blue-600 bg-blue-50' 
                : 'border-gray-200 bg-white hover:border-gray-300'
            }`}
          >
            <div className="text-2xl font-bold text-gray-900">{stat.count}</div>
            <div className="text-sm text-gray-600 mt-1">{stat.label} Returns</div>
          </button>
        ))}
      </div>

      <div className="bg-white rounded-lg border border-gray-200">
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search returns..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <button 
              onClick={() => setStatusFilter('All')}
              className={`px-4 py-2 rounded-lg transition-colors ${
                statusFilter === 'All' 
                  ? 'bg-blue-600 text-white' 
                  : 'border border-gray-200 text-gray-700 hover:bg-gray-50'
              }`}
            >
              All Returns
            </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50 border-b border-gray-200">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Return ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Refund Amount</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {filteredReturns.map((returnReq) => (
                <tr key={returnReq.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">{returnReq.id}</td>
                  <td className="px-6 py-4 text-sm text-blue-600 hover:text-blue-700 cursor-pointer">
                    {returnReq.orderId}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-900">{returnReq.customer}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">{returnReq.product}</td>
                  <td className="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title={returnReq.reason}>
                    {returnReq.reason}
                  </td>
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">
                    ₱{returnReq.amount.toLocaleString()}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-600">{returnReq.requestDate}</td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(returnReq.status)}`}>
                      {returnReq.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-2">
                      <button className="p-1.5 hover:bg-blue-50 rounded text-blue-600" title="View Details">
                        <Eye className="w-4 h-4" />
                      </button>
                      {returnReq.status === 'Pending' && (
                        <>
                          <button 
                            onClick={() => updateReturnStatus(returnReq.id, 'Approved')}
                            className="p-1.5 hover:bg-green-50 rounded text-green-600" 
                            title="Approve"
                          >
                            <Check className="w-4 h-4" />
                          </button>
                          <button 
                            onClick={() => updateReturnStatus(returnReq.id, 'Rejected')}
                            className="p-1.5 hover:bg-red-50 rounded text-red-600" 
                            title="Reject"
                          >
                            <X className="w-4 h-4" />
                          </button>
                        </>
                      )}
                      {returnReq.status === 'Approved' && (
                        <button 
                          onClick={() => updateReturnStatus(returnReq.id, 'Refunded')}
                          className="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700" 
                          title="Process Refund"
                        >
                          Process Refund
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="p-4 border-t border-gray-200">
          <div className="flex items-center gap-2 text-sm text-gray-600">
            <AlertCircle className="w-4 h-4" />
            <span>Pending returns require approval within 48 hours of submission</span>
          </div>
        </div>
      </div>
    </div>
  );
}
