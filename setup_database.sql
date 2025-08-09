-- Create database if not exists
CREATE DATABASE IF NOT EXISTS religion_db;
USE religion_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'scholar', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive',
    remember_token VARCHAR(100),
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Login attempts table
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email, attempt_time)
);

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email, token)
);

-- Create default admin user
INSERT INTO users (name, email, password, role, status, email_verified_at) VALUES 
('Admin', 'admin@islamicedu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW())
ON DUPLICATE KEY UPDATE id=id; 

-- Drop existing tables if they exist
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS questions;

-- Create questions table
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    category ENUM('aqeedah', 'fiqh', 'hadith', 'tafsir', 'seerah', 'other') NOT NULL,
    course_id INT,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    answer TEXT,
    answered_by INT,
    answered_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (answered_by) REFERENCES users(id)
);

-- Create answers table
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    scholar_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id),
    FOREIGN KEY (scholar_id) REFERENCES users(id)
);

-- Add indexes for better performance
CREATE INDEX idx_questions_user ON questions(user_id);
CREATE INDEX idx_questions_status ON questions(status);
CREATE INDEX idx_questions_course ON questions(course_id);
CREATE INDEX idx_questions_category ON questions(category);
CREATE INDEX idx_answers_question ON answers(question_id);
CREATE INDEX idx_answers_scholar ON answers(scholar_id); 

<?php
require_once 'inc/db.php';

// Add scholar_id column if not exists
$db->query("ALTER TABLE questions ADD COLUMN scholar_id INT NULL AFTER user_id");
$db->query("ALTER TABLE questions ADD CONSTRAINT fk_questions_scholar FOREIGN KEY (scholar_id) REFERENCES users(id)");

// Add is_answer_seen column if not exists
$db->query("ALTER TABLE questions ADD COLUMN is_answer_seen TINYINT(1) NOT NULL DEFAULT 0 AFTER answer");

// Add document_url column if not exists
$db->query("ALTER TABLE courses ADD COLUMN document_url VARCHAR(255) NULL AFTER image_url");
echo "document_url column added (if not exists).\n";

// Create questions table (if not exists, with scholar_id and is_answer_seen)
$sql = "CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    scholar_id INT NULL,
    question TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    answer TEXT,
    is_answer_seen TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (scholar_id) REFERENCES users(id),
    INDEX idx_questions_user (user_id),
    INDEX idx_questions_scholar (scholar_id),
    INDEX idx_questions_status (status)
)";

if ($db->query($sql)) {
    echo "Questions table created/updated successfully\n";
} else {
    echo "Error creating/updating questions table: " . $db->error . "\n";
}

echo "Database setup complete!\n";
?> 