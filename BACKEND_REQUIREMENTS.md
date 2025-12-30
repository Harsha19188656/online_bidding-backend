# 📋 Backend Requirements for Online Bidding App

## ✅ Current Backend Structure

### **Core Files (Required)**
```
onlinebidding/
├── db.php                          ✅ Database connection
├── api/
│   ├── login.php                   ✅ User login
│   ├── register.php                 ✅ User registration
│   ├── auctions/
│   │   └── list.php                 ✅ List products/auctions
│   └── admin/
│       └── products/
│           ├── helper_auth.php      ✅ Admin authentication helper
│           ├── list.php             ✅ List admin products
│           ├── add.php              ✅ Add product
│           ├── update.php           ✅ Update product
│           └── delete.php           ✅ Delete product
```

## 📦 Required Backend Files

### **1. Database Connection (`db.php`)**
**Location:** `C:\xampp\htdocs\onlinebidding\db.php`
**Status:** ✅ Created
**Purpose:** MySQL database connection configuration

**Required Configuration:**
- Host: `localhost`
- Username: `root`
- Password: `` (empty for XAMPP default)
- Database: `onlinebidding`

---

### **2. User Authentication APIs**

#### **2.1 Login API (`api/login.php`)**
**Status:** ✅ Exists
**Endpoint:** `POST /api/login.php`
**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```
**Response:**
```json
{
  "success": true,
  "token": "jwt_token_here",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "is_admin": 0
  }
}
```

#### **2.2 Register API (`api/register.php`)**
**Status:** ✅ Exists
**Endpoint:** `POST /api/register.php`
**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "name": "User Name"
}
```

---

### **3. Product/Auction APIs**

#### **3.1 List Auctions (`api/auctions/list.php`)**
**Status:** ✅ Exists
**Endpoint:** `GET /api/auctions/list.php?category=laptop&limit=20&offset=0`
**Response:**
```json
{
  "success": true,
  "items": [
    {
      "product": {
        "id": 1,
        "title": "MacBook Pro",
        "description": "...",
        "category": "laptop",
        "image_url": "...",
        "specs": "{\"RAM\":\"16GB\"}",
        "condition": "Excellent",
        "base_price": 185000.00
      },
      "auction": {
        "id": 1,
        "product_id": 1,
        "start_price": 148000.00,
        "current_price": 185000.00,
        "status": "active"
      },
      "name": "MacBook Pro",
      "specs": "16GB RAM",
      "rating": 4.9,
      "price": "₹1,85,000"
    }
  ],
  "count": 10
}
```

**Supported Categories:**
- `laptop`
- `mobile`
- `computer`
- `monitor`
- `tablet`

---

### **4. Admin Product Management APIs**

#### **4.1 List Admin Products (`api/admin/products/list.php`)**
**Status:** ✅ Exists
**Endpoint:** `GET /api/admin/products/list.php?category=laptop`
**Headers:** `Authorization: Bearer {token}`
**Response:**
```json
{
  "success": true,
  "products": [...],
  "total": 10,
  "count": 10
}
```

#### **4.2 Add Product (`api/admin/products/add.php`)**
**Status:** ✅ Exists
**Endpoint:** `POST /api/admin/products/add.php`
**Headers:** `Authorization: Bearer {token}`
**Request:**
```json
{
  "title": "Product Name",
  "description": "Product description",
  "category": "laptop",
  "image_url": "https://example.com/image.jpg",
  "specs": {
    "RAM": "16GB",
    "Storage": "512GB"
  },
  "condition_label": "Excellent",
  "base_price": 100000.00,
  "start_price": 80000.00
}
```

#### **4.3 Update Product (`api/admin/products/update.php`)**
**Status:** ✅ Exists
**Endpoint:** `POST /api/admin/products/update.php`
**Headers:** `Authorization: Bearer {token}`
**Request:**
```json
{
  "product_id": 1,
  "title": "Updated Title",
  "base_price": 120000.00
}
```

#### **4.4 Delete Product (`api/admin/products/delete.php`)**
**Status:** ✅ Exists
**Endpoint:** `POST /api/admin/products/delete.php`
**Headers:** `Authorization: Bearer {token}`
**Request:**
```json
{
  "product_id": 1
}
```

#### **4.5 Admin Auth Helper (`api/admin/products/helper_auth.php`)**
**Status:** ✅ Exists
**Purpose:** Helper functions for admin authentication
- `getAuthToken()` - Extracts token from Authorization header
- `verifyAdminToken($mysqli, $token)` - Verifies admin token and returns user

