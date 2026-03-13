<?php 
session_start();
include "config.php";

if(isset($_POST['dangky'])){

    $hoten = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $sdt = $_POST['phone']; 

    if($password != $confirm){
        echo "<script>alert('Mật khẩu không khớp');</script>";
    }else{

        $pass = md5($password);

        $check = $conn->prepare("SELECT * FROM TaiKhoan WHERE Username=? OR Email=?");
        $check->execute([$username,$email]);

        if($check->rowCount() > 0){
            echo "<script>alert('Tên đăng nhập hoặc email đã tồn tại');</script>";
        }else{

            $sql = $conn->prepare("INSERT INTO TaiKhoan 
            (Username,Password,HoTen,Email,SoDienThoai,VaiTro,TrangThai)
            VALUES (?,?,?,?,?,0,1)");

            if($sql->execute([$username,$pass,$hoten,$email,$sdt])){
                echo "<script>
                alert('Đăng ký thành công');
                window.location='trangdangnhap.php';
                </script>";
            }else{
                echo "<script>alert('Lỗi đăng ký');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng Ký</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="shortcut icon" href="/picture/png-transparent-laptop-computer-icons-computer-desktop-pc-electronics-rectangle-computer.png">
<link rel="stylesheet" href="trangdangki.css">

</head>
<body>

<header>

<a href="trangchu.php" class="logo">TechZone</a>

<div class="search-bar">

<input type="checkbox" id="menu-toggle" hidden>

<label for="menu-toggle" class="menu-btn">
<img src="picture/menu-burger.png" class="menu-icon">
</label>

<div class="dropdown-content">
<a href="sanpham-banphim.php">Bàn phím</a>
<a href="sanpham-chuot.php">Chuột</a>
<a href="sanpham-banphim.php">Tai nghe</a>
<a href="sanpham-chuot.php">Màn hình</a>
</div>

<form action="sanpham-banphim.php" method="GET" class="search-form">

<input type="search" name="q" placeholder="Tìm kiếm sản phẩm, thương hiệu...">

<button type="submit" class="search-submit">
<img src="picture/magnifying-glass.png">
</button>

</form>
</div>

<nav class="navbar">
<a href="trangchu.php">Trang chủ</a>
<a href="trangchu.php#products">Sản phẩm</a>
<a href="#bottom">Liên hệ</a>
</nav>

<div class="icon">

<div>
<a href="#" class="shopping-cart">
<img src="picture/shopping.png">
</a>
</div>

<div class="user">
<a href="trangdangnhap.php">
<img src="picture/user.png">
<span>Đăng nhập</span>
</a>
</div>

</div>

</header>

<section class="register-section">

<div class="register-wrapper">

<div class="register-box left">

<h2>Tạo tài khoản mới</h2>

<form method="POST">

<label>Họ và tên:</label>
<input type="text" name="fullname" placeholder="Nhập họ và tên" required>

<label>Email:</label>
<input type="email" name="email" placeholder="Nhập email" required>

<label>Số điện thoại:</label>
<input type="text" name="phone" placeholder="Nhập số điện thoại" required>

<label>Tên đăng nhập:</label>
<input type="text" name="username" placeholder="Nhập tên đăng nhập" required>

<label>Mật khẩu:</label>
<input type="password" name="password" placeholder="Tạo mật khẩu" required>

<label>Nhập lại mật khẩu:</label>
<input type="password" name="confirm" placeholder="Xác nhận mật khẩu" required>

<button type="submit" name="dangky" class="buttonsign">Đăng Ký</button>

<p class="register-text">
Đã có tài khoản?
<a href="trangdangnhap.php">Đăng nhập ngay</a>
</p>

</form>

</div>

<div class="divider">
<span>Hoặc</span>
</div>

<div class="register-box right">

<a href="#" class="google">
<img src="picture/google.png"> Đăng ký bằng Google
</a>

<a href="#" class="facebook">
<img src="picture/facebook.png"> Đăng ký bằng Facebook
</a>

<a href="#" class="apple">
<img src="picture/apple.png"> Đăng ký bằng Apple
</a>

</div>

</div>

</section>

</body>
</html>