DROP DATABASE IF EXISTS clothing_store;
CREATE DATABASE clothing_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clothing_store;

-- Bảng USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng PRODUCTS
CREATE TABLE products (
    product_code VARCHAR(50) PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    size VARCHAR(10) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2 USERS
INSERT INTO users (username, email, password, full_name) VALUES
('admin', 'admin@clothingstore.com', MD5('admin123'), 'Administrator'),
('user1', 'user1@example.com', MD5('password123'), 'Nguyễn Văn A');

-- 5 PRODUCTS
INSERT INTO products (product_code, product_name, category, size, price, quantity, description, image) VALUES
('SP001', 'Áo Thun Basic Trắng', 'Áo', 'M', 150000, 50, 'Áo thun cotton 100%, form regular, thấm hút mồ hôi tốt', NULL),
('SP007', 'Quần Jean Slim Fit', 'Quần', 'L', 450000, 30, 'Quần jean co giãn 4 chiều, ôm dáng', NULL),
('SP011', 'Váy Denim Ngắn', 'Váy', 'S', 320000, 20, 'Váy jean ngắn, phong cách trẻ trung', NULL),
('SP015', 'Áo Khoác Bomber', 'Áo khoác', 'L', 580000, 20, 'Áo bomber jacket, chất liệu dù cao cấp', NULL),
('SP019', 'Áo Tank Top Gym', 'Áo', 'M', 95000, 70, 'Áo ba lỗ thể thao, thấm hút mồ hôi', NULL);

-- Index cơ bản
CREATE INDEX idx_product_name ON products(product_name);
CREATE INDEX idx_category ON products(category);
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);

-- Kiểm tra dữ liệu mẫu
SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_products FROM products;
SELECT * FROM products LIMIT 5;

SELECT 'Basic clothing_store database created!' AS status;
