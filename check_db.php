<?php
require_once 'inc/db.php';

// Check questions table
$result = $db->query("SHOW CREATE TABLE questions");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Questions Table:\n";
    echo $row['Create Table'] . "\n\n";
} else {
    echo "Questions table not found\n";
}

// Check answers table
$result = $db->query("SHOW CREATE TABLE answers");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Answers Table:\n";
    echo $row['Create Table'] . "\n\n";
} else {
    echo "Answers table not found\n";
}
?> 