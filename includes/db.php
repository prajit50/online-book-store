<?php
// Change these values only if your XAMPP MySQL configuration differs.
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'bookstore_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Database connection failed. Import database/bookstore.sql and check includes/db.php.');
}
$conn->set_charset('utf8mb4');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>