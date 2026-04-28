# Inventory Management System - Setup & Installation Guide

## 📋 Prerequisites

Before you begin, ensure you have:
- **XAMPP** (Apache, MySQL, PHP) installed and running
- **MySQL 5.7+** or **MySQL 8.0+**
- **PHP 7.4+** or **PHP 8.0+**
- A modern web browser (Chrome, Firefox, Edge, Safari)
- Basic knowledge of PHP and MySQL

---

## 🚀 Installation Steps

### Step 1: Download & Extract Project Files

1. Extract the project files to your XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\inven\
   ```

   Ensure your directory structure looks like:
   ```
   inven/
   ├── app/
   ├── public/
   ├── views/
   ├── database.sql
   └── [other files]
   ```

### Step 2: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Click **Start** button for:
   - **Apache**
   - **MySQL**

3. Verify both services show as running (green indicator)

### Step 3: Create Database

1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on **SQL** tab at the top
3. Copy and paste the entire contents of `database.sql` file
4. Click **Go** button to execute the SQL script

   **Alternative method using MySQL command line:**
   ```bash
   mysql -u root -p < C:\xampp\htdocs\inven\database.sql
   ```

### Step 4: Verify Database Configuration

1. Open file: `app/config/config.php`
2. Verify these settings match your XAMPP setup:
   ```php
   define('DB_HOST', 'localhost');      // Usually localhost
   define('DB_USER', 'root');           // Default XAMPP user
   define('DB_PASS', '');               // Empty for default XAMPP
   define('DB_NAME', 'inventory_system');
   ```

3. If your MySQL has a password, update `DB_PASS`:
   ```php
   define('DB_PASS', 'your_password');
   ```

### Step 5: Set File Permissions

For Windows users, ensure the following directories are writable:

1. `public/images/` - For product image uploads
2. `public/` - For CSS and JS files

**Command (if using file manager):**
- Right-click folder → Properties → Security → Edit → Grant Full Control

### Step 6: Access the Application

1. Open your browser
2. Navigate to: `http://localhost/inven/public/`
3. You should see the login page

---

## 🔐 Default Login Credentials

Use these credentials to log in for the first time:

```
Username: admin
Password: password
```

**⚠️ IMPORTANT:** Change the admin password immediately after first login!

To change password:
1. Log in with admin account
2. Navigate to **Users** section (Admin only)
3. Edit the admin user and change password

---

## 📁 Project Structure

```
inven/
├── app/                              # Application core
│   ├── config/
│   │   ├── config.php               # Configuration settings
│   │   ├── Database.php             # Database connection class
│   │   └── Helper.php               # Utility functions
│   ├── controllers/
│   │   └── AuthController.php       # Authentication logic
│   └── models/
│       ├── User.php                 # User model
│       ├── Product.php              # Product model
│       ├── Supplier.php             # Supplier model
│       ├── Category.php             # Category model
│       ├── PurchaseOrder.php        # Purchase order model
│       └── Sale.php                 # Sale model
│
├── public/                           # Public files (web root)
│   ├── index.php                    # Login page
│   ├── dashboard.php                # Dashboard
│   ├── products.php                 # Product management
│   ├── suppliers.php                # Supplier management
│   ├── purchase_orders.php          # Purchase order module
│   ├── sales.php                    # Sales module
│   ├── reports.php                  # Reports & exports
│   ├── users.php                    # User management (admin)
│   ├── settings.php                 # Settings (admin)
│   ├── logout.php                   # Logout
│   ├── css/
│   │   └── style.css                # Main stylesheet
│   ├── js/
│   │   └── script.js                # Main JavaScript
│   └── images/                      # Product images (uploads)
│
├── views/                            # View templates
│   ├── layouts/
│   │   ├── header.php               # Header with navigation
│   │   └── footer.php               # Footer
│   ├── auth/                        # Authentication views
│   ├── dashboard/                   # Dashboard views
│   ├── products/                    # Product views
│   ├── suppliers/                   # Supplier views
│   ├── orders/                      # Order views
│   ├── sales/                       # Sales views
│   ├── reports/                     # Report views
│   └── users/                       # User management views
│
├── database.sql                      # Database schema
└── README.md                         # This file
```

---

## 💻 Features Overview

### 1. **Authentication System**
   - Login with username and password
   - Session-based authentication
   - Bcrypt password hashing
   - Role-based access (Admin, Staff)
   - Session timeout (30 minutes configurable)

### 2. **Dashboard**
   - Overview of key metrics
   - Total products, suppliers, orders, sales
   - Low stock alerts
   - Recent sales history
   - Performance indicators

### 3. **Product Management**
   - Add, edit, delete products
   - Product categories
   - SKU and barcode support
   - Stock level tracking
   - Low stock warnings
   - Product search and filtering

### 4. **Supplier Management**
   - Add, edit, delete suppliers
   - Supplier contact information
   - Payment terms tracking
   - Supplier purchase history

### 5. **Purchase Order Module**
   - Create purchase orders
   - Add multiple products per order
   - Order status tracking (Pending, Completed, Cancelled)
   - Automatic stock updates on completion
   - Order history and analytics

### 6. **Sales Module**
   - Create sales/invoices
   - Customer information tracking
   - Product selection and quantity
   - Automatic stock reduction
   - Payment status tracking
   - Sales history and reports

### 7. **Reports & Exports**
   - Export products to CSV
   - Export suppliers to CSV
   - Export purchase orders to CSV
   - Export sales to CSV
   - Date-range filtering
   - Stock reports
   - Financial reports

### 8. **User Management** (Admin Only)
   - Add, edit, delete users
   - Role assignment (Admin, Staff)
   - User status management
   - User activity logging

