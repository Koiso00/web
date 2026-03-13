<?php
$host = 'localhost';
$dbname = 'db_banhang';
$user = 'root';
$pass = '';

try {
    // Chuyển sang utf8mb4 để hỗ trợ đầy đủ ký tự tiếng Việt và icon
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
