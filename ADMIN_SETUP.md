# Admin Dashboard Setup Guide

## 1. Database Setup

Run this SQL in phpMyAdmin:
```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) DEFAULT 0;
```

Or run: `http://localhost/onlinebidding/update_schema_admin.sql` in phpMyAdmin

## 2. Create Admin User

Visit: `http://localhost/onlinebidding/create_admin_user.php`

This creates:
- Email: `admin@example.com`
- Password: `admin123`

**⚠️ Change the password after first login!**

## 3. Test Admin Login

1. Open the Android app
2. On login screen, select "Admin" tab
3. Login with:
   - Email: `admin@example.com`
   - Password: `admin123`
4. You should be redirected to Admin Dashboard

## 4. Admin Features

- **Manage Products**: Add, Edit, Delete products
- **Categories**: laptop, mobile, computer, monitor, tablet
- **Product Fields**: 
  - Title, Description, Category
  - Price, Image URL, Condition
  - Specifications (Key:Value format)
- **Real-time Updates**: Changes reflect immediately on user side

## 5. Admin API Endpoints

All admin APIs require Authorization header:
```
Authorization: Bearer {token}
```

Endpoints:
- `GET /api/admin/products/list.php` - List all products
- `POST /api/admin/products/add.php` - Add new product
- `POST /api/admin/products/update.php` - Update product
- `POST /api/admin/products/delete.php` - Delete product

## 6. Make Other Users Admin

To make an existing user an admin, run in phpMyAdmin:
```sql
UPDATE users SET is_admin = 1 WHERE email = 'user@example.com';
```

## Troubleshooting

1. **Admin login not working?**
   - Check if `is_admin` column exists in `users` table
   - Verify user's `is_admin` is set to 1

2. **Admin APIs returning 403?**
   - Check if token is being sent correctly
   - Verify user is admin in database
   - Check session hasn't expired

3. **Products not showing?**
   - Verify products were created successfully
   - Check database `products` and `auctions` tables
   - Verify category matches (laptop, mobile, etc.)

