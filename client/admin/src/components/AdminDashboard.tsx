import { useState } from 'react';
import { Sidebar } from './Sidebar';
import { TopNavbar } from './TopNavbar';
import { DashboardHome } from './pages/DashboardHome';
import { ProductManagement } from './pages/ProductManagement';
import { OrderManagement } from './pages/OrderManagement';
import { UserManagement } from './pages/UserManagement';
import { InventoryManagement } from './pages/InventoryManagement';
import { ReturnManagement } from './pages/ReturnManagement';
import { SupplierManagement } from './pages/SupplierManagement';

interface AdminDashboardProps {
  onLogout: () => void;
}

export type Page = 
  | 'dashboard' 
  | 'products' 
  | 'orders' 
  | 'users' 
  | 'inventory' 
  | 'returns' 
  | 'suppliers';

export function AdminDashboard({ onLogout }: AdminDashboardProps) {
  const [currentPage, setCurrentPage] = useState<Page>('dashboard');
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);

  const renderPage = () => {
    switch (currentPage) {
      case 'dashboard':
        return <DashboardHome />;
      case 'products':
        return <ProductManagement />;
      case 'orders':
        return <OrderManagement />;
      case 'users':
        return <UserManagement />;
      case 'inventory':
        return <InventoryManagement />;
      case 'returns':
        return <ReturnManagement />;
      case 'suppliers':
        return <SupplierManagement />;
      default:
        return <DashboardHome />;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Sidebar 
        currentPage={currentPage} 
        onNavigate={setCurrentPage}
        isOpen={isSidebarOpen}
        onToggle={() => setIsSidebarOpen(!isSidebarOpen)}
        onLogout={onLogout}
      />
      <div className={`transition-all duration-300 ${isSidebarOpen ? 'ml-64' : 'ml-0'}`}>
        <TopNavbar onMenuClick={() => setIsSidebarOpen(!isSidebarOpen)} />
        <main className="p-6">
          {renderPage()}
        </main>
      </div>
    </div>
  );
}