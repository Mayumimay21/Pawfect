<?php
// Database configuration
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'pawfect_db');
define('DB_USER', 'root');
define('DB_PASS', '');

/*
 * Change the Host to your database host for Default XAMPP is localhost:3306
 */

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
