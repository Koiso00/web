<?php 
session_start();
include "connect.php"; // Dùng connect.php cho đồng bộ với toàn hệ thống

// Xử lý đăng nhập bằng MySQLi
if(isset($_POST['dangnhap'])){
    // Chống SQL Injection cơ bản
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $pass = md5($password);

    // Truy vấn bằng MySQLi
    $sql = "SELECT * FROM TaiKhoan WHERE Username='$username' AND Password='$pass'";
    $result = mysqli_query($conn, $sql);

    // Kiểm tra xem có dòng nào khớp không
    if($result && mysqli_num_rows($result) > 0){
        // Lấy dữ liệu tài khoản
        $user = mysqli_fetch_assoc($result);
        
        // Kiểm tra trạng thái tài khoản
        if ($user['TrangThai'] == 0) {
            echo "<script>alert('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');</script>";
        } else {
            if ($user['VaiTro'] == 1) {
                $_SESSION['admin'] = $user;
                echo "<script>
                alert('Đăng nhập trang quản trị thành công');
                window.location='../Admin/Trang-chu.php';
                </script>";
            } else {
                // Gán đúng biến Session đã thống nhất ở Header và các trang khác
                $_SESSION['MaTK'] = $user['MaTK'];
                $_SESSION['HoTen'] = $user['HoTen'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['user'] = $user['Email'];

                echo "<script>
                alert('Đăng nhập thành công');
                window.location='trangchu.php';
                </script>";
            }
        }
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
    
    <link rel="stylesheet" href="styleforpr.css"> 
    <link rel="stylesheet" href="trangdangnhap.css">
</head>
<body>

    <?php include "header.php"; ?>

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

    <footer id="bottom">
        <section class="footer">
            <div class="footer-box">
                <ul>
                    <li><b>Dịch vụ khách hàng</b></li>
                    <li>Trung tâm trợ giúp</li>
                    <li>Hướng dẫn mua hàng</li>
                    <li>Đơn hàng</li>
                    <li>Trả hàng / hoàn tiền</li>
                    <li>Chính sách bảo hành</li>
                </ul>
            </div>

            <div class="footer-box">
                <ul>
                    <li><b>TechZone Việt Nam</b></li>
                    <li>Về TechZone</li>
                    <li>Tuyển dụng</li>
                    <li>Điều khoản</li>
                    <li>Chính sách bảo mật</li>
                </ul>
            </div>

            <div class="footer-box">
                <ul>
                    <li><b>Thanh toán</b></li>
                </ul>
                <div class="payment">
                    <table>
                        <tr>
                            <td><img src="picture/thanhtoan1.png"></td>
                            <td><img src="picture/thanhtoan2.png"></td>
                            <td><img src="picture/thanhtoan3.png"></td>
                            <td><img src="picture/thanhtoan7.png"></td>
                        </tr>
                        <tr>
                            <td><img src="picture/thanhtoan4.png"></td>
                            <td><img src="picture/thanhtoan5.png"></td>
                            <td><img src="picture/thanhtoan6.png"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </section>
    </footer>
</body>
</html>