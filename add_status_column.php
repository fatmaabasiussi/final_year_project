<?php
require_once 'inc/db.php';
$col = $db->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($col->num_rows == 0) {
    if ($db->query("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'inactive' AFTER role")) {
        echo "status column added successfully!";
    } else {
        echo "Error adding column: " . $db->error;
    }
} else {
    echo "status column already exists.";
}
?> 