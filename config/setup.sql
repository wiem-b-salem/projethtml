-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS school_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE school_db;

-- Create parents table
CREATE TABLE IF NOT EXISTS parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    phone_secondary VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create children table
CREATE TABLE IF NOT EXISTS children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    level VARCHAR(50) NOT NULL,
    allergies BOOLEAN DEFAULT FALSE,
    allergies_details TEXT,
    disability BOOLEAN DEFAULT FALSE,
    disability_details TEXT,
    other_health_info BOOLEAN DEFAULT FALSE,
    other_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 