import { useState } from 'react';
import { Link } from 'react-router';
import { ArrowLeft, MapPin, Mail, Phone, Send } from 'lucide-react';
import NavbarLoggedInState from '../imports/NavbarLoggedInState-4-9212';
import Footer from '../imports/Footer-4-9150';

export function Contact() {
  const [formData, setFormData] = useState({
    name: '',
    contactNumber: '',
    email: '',
    message: '',
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData(prev => ({
      ...prev,
      [e.target.name]: e.target.value,
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log('Contact form submitted:', formData);
    alert('Thank you for contacting us! We will respond within 24 hours.');
    setFormData({
      name: '',
      contactNumber: '',
      email: '',
      message: '',
    });
  };

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <NavbarLoggedInState />

      <div className="flex-1 max-w-[1000px] mx-auto px-6 py-16 w-full">
        {/* Back to Home */}
        <Link
          to="/"
          className="flex items-center gap-2 text-zinc-500 hover:text-black mb-12 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          <span className="font-bold uppercase tracking-wider text-xs">Back to Home</span>
        </Link>

        <div className="grid lg:grid-cols-[1fr_300px] gap-16">
          {/* Left Column - Contact Form */}
          <div>
            {/* Header */}
            <div className="mb-10">
              <h1 className="text-[34px] font-bold italic leading-tight mb-2">
                <span className="text-black">GET IN </span>
                <span className="text-cyan-500">TOUCH</span>
              </h1>
              <p className="text-zinc-500 text-sm leading-relaxed">
                Have questions about us? Need assistance? Fill out the form
                <br />
                below and our support unit will respond within 24 hours.
              </p>
            </div>

            {/* Form */}
            <form onSubmit={handleSubmit} className="bg-white border border-zinc-200 rounded-2xl p-8 shadow-sm">
              {/* Name and Contact Number */}
              <div className="grid grid-cols-2 gap-6 mb-6">
                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Your Name
                  </label>
                  <input
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    placeholder="Pilot Zero"
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>

                <div className="relative">
                  <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                    Contact Number
                  </label>
                  <input
                    type="tel"
                    name="contactNumber"
                    value={formData.contactNumber}
                    onChange={handleChange}
                    placeholder="+63 9XX XXX XXXX"
                    className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                  />
                </div>
              </div>

              {/* Email */}
              <div className="relative mb-6">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                  Email Address
                </label>
                <input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  placeholder="pilot@techzone.io"
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required
                />
              </div>

              {/* Message */}
              <div className="relative mb-6">
                <label className="absolute -top-2 left-1 text-[9px] font-bold text-zinc-500 uppercase tracking-wider bg-white px-1">
                  Message
                </label>
                <textarea
                  name="message"
                  value={formData.message}
                  onChange={handleChange}
                  placeholder="Describe your inquiry..."
                  className="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3.5 text-sm placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 resize-none h-36"
                  required
                />
              </div>

              {/* Submit Button */}
              <button
                type="submit"
                className="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-xl hover:bg-zinc-800 transition-colors flex items-center justify-center gap-2 shadow-lg"
              >
                Send Message
                <Send className="w-4 h-4" />
              </button>
            </form>
          </div>

          {/* Right Column - Contact Info */}
          <div className="pt-12">
            <div className="space-y-6">
              {/* Address */}
              <div className="flex gap-4">
                <div className="bg-zinc-100 rounded-lg w-10 h-10 flex items-center justify-center flex-shrink-0">
                  <MapPin className="w-5 h-5 text-cyan-500" />
                </div>
                <div>
                  <h4 className="font-bold text-sm mb-1">Address</h4>
                  <p className="text-xs text-zinc-500 leading-relaxed">
                    Sector 7G, Industrial Park
                    <br />
                    Tanza, Cavite, Philippines 4108
                  </p>
                </div>
              </div>

              {/* Email Support */}
              <div className="flex gap-4">
                <div className="bg-zinc-100 rounded-lg w-10 h-10 flex items-center justify-center flex-shrink-0">
                  <Mail className="w-5 h-5 text-cyan-500" />
                </div>
                <div>
                  <h4 className="font-bold text-sm mb-1">Email Support</h4>
                  <p className="text-xs text-zinc-500">support@shop.com</p>
                  <p className="text-xs text-zinc-500">business@shop.com</p>
                </div>
              </div>

              {/* Direct Line */}
              <div className="flex gap-4">
                <div className="bg-zinc-100 rounded-lg w-10 h-10 flex items-center justify-center flex-shrink-0">
                  <Phone className="w-5 h-5 text-cyan-500" />
                </div>
                <div>
                  <h4 className="font-bold text-sm mb-1">Direct Line</h4>
                  <p className="text-xs text-zinc-500">+63 912 345 6789</p>
                  <p className="text-xs text-zinc-500">Mon-Sat, 9AM - 6PM</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  );
}
