<?php
session_start();
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = md5($_POST['password']); // Khớp với md5 trong database

    $stmt = $conn->prepare("SELECT * FROM TaiKhoan WHERE Username = ? AND Password = ? AND VaiTro = 1");
    $stmt->execute([$user, $pass]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $_SESSION['admin'] = $row;
        header("Location: Trang-chu.php");
        exit();
    } else {
        $error = "Sai tài khoản hoặc mật khẩu Admin!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>
    <h1 class="login-page-logo">TechZone</h1>
    <section class="login-section">
        <div class="login-wrapper">
            <div class="login-box">
                <h2>Đăng Nhập Admin</h2>
                <?php if($error) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>
                <form method="POST">
                    <label>Tên đăng nhập:</label>
                    <input type="text" name="username" required>
                    <label>Mật khẩu:</label>
                    <input type="password" name="password" required>
                    <button type="submit" class="buttonsign">Đăng nhập</button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>