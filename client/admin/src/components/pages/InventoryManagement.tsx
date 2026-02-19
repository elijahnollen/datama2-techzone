import { useState } from 'react';
import { Search, Plus, AlertTriangle, TrendingDown, Package } from 'lucide-react';
import { AddStockModal } from '../modals/AddStockModal';

interface InventoryItem {
  id: string;
  product: string;
  sku: string;
  stock: number;
  damaged: number;
  reserved: number;
  available: number;
  reorderPoint: number;
  lastUpdated: string;
}

const mockInventory: InventoryItem[] = [
  { id: 'INV001', product: 'ROG Strix G16 (2025) G614', sku: 'LAP-ROG-G16', stock: 12, damaged: 1, reserved: 2, available: 9, reorderPoint: 10, lastUpdated: '2024-02-10' },
  { id: 'INV002', product: 'NVIDIA RTX 4080', sku: 'GPU-RTX-4080', stock: 8, damaged: 0, reserved: 3, available: 5, reorderPoint: 5, lastUpdated: '2024-02-11' },
  { id: 'INV003', product: 'LG UltraGear 27" Monitor', sku: 'MON-LG-27', stock: 15, damaged: 2, reserved: 1, available: 12, reorderPoint: 8, lastUpdated: '2024-02-09' },
  { id: 'INV004', product: 'Logitech G Pro Wireless', sku: 'PER-LOG-GPRO', stock: 25, damaged: 1, reserved: 5, available: 19, reorderPoint: 15, lastUpdated: '2024-02-10' },
  { id: 'INV005', product: 'Corsair Vengeance RAM 32GB', sku: 'RAM-COR-32GB', stock: 5, damaged: 0, reserved: 2, available: 3, reorderPoint: 15, lastUpdated: '2024-02-11' },
];

interface InventoryLog {
  id: string;
  product: string;
  type: 'Stock In' | 'Stock Out' | 'Damaged' | 'Returned';
  quantity: number;
  date: string;
  notes: string;
}

const mockLogs: InventoryLog[] = [
  { id: 'LOG001', product: 'ROG Strix G16', type: 'Stock In', quantity: 10, date: '2024-02-10 10:30', notes: 'New shipment from supplier' },
  { id: 'LOG002', product: 'RTX 4080', type: 'Stock Out', quantity: 2, date: '2024-02-10 14:15', notes: 'Sold - Order #ORD-045' },
  { id: 'LOG003', product: 'LG Monitor', type: 'Damaged', quantity: 1, date: '2024-02-09 16:20', notes: 'Screen crack during shipping' },
  { id: 'LOG004', product: 'Logitech G Pro', type: 'Returned', quantity: 1, date: '2024-02-09 11:00', notes: 'Customer return - defective' },
];

