<?php
require_once 'inc/db.php';
// Check if column exists
$col = $db->query("SHOW COLUMNS FROM questions LIKE 'is_answer_seen'");
if ($col->num_rows == 0) {
    if ($db->query("ALTER TABLE questions ADD COLUMN is_answer_seen TINYINT(1) NOT NULL DEFAULT 0 AFTER answer")) {
        echo "Column is_answer_seen added successfully!";
    } else {
        echo "Error adding column: " . $db->error;
    }
} else {
    echo "Column is_answer_seen already exists.";
}
?> 