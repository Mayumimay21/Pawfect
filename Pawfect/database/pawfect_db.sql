-- Pawfect Pet Shop Database Schema
-- Just copy and paste this to your database

CREATE DATABASE IF NOT EXISTS pawfect_db;
USE pawfect_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    avatar TEXT NOT NULL DEFAULT '/uploads/placeholder.png',
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
    adopted_by_user_id INT NULL,
    FOREIGN KEY (adopted_by_user_id) REFERENCES users(id)
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    product_image TEXT NOT NULL DEFAULT '/uploads/placeholder.png',
    stock_quantity INT DEFAULT 0,
    type ENUM('accessories', 'foods') NOT NULL,
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
    status ENUM('pending', 'processing', 'shipped', 'delivery', 'cancelled') DEFAULT 'pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    shipped_date DATETIME NULL,
    delivery_date DATETIME NULL,
    payment_method ENUM('COD', 'GCASH') DEFAULT 'COD',
    delivery_address_id INT NULL,
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

-- Insert default admin user password 123123123
INSERT INTO users (first_name, last_name, email, password, avatar,role) 
VALUES ('Admin', 'User', 'admin@pawfect.com', '$2y$10$obSMKhiOl.UZH4ThQCOjs.KScyb8yeW1olywqOlMyi.KnCa/6cmEW', '/uploads/avatars/683f471b0d860_PawfectPetShopLogo.jpg', 'admin');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_logo', 'http://localhost/Pawfect/public/uploads/logo/6834bad72c5c5_PawfectPetShopLogo.jpg'),
('primary_color', '#FF8C00'),
('secondary_color', '#FFD700');

-- Sample pets data
INSERT INTO pets (name, pet_image, type, gender, age, breed, description) VALUES
('Buddy', '/placeholder.svg?height=300&width=300', 'dogs', 'male', 2, 'Golden Retriever', 'Buddy is a friendly and energetic Golden Retriever who loves playing fetch and swimming. He has a gentle temperament and gets along well with children and other pets. Buddy is house-trained and knows basic commands. He would make a perfect family companion!'),
('Luna', '/placeholder.svg?height=300&width=300', 'cats', 'female', 1, 'Persian', 'Luna is a beautiful Persian cat with a calm and affectionate personality. She enjoys lounging in sunny spots and being groomed.'),
('Max', '/placeholder.svg?height=300&width=300', 'dogs', 'male', 3, 'German Shepherd', 'Max is a loyal and intelligent German Shepherd with excellent protective instincts. He is well-trained and responds well to commands. Max has a strong work ethic and would excel in activities like obedience training or agility courses. He needs an active family who can provide plenty of exercise and mental stimulation.'),
('Bella', '/placeholder.svg?height=300&width=300', 'cats', 'female', 2, 'Siamese', 'Bella is a vocal and social Siamese cat who loves attention and playtime.');

-- Sample products data
INSERT INTO products (name, product_image, stock_quantity, type, price, description) VALUES
('Premium Dog Food', '/placeholder.svg?height=200&width=200', 50, 'foods', 29.99, 'High-quality nutrition for dogs'),
('Cat Toy Set', '/placeholder.svg?height=200&width=200', 25, 'accessories', 15.99, 'Interactive toys for cats'),
('Dog Leash', '/placeholder.svg?height=200&width=200', 30, 'accessories', 12.99, 'Durable and comfortable leash'),
('Cat Food Premium', '/placeholder.svg?height=200&width=200', 40, 'foods', 24.99, 'Nutritious food for cats');
