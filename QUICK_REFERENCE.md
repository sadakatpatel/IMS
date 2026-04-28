# 🎯 Quick Reference Guide

## 🚀 Quick Start (5 Minutes)

### 1. Copy Files
```
Extract all files to: C:\xampp\htdocs\inven\
```

### 2. Start Services
- Open XAMPP Control Panel
- Click START → Apache
- Click START → MySQL

### 3. Create Database
- Visit: `http://localhost/phpmyadmin`
- Click SQL tab
- Paste contents of `database.sql`
- Click GO

### 4. Access Application
```
http://localhost/inven/public/
```

### 5. Login
```
Username: admin
Password: password
```

---

## 📱 Main URLs

| Page | URL |
|------|-----|
| Login | `http://localhost/inven/public/index.php` |
| Dashboard | `http://localhost/inven/public/dashboard.php` |
| Products | `http://localhost/inven/public/products.php` |
| Suppliers | `http://localhost/inven/public/suppliers.php` |
| Orders | `http://localhost/inven/public/purchase_orders.php` |
| Sales | `http://localhost/inven/public/sales.php` |
| Reports | `http://localhost/inven/public/reports.php` |
| Users | `http://localhost/inven/public/users.php` |
| Settings | `http://localhost/inven/public/settings.php` |
| Admin Help | `http://localhost/phpmyadmin` |

---

## 📝 Configuration Quick Edit

### Change Admin Email
File: `app/config/config.php`
```php
define('ADMIN_EMAIL', 'your@email.com');
```

### Change Session Timeout
File: `app/config/config.php`
```php
define('SESSION_TIMEOUT', 60); // 60 minutes
```

### Change App Name
File: `app/config/config.php`
```php
define('APP_NAME', 'My Inventory System');
```

### Change Database Name
File: `app/config/config.php`
```php
define('DB_NAME', 'my_database');
```

---

## 💾 Database Queries Reference

### Reset Admin Password
```sql
UPDATE users SET password = '$2y$10$7YD6H7h7qqKKMg8J5K1RIeWl7k5K5K5K5K5K5K5K5K5K5K5K5K' 
WHERE id = 1;
```
(New password: `password`)

### Check Database Size
```sql
SELECT table_schema "Database", 
ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) "Size in MB"
FROM information_schema.tables
WHERE table_schema = 'inventory_system';
```

### View All Products with Stock
```sql
SELECT id, name, quantity, reorder_level 
FROM products 
WHERE status = 'Active'
ORDER BY name;
```

### View Total Sales by Month
```sql
SELECT DATE_FORMAT(sale_date, '%Y-%m') as Month,
COUNT(*) as Orders,
SUM(total_amount) as Sales
FROM sales
WHERE status != 'Cancelled'
GROUP BY DATE_FORMAT(sale_date, '%Y-%m');
```

### View Low Stock Items
```sql
SELECT name, quantity, reorder_level 
FROM products 
WHERE quantity <= reorder_level 
AND status = 'Active'
ORDER BY quantity;
```

### View Recent Orders
```sql
SELECT po.order_number, s.name as supplier, po.total_amount, po.status, po.created_at
FROM purchase_orders po
JOIN suppliers s ON po.supplier_id = s.id
ORDER BY po.created_at DESC
LIMIT 10;
```

---

## 🛠️ Common Tasks

### Add a New Product
1. Navigate to Products
2. Click "+ Add Product" button
3. Fill in details
4. Click Save

### Create Purchase Order
1. Navigate to Orders
2. Click "+ New Order"
3. Select Supplier
4. Click "Add Items" for each product
5. Mark as Completed

### Create Sale
1. Navigate to Sales
2. Click "+ New Sale"
3. Enter Customer info
4. Click "Add Items" for products
5. Review and Save

### Generate Report
1. Navigate to Reports
2. Select report type
3. Choose date range (if needed)
4. Click "Export CSV"

### Add New User
1. Navigate to Users (Admin)
2. Click "+ Add User"
3. Fill username, email, password
4. Select role (Admin/Staff)
5. Save

---

## 🔧 Troubleshooting Quick Fixes

### Can't Connect to Database
```
1. Check XAMPP MySQL is running (green indicator)
2. Verify DB_HOST in app/config/config.php = 'localhost'
3. Verify DB_USER = 'root' and DB_PASS = '' (or your password)
4. Test in phpMyAdmin
```

### Blank Page After Login
```
1. Check PHP version: http://localhost/test.php
2. Enable DEBUG_MODE in config.php = true
3. Check browser console (F12) for errors
4. Review XAMPP error.log file
```

### Images Not Uploading
```
1. Verify public/images/ directory exists
2. Right-click → Properties → Security → Edit → Grant Full Control
3. Check MAX_FILE_SIZE in config.php
4. Verify file extension is allowed
```

### Session Expires Too Quickly
```
1. Increase SESSION_TIMEOUT in config.php
2. Check PHP session.gc_maxlifetime setting
3. Verify tmp/ directory has write permissions
```

