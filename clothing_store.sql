SET NAMES 'utf8mb4';
SET CHARACTER SET utf8mb4;

-- Bảng USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2 USERS
INSERT INTO users (username, email, password, full_name) VALUES
('admin', 'admin@clothingstore.com', MD5('admin123'), 'Administrator'),
('user1', 'user1@example.com', MD5('password123'), 'Nguyen Van A');

-- 5 PRODUCTS
INSERT INTO products (product_code, product_name, category, size, price, quantity, description, image) VALUES
('SP001', 'Ao Thun Basic Trang', 'Ao', 'M', 150000, 50, 'Ao thun cotton 100%, form regular', NULL),
('SP007', 'Quan Jean Slim Fit', 'Quan', 'L', 450000, 30, 'Quan jean co gian 4 chieu, om dang', NULL),
('SP011', 'Vay Denim Ngan', 'Vay', 'S', 320000, 20, 'Vay jean ngan, phong cach tre trung', NULL),
('SP015', 'Ao Khoac Bomber', 'Ao khoac', 'L', 580000, 20, 'Ao bomber jacket, chat lieu du cao cap', NULL),
('SP019', 'Ao Tank Top Gym', 'Ao', 'M', 95000, 70, 'Ao ba lo the thao, tham hut mo hoi', NULL);

-- Index
CREATE INDEX idx_product_name ON products(product_name);
CREATE INDEX idx_category ON products(category);
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);