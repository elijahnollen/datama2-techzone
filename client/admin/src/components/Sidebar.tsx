import { 
  LayoutDashboard, 
  Package, 
  ShoppingCart, 
  Users, 
  Warehouse, 
  RefreshCw, 
  Truck,
  LogOut
} from 'lucide-react';
import { Page } from './AdminDashboard';

interface SidebarProps {
  currentPage: Page;
  onNavigate: (page: Page) => void;
  isOpen: boolean;
  onToggle: () => void;
  onLogout: () => void;
}

const menuItems = [
  { id: 'dashboard' as Page, label: 'Dashboard', icon: LayoutDashboard },
  { id: 'products' as Page, label: 'Products', icon: Package },
  { id: 'orders' as Page, label: 'Orders', icon: ShoppingCart },
  { id: 'users' as Page, label: 'Users', icon: Users },
  { id: 'inventory' as Page, label: 'Inventory', icon: Warehouse },
  { id: 'returns' as Page, label: 'Returns & Refunds', icon: RefreshCw },
  { id: 'suppliers' as Page, label: 'Suppliers', icon: Truck },
];

export function Sidebar({ currentPage, onNavigate, isOpen, onLogout }: SidebarProps) {
  if (!isOpen) return null;

  return (
    <aside className="fixed left-0 top-0 h-full w-64 bg-white border-r border-gray-200 z-40">
      <div className="h-full flex flex-col">
        {/* Logo */}
        <div className="h-16 flex items-center px-6 border-b border-gray-200">
          <div className="text-2xl">
            <span className="font-bold text-black">TECH</span>
            <span className="font-bold text-cyan-500">ZONE</span>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex-1 overflow-y-auto py-4">
          <div className="px-3 space-y-1">
            {menuItems.map((item) => {
              const Icon = item.icon;
              const isActive = currentPage === item.id;
              
              return (
                <button
                  key={item.id}
                  onClick={() => onNavigate(item.id)}
                  className={`w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                    isActive
                      ? 'bg-blue-50 text-blue-600'
                      : 'text-gray-700 hover:bg-gray-50'
                  }`}
                >
                  <Icon className={`w-5 h-5 mr-3 ${isActive ? 'text-blue-600' : 'text-gray-500'}`} />
                  {item.label}
                </button>
              );
            })}
          </div>
        </nav>

        {/* Profile & Logout */}
        <div className="p-4 border-t border-gray-200">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-medium">
              A
            </div>
            <div className="flex-1 min-w-0">
              <div className="text-sm font-medium text-gray-900 truncate">Admin User</div>
              <div className="text-xs text-gray-500 truncate">admin@techzone.com</div>
            </div>
          </div>
          <button
            onClick={onLogout}
            className="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors"
          >
            <LogOut className="w-4 h-4" />
            Logout
          </button>
        </div>
      </div>
    </aside>
  );
}