---

## 📦 File Backup Commands

### Backup Database (Manual)
```
From phpMyAdmin:
1. Select inventory_system database
2. Click Export tab
3. Select "Custom" option
4. Click "Go"
5. Save file
```

### Backup Database (Command Line)
```bash
mysqldump -u root -p inventory_system > backup.sql
```

### Restore Database (Command Line)
```bash
mysql -u root -p inventory_system < backup.sql
```

---

## 📊 Useful System Info

### Check PHP Version
- Visit: `http://localhost/test.php`
- Or run: `php -v` in command line

### Check MySQL Version
- In phpMyAdmin, click "Server" tab
- Shows version info

### Check PHP Extensions
- In phpMyAdmin, click "Variables" tab
- View loaded modules

---

## 🔑 Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Search | Ctrl+F |
| Developer Tools | F12 |
| Refresh | F5 or Ctrl+R |
| New Tab | Ctrl+T |
| Save | Ctrl+S |

---

## 🎨 UI Elements Quick Reference

### Buttons
- **Blue buttons**: Action (Save, Update, Submit)
- **Red buttons**: Delete (Warning)
- **Green buttons**: Add/Create (Positive action)
- **Gray buttons**: Cancel/Reset

### Badges
- **Green badge**: Active/Success
- **Red badge**: Danger/Error
- **Yellow badge**: Warning/Pending
- **Gray badge**: Inactive/Disabled

### Alerts
- **Green alert**: Success message
- **Red alert**: Error message
- **Yellow alert**: Warning message
- **Blue alert**: Info message

---

## 💡 Pro Tips

1. **Regular Backups**
   - Export database weekly
   - Store in secure location
   - Keep versioned backups

2. **Monitor Stock**
   - Check dashboard daily
   - Set appropriate reorder levels
   - Act on low stock alerts

3. **Activity Logs**
   - Review logs for audit trail
   - Track all user changes
   - Help with troubleshooting

4. **Reports**
   - Export monthly reports
   - Archive for record keeping
   - Share with management

5. **Security**
   - Change password monthly
   - Review user access
   - Monitor activity logs
   - Keep backups secure

---

## 📞 When Things Go Wrong

### Step 1: Check the Logs
```
1. XAMPP error.log
2. Browser console (F12)
3. phpMyAdmin for DB issues
```

### Step 2: Verify Configuration
```
1. Check app/config/config.php
2. Verify database connection
3. Ensure files are readable
```

### Step 3: Test Basics
```
1. Can you ping XAMPP server?
2. Is database accessible in phpMyAdmin?
3. Is PHP enabled?
```

### Step 4: Check Permissions
```
1. public/images/ writable?
2. File ownership correct?
3. XAMPP user permissions set?
```

### Step 5: Restart & Clear
```
1. Restart Apache
2. Clear browser cache
3. Restart XAMPP services
4. Try incognito mode
```

---

## ✅ Daily Checklist

- [ ] Check dashboard for alerts
- [ ] Review low stock items
- [ ] Process pending orders
- [ ] Check recent sales
- [ ] Monitor user activity
- [ ] Backup database (weekly)
- [ ] Review reports
- [ ] Update inventory counts

---

## 📚 Documentation Files

| File | Content |
|------|---------|
| README.md | Quick overview |
| SETUP_GUIDE.md | Detailed setup steps |
| FILES_REFERENCE.md | File descriptions |
| PROJECT_COMPLETE.md | Completion status |
| QUICK_REFERENCE.md | This file |

---

## 🔗 Resources

- **PHP Manual**: https://www.php.net/manual/
- **MySQL Manual**: https://dev.mysql.com/doc/
- **Bootstrap Docs**: https://getbootstrap.com/docs/
- **MDN Web Docs**: https://developer.mozilla.org/

---

## 🎯 Next Steps

1. ✅ Setup complete
2. 🔐 Change admin password
3. 📝 Add company information
4. 📦 Create product categories
5. 🏢 Add suppliers
6. 📊 Import products
7. 👥 Create staff accounts
8. 🚀 Start using system!

---

**Quick Reference v1.0**  
**Keep this handy for common tasks!** 📋

---

## 💬 Common Questions

### Q: How do I backup my database?
**A:** Navigate to Settings (admin only) → Database Backup → Create Backup

### Q: Can I access this from my phone?
**A:** Yes! Use responsive design. URL: `http://your-ip:80/inven/public/`

### Q: How do I add more users?
**A:** Users page (admin) → Add User → Fill form → Save

### Q: Can I export all data?
**A:** Yes! Reports page → Select type → Export CSV

### Q: How often should I backup?
**A:** At least weekly, daily for production

### Q: What if I forget the admin password?
**A:** Use the SQL query provided in troubleshooting section

### Q: Can I customize colors?
**A:** Yes! Edit public/css/style.css CSS variables

### Q: How do I add product images?
**A:** When adding product, click image field and select file

---

**Happy inventory management!** 📦✨
