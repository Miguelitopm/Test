-- Base de datos PUBLICATE
CREATE DATABASE IF NOT EXISTS publicate;
USE publicate;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    location VARCHAR(100),
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT NULL,
    wallet_balance DECIMAL(10,2) DEFAULT 0.00,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    total_sales INT DEFAULT 0,
    member_since TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    icon VARCHAR(50) NOT NULL,
    color VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de productos
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2) DEFAULT NULL,
    condition_type ENUM('new', 'like-new', 'excellent', 'good', 'fair') NOT NULL,
    location VARCHAR(100) NOT NULL,
    shipping_type ENUM('free', 'paid', 'pickup') DEFAULT 'free',
    status ENUM('active', 'sold', 'archived', 'deleted') DEFAULT 'active',
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Tabla de imágenes de productos
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabla de pedidos
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    tracking_number VARCHAR(100) DEFAULT NULL,
    shipping_address TEXT,
    payment_method VARCHAR(50) DEFAULT 'wallet',
    commission DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabla de carrito de compras
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Tabla de favoritos
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Tabla de reseñas
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewed_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_helpful BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (reviewer_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabla de transacciones del wallet
CREATE TABLE wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('income', 'expense', 'recharge') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    reference_id INT DEFAULT NULL,
    reference_type ENUM('order', 'listing', 'recharge') DEFAULT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabla de notificaciones
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('sale', 'purchase', 'review', 'system') DEFAULT 'system',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertar categorías por defecto
INSERT INTO categories (name, slug, icon, color) VALUES
('Electrónicos', 'electronics', 'smartphone', 'bg-orange-100 text-orange-600'),
('Computación', 'computers', 'laptop', 'bg-blue-100 text-blue-600'),
('Vehículos', 'vehicles', 'car', 'bg-orange-100 text-orange-600'),
('Hogar', 'home', 'home', 'bg-blue-100 text-blue-600'),
('Moda', 'fashion', 'shirt', 'bg-orange-100 text-orange-600'),
('Libros', 'books', 'book', 'bg-blue-100 text-blue-600'),
('Juegos', 'games', 'gamepad2', 'bg-orange-100 text-orange-600'),
('Salud', 'health', 'heart', 'bg-blue-100 text-blue-600');

-- Crear usuario de prueba
INSERT INTO users (username, email, password, full_name, location, wallet_balance, rating, total_reviews, total_sales) VALUES
('admin', 'admin@publicate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Buenos Aires, Argentina', 5000.00, 4.8, 156, 89),
('techstore', 'tech@store.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'TechStore', 'Buenos Aires, Argentina', 2450.50, 4.9, 203, 67),
('buyer1', 'buyer@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Comprador', 'Córdoba, Argentina', 1500.00, 0.00, 0, 0);

-- Insertar productos de ejemplo
INSERT INTO products (user_id, category_id, title, description, price, original_price, condition_type, location, shipping_type, views, likes) VALUES
(2, 1, 'iPhone 14 Pro Max 256GB - Morado Profundo', 'iPhone 14 Pro Max en perfecto estado, usado por 6 meses. Incluye caja original, cargador y protector de pantalla instalado. Sin rayones ni golpes.', 1299.00, 1399.00, 'excellent', 'Buenos Aires', 'free', 45, 12),
(2, 1, 'Samsung Galaxy S23 Ultra', 'Samsung Galaxy S23 Ultra nuevo, sin usar. Incluye todos los accesorios originales y garantía de 1 año.', 1199.00, NULL, 'new', 'Buenos Aires', 'free', 32, 8),
(2, 2, 'MacBook Air M2 13 pulgadas', 'MacBook Air M2 en excelente estado, usado para trabajo de oficina. Batería en perfecto estado, incluye cargador original.', 1099.00, NULL, 'excellent', 'Buenos Aires', 'free', 28, 15),
(2, 7, 'PlayStation 5 Console', 'PlayStation 5 nueva, sellada de fábrica. Incluye control DualSense y todos los cables. Garantía oficial Sony.', 599.00, NULL, 'new', 'Buenos Aires', 'free', 67, 23);

-- Insertar imágenes de productos
INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES
(1, '/assets/images/products/iphone14-1.jpg', TRUE, 1),
(1, '/assets/images/products/iphone14-2.jpg', FALSE, 2),
(1, '/assets/images/products/iphone14-3.jpg', FALSE, 3),
(2, '/assets/images/products/galaxy-s23-1.jpg', TRUE, 1),
(2, '/assets/images/products/galaxy-s23-2.jpg', FALSE, 2),
(3, '/assets/images/products/macbook-1.jpg', TRUE, 1),
(3, '/assets/images/products/macbook-2.jpg', FALSE, 2),
(4, '/assets/images/products/ps5-1.jpg', TRUE, 1),
(4, '/assets/images/products/ps5-2.jpg', FALSE, 2);
