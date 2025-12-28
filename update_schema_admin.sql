-- Add is_admin column to users table
USE onlinebidding;

-- If you get an error that column already exists, that's fine - just ignore it
ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0;

-- Create an admin user (optional - password: admin123)
-- UPDATE users SET is_admin = 1 WHERE email = 'admin@example.com';
-- Or insert a default admin:
-- INSERT INTO users (email, password_hash, name, is_admin) 
-- VALUES ('admin@example.com', '$2y$10$YourHashedPasswordHere', 'Admin User', 1)
-- ON DUPLICATE KEY UPDATE is_admin = 1;

