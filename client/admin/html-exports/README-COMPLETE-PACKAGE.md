# TechZone Admin Dashboard - HTML/CSS Export Package

Complete collection of HTML/CSS pages for the TechZone Admin Dashboard.

## 📦 Package Contents

### Authentication Pages
1. **login-page.html** - Admin login with email/password
2. **forgot-password.html** - Email input for password reset
3. **reset-password.html** - Dark-themed password reset page

### Dashboard Pages
4. **dashboard.html** - Main overview with stats, charts, and recent orders
5. **products.html** - Product management with filters and CRUD operations
6. **orders.html** - Order management with status tracking
7. **users.html** - User management and analytics
8. **inventory.html** - Stock tracking and inventory logs
9. **returns.html** - Returns and refunds processing
10. **suppliers.html** - Supplier management and orders

### Modal Components
11. **add-product-modal.html** - Product creation form
12. **view-order-modal-example.html** - Order details modal
13. **add-supplier-modal.html** - Supplier creation form
14. **add-stock-modal.html** - Inventory stock entry form

## 🎨 Design System

### Brand Colors
```css
Primary Blue: #2563eb
Secondary Cyan: #00bcd4
Text Dark: #111827
Text Body: #374151
Text Muted: #6b7280
Background: #f9fafb
White: #ffffff
Border: #e5e7eb
```

### Status Colors
```css
Success/Completed: #22c55e (#dcfce7 background)
Warning/Pending: #eab308 (#fef3c7 background)
Info/Processing: #3b82f6 (#dbeafe background)
Purple/Shipped: #a855f7 (#e9d5ff background)
Error/Canceled: #ef4444 (#fee2e2 background)
```

### Typography
```css
Font Family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto
Page Title: 28px, 700 weight
Card Title: 18px, 600 weight
Body Text: 14px, 400 weight
Labels: 14px, 500 weight
Small Text: 12px, 400 weight
```

### Spacing Scale
```css
xs: 4px
sm: 8px
md: 12px
base: 16px
lg: 24px
xl: 32px
2xl: 48px
```

## 📐 Layout Structure

### Sidebar Navigation
- Fixed width: 256px
- Contains logo and navigation menu
- Active state with blue accent
- Fixed position for scrolling

### Top Navigation Bar
- Search bar (400px width)
- User profile and notifications
- Fixed at top with white background

### Main Content Area
- Left margin: 256px (for sidebar)
- Padding: 32px
- Responsive grid layouts

## 🔗 Navigation Links

All pages include the same sidebar navigation:

```html
<a href="dashboard.html">Dashboard</a>
<a href="products.html">Products</a>
<a href="orders.html">Orders</a>
<a href="users.html">Users</a>
<a href="inventory.html">Inventory</a>
<a href="returns.html">Returns & Refunds</a>
<a href="suppliers.html">Suppliers</a>
```

## 🔌 PHP Backend Integration

### Form Actions
All forms point to PHP endpoints:

```html
<!-- Login -->
<form action="/api/auth/login.php" method="POST">

<!-- Add Product -->
<form action="/api/products/index.php" method="POST">

<!-- Add Order -->
<form action="/api/orders/index.php" method="POST">

<!-- Update User -->
<form action="/api/users/update.php" method="POST">
```

### Expected PHP Response Format

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

### Authentication
Add session checking to all pages:

```php
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login-page.html');
    exit();
}
?>
```

## 📊 Data Tables

All tables follow the same structure:

```html
<table>
  <thead>
    <tr>
      <th>Column 1</th>
      <th>Column 2</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Data 1</td>
      <td>Data 2</td>
      <td>
        <button class="action-btn edit">Edit</button>
        <button class="action-btn delete">Delete</button>
      </td>
    </tr>
  </tbody>
</table>
```

## 🎯 Interactive Elements

### Buttons
```css
Primary: .add-btn (blue background)
Secondary: .filter-btn (white with border)
Action: .action-btn (icon buttons)
```

