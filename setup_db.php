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