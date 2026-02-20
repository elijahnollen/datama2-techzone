import { useState, useEffect } from 'react';
import { Search, Eye, AlertCircle } from 'lucide-react';

interface ReturnRequest {
  id: string;
  orderId: string;
  customer: string;
  product: string;
  reason: string;
  amount: number | string;
  status: string;
  date: string;
}

export function ReturnManagement() {
  const [returns, setReturns] = useState<ReturnRequest[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('All');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchReturns();
  }, []);

  const fetchReturns = async () => {
    try {
      const response = await fetch('http://localhost/api/get_returns.php');
      const data = await response.json();
      
      if (Array.isArray(data)) {
        setReturns(data);
      }
    } catch (error) {
      console.error("Connection Error:", error);
    } finally {
      setLoading(false);
    }
  };

  const filteredReturns = returns.filter(ret => {
    const id = ret.id?.toLowerCase() || '';
    const customer = ret.customer?.toLowerCase() || '';
    const orderId = ret.orderId?.toLowerCase() || '';
    const search = searchTerm.toLowerCase();

    const matchesSearch = id.includes(search) || customer.includes(search) || orderId.includes(search);
    
    const matchesStatus = statusFilter === 'All' || 
      ret.status?.toLowerCase() === statusFilter.toLowerCase();
      
    return matchesSearch && matchesStatus;
  });

  const updateReturnStatus = async (returnId: string, newStatus: string) => {
    try {
      const response = await fetch('http://localhost/api/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: returnId, status: newStatus })
      });
      
      const result = await response.json();
      if (result.success) {
        fetchReturns();
      }
    } catch (error) {
      console.error("Update Error:", error);
    }
  };

  const getStatusColor = (status: string) => {
    const s = status?.toLowerCase();
    switch (s) {
      case 'pending': return 'bg-yellow-100 text-yellow-700';
      case 'refunded': return 'bg-green-100 text-green-700';
      case 'replaced': return 'bg-purple-100 text-purple-700';
      case 'store credit': return 'bg-orange-100 text-orange-700';
      default: return 'bg-gray-100 text-gray-700';
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Return & Refund Management</h1>
        <p className="text-gray-600 mt-1">Process customer return requests and manage refunds</p>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        {['Pending', 'Refunded', 'Replaced', 'Store Credit'].map((status) => (
          <button
            key={status}
            onClick={() => setStatusFilter(status)}
            className={`p-4 rounded-lg border-2 transition-all text-left ${
              statusFilter === status 
                ? 'border-blue-600 bg-blue-50' 
                : 'border-gray-200 bg-white hover:border-gray-300'
            }`}
          >
            <div className="text-xl font-bold text-gray-900">
              {returns.filter(r => r.status?.toLowerCase() === status.toLowerCase()).length}
            </div>
            <div className="text-xs text-gray-600 mt-1">{status}</div>
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
              className={`px-4 py-2 rounded-lg transition-colors font-medium ${
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
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200 bg-white">
              {loading ? (
                 <tr><td colSpan={9} className="px-6 py-4 text-center text-gray-500">Connecting to database...</td></tr>
              ) : filteredReturns.length === 0 ? (
                <tr><td colSpan={9} className="px-6 py-4 text-center text-gray-500">No records found.</td></tr>
              ) : filteredReturns.map((returnReq) => (
                <tr key={returnReq.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">{returnReq.id}</td>
                  <td className="px-6 py-4 text-sm text-blue-600">{returnReq.orderId}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">{returnReq.customer}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">{returnReq.product}</td>
                  <td className="px-6 py-4 text-sm text-gray-600">{returnReq.reason}</td>
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">
                    ₱{Number(returnReq.amount).toLocaleString()}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-600">{returnReq.date}</td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(returnReq.status)}`}>
                      {returnReq.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-2">
                      <button className="p-1.5 hover:bg-gray-100 rounded text-gray-500" title="View Details">
                        <Eye className="w-4 h-4" />
                      </button>
                      
                      {returnReq.status?.toLowerCase() === 'pending' && (
                        <div className="flex gap-1">
                          <button 
                            onClick={() => updateReturnStatus(returnReq.id, 'Refunded')} 
                            className="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded hover:bg-green-200"
                          >
                            Refund
                          </button>
                          <button 
                            onClick={() => updateReturnStatus(returnReq.id, 'Replaced')} 
                            className="px-2 py-1 bg-purple-100 text-purple-700 text-xs font-medium rounded hover:bg-purple-200"
                          >
                            Replace
                          </button>
                          <button 
                            onClick={() => updateReturnStatus(returnReq.id, 'Store Credit')} 
                            className="px-2 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded hover:bg-orange-200"
                          >
                            Credit
                          </button>
                        </div>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
