-- Triumpay App Database Schema

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referral_id INT DEFAULT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    wallet_address VARCHAR(100),
    balanceUSD DECIMAL(15,2) DEFAULT 0.00,
    balanceTRDX DECIMAL(15,6) DEFAULT 0.000000,
    Totcommision DECIMAL(15,2) DEFAULT 0.00,
    reward DECIMAL(15,2) DEFAULT 0.00,
    omsetUSD DECIMAL(15,2) DEFAULT 0.00,
    packageStaking VARCHAR(50) DEFAULT NULL,
    status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (referral_id) REFERENCES users(id)
);

-- Deposit History Table
CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount_usd DECIMAL(15,2) NOT NULL,
    amount_trdx DECIMAL(15,6) NOT NULL,
    tx_hash VARCHAR(255) NOT NULL,
    status ENUM('pending', 'confirmed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Staking Table
CREATE TABLE IF NOT EXISTS staking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package VARCHAR(50) NOT NULL,
    amount_usd DECIMAL(15,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    roi_rate DECIMAL(5,2) NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Mining History Table
CREATE TABLE IF NOT EXISTS mining_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_trdx DECIMAL(15,6) NOT NULL,
    reward_usd DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Activity Log Table
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity VARCHAR(255) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Referral Commission Table
CREATE TABLE IF NOT EXISTS referral_commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    referral_id INT NOT NULL,
    commission_usd DECIMAL(15,2) NOT NULL,
    commission_trdx DECIMAL(15,6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (referral_id) REFERENCES users(id)
);

-- Staking Packages Table
CREATE TABLE IF NOT EXISTS staking_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    min_amount DECIMAL(15,2) NOT NULL,
    max_amount DECIMAL(15,2) NOT NULL,
    roi_rate DECIMAL(5,2) NOT NULL,
    term_days INT NOT NULL
);

-- Insert sample staking packages
INSERT INTO staking_packages (name, min_amount, max_amount, roi_rate, term_days) VALUES
('Silver', 10.00, 100.00, 0.5, 100),
('Gold', 110.00, 500.00, 1.0, 100),
('Platinum', 510.00, 1000.00, 2.0, 100);

-- Insert master user
INSERT INTO users (username, fullname, email, phone, password, wallet_address, status) VALUES
('master', 'Master User', 'master@triumpay.app', '0000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '74KEB2sE9jANeFXyqqkLDUxdwnMscCVj8HSUHNFb8c4h', 1);