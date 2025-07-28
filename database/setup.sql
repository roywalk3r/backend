-- Use the correct database
USE backend;

-- Insert default roles
INSERT IGNORE INTO roles (id, name) VALUES
(1, 'admin'),
(2, 'support_agent');

-- Add missing columns to bookings table (wrapped individually to avoid errors)
ALTER TABLE bookings ADD COLUMN quantity DECIMAL(10,2) DEFAULT 1;
ALTER TABLE bookings ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0;
ALTER TABLE bookings ADD COLUMN booking_time TIME DEFAULT '09:00:00';
ALTER TABLE bookings ADD COLUMN notes TEXT;
ALTER TABLE bookings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add missing columns to services table
ALTER TABLE services ADD COLUMN unit VARCHAR(20) DEFAULT 'piece';
ALTER TABLE services ADD COLUMN category VARCHAR(50) DEFAULT 'general';
ALTER TABLE services ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE services ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE services ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add missing columns to enquiries table
ALTER TABLE enquiries ADD COLUMN phone VARCHAR(20);
ALTER TABLE enquiries ADD COLUMN assigned_to INT;
ALTER TABLE enquiries ADD COLUMN response TEXT;
ALTER TABLE enquiries ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add foreign key if not exists (MySQL doesn’t support IF NOT EXISTS, so we must check manually in production)
ALTER TABLE enquiries 
ADD CONSTRAINT fk_enquiries_assigned_to
FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL;

-- Add missing columns to users table
ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE;
ALTER TABLE users ADD COLUMN first_name VARCHAR(50);
ALTER TABLE users ADD COLUMN last_name VARCHAR(50);
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Create activity_logs table if not exists
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create login_attempts table if not exists
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_ip (ip_address),
    INDEX idx_attempted_at (attempted_at)
);

-- Insert default admin user (hashed password: admin123)
INSERT IGNORE INTO users (name, email, password, role_id, username, first_name, last_name) VALUES
(
    'System Administrator',
    'admin@nananomfarms.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    1,
    'admin',
    'System',
    'Administrator'
);

-- Insert sample services
INSERT IGNORE INTO services (name, description, price, unit, category) VALUES
('Crude Palm Oil', 'High quality crude palm oil for industrial use', 850.00, 'ton', 'crude_oil'),
('Refined Palm Oil', 'Refined palm oil suitable for cooking', 950.00, 'ton', 'refined_oil'),
('Palm Kernel Oil', 'Premium palm kernel oil', 1200.00, 'ton', 'kernel_oil'),
('Palm Oil Consultation', 'Expert consultation on palm oil production', 150.00, 'hour', 'consultation'),
('Bulk Palm Oil Supply', 'Large scale palm oil supply for manufacturers', 800.00, 'ton', 'bulk_supply');
