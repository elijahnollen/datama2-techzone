import { useState } from 'react';
import { Link } from 'react-router';
import { ArrowLeft, Calendar, MapPin, Key } from 'lucide-react';
import NavbarLoggedInState from '../imports/NavbarLoggedInState-4-9212';
import Footer from '../imports/Footer-4-9523';

export function Account() {
  const [formData, setFormData] = useState({
    firstName: 'User',
    middleName: '',
    lastName: 'TEST',
    email: 'user@gmail.com',
    phone: '+63 912 345 6789',
    streetAddress: 'Block 1 Lot 2, Cyber St.',
    barangay: 'San Francisco',
    city: 'General Trias',
    province: 'Cavite',
    zipCode: '4107',
  });

  const [originalData] = useState(formData);
  const [showPasswordModal, setShowPasswordModal] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData(prev => ({
      ...prev,
      [e.target.name]: e.target.value,
    }));
  };

  const handleSave = () => {
    console.log('Saving profile:', formData);
    alert('Profile updated successfully!');
  };

  const handleCancel = () => {
    setFormData(originalData);
  };

  const hasChanges = JSON.stringify(formData) !== JSON.stringify(originalData);

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <NavbarLoggedInState />

      <div className="flex-1 max-w-[720px] mx-auto px-6 py-16 w-full">
        {/* Back to Home */}
        <Link
          to="/"
          className="flex items-center gap-2 text-zinc-500 hover:text-black mb-12 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="font-bold uppercase tracking-wider text-xs">Back to Home</span>
        </Link>

        {/* Header */}
        <div className="flex items-center justify-between mb-12">
          <h1 className="text-[28px] font-bold italic leading-tight">
            <span className="text-black">ACCOUNT </span>
            <span className="text-cyan-500">SETTINGS</span>
          </h1>
          <span className="text-[10px] font-bold uppercase tracking-wider text-zinc-400">
            ID: Pilot-8821
          </span>
        </div>

        {/* Profile Form Card */}
        <div className="bg-white border border-zinc-200 rounded-2xl p-8 shadow-sm">
          <form className="space-y-8">
            {/* Contact Information */}
            <div className="space-y-6">
              <div className="flex items-center gap-2 pb-4 border-b border-zinc-100">
                <Calendar className="w-4 h-4 text-cyan-500" />
                <h3 className="font-bold text-sm uppercase tracking-wider">
                  Contact Information
                </h3>
              </div>

              {/* Name Fields */}
              <div className="grid grid-cols-3 gap-6">
                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    First Name
                  </label>
                  <input
                    type="text"
                    name="firstName"
                    value={formData.firstName}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>

                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Middle Name
                  </label>
                  <input
                    type="text"
                    name="middleName"
                    value={formData.middleName}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>

                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Last Name
                  </label>
                  <input
                    type="text"
                    name="lastName"
                    value={formData.lastName}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>
              </div>

              {/* Email and Phone */}
              <div className="grid grid-cols-2 gap-6">
                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Email Address
                  </label>
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>

                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Phone Number
                  </label>
                  <input
                    type="tel"
                    name="phone"
                    value={formData.phone}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>
              </div>
            </div>

            {/* Delivery Address */}
            <div className="space-y-6">
              <div className="flex items-center gap-2 pb-4 border-b border-zinc-100">
                <MapPin className="w-4 h-4 text-cyan-500" />
                <h3 className="font-bold text-sm uppercase tracking-wider">
                  Delivery Address
                </h3>
              </div>

              {/* Street Address */}
              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                  Street Address
                </label>
                <input
                  type="text"
                  name="streetAddress"
                  value={formData.streetAddress}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
              </div>

              {/* Address Grid */}
              <div className="grid grid-cols-2 gap-6">
                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Barangay
                  </label>
                  <input
                    type="text"
                    name="barangay"
                    value={formData.barangay}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>

                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    City / Municipality
                  </label>
                  <input
                    type="text"
                    name="city"
                    value={formData.city}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-6">
                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Province
                  </label>
                  <input
                    type="text"
                    name="province"
                    value={formData.province}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>

                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Zip Code
                  </label>
                  <input
                    type="text"
                    name="zipCode"
                    value={formData.zipCode}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  />
                </div>
              </div>
            </div>

            {/* Password & Security */}
            <div className="pt-6 border-t border-zinc-100">
              <div className="bg-zinc-50 border border-zinc-200 rounded-xl p-4 flex items-center justify-between">
                <div>
                  <h4 className="font-bold text-sm mb-1">Password & Security</h4>
                  <p className="text-xs text-zinc-500">Last updated 3 months ago</p>
                </div>
                <button
                  type="button"
                  onClick={() => setShowPasswordModal(true)}
                  className="bg-white border border-zinc-300 rounded-lg px-4 py-2 flex items-center gap-2 hover:bg-zinc-50 transition-colors shadow-sm"
                >
                  <Key className="w-4 h-4" />
                  <span className="font-bold text-xs uppercase tracking-wider">
                    Change Password
                  </span>
                </button>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="flex gap-4 pt-4">
              <button
                type="button"
                onClick={handleCancel}
                disabled={!hasChanges}
                className="flex-1 border-2 border-zinc-200 text-zinc-500 font-bold text-xs uppercase tracking-wider py-4 rounded-xl hover:bg-zinc-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Cancel Changes
              </button>
              <button
                type="button"
                onClick={handleSave}
                disabled={!hasChanges}
                className="flex-1 bg-cyan-500 text-black font-bold text-xs uppercase tracking-wider py-4 rounded-xl hover:bg-cyan-600 transition-colors shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Save Profile
              </button>
            </div>
          </form>
        </div>
      </div>

      <Footer />

      {/* Change Password Modal */}
      {showPasswordModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-6">
          <div className="bg-white rounded-2xl p-8 max-w-md w-full">
            <h2 className="text-2xl font-bold mb-6">Change Password</h2>
            <div className="space-y-4 mb-6">
              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                  Current Password
                </label>
                <input
                  type="password"
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
              </div>
              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                  New Password
                </label>
                <input
                  type="password"
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
              </div>
              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                  Confirm New Password
                </label>
                <input
                  type="password"
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
              </div>
            </div>
            <div className="flex gap-3">
              <button
                onClick={() => setShowPasswordModal(false)}
                className="flex-1 border-2 border-zinc-200 text-zinc-500 font-bold text-xs uppercase tracking-wider py-3 rounded-xl hover:bg-zinc-50 transition-colors"
              >
                Cancel
              </button>
              <button
                onClick={() => {
                  alert('Password changed successfully!');
                  setShowPasswordModal(false);
                }}
                className="flex-1 bg-black text-white font-bold text-xs uppercase tracking-wider py-3 rounded-xl hover:bg-zinc-800 transition-colors"
              >
                Update Password
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