---

## 🗄️ Database Structure

### **Required Tables:**

#### **1. `users` Table**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### **2. `products` Table**
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    image_url VARCHAR(500),
    specs TEXT,  -- JSON format
    condition_label VARCHAR(100),
    base_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### **3. `auctions` Table**
```sql
CREATE TABLE auctions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    start_price DECIMAL(10,2) NOT NULL,
    current_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'scheduled',
    start_at DATETIME,
    end_at DATETIME,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

#### **4. `sessions` Table**
```sql
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### **5. `bids` Table** (Optional - for future bidding functionality)
```sql
CREATE TABLE bids (
    id INT PRIMARY KEY AUTO_INCREMENT,
    auction_id INT NOT NULL,
    user_id INT NOT NULL,
    bid_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auction_id) REFERENCES auctions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 🔧 Setup Instructions

### **Step 1: Install XAMPP**
1. Download and install XAMPP
2. Start Apache and MySQL services

### **Step 2: Create Database**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `onlinebidding`
3. Import schema or run SQL scripts

### **Step 3: Configure Backend**
1. Place all PHP files in: `C:\xampp\htdocs\onlinebidding\`
2. Ensure `db.php` has correct database credentials
3. Test connection: `http://localhost/onlinebidding/test_connection.php`

### **Step 4: Test APIs**
Test each endpoint in browser or Postman:
- `http://localhost/onlinebidding/api/login.php`
- `http://localhost/onlinebidding/api/auctions/list.php?category=laptop`
- `http://localhost/onlinebidding/api/admin/products/list.php`

---

## 📱 Android App Configuration

### **Base URL Configuration**
**File:** `app/src/main/java/com/example/onlinebidding/api/RetrofitInstance.kt`

**For Android Emulator:**
```kotlin
private const val BASE_URL = "http://10.0.2.2/onlinebidding/"
```

**For Physical Device:**
```kotlin
private const val BASE_URL = "http://YOUR_IP_ADDRESS/onlinebidding/"
```
- Find your IP: `ipconfig` in CMD
- Look for IPv4 Address under Wi-Fi
- Example: `"http://10.148.199.81/onlinebidding/"`

---

## ✅ Checklist

### **Backend Files (All Required)**
- [x] `db.php` - Database connection
- [x] `api/login.php` - User login
- [x] `api/register.php` - User registration
- [x] `api/auctions/list.php` - List products
- [x] `api/admin/products/list.php` - Admin list products
- [x] `api/admin/products/add.php` - Add product
- [x] `api/admin/products/update.php` - Update product
- [x] `api/admin/products/delete.php` - Delete product
- [x] `api/admin/products/helper_auth.php` - Admin auth helper

### **Database Tables (All Required)**
- [x] `users` - User accounts
- [x] `products` - Product catalog
- [x] `auctions` - Auction listings
- [x] `sessions` - User sessions/tokens
- [ ] `bids` - Bidding history (optional for now)

### **Network Configuration**
- [x] XAMPP Apache running
- [x] XAMPP MySQL running
- [x] Database `onlinebidding` created
- [x] All tables created
- [x] Sample data inserted (laptops, mobiles, computers, monitors, tablets)

---

## 🚀 Quick Start Commands

### **Add Sample Data:**
```bash
# Add mobile devices
php C:\xampp\htdocs\onlinebidding\add_mobile_devices.php

# Add computer devices
php C:\xampp\htdocs\onlinebidding\add_computer_devices.php

# Add monitor devices
php C:\xampp\htdocs\onlinebidding\add_monitor_devices.php

# Add tablet devices
php C:\xampp\htdocs\onlinebidding\add_tablet_devices.php
```

### **Test Backend:**
```bash
# Test connection
http://localhost/onlinebidding/test_connection.php

# Test all endpoints
http://localhost/onlinebidding/test_all_backend.php
```

---

## 📝 Notes

1. **All backend files are in place** ✅
2. **Database structure is complete** ✅
3. **Admin APIs are working** ✅
4. **Product CRUD operations functional** ✅

**You have everything needed for the backend!** 🎉

The only thing you might need to add in the future:
- Bidding functionality (`api/bids/place.php`)
- Payment processing (`api/payments/`)
- Notifications (`api/notifications/`)

But for the current admin product management features, everything is complete!

