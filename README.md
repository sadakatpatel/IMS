# 📦 Inventory Management System

A complete, production-level Inventory Management System built with **PHP**, **MySQL**, **HTML5**, **CSS3**, **JavaScript**, and **Bootstrap 5**.

## ⭐ Features

✅ **Authentication & Security**
- User login/logout with session handling
- Bcrypt password hashing
- Role-based access control (Admin, Staff)
- Session timeout management
- Activity logging

✅ **Dashboard**
- Real-time statistics
- Low stock alerts
- Recent sales overview
- Performance metrics

✅ **Product Management**
- Add, edit, delete products
- Product categories
- SKU and barcode tracking
- Stock level monitoring
- Low stock warnings

✅ **Supplier Management**
- Manage supplier information
- Contact tracking
- Payment terms
- Purchase history

✅ **Purchase Orders**
- Create purchase orders
- Multiple products per order
- Status tracking (Pending, Completed, Cancelled)
- Auto stock updates
- Order management

✅ **Sales Module**
- Create sales invoices
- Customer information management
- Automatic stock reduction
- Payment status tracking
- Sales history

✅ **Reports & Exports**
- Export to CSV (Products, Suppliers, Orders, Sales)
- Date range filtering
- Stock reports
- Financial reports
- Comprehensive analytics

✅ **User Management**
- Add/edit/delete users (Admin only)
- Role assignment
- User activity tracking
- Status management

✅ **Settings**
- Company configuration
- Currency and tax settings
- System preferences
- Database backup

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- MySQL 5.7+
- PHP 7.4+
- Modern web browser

### Installation

1. **Extract to XAMPP:**
   ```
   C:\xampp\htdocs\inven\
   ```

2. **Start XAMPP:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

3. **Create Database:**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Import `database.sql` file
   - Database will be created automatically

4. **Update Configuration (if needed):**
   - Edit: `app/config/config.php`
   - Update DB_HOST, DB_USER, DB_PASS, DB_NAME

5. **Access Application:**
   ```
   http://localhost/inven/public/
   ```

6. **Login with default credentials:**
   ```
   Username: admin
   Password: password
   ```

⚠️ **Change admin password immediately after first login!**

## 📁 Directory Structure

```
inven/
├── app/
│   ├── config/          # Configuration & database
│   ├── controllers/     # Business logic
│   └── models/          # Database models
├── public/              # Web root
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript
│   ├── images/          # Product images
│   └── *.php            # Page files
├── views/               # Template files
└── database.sql         # Database schema
```

## 🔐 Default Credentials

```
Username: admin
Password: password
```

## 💻 Modules

| Module | Description | Access |
|--------|-------------|--------|
| **Dashboard** | Overview & metrics | All |
| **Products** | Inventory management | All |
| **Suppliers** | Vendor management | All |
| **Purchase Orders** | Procurement | All |
| **Sales** | Customer transactions | All |
| **Reports** | Data exports & analytics | All |
| **Users** | Staff management | Admin only |
| **Settings** | System configuration | Admin only |

## 🎨 Technology Stack

**Frontend:**
- HTML5
- CSS3 (Bootstrap 5)
- JavaScript (ES6+)
- Chart.js for graphs

**Backend:**
- PHP 7.4+ / 8.0+
- MySQL 5.7+ / 8.0+
- MySQLi with prepared statements
- MVC-like architecture

**Security:**
- Password hashing (bcrypt)
- SQL injection prevention
- XSS protection
- CSRF protection via sessions
- Input validation & sanitization

## 🔧 Configuration

### Database

Edit `app/config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventory_system');
```

### Application Settings

```php
define('APP_NAME', 'Inventory Management System');
define('APP_URL', 'http://localhost/inven');
define('SESSION_TIMEOUT', 30); // minutes
define('DEBUG_MODE', true); // false in production
```

## 🚨 Troubleshooting

### "Database Connection Failed"
1. Ensure MySQL is running
2. Verify credentials in `config.php`
3. Check database exists in phpMyAdmin

### "Blank Page or Error"
1. Enable DEBUG_MODE in config.php
2. Check browser console (F12)
3. Verify file permissions on uploads directory

### "Login Not Working"
1. Check users table in database
2. Clear browser cache
3. Verify session directory has write permissions

### "Image Upload Failed"
1. Ensure `public/images/` directory exists
2. Check file permissions (write access)
3. Verify file type is allowed
4. Check file size limits

## 📚 Key Features Details

### Authentication
- Sessions with timeout (configurable)
- Password hashing with bcrypt
- Admin and Staff roles
- Activity logging

### Dashboard
- Real-time metrics
- Low stock alerts
- Recent transactions
- Quick navigation

### Product Management
- Category organization
- Stock tracking
- SKU & barcode support
- Image uploads
- Reorder level alerts

### Purchase Orders
- Multi-product orders
- Status management
- Automatic stock updates
- Supplier tracking
- Order history

### Sales Module
- Invoice generation
- Customer tracking
- Inventory reduction
- Payment tracking
- Sales analytics

### Reports
- CSV exports
- Date filtering
- Stock analysis
- Financial reports
- Supplier analytics

## 🔒 Security Features

✅ Prepared statements (SQL injection prevention)
✅ Password hashing with bcrypt
✅ Input validation & sanitization
✅ Session-based authentication
✅ Role-based access control
✅ Activity logging
✅ HTTPS ready
✅ Secure file uploads

## 📊 Database Schema

**Tables:**
- users
- products
- categories
- suppliers
- purchase_orders
- purchase_order_items
- sales
- sales_items
- activity_logs
- settings

All tables have proper relationships, indexes, and constraints.

## 💡 Tips

1. **Regular Backups**
   - Use Settings > Backup Database
   - Store backups securely

2. **Low Stock Monitoring**
   - Set appropriate reorder levels per product
   - Check dashboard alerts daily

3. **User Management**
   - Create separate accounts for staff
   - Restrict admin access to trusted users

4. **Data Exports**
   - Regular exports for backup
   - Share reports with management
   - Archive monthly reports

## 🎯 Next Steps

1. ✅ Install and verify setup
2. 📝 Add company information in Settings
3. 📦 Create product categories
4. 🏢 Add suppliers
5. 📊 Import products
6. 👥 Create staff user accounts
7. 🚀 Start inventory management!

## 📞 Support

For issues or questions:
1. Check SETUP_GUIDE.md for detailed instructions
2. Review browser console (F12)
3. Check XAMPP error logs
4. Verify database in phpMyAdmin

## 📝 Notes

- All data is stored in MySQL database
- Session files stored in PHP temp directory
- Uploaded images stored in `public/images/`
- Activity logs help with compliance

## 🎉 You're Ready!

The system is ready to use. Start by logging in and exploring the dashboard.

---

**Version:** 1.0.0  
**Framework:** Core PHP (No external frameworks)  
**Database:** MySQL  
**License:** Open Source

Enjoy managing your inventory! 📦✨
