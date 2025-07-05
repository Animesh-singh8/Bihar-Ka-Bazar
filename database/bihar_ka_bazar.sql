-- Create database
CREATE DATABASE IF NOT EXISTS bihar_ka_bazar;
USE bihar_ka_bazar;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    user_type ENUM('farmer', 'buyer') NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default-avatar.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) DEFAULT 'kg',
    quantity_available INT DEFAULT 0,
    image VARCHAR(255),
    location VARCHAR(100),
    is_organic BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'sold') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders table (basic)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    product_id INT NOT NULL,
    farmer_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    buyer_name VARCHAR(100) NOT NULL,
    buyer_phone VARCHAR(15) NOT NULL,
    buyer_address TEXT NOT NULL,
    status ENUM('pending', 'confirmed', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description, image) VALUES
('Fruits', 'Fresh fruits from Bihar farms', 'fruits-category.jpg'),
('Organic Fertilizers', 'Natural and organic fertilizers', 'fertilizers-category.jpg'),
('Vegetables', 'Fresh seasonal vegetables', 'vegetables-category.jpg'),
('Grains', 'Rice, wheat and other grains', 'grains-category.jpg');

-- Insert sample admin user
INSERT INTO users (username, email, password, full_name, phone, user_type) VALUES
('admin', 'admin@bindisa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', '9876543210', 'farmer');

-- Insert sample products
INSERT INTO products (farmer_id, category_id, title, description, price, unit, quantity_available, image, location, is_organic) VALUES
(1, 1, 'Muzaffarpur Litchi', 'Fresh and sweet litchi from Muzaffarpur, Bihar', 150.00, 'kg', 100, 'litchi.jpg', 'Muzaffarpur', true),
(1, 1, 'Hajipur Guava', 'Premium quality guava from Hajipur', 80.00, 'kg', 50, 'guava.jpg', 'Hajipur', true),
(1, 1, 'Bihar Mango', 'Delicious Digha variety mango', 200.00, 'kg', 75, 'mango.jpg', 'Digha', true),
(1, 2, 'Vermicompost', 'High quality vermicompost for organic farming', 25.00, 'kg', 200, 'vermicompost.jpg', 'Patna', true),
(1, 2, 'Neem Cake', 'Natural neem cake fertilizer', 30.00, 'kg', 150, 'neem-cake.jpg', 'Gaya', true);
