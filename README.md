# Online Bidding Backend API

## Setup Instructions

### 1. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Import the `schema.sql` file to create the database and tables
   - Or run the SQL commands manually in phpMyAdmin

### 2. Configuration
- Database is configured in `db.php`
- Default XAMPP settings:
  - Host: localhost
  - User: root
  - Password: (empty)
  - Database: onlinebidding

### 3. API Endpoints

#### Register User
- **URL:** `http://localhost/onlinebidding/api/register.php`
- **Method:** POST
- **Content-Type:** application/json
- **Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+91 9876543210",
  "dob": "01/01/1990",
  "gender": "Male",
  "password": "password123"
}
```
- **Response:**
```json
{
  "status": true,
  "success": true,
  "message": "Registration successful",
  "user_id": 1
}
```

#### Login
- **URL:** `http://localhost/onlinebidding/api/login.php`
- **Method:** POST
- **Content-Type:** application/json
- **Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### 4. Testing
- Test the API using Postman or browser
- Make sure XAMPP Apache and MySQL are running
- Access: http://localhost/onlinebidding/api/register.php

### 5. Android App Configuration
Update `RetrofitInstance.kt` with:
- For Emulator: `http://10.0.2.2/onlinebidding/`
- For Physical Device: `http://YOUR_COMPUTER_IP/onlinebidding/`

