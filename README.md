# Groco - Multi-Seller Grocery Marketplace

A comprehensive multi-seller grocery marketplace platform built with PHP, MySQL, and XAMPP. This platform enables multiple sellers to list their products, buyers to shop, and administrators to manage the marketplace efficiently.

## 🚀 Key Features

- **Multi-Seller System**
  - Sellers can register with company details
  - Each product is uniquely linked to a seller and their company
  - Support for duplicate product names across different sellers
  - Sellers can manage their products and track orders

- **Product Management**
  - Admin approval system for products (pending/approved/rejected)
  - Product reviews and ratings (only from verified buyers)
  - One review per user per product-seller combination
  - Product cards display average rating and review count
  - Image upload with automatic resizing for better UI

- **Order System**
  - Comprehensive order status tracking:
    - Pending
    - Processed
    - Shipped
    - Delivered
    - Cancelled
  - Visual order progress timeline
  - **Update button is instantly disabled after marking as delivered or cancelled**
  - Separate views for pending and completed orders

- **Product Comparison**
  - Compare products by price across sellers
  - **Compare reviews:** See average rating and review count for each seller's product in the comparison table

- **Admin Dashboard**
  - Product approval management with seller/company details
  - Advanced filtering by:
    - Product name
    - Category
    - Seller/Company
    - Order status
    - User name
  - Order management with status updates
  - User management
  - Clean, modern UI with responsive design

- **Seller Dashboard**
  - View product stats and recent orders
  - **Order management placeholder:** Seller order management page is a placeholder; implement as needed

## 📋 System Requirements

- XAMPP (PHP 7.4+)
- MySQL 5.7+
- Modern web browser
- Minimum 2GB RAM
- 1GB free disk space

## 🛠️ Installation

1. **XAMPP Setup**
   - Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Start Apache and MySQL services

2. **Project Setup**
   - Place project files in `C:/xampp/htdocs/Groco/Groco/grocery store/`
   - Ensure proper file permissions

3. **Database Configuration**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create database: `shop_db`
   - Import `shop_db.sql`
   - Verify database connection in `config.php`
   - **Schema Note:** Ensure `orders.placed_on` is of type `DATETIME` for correct order tracking

## 🔄 Backup Instructions

### Database Backup
1. Open phpMyAdmin
2. Select `shop_db`
3. Click "Export"
4. Choose "Custom" export
5. Select "SQL" format
6. Enable "Add CREATE DATABASE / USE statement"
7. Click "Go" to download

### Code Backup
1. Copy entire project directory
2. Store in secure location
3. Include all files and folders:
   - PHP files
   - CSS/JS assets
   - Uploaded images
   - Configuration files

## 👥 User Access

### Admin Access
- URL: `http://localhost/Groco/Groco/grocery%20store/admin.php`
- Default credentials:
  - Username: admin
  - Password: admin123
- **Important:** Change default password after first login

### Seller Access
- Register at: `http://localhost/Groco/Groco/grocery%20store/seller_register.php`
- Required fields:
  - Company name
  - Contact details
  - Business information

### Buyer Access
- Register at: `http://localhost/Groco/Groco/grocery%20store/register.php`
- Browse products without login
- Login required for:
  - Placing orders
  - Writing reviews
  - Tracking orders

## 🔒 Security Recommendations

1. **Database Security**
   - Change default MySQL root password
   - Use strong passwords for all user accounts
   - Regular database backups

2. **File Security**
   - Set proper file permissions
   - Protect sensitive directories
   - Regular code backups

3. **Server Security**
   - Keep XAMPP updated
   - Monitor error logs
   - Regular security audits

4. **Password Security**
   - **Current implementation uses MD5 for password hashing. For production, upgrade to `password_hash()` and `password_verify()` for better security.**

## 🐛 Common Issues & Solutions

1. **Database Connection**
   - Verify XAMPP services are running
   - Check `config.php` credentials
   - Ensure database exists

2. **Image Upload**

   -![image alt](https://github.com/Smartswagg/Grocery-Groco-website/blob/e2581e064cd51fa9aa2bb6175c6d39c27553ff56/IMAGES/customer%20homepage.png)
4. **Session Issues**
   - Clear browser cookies
   - Check PHP session settings
   - Verify session directory permissions

## 📞 Support

For technical support:
1. Check error logs in XAMPP
2. Review troubleshooting section
3. Contact system administrator

## 📝 License

This project is proprietary software. All rights reserved.

---

**Note**: This README is specific to the Groco Multi-Seller Grocery Marketplace project. Customize paths and settings according to your deployment environment. 
