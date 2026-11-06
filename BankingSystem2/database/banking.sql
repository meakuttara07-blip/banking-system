CREATE DATABASE IF NOT EXISTS banking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE banking;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE accounts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  account_number CHAR(10) NOT NULL UNIQUE,
  balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 CHECK (balance >= 0),
  status ENUM('active','frozen') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id INT UNSIGNED NOT NULL,
  type ENUM('deposit','withdraw','transfer_out','transfer_in') NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  balance_after DECIMAL(15,2) NOT NULL,
  related_account_id INT UNSIGNED NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (account_id, created_at),
  FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
  FOREIGN KEY (related_account_id) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default admin: admin@bank.local / admin123  (change after first login!)
INSERT INTO users (full_name,email,password,role) VALUES
('Administrator','admin@bank.local','$2b$12$HSQLColq8yLjQwmoMqoAS.Sd4FzwskFAFxOQLs5bSnwPkByhSvrfm','admin');
