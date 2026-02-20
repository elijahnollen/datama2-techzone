import { createBrowserRouter } from 'react-router';
import { Root } from './Root';
import { Home } from './pages/Home';
import { Cart } from './pages/Cart';
import { ProductDetail } from './pages/ProductDetail';
import { Login } from './pages/Login';
import { SignUp } from './pages/SignUp';
import { ProductResults } from './pages/ProductResults';
import { MyOrders } from './pages/MyOrders';
import { Checkout } from './pages/Checkout';
import { ReturnRequest } from './pages/ReturnRequest';
import { Contact } from './pages/Contact';
import { Account } from './pages/Account';
import { Wallet } from './pages/Wallet';
import { Messages } from './pages/Messages';
import { Profile } from './pages/Profile';
// REMOVED: ForgotPassword import

export const router = createBrowserRouter([
  {
    path: '/',
    Component: Root,
    children: [
      { index: true, Component: Home },
      { path: 'cart', Component: Cart },
      { path: 'product/:id', Component: ProductDetail },
      { path: 'login', Component: Login },
      { path: 'signup', Component: SignUp }, 
      { path: 'product-results', Component: ProductResults },
      { path: 'orders', Component: MyOrders },
      { path: 'my-orders', Component: MyOrders },
      { path: 'checkout', Component: Checkout },
      { path: 'return-request', Component: ReturnRequest },
      { path: 'contact', Component: Contact },
      { path: 'account', Component: Account },
      { path: 'wallet', Component: Wallet },
      { path: 'messages', Component: Messages },
      { path: 'profile', Component: Profile },
      { path: 'favorites', Component: Home },
      // REMOVED: both forgot-password paths
    ],
  },
]);