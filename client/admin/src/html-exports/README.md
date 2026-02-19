# HTML/CSS Admin Dashboard Pages

This folder contains pure HTML/CSS versions of the admin dashboard pages for your backend team.

## 📁 Files Included

### Authentication Pages
- `login-page.html` - Admin login page
- `forgot-password.html` - Password reset request page

### Modal Components
- `add-product-modal.html` - Add/Edit product modal
- `view-order-modal-example.html` - Order details modal
- `add-supplier-modal.html` - Add supplier modal
- `add-stock-modal.html` - Inventory stock entry modal

## 🎨 Design System

### Colors
- **Primary Blue**: `#2563eb` (Buttons, active states)
- **Cyan Accent**: `#00bcd4` (Logo, highlights)
- **Text**: `#111827` (Headings), `#374151` (Body), `#6b7280` (Muted)
- **Backgrounds**: `#ffffff` (White), `#f9fafb` (Light gray)
- **Borders**: `#e5e7eb`, `#d1d5db`

### Typography
- **Font Family**: System fonts (-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto)
- **Headings**: 700 weight, sizes 18px-30px
- **Body**: 400 weight, 14px
- **Labels**: 500 weight, 14px

### Spacing
- Small: 4px, 8px
- Medium: 12px, 16px, 24px
- Large: 32px, 40px, 48px

## 🔌 Backend Integration

### Form Actions
All forms include `action` attributes pointing to PHP endpoints:

```html
<!-- Login Form -->
<form action="/api/auth/login.php" method="POST">

<!-- Add Product Form -->
<form action="/api/products/index.php" method="POST">

<!-- Forgot Password Form -->
<form action="/api/auth/forgot-password.php" method="POST">
```

### Form Fields
All inputs include proper `name` attributes for PHP processing:

```html
<input type="email" name="email" required>
<input type="password" name="password" required>
<input type="text" name="name" required>
<select name="category" required>
```

### PHP Integration Example

**Login Handler (`/api/auth/login.php`):**
```php
<?php
session_start();
$email = $_POST['email'];
$password = $_POST['password'];

// Validate credentials against database
// ...

if ($valid) {
    $_SESSION['admin_id'] = $user['id'];
    header('Location: /admin/dashboard.php');
} else {
    header('Location: /admin/login.html?error=invalid');
}
?>
```

**Add Product Handler (`/api/products/index.php`):**
```php
<?php
$name = $_POST['name'];
$sku = $_POST['sku'];
$category = $_POST['category'];
$price = $_POST['price'];
$stock = $_POST['stock'];
$status = $_POST['status'];
$description = $_POST['description'];
$image = $_POST['image'];

// Insert into database
$stmt = $pdo->prepare("INSERT INTO products (name, sku, category, price, stock, status, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$name, $sku, $category, $price, $stock, $status, $description, $image]);

header('Location: /admin/products.php?success=1');
?>
```

## 📋 Using the Modals

Modals are standalone HTML pages. To use them:

### Option 1: JavaScript Popup
```javascript
function openAddProductModal() {
    window.open('add-product-modal.html', 'Add Product', 'width=700,height=600');
}
```

### Option 2: Include in Page
```html
<!-- Add this div where you want the modal -->
<div id="add-product-modal" class="modal-overlay" style="display: none;">
    <!-- Copy modal content here -->
</div>

<script>
function showModal() {
    document.getElementById('add-product-modal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('add-product-modal').style.display = 'none';
}
</script>
```

### Option 3: PHP Include
```php
<?php if (isset($_GET['add_product'])): ?>
    <?php include 'modals/add-product-modal.php'; ?>
<?php endif; ?>
```

## 🎯 Button Actions

All interactive buttons should trigger these functions:

```javascript
// Add Product Button
<button onclick="showAddProductModal()">Add Product</button>

// View Order Button
<button onclick="viewOrder(orderId)">View Details</button>

// Edit Product Button
<button onclick="editProduct(productId)">Edit</button>

// Delete Product Button
<button onclick="if(confirm('Delete this product?')) deleteProduct(productId)">Delete</button>
```

## 📱 Responsive Design

All pages are mobile-responsive using:
- Flexbox layouts
- CSS Grid for forms
- Media queries (built into the styles)
- Max-width containers

## ✅ Checklist for Backend Team

- [ ] Replace `/api/` paths with your actual API endpoints
- [ ] Add PHP session handling
- [ ] Implement form validation
- [ ] Add error message displays
- [ ] Configure database connections
- [ ] Add CSRF protection tokens
- [ ] Implement success/error redirects
- [ ] Add loading states for forms
- [ ] Test all form submissions

## 🔒 Security Notes

Don't forget to add:
1. **CSRF Tokens** in all forms
2. **Input Sanitization** on server side
3. **SQL Injection Protection** (use prepared statements)
4. **XSS Protection** (escape output)
5. **Session Security** (HTTP-only cookies)

## 💡 Tips

1. **Keep the design consistent** - Use the exact colors and spacing provided
2. **Test forms** - Make sure all field names match your database columns
3. **Add validation** - Both client-side (HTML5) and server-side (PHP)
4. **Error handling** - Show user-friendly error messages
5. **Success feedback** - Redirect or show success messages after actions

## 📞 Questions?

Refer to:
- `/services/` folder for API endpoint specifications
- `/BACKEND_HANDOFF.md` for complete backend documentation
- React components in `/components/` for interactive behavior reference

---

**Note**: These are static HTML/CSS files. You'll need to add JavaScript for modal interactions and AJAX form submissions if you want a more dynamic experience without page reloads.