export function InventoryManagement() {
  const [inventory, setInventory] = useState<InventoryItem[]>(mockInventory);
  const [logs, setLogs] = useState<InventoryLog[]>(mockLogs);
  const [searchTerm, setSearchTerm] = useState('');
  const [activeTab, setActiveTab] = useState<'inventory' | 'logs'>('inventory');
  const [showAddModal, setShowAddModal] = useState(false);

  const filteredInventory = inventory.filter(item =>
    item.product.toLowerCase().includes(searchTerm.toLowerCase()) ||
    item.sku.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const lowStockItems = inventory.filter(item => item.available <= item.reorderPoint);

  const handleAddStock = (stockData: any) => {
    const newLog: InventoryLog = {
      id: `LOG${String(logs.length + 1).padStart(3, '0')}`,
      product: stockData.product,
      type: stockData.type,
      quantity: Number(stockData.quantity),
      date: new Date().toLocaleString(),
      notes: stockData.notes
    };
    setLogs([newLog, ...logs]);
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Inventory Management</h1>
        <p className="text-gray-600 mt-1">Track stock levels, manage inventory, and monitor product movements</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
              <Package className="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <div className="text-2xl font-bold text-gray-900">
                {inventory.reduce((sum, item) => sum + item.stock, 0)}
              </div>
              <div className="text-sm text-gray-600">Total Items</div>
            </div>
          </div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
              <Package className="w-5 h-5 text-green-600" />
            </div>
            <div>
              <div className="text-2xl font-bold text-green-600">
                {inventory.reduce((sum, item) => sum + item.available, 0)}
              </div>
              <div className="text-sm text-gray-600">Available</div>
            </div>
          </div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
              <AlertTriangle className="w-5 h-5 text-red-600" />
            </div>
            <div>
              <div className="text-2xl font-bold text-red-600">{lowStockItems.length}</div>
              <div className="text-sm text-gray-600">Low Stock Alerts</div>
            </div>
          </div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
              <TrendingDown className="w-5 h-5 text-orange-600" />
            </div>
            <div>
              <div className="text-2xl font-bold text-orange-600">
                {inventory.reduce((sum, item) => sum + item.damaged, 0)}
              </div>
              <div className="text-sm text-gray-600">Damaged Items</div>
            </div>
          </div>
        </div>
      </div>

      {/* Add Stock Modal */}
      <AddStockModal 
        isOpen={showAddModal}
        onClose={() => setShowAddModal(false)}
        onSubmit={handleAddStock}
      />

      <div className="bg-white rounded-lg border border-gray-200">
        <div className="border-b border-gray-200">
          <div className="flex gap-4 px-6">
            <button
              onClick={() => setActiveTab('inventory')}
              className={`py-4 px-2 border-b-2 font-medium text-sm transition-colors ${
                activeTab === 'inventory'
                  ? 'border-blue-600 text-blue-600'
                  : 'border-transparent text-gray-600 hover:text-gray-900'
              }`}
            >
              Inventory Overview
            </button>
            <button
              onClick={() => setActiveTab('logs')}
              className={`py-4 px-2 border-b-2 font-medium text-sm transition-colors ${
                activeTab === 'logs'
                  ? 'border-blue-600 text-blue-600'
                  : 'border-transparent text-gray-600 hover:text-gray-900'
              }`}
            >
              Inventory Logs
            </button>
          </div>
        </div>

        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search inventory..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <button 
              onClick={() => setShowAddModal(true)}
              className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus className="w-5 h-5" />
              Add Stock Entry
            </button>
          </div>
        </div>

        {activeTab === 'inventory' ? (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Stock</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reserved</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Damaged</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Updated</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {filteredInventory.map((item) => (
                  <tr key={item.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 text-sm font-medium text-gray-900">{item.sku}</td>
                    <td className="px-6 py-4 text-sm text-gray-900">{item.product}</td>
                    <td className="px-6 py-4 text-sm text-gray-900">{item.stock}</td>
                    <td className="px-6 py-4 text-sm">
                      <span className={`font-medium ${item.available <= item.reorderPoint ? 'text-red-600' : 'text-green-600'}`}>
                        {item.available}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600">{item.reserved}</td>
                    <td className="px-6 py-4 text-sm">
                      <span className={item.damaged > 0 ? 'text-orange-600 font-medium' : 'text-gray-600'}>
                        {item.damaged}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      {item.available <= item.reorderPoint ? (
                        <span className="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                          <AlertTriangle className="w-3 h-3" />
                          Low Stock
                        </span>
                      ) : (
                        <span className="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                          In Stock
                        </span>
                      )}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600">{item.lastUpdated}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Log ID</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {logs.map((log) => (
                  <tr key={log.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 text-sm font-medium text-gray-900">{log.id}</td>
                    <td className="px-6 py-4 text-sm text-gray-900">{log.product}</td>
                    <td className="px-6 py-4">
                      <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${
                        log.type === 'Stock In' ? 'bg-green-100 text-green-700' :
                        log.type === 'Stock Out' ? 'bg-blue-100 text-blue-700' :
                        log.type === 'Damaged' ? 'bg-red-100 text-red-700' :
                        'bg-purple-100 text-purple-700'
                      }`}>
                        {log.type}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-900">{log.quantity}</td>
                    <td className="px-6 py-4 text-sm text-gray-600">{log.date}</td>
                    <td className="px-6 py-4 text-sm text-gray-600">{log.notes}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}