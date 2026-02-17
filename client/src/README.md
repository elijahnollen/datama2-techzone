# TechZone E-commerce Frontend

A modern, fully-functional e-commerce website for tech products built with React, TypeScript, and Tailwind CSS.

## 🚀 Features

- **Product Browsing**: Browse tech products by category (Graphics, Processors, Memory, Cooling, Peripherals)
- **Search & Filtering**: Advanced search with price range filters
- **Shopping Cart**: Full cart management with localStorage persistence
- **User Authentication**: Login/Signup with protected routes
- **Product Details**: Detailed product pages with reviews
- **Checkout System**: Complete checkout flow with delivery options
- **Order Management**: View order history and submit return requests
- **Responsive Design**: Mobile-friendly interface
- **16 Routes**: Complete multi-page navigation

## 🛠️ Tech Stack

- **React 18** - UI Library
- **TypeScript** - Type Safety
- **React Router 7** - Client-side Routing
- **Tailwind CSS 4** - Styling
- **Lucide React** - Icons
- **Motion (Framer Motion)** - Animations
- **Vite** - Build Tool

## 📁 Project Structure

```
techzone-frontend/
├── components/          # Reusable UI components
│   ├── ui/             # shadcn/ui components
│   ├── Header.tsx      # Navigation header
│   ├── ProductCard.tsx # Product display card
│   └── AuthModal.tsx   # Authentication modal
├── contexts/           # React Context providers
│   ├── AuthContext.tsx # Authentication state
│   └── CartContext.tsx # Shopping cart state
├── pages/              # Route pages
│   ├── Home.tsx
│   ├── ProductResults.tsx
│   ├── ProductDetail.tsx
│   ├── Cart.tsx
│   ├── Checkout.tsx
│   └── ... (11 more pages)
├── services/           # API services
│   └── database.ts     # Backend API layer
├── imports/            # Figma design imports
├── styles/             # Global styles
├── types/              # TypeScript types
├── App.tsx             # Main app component
├── Root.tsx            # Layout wrapper
└── routes.tsx          # Route configuration
```

## 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/techzone-frontend.git
   cd techzone-frontend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Run development server**
   ```bash
   npm run dev
   ```

4. **Open in browser**
   ```
   http://localhost:5173
   ```

## 🔌 Backend Integration

This frontend is designed to work with a **Layer Cake Architecture** backend:
- **PHP Middleware** - Transaction Bridge
- **MySQL (The Vault)** - Source of truth
- **MongoDB (The Library)** - Performance layer

### Connect Your Backend

Update the API endpoint in `/services/database.ts`:

```typescript
// Line 27
const API_BASE_URL = 'YOUR_PHP_API_ENDPOINT_HERE';
```

Change to your actual PHP API URL:
```typescript
const API_BASE_URL = 'https://api.yourwebsite.com';
// or for local development:
const API_BASE_URL = 'http://localhost:8000/api';
```

### API Endpoints Expected

The frontend expects these endpoints:

**Products** (MongoDB)
- `GET /products` - Get all products
- `GET /products/:id` - Get product by ID
- `GET /products?category={category}` - Filter by category
- `GET /products/search?q={query}` - Search products

**Orders** (MongoDB)
- `POST /orders` - Create new order
- `GET /orders?userId={userId}` - Get user orders
- `GET /orders/:id` - Get order by ID

**Cart** (MongoDB)
- `POST /cart` - Save cart
- `GET /cart?userId={userId}` - Get saved cart

**Reviews** (MongoDB)
- `GET /reviews?productId={productId}` - Get product reviews
- `POST /reviews` - Submit review

**Returns** (MongoDB)
- `POST /returns` - Submit return request
- `GET /returns?userId={userId}` - Get user returns

**Inventory** (MySQL)
- `GET /inventory/:productId` - Check stock

**Contact** (MongoDB)
- `POST /inquiries` - Submit contact form

**Health Check**
- `GET /health` - API health check

## 📦 Build for Production

```bash
npm run build
```

The build output will be in the `/dist` folder, ready to deploy.

## 🌐 Deployment Options

- **Vercel**: Connect your GitHub repo to Vercel
- **Netlify**: Drag and drop the `/dist` folder
- **Traditional Hosting**: Upload `/dist` contents to your web server

## 🔐 Environment Variables (Optional)

Create a `.env` file if needed:

```env
VITE_API_URL=https://api.yourwebsite.com
```

Then use in code:
```typescript
const API_BASE_URL = import.meta.env.VITE_API_URL;
```

## 📝 Current State

- ✅ All 16 routes working
- ✅ Authentication system with protected checkout
- ✅ Shopping cart with localStorage
- ✅ Mock data for demo mode
- ✅ Ready for backend connection
- ✅ Responsive design
- ✅ All React Router errors resolved

## 🎨 Design

This frontend is based on a Figma design imported into Figma Make, featuring:
- Clean white background
- TECHZONE branding
- Modern card-based layout
- Professional footer with social links
- Intuitive navigation

## 📄 License

This project is for educational/commercial use.

## 👥 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

For issues or questions, please open an issue on GitHub.

---

**Built with ❤️ using React, TypeScript, and Tailwind CSS**
