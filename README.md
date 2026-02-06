# datama2-techzone

## 📂 Folder Structure

The project is structured into the following main folders:

* **`client/`** → Customer-facing application
    * *Example:* login/register, product catalog, cart, checkout, order tracking, payment simulation, build-a-PC
* **`admin/`** → Admin-facing application
    * *Example:* admin login, product management, order management, user management, inventory management, supplier management, sales dashboard
* **`server/`** → Backend APIs (PHP) for both client and admin applications
    * *Example:* database connections, authentication, order handling
* **`assets/`** → Static files such as images, icons, and CSS used throughout the project

---

## 🔀 Feature Branches

The project uses **feature-based branching**. Each branch corresponds to a specific feature. Some features involve both **client and admin** functionalities; these live in **one branch**, but in separate folders. Other features are specific to either **client** or **admin** only.

---

### **Combined Client + Admin Features**

| Branch Name | Description | Folders |
| :--- | :--- | :--- |
| `feature/auth` | Handles customer login/register and admin login/authentication | `client/auth/`, `admin/auth/` |
| `feature/reviews` | Handles customer posting of reviews & ratings and admin management | `client/reviews/`, `admin/reviews/` |
| `feature/return-items` | Handles customer return requests and admin processing | `client/return/`, `admin/return/` |

---

### **Customer-only Features**

| Branch Name | Description | Folder |
| :--- | :--- | :--- |
| `feature/product-catalog` | Browse, search, and filter products | `client/product-catalog/` |
| `feature/cart-checkout` | Add to cart and complete checkout process | `client/cart/` |
| `feature/order-tracking` | Track order status and history | `client/order-tracking/` |
| `feature/payment` | Simulated payment integration | `client/payment/` |
| `feature/build-a-pc` *(optional)* | Check PC part compatibility | `client/build-a-pc/` |

---

### **Admin-only Features**

| Branch Name | Description | Folder |
| :--- | :--- | :--- |
| `feature/product-management` | Add/edit/delete products, update stock | `admin/product-management/` |
| `feature/order-management` | View and update orders | `admin/order-management/` |
| `feature/user-management` | Monitor users, manage accounts | `admin/user-management/` |
| `feature/inventory-management` | Track stock, inventory log, manage damaged items | `admin/inventory-management/` |
| `feature/supplier-management` | Manage suppliers | `admin/supplier-management/` |
| `feature/sales-dashboard` *(optional)* | Sales overview, stock alerts, best-sellers | `admin/sales-dashboard/` |

---

## 💡 Development Notes

* **Branching Strategy:**
    * Each feature should be developed in its **own branch**.
    * Combined client/admin features live in **one branch** but in separate folders.
    * All branches merge into `develop` for testing before merging to `main`.

* **CSS / Styling:**
    * Each feature or folder has its own CSS file to prevent design conflicts across different modules.
    * In cases where both the client and admin applications share the same design elements, the styling should be placed in a global CSS file for consistency.
