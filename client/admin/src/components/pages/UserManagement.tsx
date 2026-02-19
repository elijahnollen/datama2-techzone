import { useState } from 'react';
import { Search, Eye, Ban, CheckCircle, Mail } from 'lucide-react';

interface User {
  id: string;
  name: string;
  email: string;
  phone: string;
  orders: number;
  totalSpent: number;
  status: 'Active' | 'Inactive' | 'Suspended';
  joinDate: string;
}

const mockUsers: User[] = [
  { id: 'U001', name: 'Juan Dela Cruz', email: 'juan@email.com', phone: '+63 912 345 6789', orders: 12, totalSpent: 245000, status: 'Active', joinDate: '2024-01-15' },
  { id: 'U002', name: 'Maria Santos', email: 'maria@email.com', phone: '+63 923 456 7890', orders: 8, totalSpent: 156000, status: 'Active', joinDate: '2024-01-20' },
  { id: 'U003', name: 'Pedro Reyes', email: 'pedro@email.com', phone: '+63 934 567 8901', orders: 5, totalSpent: 89000, status: 'Active', joinDate: '2024-02-01' },
  { id: 'U004', name: 'Ana Garcia', email: 'ana@email.com', phone: '+63 945 678 9012', orders: 15, totalSpent: 312000, status: 'Active', joinDate: '2023-12-10' },
  { id: 'U005', name: 'Carlos Miguel', email: 'carlos@email.com', phone: '+63 956 789 0123', orders: 0, totalSpent: 0, status: 'Inactive', joinDate: '2024-02-08' },
];

export function UserManagement() {
  const [users, setUsers] = useState<User[]>(mockUsers);
  const [searchTerm, setSearchTerm] = useState('');

  const filteredUsers = users.filter(user =>
    user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.email.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const updateUserStatus = (userId: string, newStatus: User['status']) => {
    setUsers(users.map(user => 
      user.id === userId ? { ...user, status: newStatus } : user
    ));
  };

  const getStatusColor = (status: User['status']) => {
    switch (status) {
      case 'Active': return 'bg-green-100 text-green-700';
      case 'Inactive': return 'bg-gray-100 text-gray-700';
      case 'Suspended': return 'bg-red-100 text-red-700';
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">User Management</h1>
        <p className="text-gray-600 mt-1">Monitor and manage customer accounts</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="text-2xl font-bold text-gray-900">{users.length}</div>
          <div className="text-sm text-gray-600 mt-1">Total Users</div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="text-2xl font-bold text-green-600">
            {users.filter(u => u.status === 'Active').length}
          </div>
          <div className="text-sm text-gray-600 mt-1">Active Users</div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="text-2xl font-bold text-gray-600">
            {users.filter(u => u.status === 'Inactive').length}
          </div>
          <div className="text-sm text-gray-600 mt-1">Inactive Users</div>
        </div>
        <div className="bg-white rounded-lg border border-gray-200 p-6">
          <div className="text-2xl font-bold text-blue-600">
            ₱{Math.round(users.reduce((sum, u) => sum + u.totalSpent, 0) / users.length).toLocaleString()}
          </div>
          <div className="text-sm text-gray-600 mt-1">Avg. Spend per User</div>
        </div>
      </div>

      <div className="bg-white rounded-lg border border-gray-200">
        <div className="p-6 border-b border-gray-200">
          <div className="flex items-center gap-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search users..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <select className="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option>All Status</option>
              <option>Active</option>
              <option>Inactive</option>
              <option>Suspended</option>
            </select>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50 border-b border-gray-200">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User ID</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {filteredUsers.map((user) => (
                <tr key={user.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">{user.id}</td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-gray-900 font-medium">{user.name}</div>
                    <div className="text-xs text-gray-500">Joined {user.joinDate}</div>
                  </td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-gray-900">{user.email}</div>
                    <div className="text-xs text-gray-500">{user.phone}</div>
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-900">{user.orders}</td>
                  <td className="px-6 py-4 text-sm font-medium text-gray-900">
                    ₱{user.totalSpent.toLocaleString()}
                  </td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(user.status)}`}>
                      {user.status}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-2">
                      <button className="p-1.5 hover:bg-blue-50 rounded text-blue-600" title="View Details">
                        <Eye className="w-4 h-4" />
                      </button>
                      <button className="p-1.5 hover:bg-cyan-50 rounded text-cyan-600" title="Send Email">
                        <Mail className="w-4 h-4" />
                      </button>
                      {user.status === 'Active' ? (
                        <button 
                          onClick={() => updateUserStatus(user.id, 'Suspended')}
                          className="p-1.5 hover:bg-red-50 rounded text-red-600" 
                          title="Suspend"
                        >
                          <Ban className="w-4 h-4" />
                        </button>
                      ) : user.status === 'Suspended' && (
                        <button 
                          onClick={() => updateUserStatus(user.id, 'Active')}
                          className="p-1.5 hover:bg-green-50 rounded text-green-600" 
                          title="Activate"
                        >
                          <CheckCircle className="w-4 h-4" />
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
            Showing {filteredUsers.length} of {users.length} users
          </div>
        </div>
      </div>
    </div>
  );
}
