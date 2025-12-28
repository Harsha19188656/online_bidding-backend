================================================================================
  ONLINE BIDDING - BACKEND SETUP COMPLETE
================================================================================

All backend files are ready in: C:\xampp\htdocs\onlinebidding

================================================================================
  HOW TO RUN (3 SIMPLE STEPS)
================================================================================

STEP 1: Start XAMPP Services
-----------------------------
1. Open XAMPP Control Panel
2. Click "Start" for Apache (wait until green)
3. Click "Start" for MySQL (wait until green)

STEP 2: Run Setup Script (First Time Only)
--------------------------------------------
Open your browser and go to:
  http://localhost/onlinebidding/setup_complete.php

You should see success messages for:
  - Database created
  - Tables created
  - Test data inserted (3 laptops)

STEP 3: Test Backend APIs
--------------------------
Test Registration API:
  http://localhost/onlinebidding/api/register.php

Test Laptops List API:
  http://localhost/onlinebidding/api/auctions/list.php?category=laptop

Test Everything:
  http://localhost/onlinebidding/test_backend.php

================================================================================
  ANDROID APP CONFIGURATION
================================================================================

1. Open: app/src/main/java/com/example/onlinebidding/api/RetrofitInstance.kt

2. Update BASE_URL:
   - For Emulator: "http://10.0.2.2/onlinebidding/"
   - For Physical Device: "http://YOUR_IP/onlinebidding/"
     (Find your IP: Open CMD, type "ipconfig", look for IPv4 Address)

3. Rebuild and run Android app

================================================================================
  VERIFY IT WORKS
================================================================================

Create Account Screen:
  - Fill all fields and click "Sign Up"
  - Should see "Registration successful"
  - User saved to database

LaptopList Screen:
  - Should show "✅ Online" in header
  - Should show green banner: "✅ Connected to backend - 3 laptops loaded"
  - Should display 3 laptops from database

================================================================================
  FILE STRUCTURE
================================================================================

C:\xampp\htdocs\onlinebidding\
├── db.php                    (Database connection)
├── schema.sql                (Database structure)
├── setup_complete.php        (Run this first!)
├── test_backend.php          (Test everything)
├── HOW_TO_RUN.md            (Detailed instructions)
└── api/
    ├── register.php          (User registration API)
    └── auctions/
        └── list.php          (Get laptop list API)

================================================================================
  TROUBLESHOOTING
================================================================================

Problem: "Cannot connect to MySQL"
  Solution: Start MySQL in XAMPP Control Panel

Problem: "404 Not Found" when accessing setup_complete.php
  Solution: Make sure Apache is running in XAMPP

Problem: "Failed to connect" in Android app
  Solution: 
    1. Check XAMPP Apache is running
    2. Test API in browser first
    3. Check IP address in RetrofitInstance.kt
    4. For physical device: Phone and computer must be on same WiFi

Problem: LaptopList shows "Offline"
  Solution:
    1. Run setup_complete.php again
    2. Check database has laptops (phpMyAdmin → products → Browse)

================================================================================

That's it! Your backend is ready to use! 🚀

