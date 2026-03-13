<?php
$servername = "localhost";
$username = "root"; // Tên đăng nhập mysql của bạn (thường là root)
$password = ""; // Mật khẩu mysql (thường để trống nếu dùng XAMPP)
$dbname = "db_banhang";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>