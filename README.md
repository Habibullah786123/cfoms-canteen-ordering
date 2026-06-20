# CFOMS - Café Food Order Management System

A robust, multi-role PHP & MySQL web application designated for managing university café food orders, inventory, and canteens.

![Status](https://img.shields.io/badge/Status-Active-success)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-blue)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-orange)

---

## 🚀 Key Features

### 🎓 For Students (Users)

- **Secure Authentication**: Registration and login system.
- **Multi-Canteen Shopping**: Browse menus from different canteens.
- **Cart System**: Add items to cart and checkout securely.
- **Order Tracking**: Track order status (Pending, Preparing, Ready, Completed) in real-time.
- **Order History**: View past orders.
- **Contact Us**: Send messages/feedback directly to admins.

### 🏪 For Canteen Owners

- **Dashboard**: View daily sales, pending orders, and menu counts.
- **Menu Management**: Add, edit, remove, and toggle availability of food items.
- **Order Management**: View new orders and update their status (Pending → Preparing → Ready → Completed).
- **Sales Analytics**: Visualize weekly sales (Interactive Charts).
- **Reviews**: View customer ratings and reviews.

### 🛡️ For Super Admins

- **System Overview**: Track total canteens, revenue, and active users.
- **Canteen Management**: Approve, ban, or delete canteens.
- **User Management**: View and remove users.
- **Message Center**: View and delete messages sent via the "Contact Us" page.

---

## 🛠️ Technology Stack

- **Backend**: PHP (Native, Procedural & OOP)
- **Database**: MySQL (Improved `mysqli` extension with Prepared Statements)
- **Frontend**: HTML5, CSS3, Bootstrap 5, FontAwesome 6
- **JavaScript**: Chart.js for analytics, Custom AJAX for status updates
- **Security**:
  - CSRF Protection (Custom implementation)
  - XSS Prevention (`htmlspecialchars`)
  - SQL Injection Prevention (Prepared Statements)
  - Session Security (`HttpOnly`, `Strict`)
  - Password Hashing (`bcrypt`)

---

## 📂 Project Structure

```
CFOMS/
├── admin/              # Super Admin panel
│   ├── dashboard.php   # System stats
│   ├── canteens.php    # Manage canteens
│   ├── users.php       # Manage users
│   └── messages.php    # View contact messages
├── auth/               # Authentication
│   ├── login.php       # Unified login (Student/Canteen)
│   ├── register.php    # unified register
│   └── logout.php      # Session destroy
├── canteen/            # Canteen Owner panel
│   ├── dashboard.php   # Sales & orders
│   ├── menu.php        # Menu CRUDS
│   ├── orders.php      # Order status management
│   └── reviews.php     # View feedback
├── config/             # Database configuration
├── includes/           # Shared helpers (Session, CSRF, Functions)
├── uploads/            # Initial upload directories
├── user/               # Student interface
│   ├── dashboard.php   # Canteen selection
│   ├── checkout.php    # Order placement
│   ├── contact.php     # Contact form
│   └── views/          # Product & Order views
├── assets/             # CSS/JS resources
├── database.sql        # Main DB Schema
└── index.php           # Entry point (Router)
```

---

## ⚙️ Installation & Setup

1.  **Clone the Repository**

    ```bash
    git clone https://github.com/your-repo/cfoms.git
    cd CFOMS
    ```

2.  **Setup Database**
    - Open PHPMyAdmin (or your preferred SQL client).
    - Create a database named `cfoms`.
    - Import `database.sql` to create the tables.

3.  **Configure Connection**
    - Edit `config/db_connect.php`:

    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'cfoms');
    ```

4.  **Directory Permissions**
    - Ensure the `uploads/` directory is writable:

    ```bash
    chmod -R 755 uploads/
    ```

5.  **Run the App**
    - Place the folder in your `htdocs` (XAMPP) or `www` (WAMP) directory.
    - Visit `http://localhost/CFOMS/` in your browser.

---

## 🧪 Test Credentials

### Super Admin

- **Username**: `admin`
- **Password**: `admin1234`
- **URL**: `http://localhost/CFOMS/admin/login.php`

### Canteen Owner

- **Username**: `canteen1`
- **Password**: `password`
- **Canteen**: Campus Cafe

### Student (User)

- **Username**: `student1`
- **Password**: `password`

---

## 🔒 Security Measures Implemented

1.  **CSRF Protection**: All forms generate and verify unique tokens to prevent Cross-Site Request Forgery.
2.  **Session Hardening**: Sessions use strict cookie parameters (`HttpOnly`, `SameSite`) to prevent hijacking.
3.  **Transactions**: Critical operations like "Checkout" use Database Transactions to ensure data integrity.
4.  **Input Sanitation**: All user inputs are sanitized to prevent XSS.
5.  **Access Control**: Role-Based Access Control (RBAC) enforces strict permission checks on every page load.

---

## 🤝 Contributing

1.  Fork the project.
2.  Create your feature branch (`git checkout -b feature/AmazingFeature`).
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4.  Push to the branch (`git push origin feature/AmazingFeature`).
5.  Open a Pull Request.

---

**© 2026 CFOMS Project**
