import { AlertCircle, CheckCircle2, Info } from 'lucide-react';
import { useState, useEffect } from 'react';
import { testConnection } from '../services/database';

export function DatabaseStatusBanner() {
  const [status, setStatus] = useState<'checking' | 'connected' | 'fallback'>('checking');
  const [dismissed, setDismissed] = useState(false);

  useEffect(() => {
    async function checkStatus() {
      const isConnected = await testConnection();
      setStatus(isConnected ? 'connected' : 'fallback');
    }
    checkStatus();
  }, []);

  if (dismissed || status === 'checking') {
    return null;
  }

  if (status === 'connected') {
    return (
      <div className="bg-green-50 border-b border-green-200 px-6 py-3">
        <div className="max-w-[1400px] mx-auto flex items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <CheckCircle2 className="w-5 h-5 text-green-600 flex-shrink-0" />
            <p className="text-sm text-green-800">
              <strong>Connected to your database</strong> - All features are live!
            </p>
          </div>
          <button
            onClick={() => setDismissed(true)}
            className="text-green-600 hover:text-green-800 text-sm font-bold"
          >
            Dismiss
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-amber-50 border-b border-amber-200 px-6 py-3">
      <div className="max-w-[1400px] mx-auto flex items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <Info className="w-5 h-5 text-amber-600 flex-shrink-0" />
          <p className="text-sm text-amber-800">
            <strong>Demo Mode:</strong> Using sample data. To connect your database, update <code className="bg-amber-100 px-2 py-0.5 rounded text-xs">API_BASE_URL</code> in <code className="bg-amber-100 px-2 py-0.5 rounded text-xs">/services/database.ts</code>
          </p>
        </div>
        <button
          onClick={() => setDismissed(true)}
          className="text-amber-600 hover:text-amber-800 text-sm font-bold"
        >
          Dismiss
        </button>
      </div>
    </div>
  );
}
