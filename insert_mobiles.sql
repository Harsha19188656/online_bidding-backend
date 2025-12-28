-- Insert Mobile Products
INSERT INTO products (title, description, category, image_url, specs, condition_label, base_price) VALUES
('iPhone 15 Pro Max', 'Premium Apple smartphone with A17 Pro chip', 'mobile', 'https://example.com/iphone.jpg', '{"Processor": "A17 Pro Chip", "Storage": "512GB", "Display": "6.7\" Super Retina XDR"}', 'Excellent', 128000.00),
('Samsung Galaxy S24 Ultra', 'Premium Samsung smartphone with Snapdragon 8 Gen 3', 'mobile', 'https://example.com/samsung.jpg', '{"Processor": "Snapdragon 8 Gen 3", "Storage": "256GB", "Display": "6.8\" Dynamic AMOLED"}', 'Very Good', 98000.00),
('OnePlus 12 Pro', 'Premium OnePlus smartphone with fast charging', 'mobile', 'https://example.com/oneplus.jpg', '{"Processor": "Snapdragon 8 Gen 3", "Storage": "256GB", "Display": "6.7\" AMOLED"}', 'Excellent', 54000.00);

-- Insert Auctions for Mobiles
INSERT INTO auctions (product_id, start_price, current_price, status, start_at, end_at)
SELECT id, base_price, base_price, 'live', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)
FROM products WHERE category = 'mobile' AND id NOT IN (SELECT product_id FROM auctions WHERE product_id IS NOT NULL);

