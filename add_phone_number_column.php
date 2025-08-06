<?php
require_once 'inc/db.php';
$col = $db->query("SHOW COLUMNS FROM users LIKE 'phone_number'");
if ($col->num_rows == 0) {
    if ($db->query("ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) NULL AFTER email")) {
        echo "phone_number column added successfully!";
    } else {
        echo "Error adding column: " . $db->error;
    }
} else {
    echo "phone_number column already exists.";
}
?> 