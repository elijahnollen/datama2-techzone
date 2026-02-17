import { useState } from 'react';
import { Link, useNavigate } from 'react-router';
import { ArrowLeft, MapPin } from 'lucide-react';
import Footer from '../imports/Footer-4-5279';

export function SignUp() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    firstName: '',
    middleName: '',
    lastName: '',
    email: '',
    contactNumber: '',
    streetAddress: '',
    city: '',
    province: '',
    zipCode: '',
    password: '',
    passwordConfirmation: '',
    newsletter: false,
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? (e.target as HTMLInputElement).checked : value,
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // Handle signup logic here
    console.log('Signup data:', formData);
  };

  return (
    <div className="min-h-screen bg-white flex flex-col">
      {/* Main Content */}
      <div className="flex-1 flex items-center justify-center px-6 py-32">
        <div className="bg-white rounded-[30px] border border-zinc-200 shadow-[0px_23px_47px_-11px_rgba(0,0,0,0.25)] w-full max-w-[720px] px-12 py-12 relative">
          {/* Back Button */}
          <button
            onClick={() => navigate(-1)}
            className="absolute top-8 left-8 p-2 hover:bg-zinc-50 rounded-lg transition-colors"
          >
            <ArrowLeft className="w-5 h-5 text-zinc-400" />
          </button>

          <div className="flex flex-col items-center gap-2 mb-8">
            {/* Header */}
            <h2 className="text-[27px] font-bold italic text-center leading-tight">
              <span className="text-black">Create your own </span>
              <span className="text-cyan-500">Account</span>
            </h2>

            {/* Country Indicator */}
            <div className="flex items-center gap-2">
              <MapPin className="w-[11px] h-[11px] text-cyan-500" />
              <span className="text-[11px] font-bold text-zinc-400 uppercase tracking-[1.1px]">
                Philippines
              </span>
            </div>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Name Fields */}
            <div className="grid grid-cols-3 gap-5">
              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                  First name*
                </label>
                <input
                  type="text"
                  name="firstName"
                  value={formData.firstName}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>

              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold uppercase tracking-[0.9px] bg-white px-1">
                  <span className="text-zinc-500">Middle Name </span>
                  <span className="text-zinc-400 font-normal">(Optional)</span>
                </label>
                <input
                  type="text"
                  name="middleName"
                  value={formData.middleName}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                />
              </div>

              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                  Last name*
                </label>
                <input
                  type="text"
                  name="lastName"
                  value={formData.lastName}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>
            </div>

            {/* Email and Contact */}
            <div className="grid grid-cols-2 gap-5">
              <div>
                <label className="block text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] mb-1.5">
                  Email address
                </label>
                <input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
                <p className="text-[9px] text-zinc-400 mt-1.5">
                  Used for account login and order notifications
                </p>
              </div>

              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                  Contact number
                </label>
                <input
                  type="tel"
                  name="contactNumber"
                  value={formData.contactNumber}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>
            </div>

            {/* Address Fields */}
            <div className="space-y-5 pt-2">
              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                  Street Address*
                </label>
                <input
                  type="text"
                  name="streetAddress"
                  placeholder="House number and street name"
                  value={formData.streetAddress}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-4 text-sm placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>

              <div className="grid grid-cols-3 gap-5">
                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                    City*
                  </label>
                  <select
                    name="city"
                    value={formData.city}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm text-black appearance-none focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  >
                    <option value="">Select an option...</option>
                    <option value="manila">Manila</option>
                    <option value="quezon">Quezon City</option>
                    <option value="makati">Makati</option>
                    <option value="taguig">Taguig</option>
                  </select>
                  <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                      <path d="M3.75 5.625L7.5 9.375L11.25 5.625" stroke="#A1A1AA" strokeWidth="1.25" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </div>
                </div>

                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                    Province*
                  </label>
                  <select
                    name="province"
                    value={formData.province}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm text-black appearance-none focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  >
                    <option value="">Select an option...</option>
                    <option value="metro-manila">Metro Manila</option>
                    <option value="cavite">Cavite</option>
                    <option value="laguna">Laguna</option>
                    <option value="rizal">Rizal</option>
                  </select>
                  <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                      <path d="M3.75 5.625L7.5 9.375L11.25 5.625" stroke="#A1A1AA" strokeWidth="1.25" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  </div>
                </div>

                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                    Zip Code*
                  </label>
                  <input
                    type="text"
                    name="zipCode"
                    value={formData.zipCode}
                    onChange={handleChange}
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>
              </div>
            </div>

            {/* Password Fields */}
            <div className="grid grid-cols-2 gap-5">
              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                  Password*
                </label>
                <input
                  type="password"
                  name="password"
                  value={formData.password}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>

              <div className="relative">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-[0.9px] bg-white px-1">
                  Password confirmation*
                </label>
                <input
                  type="password"
                  name="passwordConfirmation"
                  value={formData.passwordConfirmation}
                  onChange={handleChange}
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-[11px] px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>
            </div>

            {/* Newsletter */}
            <div className="bg-zinc-50 border border-zinc-100 rounded-[11px] p-4 flex items-center gap-3">
              <input
                type="checkbox"
                name="newsletter"
                id="newsletter"
                checked={formData.newsletter}
                onChange={handleChange}
                className="w-4 h-4 accent-cyan-500 border border-zinc-400 rounded"
              />
              <label htmlFor="newsletter" className="text-[11px] font-bold text-zinc-600">
                Subscribe to our newsletter
              </label>
            </div>

            {/* Agreement */}
            <div className="text-center text-[9px] text-zinc-400 leading-relaxed py-2">
              <p>
                By clicking 'Create account', I hereby agree to and accept the following{' '}
                <Link to="/terms" className="text-cyan-600 font-bold hover:underline">
                  terms and conditions
                </Link>{' '}
                and hereby certify that all of the above
              </p>
              <p>information is true to the best of my knowledge and belief.</p>
            </div>

            {/* Buttons */}
            <div className="grid grid-cols-2 gap-4 pt-2">
              <button
                type="button"
                onClick={() => navigate(-1)}
                className="border border-zinc-200 text-zinc-500 font-bold text-[11px] uppercase tracking-wider py-4 rounded-[11px] hover:bg-zinc-50 transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                className="bg-cyan-500 hover:bg-cyan-600 text-black font-bold text-[11px] uppercase tracking-[2px] py-4 rounded-[11px] shadow-lg transition-colors"
              >
                Create Account
              </button>
            </div>
          </form>
        </div>
      </div>

      {/* Footer */}
      <Footer />
    </div>
  );
}
