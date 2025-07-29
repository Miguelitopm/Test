-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 03-07-2025 a las 14:08:19
-- Versión del servidor: 8.3.0
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `publicate`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `color`, `created_at`) VALUES
(1, 'Electrónicos', 'electronics', 'smartphone', 'bg-orange-100 text-o', '2025-07-03 00:34:30'),
(2, 'Computación', 'computers', 'laptop', 'bg-blue-100 text-blu', '2025-07-03 00:34:30'),
(3, 'Vehículos', 'vehicles', 'car', 'bg-orange-100 text-o', '2025-07-03 00:34:30'),
(4, 'Hogar', 'home', 'home', 'bg-blue-100 text-blu', '2025-07-03 00:34:30'),
(5, 'Moda', 'fashion', 'shirt', 'bg-orange-100 text-o', '2025-07-03 00:34:30'),
(6, 'Libros', 'books', 'book', 'bg-blue-100 text-blu', '2025-07-03 00:34:30'),
(7, 'Juegos', 'games', 'gamepad2', 'bg-orange-100 text-o', '2025-07-03 00:34:30'),
(8, 'Salud', 'health', 'heart', 'bg-blue-100 text-blu', '2025-07-03 00:34:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('sale','purchase','review','system') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `buyer_id` int NOT NULL,
  `seller_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipping_address` text,
  `payment_method` varchar(50) DEFAULT 'wallet',
  `commission` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `buyer_id` (`buyer_id`),
  KEY `seller_id` (`seller_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `condition_type` enum('new','like-new','excellent','good','fair') NOT NULL,
  `location` varchar(100) NOT NULL,
  `shipping_type` enum('free','paid','pickup') DEFAULT 'free',
  `status` enum('active','sold','archived','deleted') DEFAULT 'active',
  `views` int DEFAULT '0',
  `likes` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `idx_products_user_status` (`user_id`,`status`),
  KEY `idx_products_created_at` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `original_price`, `condition_type`, `location`, `shipping_type`, `status`, `views`, `likes`, `created_at`, `updated_at`) VALUES
(15, 4, 2, 'MacBook Air M2 13 pulgadas', 'MacBook Air M2 en excelente estado, usado para trabajo de oficina. Batería en perfecto estado, incluye cargador original.', 1099.00, 1200.00, 'new', 'Caracas', 'free', 'active', 2, 0, '2025-07-03 05:20:25', '2025-07-03 12:46:55'),
(13, 4, 7, 'PlayStation 5 console', 'PlayStation 5 nueva, sellada de fábrica. Incluye control DualSense y todos los cables. Garantía oficial Sony.', 599.00, 610.00, 'new', 'Caracas', 'free', 'active', 1, 0, '2025-07-03 05:17:09', '2025-07-03 05:17:12'),
(14, 4, 1, 'Samsung Galaxy S23 Ultra', 'Samsung Galaxy S23 Ultra nuevo, sin usar. Incluye todos los accesorios originales y garantía de 1 año.', 1199.00, NULL, 'new', 'Caracas', 'free', 'active', 2, 0, '2025-07-03 05:18:54', '2025-07-03 12:16:11'),
(12, 4, 1, 'iPhone 14 Pro Max 256GB', 'Phone 14 Pro Max en perfecto estado, usado por 6 meses. Incluye caja original, cargador y protector de pantalla instalado. Sin rayones ni golpes.', 1300.00, NULL, 'new', 'Caracas', 'free', 'active', 1, 0, '2025-07-03 05:15:35', '2025-07-03 05:15:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_images_product_primary` (`product_id`,`is_primary`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_primary`, `sort_order`, `created_at`) VALUES
(16, 15, 'assets/uploads/products/15_0_1751520025.jpg', 1, 0, '2025-07-03 05:20:25'),
(15, 13, 'assets/uploads/products/13_0_1751519829.jpg', 1, 0, '2025-07-03 05:17:09'),
(14, 12, 'assets/uploads/products/12_0_1751519735.jpg', 1, 0, '2025-07-03 05:15:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `reviewer_id` int NOT NULL,
  `reviewed_id` int NOT NULL,
  `product_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `is_helpful` tinyint(1) DEFAULT '0',
  `helpful_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `reviewer_id` (`reviewer_id`),
  KEY `reviewed_id` (`reviewed_id`),
  KEY `product_id` (`product_id`)
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `wallet_balance` decimal(10,2) DEFAULT '0.00',
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int DEFAULT '0',
  `total_sales` int DEFAULT '0',
  `member_since` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `location`, `phone`, `avatar`, `wallet_balance`, `rating`, `total_reviews`, `total_sales`, `member_since`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@publicate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Caracas, Chacao', NULL, NULL, 5000.00, 4.80, 156, 89, '2025-07-03 00:34:30', 1, '2025-07-03 00:34:30', '2025-07-03 04:28:25'),
(2, 'techstore', 'tech@store.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'TechStore', 'Caracas, Chacao', NULL, NULL, 2450.50, 4.90, 203, 67, '2025-07-03 00:34:30', 1, '2025-07-03 00:34:30', '2025-07-03 04:28:25'),
(3, 'buyer1', 'buyer@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Comprador', 'Caracas, Chacao', NULL, NULL, 1500.00, 0.00, 0, 0, '2025-07-03 00:34:30', 1, '2025-07-03 00:34:30', '2025-07-03 04:28:25'),
(4, 'MelvinP', 'panilesmelvin@gmail.com', '$2y$10$TghMGaOMnvgOP.Cm476KG.JS8A9yEQ66RvZWQlR0iZil0ipmWPnXa', 'Melvin Pagnini', 'Caracas', NULL, NULL, 113.50, 0.00, 0, 0, '2025-07-03 00:38:12', 1, '2025-07-03 00:38:12', '2025-07-03 12:37:29'),
(5, 'Samuel123', 'samuel@gmail.com', '$2y$10$1FmUtnz.rwwpeY7qusgNSuCjwRzTiF3HCgS51ph8qzraoia1rAb3q', 'Samuel', 'petare', NULL, NULL, 93.00, 0.00, 0, 0, '2025-07-03 12:34:59', 1, '2025-07-03 12:34:59', '2025-07-03 12:37:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wallet_transactions`
--

DROP TABLE IF EXISTS `wallet_transactions`;
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('income','expense','recharge') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `reference_id` int DEFAULT NULL,
  `reference_type` enum('order','listing','recharge') DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `type`, `amount`, `description`, `reference_id`, `reference_type`, `status`, `created_at`) VALUES
(1, 4, 'recharge', 100.00, 'Bono de bienvenida', NULL, NULL, 'completed', '2025-07-03 00:38:12'),
(2, 4, 'expense', 1.00, 'Publicación de producto', 8, 'listing', 'completed', '2025-07-03 04:59:04'),
(3, 4, 'expense', 1.00, 'Publicación de producto', 9, 'listing', 'completed', '2025-07-03 05:00:32'),
(4, 4, 'expense', 1.00, 'Publicación de producto', 10, 'listing', 'completed', '2025-07-03 05:01:58'),
(5, 4, 'expense', 1.00, 'Publicación de producto', 11, 'listing', 'completed', '2025-07-03 05:04:39'),
(6, 4, 'expense', 1.00, 'Publicación de producto', 12, 'listing', 'completed', '2025-07-03 05:15:35'),
(7, 4, 'expense', 1.00, 'Publicación de producto', 13, 'listing', 'completed', '2025-07-03 05:17:09'),
(8, 4, 'expense', 1.00, 'Publicación de producto', 14, 'listing', 'completed', '2025-07-03 05:18:54'),
(9, 4, 'expense', 1.00, 'Publicación de producto', 15, 'listing', 'completed', '2025-07-03 05:20:25'),
(10, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:54:08'),
(11, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:54:11'),
(12, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:54:14'),
(13, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:54:17'),
(14, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:54:20'),
(15, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:54:37'),
(16, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:54:40'),
(17, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:54:44'),
(18, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:55:17'),
(19, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:55:20'),
(20, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:55:22'),
(21, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:55:24'),
(22, 4, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 05:55:27'),
(23, 4, 'expense', 1.00, 'Publicación de producto', 16, 'listing', 'completed', '2025-07-03 12:31:49'),
(24, 5, 'recharge', 100.00, 'Bono de bienvenida', NULL, NULL, 'completed', '2025-07-03 12:34:59'),
(25, 5, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 12:36:49'),
(26, 5, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 12:36:53'),
(27, 5, 'recharge', 1.00, 'Recarga de wallet', NULL, NULL, 'completed', '2025-07-03 12:37:05'),
(28, 5, 'expense', 10.00, 'Compra de productos', NULL, 'order', 'completed', '2025-07-03 12:37:29'),
(29, 4, 'income', 9.50, 'Venta de productos (menos comisión)', NULL, 'order', 'completed', '2025-07-03 12:37:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
