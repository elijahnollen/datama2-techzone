import { useState } from 'react';
import { useNavigate, Link } from 'react-router';
import { ArrowLeft, Plus, Send } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { Header } from '../components/Header';
import Footer from '../imports/Footer-4-4788';

export function Wallet() {
  const navigate = useNavigate();
  const { user, updateWallet } = useAuth();
  const [showAddFunds, setShowAddFunds] = useState(false);
  const [amount, setAmount] = useState('');

  if (!user) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <div className="flex-1 flex items-center justify-center">
          <div className="text-center">
            <h1 className="text-2xl font-bold mb-4">Please log in to view your wallet</h1>
            <Link to="/" className="text-cyan-600 hover:underline">
              Return to Home
            </Link>
          </div>
        </div>
        <Footer />
      </div>
    );
  }

  const handleAddFunds = () => {
    const amountNum = parseFloat(amount);
    if (amountNum > 0) {
      updateWallet(user.wallet + amountNum);
      setAmount('');
      setShowAddFunds(false);
    }
  };

  // Mock transaction history
  const transactions = [
    { id: '1', type: 'add', amount: 1250, date: '2026-02-13', description: 'Added funds' },
    { id: '2', type: 'purchase', amount: -899, date: '2026-02-12', description: 'Logitech G Pro X Superlight' },
    { id: '3', type: 'purchase', amount: -1599, date: '2026-02-10', description: 'Razer BlackWidow V4 Pro' },
  ];

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <Header />

      <div className="flex-1 max-w-[1400px] mx-auto px-6 py-12 w-full">
        {/* Back Button */}
        <button
          onClick={() => navigate('/')}
          className="flex items-center gap-2 text-zinc-500 hover:text-black mb-8 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="font-bold uppercase tracking-wider text-xs">Back to Home</span>
        </button>

        {/* Page Header */}
        <h1 className="text-[32px] font-bold italic leading-tight mb-8">
          <span className="text-black">MY </span>
          <span className="text-cyan-500">WALLET</span>
        </h1>

        <div className="grid lg:grid-cols-[1fr_400px] gap-8">
          {/* Left: Transaction History */}
          <div>
            <div className="bg-white border border-zinc-200 rounded-2xl p-6 mb-6">
              <h2 className="font-bold text-lg mb-4">Transaction History</h2>
              <div className="space-y-4">
                {transactions.map((transaction) => (
                  <div
                    key={transaction.id}
                    className="flex items-center justify-between py-3 border-b border-zinc-100 last:border-0"
                  >
                    <div>
                      <p className="font-bold text-sm">{transaction.description}</p>
                      <p className="text-xs text-zinc-500">{transaction.date}</p>
                    </div>
                    <span
                      className={`font-bold text-lg ${
                        transaction.amount > 0 ? 'text-green-600' : 'text-red-600'
                      }`}
                    >
                      {transaction.amount > 0 ? '+' : ''}₱{Math.abs(transaction.amount).toLocaleString()}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          </div>

          {/* Right: Wallet Card */}
          <div>
            <div className="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-8 text-white sticky top-6">
              <h3 className="text-sm opacity-80 mb-2">Available Balance</h3>
              <p className="text-4xl font-bold mb-8">₱{user.wallet.toFixed(2)}</p>

              <button
                onClick={() => setShowAddFunds(true)}
                className="w-full bg-white text-black font-bold py-3 rounded-lg hover:bg-gray-100 transition-colors flex items-center justify-center gap-2"
              >
                <Plus className="w-5 h-5" />
                Add Funds
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Add Funds Modal */}
      {showAddFunds && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h2 className="text-2xl font-bold mb-4">Add Funds</h2>
            <div className="mb-4">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Amount (₱)
              </label>
              <input
                type="number"
                value={amount}
                onChange={(e) => setAmount(e.target.value)}
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none"
                placeholder="0.00"
                min="0"
                step="0.01"
              />
            </div>
            <div className="flex gap-3">
              <button
                onClick={() => {
                  setShowAddFunds(false);
                  setAmount('');
                }}
                className="flex-1 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
              <button
                onClick={handleAddFunds}
                disabled={!amount || parseFloat(amount) <= 0}
                className="flex-1 bg-cyan-500 text-black font-bold px-4 py-3 rounded-lg hover:bg-cyan-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Add Funds
              </button>
            </div>
          </div>
        </div>
      )}

      <Footer />
    </div>
  );
}