### Status Badges
```html
<span class="status-badge status-completed">Completed</span>
<span class="status-badge status-pending">Pending</span>
<span class="status-badge status-processing">Processing</span>
<span class="status-badge status-shipped">Shipped</span>
```

### Form Inputs
```html
<input type="text" class="form-input">
<select class="form-select">
<textarea class="form-textarea">
```

## 📱 Responsive Design

The pages use a desktop-first approach. For mobile responsiveness, add:

```css
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
  }
  .main-content {
    margin-left: 0;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .filters-grid {
    grid-template-columns: 1fr;
  }
}
```

## 🔒 Security Checklist

- [ ] Add CSRF tokens to all forms
- [ ] Validate and sanitize all inputs server-side
- [ ] Use prepared statements for SQL queries
- [ ] Implement rate limiting on login
- [ ] Add XSS protection (escape output)
- [ ] Use HTTPS for all pages
- [ ] Implement proper session management
- [ ] Add brute force protection

## 🚀 Implementation Steps

### 1. Set Up Directory Structure
```
/admin/
  ├── login-page.html
  ├── dashboard.html
  ├── products.html
  ├── orders.html
  ├── users.html
  ├── inventory.html
  ├── returns.html
  ├── suppliers.html
  └── /modals/
      ├── add-product-modal.html
      ├── add-supplier-modal.html
      └── add-stock-modal.html
```

### 2. Convert to PHP
Rename `.html` to `.php` and add:

```php
<?php
session_start();
// Add authentication check
// Add database connection
// Process form submissions
?>
<!DOCTYPE html>
...
```

### 3. Connect Database
```php
<?php
$host = 'localhost';
$dbname = 'techzone_db';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

### 4. Replace Static Data with PHP
```php
<!-- Before -->
<td>₱75,000</td>

<!-- After -->
<td>₱<?php echo number_format($product['price'], 0); ?></td>
```

### 5. Add Form Handlers
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    // Validate and insert into database
    $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->execute([$name, $email]);
    
    header('Location: users.php?success=1');
    exit();
}
```

## 🎨 Customization

### Change Brand Colors
Find and replace in all files:

```css
/* Primary Blue */
#2563eb → Your color

/* Cyan Accent */
#00bcd4 → Your color

/* Logo */
.logo-tech { color: #000; }
.logo-zone { color: #00bcd4; }
```

### Add New Pages
1. Copy `dashboard.html`
2. Update active nav item
3. Change content area
4. Update page title

## 📚 External Libraries (Optional)

For enhanced functionality, add:

### Chart.js (for dashboard charts)
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

### DataTables (for advanced tables)
```html
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
```

### SweetAlert2 (for beautiful alerts)
```html
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

## 💡 Tips for Backend Team

1. **Use the exact form field names** - Forms have `name` attributes that match database columns
2. **Maintain the HTML structure** - Classes are used for styling
3. **Keep status values consistent** - Use the same status values in database
4. **Add loading states** - Disable buttons during form submission
5. **Show success/error messages** - Use the error message styling provided
6. **Implement pagination** - For tables with many rows
7. **Add export features** - CSV/Excel export for reports
8. **Log admin actions** - Track all changes for audit trail

## 🐛 Common Issues

### Issue: Forms not submitting
**Solution:** Check `action` attribute and method (`POST` vs `GET`)

### Issue: Styling broken
**Solution:** Ensure CSS is in `<style>` tag or linked correctly

### Issue: Modal not opening
**Solution:** Check JavaScript functions and window.open() parameters

### Issue: Data not displaying
**Solution:** Verify database connection and query results

## 📞 Support

For questions or issues:
1. Check the `/services/` folder for API specifications
2. Review `/BACKEND_HANDOFF.md` for complete documentation
3. Refer to React components in `/components/` for behavior reference

---

**Version:** 1.0  
**Last Updated:** February 15, 2026  
**Framework:** Pure HTML/CSS (No dependencies)  
**Compatible:** All modern browsers (Chrome, Firefox, Safari, Edge)
