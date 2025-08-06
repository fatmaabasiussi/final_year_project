<?php
$db = new mysqli('localhost', 'root', '1234', 'religion_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>
