<?php
require_once 'inc/db.php';
$col = $db->query("SHOW COLUMNS FROM courses LIKE 'document_url'");
if ($col->num_rows == 0) {
    if ($db->query("ALTER TABLE courses ADD COLUMN document_url VARCHAR(255) NULL AFTER image_url")) {
        echo "document_url column added successfully!";
    } else {
        echo "Error adding column: " . $db->error;
    }
} else {
    echo "document_url column already exists.";
}
?> 