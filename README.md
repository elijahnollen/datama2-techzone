# datama2-techzone

## 📂 Folder Structure

The project is structured into four primary directories to separate concerns between the user interface, administrative tools, and backend logic:

* **`client/`** → Customer-facing application. Includes the product catalog, shopping cart, and checkout.
* **`admin/`** → Admin-facing application. Includes tools for managing products, inventory, and users.
* **`server/`** → Backend APIs (PHP) serving both client and admin applications (e.g., DB connections, Auth logic).
* **`assets/`** → Shared static files such as images, icons, and global CSS.

---

## 🔀 Feature Branches

We follow a **feature-based branching** strategy. Features that require a synchronized update between the customer view and the administrative dashboard are grouped into single branches.

### **Combined Client + Admin Features**
*These branches contain code for both the `client/` and `admin/` folders.*

| Branch Name | Description | Folders Involved |
| :--- | :--- | :--- |
| `feature/auth` | Customer registration/login & Admin authentication | `client/auth/`, `admin/auth/` |
| `feature/product-management` | Unified branch for browsing (Client) and CRUD operations (Admin) | `client/products/`, `admin/products/` |
| `feature/reviews` | Customer posting of reviews & Admin moderation | `client/reviews/`, `admin/reviews/` |
| `feature/return-items` | Customer return requests & Admin processing | `client/return/`, `admin/return/` |

### **Client-only Features**
| Branch Name | Description | Folder |
| :--- | :--- | :--- |
| `feature/cart-checkout` | Shopping cart logic and checkout flow | `client/cart/` |
| `feature/order-tracking` | Real-time order status and history | `client/order-tracking/` |
| `feature/payment` | Simulated payment gateway integration | `client/payment/` |
| `feature/build-a-pc` | PC part compatibility checker | `client/build-a-pc/` |

### **Admin-only Features**
| Branch Name | Description | Folder |
| :--- | :--- | :--- |
| `feature/order-management` | Viewing, updating, and fulfilling orders | `admin/order-management/` |
| `feature/user-management` | User monitoring and account permissions | `admin/user-management/` |
| `feature/inventory-management` | Stock tracking, logs, and damaged item reports | `admin/inventory-management/` |
| `feature/supplier-management` | Database of part suppliers and contact info | `admin/supplier-management/` |
| `feature/sales-dashboard` | Sales analytics and stock alerts | `admin/sales-dashboard/` |

---

> [!IMPORTANT]
> * **Branching Strategy:**
>     * Each feature should be developed in its **own branch**.
>     * Combined client/admin features live in **one branch** but in separate folders.
>     * All branches merge into `develop` for testing before merging to `main`.
> 
> * **CSS / Styling:**
>     * Each feature or folder has its own CSS file to prevent design conflicts across different modules.
>     * In cases where both the client and admin applications share the same design elements, the styling should be placed in a global CSS file for consistency.
