import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router';
import { ArrowLeft, Upload, CheckCircle2, Truck, Package } from 'lucide-react';
import Footer from '../imports/Footer-4-8564';
import { getProductById } from '../services/database';
import { Product } from '../types';

type ShipmentMethod = 'courier' | 'dropoff';
type RefundMethod = 'wallet' | 'original';

export function ReturnRequest() {
  const navigate = useNavigate();
  const [selectedItem, setSelectedItem] = useState(true);
  const [reason, setReason] = useState('');
  const [description, setDescription] = useState('');
  const [shipmentMethod, setShipmentMethod] = useState<ShipmentMethod>('courier');
  const [refundMethod, setRefundMethod] = useState<RefundMethod>('wallet');
  const [uploadedFiles, setUploadedFiles] = useState<File[]>([]);
  const [product, setProduct] = useState<Product | null>(null);

  // Mock order data
  const orderNumber = '#TZ-9828';
  const orderDate = 'Feb 08, 2026';
  const customerName = 'Pilot Zero';
  const orderProductId = 'gpu-1';

  useEffect(() => {
    const fetchProduct = async () => {
      const fetchedProduct = await getProductById(orderProductId);
      setProduct(fetchedProduct);
    };

    fetchProduct();
  }, [orderProductId]);

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      setUploadedFiles(Array.from(e.target.files));
    }
  };

  const handleSubmit = () => {
    if (!reason || !description) {
      alert('Please fill in all required fields');
      return;
    }

    console.log('Return request submitted:', {
      orderNumber,
      selectedItem,
      reason,
      description,
      shipmentMethod,
      refundMethod,
      uploadedFiles,
    });

    alert('Return request submitted successfully!');
    navigate('/orders');
  };

  return (
    <div className="min-h-screen bg-zinc-50 flex flex-col">
      <div className="flex-1 max-w-[760px] mx-auto px-6 py-12 w-full">
        {/* Back to Order */}
        <Link
          to="/orders"
          className="flex items-center gap-2 text-zinc-500 hover:text-black mb-8 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="font-bold uppercase tracking-wider text-xs">Back to Order</span>
        </Link>

        {/* Header */}
        <div className="flex items-start justify-between mb-2">
          <div>
            <h1 className="text-[32px] font-bold italic leading-tight mb-1">
              <span className="text-black">RETURN </span>
              <span className="text-cyan-500">REQUEST</span>
            </h1>
            <p className="text-zinc-500 text-sm">
              Submit a request to return items from order {orderNumber}
            </p>
          </div>
          <div className="text-right">
            <p className="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">
              Order Date
            </p>
            <p className="font-bold text-sm">{orderDate}</p>
          </div>
        </div>

        {/* Form */}
        <div className="bg-white rounded-2xl border border-zinc-200 p-8 mt-8">
          {/* Customer Info */}
          <div className="grid grid-cols-2 gap-4 mb-8">
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">
                Customer Name
              </label>
              <input
                type="text"
                value={customerName}
                disabled
                className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm text-zinc-700"
              />
            </div>
            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">
                Order ID
              </label>
              <input
                type="text"
                value={orderNumber}
                disabled
                className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm text-zinc-700"
              />
            </div>
          </div>

          {/* Select Item to Return */}
          <div className="mb-8">
            <div className="flex items-center gap-2 mb-4">
              <input
                type="checkbox"
                id="selectItem"
                checked={selectedItem}
                onChange={(e) => setSelectedItem(e.target.checked)}
                className="w-4 h-4 accent-cyan-500"
              />
              <label htmlFor="selectItem" className="font-bold text-sm uppercase tracking-wider">
                Select Item to Return
              </label>
            </div>

            {/* Product Card */}
            {product && (
              <div className="border-2 border-cyan-500 bg-cyan-50/30 rounded-xl p-4 flex items-center justify-between">
                <div className="flex items-center gap-4">
                  <div className="w-16 h-16 bg-white border border-zinc-200 rounded-lg flex items-center justify-center p-2">
                    <img
                      src={product.image}
                      alt={product.name}
                      className="w-full h-full object-contain"
                    />
                  </div>
                  <div>
                    <h3 className="font-bold text-sm mb-1">{product.name}</h3>
                    <p className="text-xs text-zinc-500 mb-1">Volume: 250G (6AM)</p>
                    <p className="font-bold text-cyan-600">₱{product.price.toLocaleString()}</p>
                  </div>
                </div>
                <div className="w-6 h-6 bg-cyan-500 rounded flex items-center justify-center">
                  <CheckCircle2 className="w-4 h-4 text-white" />
                </div>
              </div>
            )}
          </div>

          {/* Reason for Return */}
          <div className="mb-8">
            <h3 className="flex items-center gap-2 font-bold text-sm uppercase tracking-wider mb-4">
              <div className="w-5 h-5 bg-cyan-500 rounded flex items-center justify-center">
                <span className="text-white text-xs">📋</span>
              </div>
              Reason for Return
            </h3>

            <div className="mb-4">
              <label className="block text-xs font-bold uppercase tracking-wider text-zinc-500 mb-2">
                Reason*
              </label>
              <select
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 appearance-none"
                required
              >
                <option value="">Select a reason...</option>
                <option value="defective">Defective/Not Working</option>
                <option value="wrong-item">Wrong Item Received</option>
                <option value="not-as-described">Not As Described</option>
                <option value="changed-mind">Changed My Mind</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold uppercase tracking-wider text-zinc-500 mb-2">
                Description*
              </label>
              <textarea
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                placeholder="Please provide more details about the issue..."
                className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 resize-none h-32"
                required
              />
            </div>
          </div>

          {/* Evidence Upload */}
          <div className="mb-8">
            <label className="block text-xs font-bold uppercase tracking-wider text-zinc-500 mb-3">
              Evidence (Photos/Videos)
            </label>
            <div className="border-2 border-dashed border-zinc-300 rounded-xl p-8 text-center bg-zinc-50 hover:border-cyan-500 transition-colors cursor-pointer">
              <input
                type="file"
                multiple
                accept="image/*,video/*"
                onChange={handleFileUpload}
                className="hidden"
                id="fileUpload"
              />
              <label htmlFor="fileUpload" className="cursor-pointer">
                <Upload className="w-12 h-12 text-zinc-400 mx-auto mb-3" />
                <p className="font-bold text-sm text-zinc-700 mb-1">
                  Click to upload or drag and drop
                </p>
                <p className="text-xs text-zinc-400">
                  MAX: 10MB, JPG or PNG (max. 10MB)
                </p>
              </label>
              {uploadedFiles.length > 0 && (
                <div className="mt-4 text-left">
                  <p className="text-xs font-bold text-cyan-600 mb-2">
                    {uploadedFiles.length} file(s) selected:
                  </p>
                  {uploadedFiles.map((file, index) => (
                    <p key={index} className="text-xs text-zinc-600">
                      {file.name}
                    </p>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Return Shipment Method */}
          <div className="mb-8">
            <h3 className="flex items-center gap-2 font-bold text-sm uppercase tracking-wider mb-4">
              <div className="w-5 h-5 bg-cyan-500 rounded flex items-center justify-center">
                <Package className="w-3 h-3 text-white" />
              </div>
              Return Shipment Method
            </h3>

            <div className="grid grid-cols-2 gap-4">
              <button
                onClick={() => setShipmentMethod('courier')}
                className={`p-4 rounded-xl border-2 text-left transition-colors ${
                  shipmentMethod === 'courier'
                    ? 'border-cyan-500 bg-cyan-50'
                    : 'border-zinc-200 hover:border-zinc-300'
                }`}
              >
                <div className="flex items-center gap-2 mb-2">
                  <div
                    className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${
                      shipmentMethod === 'courier'
                        ? 'border-cyan-500'
                        : 'border-zinc-300'
                    }`}
                  >
                    {shipmentMethod === 'courier' && (
                      <div className="w-2 h-2 bg-cyan-500 rounded-full" />
                    )}
                  </div>
                  <p className="font-bold text-sm">Courier Pickup</p>
                </div>
                <p className="text-xs text-zinc-500 ml-6">
                  We will arrange a return to pick-up from your delivery address.
                </p>
              </button>

              <button
                onClick={() => setShipmentMethod('dropoff')}
                className={`p-4 rounded-xl border-2 text-left transition-colors ${
                  shipmentMethod === 'dropoff'
                    ? 'border-cyan-500 bg-cyan-50'
                    : 'border-zinc-200 hover:border-zinc-300'
                }`}
              >
                <div className="flex items-center gap-2 mb-2">
                  <div
                    className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${
                      shipmentMethod === 'dropoff'
                        ? 'border-cyan-500'
                        : 'border-zinc-300'
                    }`}
                  >
                    {shipmentMethod === 'dropoff' && (
                      <div className="w-2 h-2 bg-cyan-500 rounded-full" />
                    )}
                  </div>
                  <p className="font-bold text-sm">Drop Off (Self-Return)</p>
                </div>
                <p className="text-xs text-zinc-500 ml-6">
                  Send the item by yourself to our RCEAN partner send-out...
                </p>
              </button>
            </div>
          </div>

          {/* Refund Method */}
          <div className="mb-8">
            <h3 className="flex items-center gap-2 font-bold text-sm uppercase tracking-wider mb-4">
              <div className="w-5 h-5 bg-cyan-500 rounded flex items-center justify-center">
                <span className="text-white text-xs">₱</span>
              </div>
              Refund Method
            </h3>

            <div className="grid grid-cols-2 gap-4">
              <button
                onClick={() => setRefundMethod('wallet')}
                className={`p-4 rounded-xl border-2 text-left transition-colors ${
                  refundMethod === 'wallet'
                    ? 'border-cyan-500 bg-cyan-50'
                    : 'border-zinc-200 hover:border-zinc-300'
                }`}
              >
                <div className="flex items-center gap-2 mb-1">
                  <div
                    className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${
                      refundMethod === 'wallet'
                        ? 'border-cyan-500'
                        : 'border-zinc-300'
                    }`}
                  >
                    {refundMethod === 'wallet' && (
                      <div className="w-2 h-2 bg-cyan-500 rounded-full" />
                    )}
                  </div>
                  <div>
                    <p className="font-bold text-sm">Store Wallet Credit</p>
                    <p className="text-xs text-cyan-600 font-bold">Est. ₱1350.00</p>
                  </div>
                </div>
                <p className="text-xs text-zinc-500 ml-6">
                  Fastest option. Funds added immediately after approval.
                </p>
              </button>

              <button
                onClick={() => setRefundMethod('original')}
                className={`p-4 rounded-xl border-2 text-left transition-colors ${
                  refundMethod === 'original'
                    ? 'border-cyan-500 bg-cyan-50'
                    : 'border-zinc-200 hover:border-zinc-300'
                }`}
              >
                <div className="flex items-center gap-2 mb-2">
                  <div
                    className={`w-4 h-4 rounded-full border-2 flex items-center justify-center ${
                      refundMethod === 'original'
                        ? 'border-cyan-500'
                        : 'border-zinc-300'
                    }`}
                  >
                    {refundMethod === 'original' && (
                      <div className="w-2 h-2 bg-cyan-500 rounded-full" />
                    )}
                  </div>
                  <p className="font-bold text-sm">Original Payment Method</p>
                </div>
                <p className="text-xs text-zinc-500 ml-6">
                  Refund to your card/bank. Takes 5-10 business days.
                </p>
              </button>
            </div>
          </div>

          {/* Action Buttons */}
          <div className="grid grid-cols-2 gap-4 pt-4">
            <button
              onClick={() => navigate('/orders')}
              className="border border-zinc-300 text-zinc-600 font-bold text-xs uppercase tracking-wider py-4 rounded-xl hover:bg-zinc-50 transition-colors"
            >
              Cancel
            </button>
            <button
              onClick={handleSubmit}
              className="bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-xl hover:bg-zinc-800 transition-colors flex items-center justify-center gap-2"
            >
              Submit Request
              <span>→</span>
            </button>
          </div>
        </div>
      </div>

      {/* Footer */}
      <Footer />
    </div>
  );
}