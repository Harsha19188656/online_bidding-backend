# 🚀 How to Run the Backend

Everything is set up in: **C:\xampp\htdocs\onlinebidding**

---

## ⚡ Quick Start (3 Steps)

### Step 1: Start XAMPP Services

1. **Open XAMPP Control Panel**
2. **Click "Start"** for **Apache** (wait until it turns green)
3. **Click "Start"** for **MySQL** (wait until it turns green)

✅ Both should show **"Running"** in green

---

### Step 2: Run Setup Script (First Time Only)

1. **Open your web browser**
2. **Type in address bar:**
   ```
   http://localhost/onlinebidding/setup_complete.php
   ```
   OR if accessing from another device:
   ```
   http://10.148.199.81/onlinebidding/setup_complete.php
   ```
   (Replace `10.148.199.81` with your computer's IP address)

3. **You should see:**
   - ✅ Connected to MySQL
   - ✅ Database 'onlinebidding' created/verified
   - ✅ Table created: users
   - ✅ Table created: products
   - ✅ Table created: auctions
   - ✅ Inserted 3 laptop product(s)
   - ✅ Created 3 auction(s) for laptops
   - ✅ Setup Complete!

---

### Step 3: Test Backend APIs

**Test Registration API:**
- Open: http://localhost/onlinebidding/api/register.php
- Should show JSON response (even if error, means API is working)

**Test Laptops List API:**
- Open: http://localhost/onlinebidding/api/auctions/list.php?category=laptop
- Should show JSON with 3 laptops

**Test Backend (Comprehensive):**
- Open: http://localhost/onlinebidding/test_backend.php
- This will test everything and show detailed status

---

## 📱 Connect Android App

### Update IP Address in Android App:

1. **Open**: `app/src/main/java/com/example/onlinebidding/api/RetrofitInstance.kt`
2. **Change BASE_URL**:
   - **For Emulator**: `"http://10.0.2.2/onlinebidding/"`
   - **For Physical Device**: `"http://10.148.199.81/onlinebidding/"`
     (Replace `10.148.199.81` with your computer's actual IP address)

3. **Find your IP address**:
   - Open Command Prompt
   - Type: `ipconfig`
   - Look for **IPv4 Address** under **Wi-Fi**
   - Use that IP in BASE_URL

---

## ✅ Verify Everything Works

### Check Create Account:
1. Run Android app
2. Go to Create Account screen
3. Fill all fields and click "Sign Up"
4. Should see "Registration successful"
5. Check database: phpMyAdmin → onlinebidding → users → Browse

### Check LaptopList:
1. Navigate to Laptops screen
2. Should see **"✅ Online"** in header (not "⚠️ Offline")
3. Should see green banner: **"✅ Connected to backend - 3 laptops loaded"**
4. Should see 3 laptops from database

---

## 🐛 Troubleshooting

### Problem: "Cannot connect to MySQL"
**Solution**: Start MySQL in XAMPP Control Panel

### Problem: "404 Not Found" when accessing setup_complete.php
**Solution**: 
- Make sure Apache is running in XAMPP
- Check file exists at: `C:\xampp\htdocs\onlinebidding\setup_complete.php`

### Problem: "Failed to connect" in Android app
**Solution**:
1. Check XAMPP Apache is running
2. Test API in browser: http://localhost/onlinebidding/api/register.php
3. Check IP address in RetrofitInstance.kt matches your computer's IP
4. For physical device: Phone and computer must be on same WiFi

### Problem: LaptopList shows "Offline"
**Solution**:
1. Run setup_complete.php again
2. Check database has laptops: phpMyAdmin → products → Browse
3. Should see 3 laptops with category = 'laptop'

---

## 📂 File Structure

```
C:\xampp\htdocs\onlinebidding\
├── db.php                    (Database connection)
├── schema.sql                (Database structure)
├── setup_complete.php        (Run this first!)
├── test_backend.php          (Test everything)
├── HOW_TO_RUN.md            (This file)
└── api/
    ├── register.php          (User registration)
    └── auctions/
        └── list.php          (Get laptop list)
```

---

## 🎯 Summary

**Every time you want to use the backend:**

1. ✅ Start XAMPP (Apache + MySQL)
2. ✅ Run setup_complete.php (only first time, or to reset data)
3. ✅ Test APIs in browser
4. ✅ Run Android app

**That's it!** Your backend is ready! 🚀

---

**Location**: All files are in `C:\xampp\htdocs\onlinebidding`

