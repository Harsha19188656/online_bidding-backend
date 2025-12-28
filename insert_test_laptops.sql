-- Insert test laptop data for LaptopList.kt
-- Run this SQL in phpMyAdmin after creating the database

USE onlinebidding;

-- Insert Laptop Products
INSERT INTO products (title, description, category, image_url, specs, condition_label, base_price) VALUES
('MacBook Pro 16" M3', 'Premium Apple laptop with M3 Max chip', 'laptop', 'https://example.com/macbook.jpg', '{"Processor": "Apple M3 Max", "RAM": "48GB", "Storage": "1TB SSD"}', 'Excellent', 185000.00),
('Dell XPS 15 OLED', 'Premium Dell laptop with OLED display', 'laptop', 'https://example.com/dell.jpg', '{"Processor": "Intel i7-13700H", "RAM": "32GB", "Storage": "1TB SSD"}', 'Very Good', 95000.00),
('ASUS ROG Zephyrus G16', 'Gaming laptop with Ryzen processor', 'laptop', 'https://example.com/asus.jpg', '{"Processor": "Ryzen 9 7940HS", "RAM": "32GB", "Storage": "2TB SSD"}', 'Excellent', 142000.00)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Insert corresponding auctions
INSERT INTO auctions (product_id, start_price, current_price, status, start_at, end_at)
SELECT id, base_price, base_price, 'live', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)
FROM products 
WHERE category = 'laptop'
ON DUPLICATE KEY UPDATE current_price = VALUES(current_price);

