# Backend Setup Instructions

## Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** (click "Start" button)
3. Start **MySQL** (click "Start" button)
4. Both should show green "Running" status

## Step 2: Create Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on "New" in the left sidebar
3. Database name: `onlinebidding`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

## Step 3: Import Database Schema
1. In phpMyAdmin, select the `onlinebidding` database
2. Click on "Import" tab
3. Click "Choose File"
4. Select: `C:\xampp\htdocs\onlinebidding\schema.sql`
5. Click "Go" at the bottom
6. You should see "Import has been successfully finished"

## Step 4: Verify Database
1. In phpMyAdmin, click on `onlinebidding` database
2. You should see these tables:
   - users
   - sessions
   - products
   - auctions
   - bids
   - payments
   - notifications

## Step 5: Test Backend
Open these URLs in your browser:

1. **Test Database Connection:**
   http://10.148.199.81/onlinebidding/test_connection.php
   - Should show: `{"success":true,"message":"Database connection successful","table_exists":true}`

2. **Test Registration Endpoint:**
   http://10.148.199.81/onlinebidding/api/test_register.php
   - Should show test data and connection status

3. **Test Register API:**
   http://10.148.199.81/onlinebidding/api/register.php
   - Should show an error (expected, since no POST data)

## Step 6: Configure Android App

### For Android Emulator:
Update `RetrofitInstance.kt`:
```kotlin
private const val BASE_URL = "http://10.0.2.2/onlinebidding/"
```

### For Physical Device:
Update `RetrofitInstance.kt`:
```kotlin
private const val BASE_URL = "http://10.148.199.81/onlinebidding/"
```

**Important:** Make sure your phone and computer are on the **same WiFi network**.

## Troubleshooting

### If "Registration failed" appears:

1. **Check XAMPP is running:**
   - Apache: Green "Running"
   - MySQL: Green "Running"

2. **Check database exists:**
   - Open phpMyAdmin
   - Check if `onlinebidding` database exists
   - Check if `users` table exists

3. **Test connection:**
   - Open: http://10.148.199.81/onlinebidding/test_connection.php
   - Should show success message

4. **Check error logs:**
   - Apache logs: `C:\xampp\apache\logs\error.log`
   - MySQL logs: Check phpMyAdmin for errors

5. **Check firewall:**
   - Windows Firewall might be blocking port 80
   - Allow Apache through firewall

6. **Check IP address:**
   - Run `ipconfig` in CMD
   - Make sure you're using the correct IPv4 address
   - Update `RetrofitInstance.kt` if IP changed

## Common Errors

### "Database connection failed"
- MySQL is not running in XAMPP
- Database `onlinebidding` doesn't exist
- Wrong database credentials in `db.php`

### "Table 'users' doesn't exist"
- Database exists but schema wasn't imported
- Import `schema.sql` file

### "Cannot connect to server"
- Wrong IP address in `RetrofitInstance.kt`
- Phone and computer not on same WiFi
- Firewall blocking connection
- Apache not running

### "Email already exists"
- User with this email is already registered
- Try with a different email

