# Bihar ka Bazar - Complete E-commerce Platform

Bihar ka Bazar is a comprehensive web-based marketplace that connects Bihar's farmers directly with consumers. Built with PHP, MySQL, Bootstrap, and modern web technologies.

##  Features

### For Farmers
- **Product Management**: Add, edit, and manage agricultural products
- **Order Management**: Track and manage incoming orders
- **Dashboard**: Comprehensive analytics and insights
- **Direct Sales**: Sell directly to consumers without middlemen
- **Profile Management**: Manage farm details and certifications

### For Buyers
- **Product Browsing**: Search and filter products by category, location, price
- **Shopping Cart**: Add products and manage orders
- **Wishlist**: Save favorite products for later
- **Order Tracking**: Track order status and delivery
- **Reviews & Ratings**: Rate and review products

### For Admins
- **User Management**: Manage farmers and buyers
- **Product Moderation**: Approve and manage product listings
- **Order Oversight**: Monitor all transactions
- **Analytics**: Platform-wide statistics and reports
- **Content Management**: Manage categories, coupons, and content

### General Features
- **Responsive Design**: Works on all devices
- **Payment Integration**: Multiple payment options including COD
- **Email Notifications**: Automated email system
- **Search & Filters**: Advanced search and filtering options
- **Multi-language Support**: Ready for localization
- **SEO Optimized**: Search engine friendly URLs and meta tags

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6.4
- **Animations**: AOS (Animate On Scroll)
- **Server**: Apache/Nginx

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)
- PHP extensions: PDO, PDO_MySQL, GD, cURL, mbstring

## 🚀 Installation Guide

### Step 1: Download and Extract
1. Download the complete project files
2. Extract to your web server directory (e.g., `htdocs`, `www`, `public_html`)

### Step 2: Database Setup
1. Create a new MySQL database named `bihar_ka_bazar`
2. Import the database schema:
   \`\`\`sql
   mysql -u your_username -p bihar_ka_bazar < database/bihar_ka_bazar_complete.sql
   \`\`\`

### Step 3: Configuration
1. Open `config/database.php`
2. Update database credentials:
   \`\`\`php
   private $host = "localhost";
   private $db_name = "bihar_ka_bazar";
   private $username = "your_db_username";
   private $password = "your_db_password";
   \`\`\`

### Step 4: File Permissions
Set proper permissions for upload directories:
\`\`\`bash
chmod 755 assets/images/
chmod 755 assets/images/products/
chmod 755 assets/images/categories/
chmod 755 assets/images/avatars/
\`\`\`

### Step 5: Web Server Configuration

#### For Apache (.htaccess)
Create `.htaccess` in the root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
</IfModule>
