-- Create database
CREATE DATABASE IF NOT EXISTS bihar_ka_bazar;
USE bihar_ka_bazar;

-- Users table (enhanced)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    user_type ENUM('farmer', 'buyer', 'admin') NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default-avatar.jpg',
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table (enhanced)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) DEFAULT 'kg',
    quantity_available INT DEFAULT 0,
    min_order_quantity INT DEFAULT 1,
    image VARCHAR(255),
    gallery TEXT, -- JSON array of additional images
    location VARCHAR(100),
    harvest_date DATE,
    expiry_date DATE,
    is_organic BOOLEAN DEFAULT FALSE,
    certification VARCHAR(255),
    status ENUM('active', 'inactive', 'sold', 'pending') DEFAULT 'active',
    views INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders table (enhanced)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    buyer_id INT NOT NULL,
    farmer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cod', 'online', 'wallet') DEFAULT 'cod',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_id VARCHAR(100),
    transaction_id VARCHAR(100),
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    billing_address TEXT,
    delivery_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    buyer_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Wishlist table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('order', 'payment', 'product', 'system') DEFAULT 'system',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Coupons table
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    max_discount_amount DECIMAL(10,2),
    usage_limit INT DEFAULT 1,
    used_count INT DEFAULT 0,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User sessions table
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Payment transactions table
CREATE TABLE payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    payment_gateway VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    status ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    gateway_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample categories
INSERT INTO categories (name, description, image) VALUES
('Fruits', 'Fresh seasonal fruits from Bihar farms', 'fruits-category.jpg'),
('Organic Fertilizers', 'Natural and organic fertilizers for sustainable farming', 'fertilizers-category.jpg'),
('Vegetables', 'Fresh seasonal vegetables grown locally', 'vegetables-category.jpg'),
('Grains & Cereals', 'Rice, wheat and other grains', 'grains-category.jpg'),
('Dairy Products', 'Fresh milk and dairy products', 'dairy-category.jpg'),
('Spices & Herbs', 'Traditional spices and medicinal herbs', 'spices-category.jpg');

-- Insert sample admin user
INSERT INTO users (username, email, password, full_name, phone, user_type, city, state) VALUES
('admin', 'admin@bindisa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', '9876543210', 'admin', 'Patna', 'Bihar');

-- Insert sample farmer
INSERT INTO users (username, email, password, full_name, phone, address, city, state, pincode, user_type) VALUES
('farmer1', 'farmer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ram Kumar Singh', '9876543211', 'Village Rampur, Block Samastipur', 'Samastipur', 'Bihar', '848101', 'farmer');

-- Insert sample buyer
INSERT INTO users (username, email, password, full_name, phone, address, city, state, pincode, user_type) VALUES
('buyer1', 'buyer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya Sharma', '9876543212', 'Boring Road, Patna', 'Patna', 'Bihar', '800001', 'buyer');

-- Insert sample products
INSERT INTO products (farmer_id, category_id, title, description, price, unit, quantity_available, min_order_quantity, image, location, harvest_date, is_organic, certification) VALUES
(2, 1, 'Premium Muzaffarpur Litchi', 'Fresh and juicy litchi from the famous orchards of Muzaffarpur. Known for their sweet taste and aromatic flavor. Handpicked at perfect ripeness.', 180.00, 'kg', 500, 2, 'litchi.jpg', 'Muzaffarpur', '2024-05-15', true, 'Organic India Certified'),
(2, 1, 'Hajipur Guava', 'Crisp and sweet guava from Hajipur region. Rich in Vitamin C and fiber. Perfect for fresh consumption or juice making.', 90.00, 'kg', 300, 1, 'guava.jpg', 'Hajipur', '2024-01-10', true, 'NPOP Certified'),
(2, 1, 'Bihar Mango (Digha Variety)', 'Premium quality Digha variety mango, famous for its unique taste and aroma. Naturally ripened and chemical-free.', 250.00, 'kg', 200, 1, 'mango.jpg', 'Digha', '2024-06-01', true, 'Organic India Certified'),
(2, 1, 'Vaishali Banana', 'Fresh bananas from Vaishali district. Rich in potassium and natural sugars. Perfect for daily consumption.', 60.00, 'dozen', 100, 1, 'banana.jpg', 'Vaishali', '2024-01-20', false, ''),
(2, 2, 'Premium Vermicompost', 'High-quality vermicompost made from organic waste. Rich in nutrients and beneficial microorganisms. Perfect for organic farming.', 25.00, 'kg', 1000, 10, 'vermicompost.jpg', 'Patna', '2024-01-01', true, 'NPOP Certified'),
(2, 2, 'Neem Cake Fertilizer', 'Natural neem cake fertilizer with pest control properties. Slow-release organic fertilizer ideal for all crops.', 35.00, 'kg', 500, 5, 'neem-cake.jpg', 'Gaya', '2024-01-01', true, 'Organic India Certified'),
(2, 2, 'Cow Dung Manure', 'Well-decomposed cow dung manure. Rich in organic matter and essential nutrients. Improves soil structure and fertility.', 20.00, 'kg', 800, 10, 'cow-dung.jpg', 'Muzaffarpur', '2024-01-01', true, 'NPOP Certified'),
(2, 3, 'Fresh Potato', 'High-quality potatoes from Bihar farms. Perfect for cooking and processing. Stored in proper conditions.', 40.00, 'kg', 1000, 5, 'potato.jpg', 'Nalanda', '2024-02-01', false, ''),
(2, 3, 'Organic Tomato', 'Fresh organic tomatoes grown without chemicals. Rich in lycopene and vitamins. Perfect for cooking and salads.', 80.00, 'kg', 200, 2, 'tomato.jpg', 'Patna', '2024-02-15', true, 'Organic India Certified'),
(2, 4, 'Bihar Basmati Rice', 'Premium quality basmati rice from Bihar. Long grain, aromatic, and perfect for special occasions.', 120.00, 'kg', 500, 5, 'basmati-rice.jpg', 'Darbhanga', '2023-12-01', false, '');

-- Insert sample coupons
INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_discount_amount, valid_from, valid_until) VALUES
('WELCOME10', 'Welcome discount for new users', 'percentage', 10.00, 500.00, 100.00, '2024-01-01', '2024-12-31'),
('ORGANIC20', 'Special discount on organic products', 'percentage', 20.00, 1000.00, 200.00, '2024-01-01', '2024-12-31'),
('BIHAR50', 'Flat discount for Bihar customers', 'fixed', 50.00, 300.00, 50.00, '2024-01-01', '2024-12-31');
