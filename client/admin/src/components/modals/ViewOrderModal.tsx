import { X, User, Package, MapPin, CreditCard } from 'lucide-react';

interface ViewOrderModalProps {
  isOpen: boolean;
  onClose: () => void;
  order: any;
}

export function ViewOrderModal({ isOpen, onClose, order }: ViewOrderModalProps) {
  if (!isOpen || !order) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto">
      <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {/* Background overlay */}
        <div 
          className="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
          onClick={onClose}
        ></div>

        {/* Modal panel */}
        <div className="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
          {/* Header */}
          <div className="bg-white px-6 py-4 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-lg font-semibold text-gray-900">Order Details</h3>
                <p className="text-sm text-gray-600 mt-1">{order.id}</p>
              </div>
              <button
                onClick={onClose}
                className="text-gray-400 hover:text-gray-500 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
          </div>

          {/* Content */}
          <div className="bg-white px-6 py-4 max-h-96 overflow-y-auto">
            <div className="space-y-6">
              {/* Customer Info */}
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <User className="w-5 h-5 text-gray-400" />
                  <h4 className="font-medium text-gray-900">Customer Information</h4>
                </div>
                <div className="bg-gray-50 rounded-lg p-4 space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Name:</span>
                    <span className="text-sm font-medium text-gray-900">{order.customer}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Email:</span>
                    <span className="text-sm font-medium text-gray-900">{order.email}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Order Date:</span>
                    <span className="text-sm font-medium text-gray-900">{order.date}</span>
                  </div>
                </div>
              </div>

              {/* Products */}
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <Package className="w-5 h-5 text-gray-400" />
                  <h4 className="font-medium text-gray-900">Order Items</h4>
                </div>
                <div className="bg-gray-50 rounded-lg p-4">
                  <div className="space-y-3">
                    <div className="flex justify-between items-start">
                      <div>
                        <p className="font-medium text-gray-900">{order.products}</p>
                        <p className="text-sm text-gray-600">Quantity: 1</p>
                      </div>
                      <span className="font-medium text-gray-900">{order.total.toLocaleString()}</span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Shipping Info */}
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <MapPin className="w-5 h-5 text-gray-400" />
                  <h4 className="font-medium text-gray-900">Shipping Address</h4>
                </div>
                <div className="bg-gray-50 rounded-lg p-4">
                  <p className="text-sm text-gray-900">
                    123 Main Street, Barangay San Antonio<br />
                    Makati City, Metro Manila 1203<br />
                    Philippines
                  </p>
                </div>
              </div>

              {/* Payment Info */}
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <CreditCard className="w-5 h-5 text-gray-400" />
                  <h4 className="font-medium text-gray-900">Payment Information</h4>
                </div>
                <div className="bg-gray-50 rounded-lg p-4 space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Payment Method:</span>
                    <span className="text-sm font-medium text-gray-900">Credit Card</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Payment Status:</span>
                    <span className="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                      Paid
                    </span>
                  </div>
                  <div className="flex justify-between pt-2 border-t border-gray-200">
                    <span className="font-medium text-gray-900">Total Amount:</span>
                    <span className="font-bold text-gray-900">₱{order.total.toLocaleString()}</span>
                  </div>
                </div>
              </div>

              {/* Order Status */}
              <div>
                <h4 className="font-medium text-gray-900 mb-3">Order Status</h4>
                <div className="bg-gray-50 rounded-lg p-4">
                  <span className={`inline-flex px-3 py-1 text-sm font-medium rounded-full ${
                    order.status === 'Completed' ? 'bg-green-100 text-green-700' :
                    order.status === 'Processing' ? 'bg-blue-100 text-blue-700' :
                    order.status === 'Shipped' ? 'bg-purple-100 text-purple-700' :
                    'bg-yellow-100 text-yellow-700'
                  }`}>
                    {order.status}
                  </span>
                </div>
              </div>
            </div>
          </div>

          {/* Footer */}
          <div className="bg-gray-50 px-6 py-4 flex justify-end gap-3">
            <button
              onClick={onClose}
              className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Close
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              Update Status
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
