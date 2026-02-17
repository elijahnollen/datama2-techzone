import { useState } from 'react';
import { useNavigate } from 'react-router';
import { ArrowLeft, Send } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { Header } from '../components/Header';
import Footer from '../imports/Footer-4-4788';

export function Messages() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [newMessage, setNewMessage] = useState('');

  // Mock conversations
  const conversations = [
    {
      id: '1',
      user: 'TechZone Support',
      lastMessage: 'How can we help you today?',
      time: '10:30 AM',
      unread: 2,
    },
    {
      id: '2',
      user: 'Order Updates',
      lastMessage: 'Your order #ORD-123 has been shipped!',
      time: 'Yesterday',
      unread: 0,
    },
  ];

  const [selectedConversation, setSelectedConversation] = useState(conversations[0]);

  // Mock messages
  const messages = [
    { id: '1', sender: 'them', text: 'Hello! How can we help you today?', time: '10:25 AM' },
    { id: '2', sender: 'me', text: 'I have a question about my order', time: '10:27 AM' },
    { id: '3', sender: 'them', text: 'Of course! What would you like to know?', time: '10:30 AM' },
  ];

  const handleSend = () => {
    if (newMessage.trim()) {
      // TODO: Send message to backend
      setNewMessage('');
    }
  };

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
          <span className="text-cyan-500">MESSAGES</span>
        </h1>

        {/* Messages Interface */}
        <div className="grid lg:grid-cols-[300px_1fr] gap-6 h-[600px]">
          {/* Conversations List */}
          <div className="bg-white border border-zinc-200 rounded-2xl overflow-hidden">
            <div className="p-4 border-b border-zinc-200">
              <h2 className="font-bold text-sm">Conversations</h2>
            </div>
            <div className="overflow-y-auto">
              {conversations.map((conv) => (
                <button
                  key={conv.id}
                  onClick={() => setSelectedConversation(conv)}
                  className={`w-full p-4 text-left border-b border-zinc-100 hover:bg-zinc-50 transition-colors ${
                    selectedConversation.id === conv.id ? 'bg-cyan-50' : ''
                  }`}
                >
                  <div className="flex items-start justify-between mb-1">
                    <p className="font-bold text-sm">{conv.user}</p>
                    {conv.unread > 0 && (
                      <span className="bg-cyan-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        {conv.unread}
                      </span>
                    )}
                  </div>
                  <p className="text-xs text-zinc-500 line-clamp-1">{conv.lastMessage}</p>
                  <p className="text-xs text-zinc-400 mt-1">{conv.time}</p>
                </button>
              ))}
            </div>
          </div>

          {/* Chat Area */}
          <div className="bg-white border border-zinc-200 rounded-2xl flex flex-col overflow-hidden">
            {/* Chat Header */}
            <div className="p-4 border-b border-zinc-200">
              <h2 className="font-bold">{selectedConversation.user}</h2>
            </div>

            {/* Messages */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4">
              {messages.map((msg) => (
                <div
                  key={msg.id}
                  className={`flex ${msg.sender === 'me' ? 'justify-end' : 'justify-start'}`}
                >
                  <div
                    className={`max-w-[70%] rounded-lg p-3 ${
                      msg.sender === 'me'
                        ? 'bg-cyan-500 text-black'
                        : 'bg-zinc-100 text-black'
                    }`}
                  >
                    <p className="text-sm">{msg.text}</p>
                    <p className="text-xs opacity-70 mt-1">{msg.time}</p>
                  </div>
                </div>
              ))}
            </div>

            {/* Input Area */}
            <div className="p-4 border-t border-zinc-200">
              <div className="flex gap-2">
                <input
                  type="text"
                  value={newMessage}
                  onChange={(e) => setNewMessage(e.target.value)}
                  onKeyPress={(e) => e.key === 'Enter' && handleSend()}
                  placeholder="Type a message..."
                  className="flex-1 px-4 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none"
                />
                <button
                  onClick={handleSend}
                  disabled={!newMessage.trim()}
                  className="bg-cyan-500 text-black p-2 rounded-lg hover:bg-cyan-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Send className="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  );
}
