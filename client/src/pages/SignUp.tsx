import { useState } from 'react';
import { useNavigate, Link } from 'react-router';
import { ArrowLeft, User, MapPin, ShieldCheck } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';

export function SignUp() {
  const navigate = useNavigate();
  const { signup } = useAuth();
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const [formData, setFormData] = useState({
    firstName: '',
    middleName: '',
    lastName: '',
    email: '',
    contactNumber: '',
    streetAddress: '',
    barangay: '',
    city: '',
    province: '',
    zipCode: '',
    password: '',
    passwordConfirmation: '',
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    if (formData.password !== formData.passwordConfirmation) {
      setError('Passwords do not match.');
      return;
    }
    setIsLoading(true);
    try {
      await signup(formData);
      navigate('/'); 
    } catch (err: any) {
      setError(err.message || 'Registration failed.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    /* The Shell: Using w-screen and flex-col to force a centered layout */
    <div className="w-full min-h-screen bg-[#f8f9fa] flex flex-col items-center py-12 px-4">
      
      {/* The Limit: We use a hard-coded max-width style here to override global CSS */}
      <div 
        className="w-full bg-white rounded-[32px] border border-zinc-100 shadow-sm p-6 md:p-12"
        style={{ maxWidth: '850px' }} 
      >
        
        <button 
          onClick={() => navigate(-1)} 
          className="flex items-center gap-2 text-zinc-400 hover:text-black transition-colors mb-8"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="text-[10px] font-bold uppercase tracking-[2px]">Back</span>
        </button>

        <div className="mb-10">
          <h2 className="text-3xl font-black text-black italic tracking-tight">Create Account</h2>
          <p className="text-zinc-400 text-sm">Fill in your details to get started.</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-10">
          {error && (
            <div className="bg-red-50 text-red-600 p-4 rounded-xl text-xs font-bold border border-red-100 text-center">
              {error}
            </div>
          )}

          {/* Identity */}
          <section className="space-y-5">
            <div className="flex items-center gap-2 border-b border-zinc-50 pb-2">
              <User className="w-4 h-4 text-cyan-500" />
              <h3 className="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Personal</h3>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <InputField label="First Name" name="firstName" value={formData.firstName} onChange={handleChange} required />
              <InputField label="Middle Name" name="middleName" value={formData.middleName} onChange={handleChange} />
              <InputField label="Last Name" name="lastName" value={formData.lastName} onChange={handleChange} required />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputField label="Email" type="email" name="email" value={formData.email} onChange={handleChange} required />
              <InputField label="Mobile" type="tel" name="contactNumber" value={formData.contactNumber} onChange={handleChange} required />
            </div>
          </section>

          {/* Address */}
          <section className="space-y-5">
            <div className="flex items-center gap-2 border-b border-zinc-50 pb-2">
              <MapPin className="w-4 h-4 text-cyan-500" />
              <h3 className="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Shipping</h3>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputField label="Street" name="streetAddress" value={formData.streetAddress} onChange={handleChange} required />
              <InputField label="Barangay" name="barangay" value={formData.barangay} onChange={handleChange} required />
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">City</label>
                <select 
                  name="city" 
                  value={formData.city} 
                  onChange={handleChange} 
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:border-cyan-500 outline-none font-semibold"
                >
                  <option value="">Select...</option>
                  <option value="Manila">Manila</option>
                  <option value="Quezon City">Quezon City</option>
                  <option value="Taguig">Taguig</option>
                  <option value="Taguig">Mandaluyong</option>
                  <option value="Taguig">Pasay</option>
                  <option value="Taguig">Pasig</option>
                </select>
              </div>
              <InputField label="Province" name="province" value={formData.province} onChange={handleChange} required />
              <InputField label="Zip" name="zipCode" value={formData.zipCode} onChange={handleChange} required />
            </div>
          </section>

          {/* Security */}
          <section className="space-y-5">
            <div className="flex items-center gap-2 border-b border-zinc-50 pb-2">
              <ShieldCheck className="w-4 h-4 text-cyan-500" />
              <h3 className="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Security</h3>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <InputField label="Password" type="password" name="password" value={formData.password} onChange={handleChange} required />
              <InputField label="Confirm" type="password" name="passwordConfirmation" value={formData.passwordConfirmation} onChange={handleChange} required />
            </div>
          </section>

          <div className="pt-4">
            <button 
              disabled={isLoading} 
              type="submit" 
              className="w-full bg-black text-white font-bold py-4 rounded-xl shadow-md hover:bg-zinc-800 transition-all uppercase tracking-widest text-xs"
            >
              {isLoading ? 'Creating...' : 'Create Account'}
            </button>
            <p className="text-center mt-6 text-xs text-zinc-400 font-bold">
               
            </p>
          </div>
        </form>
      </div>
    </div>
  );
}

function InputField({ label, ...props }: any) {
  return (
    <div className="space-y-1">
      <label className="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">
        {label}
      </label>
      <input
        {...props}
        className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 text-sm focus:border-cyan-500 outline-none font-semibold text-zinc-700"
      />
    </div>
  );
}