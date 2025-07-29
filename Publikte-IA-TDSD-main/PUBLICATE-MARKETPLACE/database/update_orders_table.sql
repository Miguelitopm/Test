-- Actualizar tabla de órdenes para incluir campos faltantes
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS shipping_address TEXT DEFAULT NULL;

-- Insertar datos de ejemplo para las ventas
INSERT INTO orders (buyer_id, seller_id, product_id, quantity, total_amount, commission, status, tracking_number, created_at) VALUES
(3, 2, 1, 1, 1299.00, 64.95, 'delivered', 'TRK123456789', '2024-01-15 10:30:00'),
(3, 2, 2, 1, 1199.00, 59.95, 'shipped', 'TRK987654321', '2024-01-18 14:20:00'),
(3, 2, 3, 1, 1099.00, 54.95, 'processing', NULL, '2024-01-20 16:45:00'),
(1, 2, 4, 1, 599.00, 29.95, 'delivered', 'TRK456789123', '2024-01-12 09:15:00'),
(1, 2, 1, 1, 1299.00, 64.95, 'shipped', 'TRK789123456', '2024-01-22 11:30:00');

-- Actualizar estadísticas de usuarios
UPDATE users SET total_sales = (
    SELECT COUNT(*) FROM orders WHERE seller_id = users.id
) WHERE id IN (1, 2, 3);
