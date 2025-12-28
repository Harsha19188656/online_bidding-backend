# 📱 Insert Mobile Data in phpMyAdmin

## Quick Steps

### Step 1: Open phpMyAdmin
Go to: **http://localhost/phpmyadmin/index.php?route=/sql&pos=0&db=onlinebidding&table=products**

### Step 2: Copy and Paste This SQL

```sql
-- Insert Mobile Products
INSERT INTO products (title, description, category, image_url, specs, condition_label, base_price) VALUES
('iPhone 15 Pro Max', 'Premium Apple smartphone with A17 Pro chip', 'mobile', 'https://example.com/iphone.jpg', '{"Processor": "A17 Pro Chip", "Storage": "512GB", "Display": "6.7\" Super Retina XDR"}', 'Excellent', 128000.00),
('Samsung Galaxy S24 Ultra', 'Premium Samsung smartphone with Snapdragon 8 Gen 3', 'mobile', 'https://example.com/samsung.jpg', '{"Processor": "Snapdragon 8 Gen 3", "Storage": "256GB", "Display": "6.8\" Dynamic AMOLED"}', 'Very Good', 98000.00),
('OnePlus 12 Pro', 'Premium OnePlus smartphone with fast charging', 'mobile', 'https://example.com/oneplus.jpg', '{"Processor": "Snapdragon 8 Gen 3", "Storage": "256GB", "Display": "6.7\" AMOLED"}', 'Excellent', 54000.00);

-- Insert Auctions for Mobiles
INSERT INTO auctions (product_id, start_price, current_price, status, start_at, end_at)
SELECT id, base_price, base_price, 'live', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)
FROM products WHERE category = 'mobile' AND id NOT IN (SELECT product_id FROM auctions WHERE product_id IS NOT NULL);
```

### Step 3: Click "Go" Button
Execute the SQL query.

### Step 4: Verify
Check that 3 mobile products were inserted:
- Go to **products** table → Browse
- Filter by `category = 'mobile'`
- Should see 3 mobiles

### Step 5: Test API
Open: **http://localhost/onlinebidding/api/auctions/list.php?category=mobile**
- Should return JSON with 3 mobiles

---

## Alternative: If You Want to Delete Existing Mobiles First

If you want to replace existing mobile data, run this first:

```sql
-- Delete existing mobile auctions and products
DELETE FROM auctions WHERE product_id IN (SELECT id FROM products WHERE category = 'mobile');
DELETE FROM products WHERE category = 'mobile';
```

Then run the INSERT statements above.

---

**After running the SQL, rebuild and run your Android app!** 🚀

