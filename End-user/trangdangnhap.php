<?php 
session_start();
include "config.php";
include "header.php";

if(isset($_POST['dangnhap'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $pass = md5($password);

    $sql = $conn->prepare("SELECT * FROM TaiKhoan WHERE Username=? AND Password=?");
    $sql->execute([$username, $pass]);

    $user = $sql->fetch(PDO::FETCH_ASSOC);

    if($user){
        $_SESSION['user'] = $user['Username'];
        echo "<script>
        alert('Đăng nhập thành công');
        window.location='trangchu.php';
        </script>";
    } else {
        echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - TechZone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="trangdangnhap.css">
</head>
<body>

<section class="login-section">
    <div class="login-wrapper">
        <div class="login-box">
            <h2>Đăng Nhập</h2>

            <form method="POST">
                <label>Tên đăng nhập:</label>
                <input type="text" name="username" placeholder="Nhập tên đăng nhập" required>

                <label>Mật khẩu:</label>
                <input type="password" name="password" placeholder="Nhập mật khẩu" required>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox">
                        <span>Nhớ tài khoản</span>
                    </label>
                    <a href="#">Quên mật khẩu?</a>
                </div>

                <button class="buttonsign" type="submit" name="dangnhap">
                    Đăng nhập
                </button>

                <p class="register-text">
                    Chưa có tài khoản?
                    <a href="trangdangki.php">Đăng ký ngay</a>
                </p>
            </form>
        </div>
    </div>
</section>

</body>
</html>