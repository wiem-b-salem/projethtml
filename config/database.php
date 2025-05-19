<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Laragon default username
define('DB_PASS', '');         // Laragon default password
define('DB_NAME', 'school_db');

// Create database connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// SQL to create tables
$createTablesSQL = "
-- Parents table
CREATE TABLE IF NOT EXISTS parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    secondary_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Children table
CREATE TABLE IF NOT EXISTS children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    grade_level ENUM('3rd Grade', '4th Grade', '5th Grade') NOT NULL,
    allergies TEXT,
    special_needs TEXT,
    other_health_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
);

-- Activities table
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    category ENUM('art', 'sports', 'activities') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Photos table
CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    category ENUM('art', 'sports', 'activities') NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE SET NULL
);

-- Messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
);

-- Progress tracking table
CREATE TABLE IF NOT EXISTS progress_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    social_development INT CHECK (social_development BETWEEN 0 AND 100),
    cognitive_skills INT CHECK (cognitive_skills BETWEEN 0 AND 100),
    physical_development INT CHECK (physical_development BETWEEN 0 AND 100),
    artistic_skills INT CHECK (artistic_skills BETWEEN 0 AND 100),
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE
);
";

// Function to initialize database
function initializeDatabase() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if it doesn't exist
        $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->exec("USE " . DB_NAME);
        
        // Create tables
        global $createTablesSQL;
        $conn->exec($createTablesSQL);
        
        return true;
    } catch(PDOException $e) {
        die("Database initialization failed: " . $e->getMessage());
    }
}

// Initialize database when this file is included
initializeDatabase();
?> 