-- Pawfect Pet Shop Database Schema
-- Just copy and paste this to your database

CREATE DATABASE IF NOT EXISTS pawfect_db;
USE pawfect_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    avatar TEXT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password TEXT NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    is_banned BOOLEAN DEFAULT FALSE
);

-- Pets table
CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    pet_image TEXT NOT NULL DEFAULT '/uploads/placeholder.png',
    is_adopted BOOLEAN DEFAULT FALSE,
    type ENUM('cats', 'dogs') NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    age INT NOT NULL,
    breed VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    adopted_by_user_id INT NULL,
    FOREIGN KEY (adopted_by_user_id) REFERENCES users(id)
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    product_image VARCHAR(255),
    stock_quantity INT NOT NULL DEFAULT 0,
    type ENUM('foods', 'accessories') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    is_archived BOOLEAN DEFAULT FALSE
);

-- Create delivery_addresses table
CREATE TABLE IF NOT EXISTS delivery_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    city VARCHAR(100) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    street VARCHAR(255) NOT NULL,
    zipcode VARCHAR(20) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    shipped_date DATETIME NULL,
    delivery_date DATETIME NULL,
    cancelled_date DATETIME NULL,
    payment_method ENUM('COD', 'GCASH') DEFAULT 'COD',
    delivery_address_id INT NULL,
    notes TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (delivery_address_id) REFERENCES delivery_addresses(id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Settings table for admin customization
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT
);

-- Pawket table (Pet adoption cart)
CREATE TABLE IF NOT EXISTS pawket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

-- Pet Orders table
CREATE TABLE IF NOT EXISTS pet_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('COD', 'GCASH') DEFAULT 'COD',
    delivery_address_id INT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cancelled_at TIMESTAMP NULL,
    approved_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_address_id) REFERENCES delivery_addresses(id)
);

-- Pet Order Items table
CREATE TABLE IF NOT EXISTS pet_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    pet_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES pet_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);

-- Insert default admin user password 123123123
INSERT INTO users (first_name, last_name, email, password, avatar,role) 
VALUES ('Admin', 'User', 'admin@pawfect.com', '$2y$10$obSMKhiOl.UZH4ThQCOjs.KScyb8yeW1olywqOlMyi.KnCa/6cmEW', '/uploads/avatars/admin_avatarjpg', 'admin');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_logo', 'http://localhost/Pawfect/public/uploads/logo/6834bad72c5c5_PawfectPetShopLogo.jpg'),
('primary_color', '#FF8C00'),
('secondary_color', '#FFD700');

-- Sample pets data
INSERT INTO pets (name, pet_image, type, gender, age, breed, description, price) VALUES
('Buddy', '/public/placeholder.svg', 'dogs', 'male', 2, 'Golden Retriever', 'Buddy is a friendly and energetic Golden Retriever who loves playing fetch and swimming. He has a gentle temperament and gets along well with children and other pets. Buddy is house-trained and knows basic commands. He would make a perfect family companion!', 299.99),
('Luna', '/public/placeholder.svg', 'cats', 'female', 1, 'Persian', 'Luna is a beautiful Persian cat with a calm and affectionate personality. She enjoys lounging in sunny spots and being groomed.', 249.99),
('Max', '/public/placeholder.svg', 'dogs', 'male', 3, 'German Shepherd', 'Max is a loyal and intelligent German Shepherd with excellent protective instincts. He is well-trained and responds well to commands. Max has a strong work ethic and would excel in activities like obedience training or agility courses. He needs an active family who can provide plenty of exercise and mental stimulation.', 349.99),
('Bella', '/public/placeholder.svg', 'cats', 'female', 2, 'Siamese', 'Bella is a vocal and social Siamese cat who loves attention and playtime.', 199.99);

-- Sample products data
INSERT INTO products (name, product_image, stock_quantity, type, price, description) VALUES
('Premium Dog Food', '/public/placeholder.svg', 50, 'foods', 29.99, 'High-quality nutrition for dogs'),
('Cat Toy Set', '/public/placeholder.svg', 25, 'accessories', 15.99, 'Interactive toys for cats'),
('Dog Leash', '/public/placeholder.svg', 30, 'accessories', 12.99, 'Durable and comfortable leash'),
('Cat Food Premium', '/public/placeholder.svg', 40, 'foods', 24.99, 'Nutritious food for cats');