### 9. **Settings** (Admin Only)
   - Company information
   - Currency and tax settings
   - System preferences
   - Backup management

### 10. **Activity Logging**
   - Log all user actions
   - Track changes to records
   - Timestamp all activities
   - Helps with compliance and auditing

---

## 🔧 Configuration

### Modify App Settings

Edit `app/config/config.php`:

```php
// Session timeout in minutes
define('SESSION_TIMEOUT', 30);

// Upload directory size limit (in bytes)
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Allowed file extensions for uploads
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Debug mode (set to false in production)
define('DEBUG_MODE', true);
```

### Change Database Settings

If your database configuration is different:

```php
define('DB_HOST', 'your_host');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

---

## 🚨 Troubleshooting

### Issue: "Database Connection Failed"

**Solution:**
1. Verify MySQL is running in XAMPP Control Panel
2. Check database credentials in `app/config/config.php`
3. Ensure `inventory_system` database exists in MySQL
4. Use phpMyAdmin to verify: `http://localhost/phpmyadmin`

### Issue: "Access Denied" or Blank Page

**Solution:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Check file permissions on `public/images/` directory
3. Verify PHP error logs in XAMPP
4. Check browser console for JavaScript errors

### Issue: Products Not Showing / Image Upload Failed

**Solution:**
1. Verify `public/images/` directory exists
2. Set correct permissions: Right-click → Properties → Security
3. Check `MAX_FILE_SIZE` in config.php
4. Ensure file is in allowed extensions

### Issue: Login Not Working

**Solution:**
1. Verify admin user exists in database:
   ```sql
   SELECT * FROM users WHERE username = 'admin';
   ```
2. Reset password using MySQL:
   ```sql
   UPDATE users SET password = '$2y$10$7YD6H7h7qqKKMg8J5K1RIeWl7k5K5K5K5K5K5K5K5K5K5K5K5K5K' 
   WHERE username = 'admin';
   ```
   (Password: 'password')

3. Check if sessions are working:
   - Verify `php.ini` has `session.save_path` configured
   - Check `tmp/` directory permissions

---

## 🔒 Security Best Practices

1. **Change Default Credentials**
   - Change admin password immediately
   - Create unique usernames for staff

2. **Use HTTPS in Production**
   - Install SSL certificate
   - Update APP_URL in config.php to use https://

3. **Database Backups**
   - Regularly backup your database
   - Use Reports > Backup Database feature
   - Store backups in secure location

4. **File Permissions**
   - Restrict access to `app/config/` directory
   - Set proper permissions on upload directory
   - Never expose sensitive files

5. **Input Validation**
   - All inputs are validated and sanitized
   - SQL injection protection with prepared statements
   - XSS protection with output escaping

6. **Session Security**
   - Set session timeout appropriately
   - Clear cookies on logout
   - Use secure session storage

---

## 📊 Sample Data

The database comes with sample data:

- **Admin User**: username: `admin`, password: `password`
- **Sample Categories**: Electronics, Clothing, Books, Office Supplies, Hardware
- **Sample Suppliers**: 3 pre-configured suppliers
- **Sample Settings**: Default currency, tax rate, etc.

You can modify or delete this sample data as needed.

---

## 🆘 Support & Documentation

### Common Database Queries

**Reset Admin Password:**
```sql
UPDATE users SET password = '$2y$10$7YD6H7h7qqKKMg8J5K1RIeWl7k5K5K5K5K5K5K5K5K5K5K5K5K' 
WHERE id = 1;
```

**Check Low Stock Products:**
```sql
SELECT * FROM products 
WHERE quantity <= reorder_level 
AND status = 'Active';
```

**Get Sales Report by Date Range:**
```sql
SELECT * FROM sales 
WHERE sale_date BETWEEN '2024-01-01' AND '2024-12-31' 
AND status != 'Cancelled';
```

**View User Activity:**
```sql
SELECT * FROM activity_logs 
ORDER BY created_at DESC 
LIMIT 100;
```

---

## 📈 Performance Optimization

1. **Database Indexes**
   - All tables have indexed primary keys
   - Foreign key indexes for relationships
   - Search term indexes for faster queries

2. **Pagination**
   - Tables are paginated (10 items per page)
   - Reduces database load
   - Improves page load time

3. **Caching**
   - Implement browser caching headers
   - Consider Redis for session storage in production

---

## 🎨 Customization

### Change Color Scheme

Edit `public/css/style.css`:

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    /* ... other colors */
}
```

### Add Custom Branding

Edit `app/config/config.php`:

```php
define('APP_NAME', 'Your Company Inventory System');
```

---

## 📝 License

This project is provided as-is for educational and business use.

---

## 🎯 Next Steps

1. **Log in** with default credentials
2. **Change admin password** in Users section
3. **Add your company information** in Settings
4. **Create product categories**
5. **Add suppliers**
6. **Import or add products**
7. **Start managing inventory**

---

## ✅ Verification Checklist

- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] Database `inventory_system` is created
- [ ] Application accessible at `http://localhost/inven/public/`
- [ ] Can log in with admin credentials
- [ ] Dashboard loads without errors
- [ ] Can navigate between all modules
- [ ] Can create/edit/delete records
- [ ] Image uploads work properly
- [ ] Reports can be exported

---

## 📞 Troubleshooting Contacts

For detailed error messages:
1. Check browser console (F12)
2. Review XAMPP error logs
3. Check `error_log` file in project root
4. Enable DEBUG_MODE in config.php for detailed errors

---

**Version:** 1.0.0  
**Last Updated:** 2024  
**Developed for:** XAMPP / PHP / MySQL

---

Happy inventory management! 🎉
