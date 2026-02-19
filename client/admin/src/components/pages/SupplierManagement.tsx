import { useState } from 'react';
import { Search, Plus, Edit2, Eye, Phone, Mail, MapPin } from 'lucide-react';
import { AddSupplierModal } from '../modals/AddSupplierModal';

interface Supplier {
  id: string;
  name: string;
  contact: string;
  email: string;
  phone: string;
  address: string;
  products: string[];
  totalOrders: number;
  totalValue: number;
  status: 'Active' | 'Inactive';
  rating: number;
}

const mockSuppliers: Supplier[] = [
  {
    id: 'SUP-001',
    name: 'ASUS Philippines Inc.',
    contact: 'Michael Chen',
    email: 'michael.chen@asus.com.ph',
    phone: '+63 2 8888 4788',
    address: '8F, Summit One Tower, Makati City',
    products: ['Laptops', 'Motherboards', 'Graphics Cards'],
    totalOrders: 45,
    totalValue: 5400000,
    status: 'Active',
    rating: 4.8
  },
  {
    id: 'SUP-002',
    name: 'MSI Technology Philippines',
    contact: 'Sarah Lim',
    email: 'sarah@msi.com.ph',
    phone: '+63 2 7755 0088',
    address: 'Robinsons Cybergate, Mandaluyong City',
    products: ['Gaming Laptops', 'Components'],
    totalOrders: 32,
    totalValue: 3200000,
    status: 'Active',
    rating: 4.6
  },
  {
    id: 'SUP-003',
    name: 'Logitech Distribution Inc.',
    contact: 'David Santos',
    email: 'david@logitech.ph',
    phone: '+63 2 8856 3000',
    address: 'BGC, Taguig City',
    products: ['Peripherals', 'Accessories'],
    totalOrders: 67,
    totalValue: 1800000,
    status: 'Active',
    rating: 4.9
  },
  {
    id: 'SUP-004',
    name: 'Corsair Tech Supply',
    contact: 'Jennifer Reyes',
    email: 'jen@corsair-ph.com',
    phone: '+63 2 8234 5678',
    address: 'Ortigas Center, Pasig City',
    products: ['RAM', 'Power Supply', 'Cooling'],
    totalOrders: 28,
    totalValue: 980000,
    status: 'Active',
    rating: 4.5
  },
];

export function SupplierManagement() {
  const [suppliers, setSuppliers] = useState<Supplier[]>(mockSuppliers);
  const [searchTerm, setSearchTerm] = useState('');
  const [showAddModal, setShowAddModal] = useState(false);

  const filteredSuppliers = suppliers.filter(supplier =>
    supplier.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    supplier.contact.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const handleAddSupplier = (supplierData: any) => {
    const newSupplier: Supplier = {
      id: `SUP-${String(suppliers.length + 1).padStart(3, '0')}`,
      name: supplierData.name,
      contact: supplierData.contact,
      email: supplierData.email,
      phone: supplierData.phone,
      address: supplierData.address,
      products: supplierData.products.split(',').map((p: string) => p.trim()),
      totalOrders: 0,
      totalValue: 0,
      status: supplierData.status as 'Active' | 'Inactive',
      rating: 0
    };
    setSuppliers([...suppliers, newSupplier]);
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Supplier Management</h1>
          <p className="text-gray-600 mt-1">Manage supplier relationships and track orders</p>
        </div>
        <button 
          onClick={() => setShowAddModal(true)}
          className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <Plus className="w-5 h-5" />
          Add Supplier
        </button>
      </div>

      {/* Add Supplier Modal */}
      <AddSupplierModal 
        isOpen={showAddModal}
        onClose={() => setShowAddModal(false)}
        onSubmit={handleAddSupplier}
      />

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="text-2xl font-bold text-gray-900">{suppliers.length}</div>
          <div className="text-sm text-gray-600 mt-1">Total Suppliers</div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="text-2xl font-bold text-green-600">
            {suppliers.filter(s => s.status === 'Active').length}
          </div>
          <div className="text-sm text-gray-600 mt-1">Active Suppliers</div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="text-2xl font-bold text-blue-600">
            {suppliers.reduce((sum, s) => sum + s.totalOrders, 0)}
          </div>
          <div className="text-sm text-gray-600 mt-1">Total Orders</div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="text-2xl font-bold text-blue-600">
            ₱{Math.round(suppliers.reduce((sum, s) => sum + s.totalValue, 0) / 1000000)}M
          </div>
          <div className="text-sm text-gray-600 mt-1">Total Value</div>
        </div>
      </div>

      <div className="bg-white rounded-lg border border-gray-200">
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search suppliers..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
          {filteredSuppliers.map((supplier) => (
            <div key={supplier.id} className="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
              <div className="flex items-start justify-between mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{supplier.name}</h3>
                  <p className="text-sm text-gray-600 mt-1">Supplier ID: {supplier.id}</p>
                </div>
                <div className="flex items-center gap-1">
                  <span className="text-yellow-500">★</span>
                  <span className="text-sm font-medium text-gray-900">{supplier.rating}</span>
                </div>
              </div>

              <div className="space-y-3 mb-4">
                <div className="flex items-start gap-3">
                  <Phone className="w-4 h-4 text-gray-400 mt-0.5" />
                  <div>
                    <div className="text-sm text-gray-900">{supplier.contact}</div>
                    <div className="text-sm text-gray-600">{supplier.phone}</div>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <Mail className="w-4 h-4 text-gray-400 mt-0.5" />
                  <div className="text-sm text-gray-600">{supplier.email}</div>
                </div>
                <div className="flex items-start gap-3">
                  <MapPin className="w-4 h-4 text-gray-400 mt-0.5" />
                  <div className="text-sm text-gray-600">{supplier.address}</div>
                </div>
              </div>

              <div className="border-t border-gray-200 pt-4 mb-4">
                <div className="text-xs text-gray-500 mb-2">Products Supplied</div>
                <div className="flex flex-wrap gap-2">
                  {supplier.products.map((product, index) => (
                    <span key={index} className="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-full">
                      {product}
                    </span>
                  ))}
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4 mb-4">
                <div className="bg-gray-50 rounded-lg p-3">
                  <div className="text-xs text-gray-500">Total Orders</div>
                  <div className="text-lg font-semibold text-gray-900 mt-1">{supplier.totalOrders}</div>
                </div>
                <div className="bg-gray-50 rounded-lg p-3">
                  <div className="text-xs text-gray-500">Total Value</div>
                  <div className="text-lg font-semibold text-gray-900 mt-1">
                    ₱{(supplier.totalValue / 1000000).toFixed(1)}M
                  </div>
                </div>
              </div>

              <div className="flex items-center justify-between pt-4 border-t border-gray-200">
                <span className="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                  {supplier.status}
                </span>
                <div className="flex items-center gap-2">
                  <button className="p-2 hover:bg-blue-50 rounded text-blue-600" title="View Details">
                    <Eye className="w-4 h-4" />
                  </button>
                  <button className="p-2 hover:bg-cyan-50 rounded text-cyan-600" title="Edit">
                    <Edit2 className="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}