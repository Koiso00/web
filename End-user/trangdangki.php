<?php 
session_start();
include "config.php"; 
include "header.php"; 

if(isset($_POST['dangky'])){

    $hoten = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $sdt = $_POST['phone']; 
    
    // Nhận 4 trường địa chỉ từ form mới
    $diachichitiet = $_POST['diachichitiet']; 
    $phuongxa = $_POST['phuongxa'];
    $quanhuyen = $_POST['quanhuyen'];
    $tinhthanh = $_POST['tinhthanh'];

    if($password != $confirm){
        echo "<script>alert('Mật khẩu nhập lại không chính xác!');</script>";
    } else {

        $pass = md5($password); 

        $check = $conn->prepare("SELECT * FROM taikhoan WHERE Username=? OR Email=?");
        $check->execute([$username, $email]);

        if($check->rowCount() > 0){
            echo "<script>alert('Tên đăng nhập hoặc Email này đã được sử dụng!');</script>";
        } else {
            try {
                $conn->beginTransaction();

                // Chèn vào bảng taikhoan
                $sql_tk = $conn->prepare("INSERT INTO taikhoan 
                    (Username, Password, HoTen, Email, SoDienThoai, VaiTro, TrangThai) 
                    VALUES (?, ?, ?, ?, ?, 0, 1)");
                $sql_tk->execute([$username, $pass, $hoten, $email, $sdt]);

                $last_id = $conn->lastInsertId();

                // Chèn 4 trường địa chỉ cụ thể vào bảng diachikhachhang
                $sql_dc = $conn->prepare("INSERT INTO diachikhachhang 
                    (MaTK, TenNguoiNhan, SDTNhan, DiaChiChiTiet, PhuongXa, QuanHuyen, TinhThanh) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                $sql_dc->execute([$last_id, $hoten, $sdt, $diachichitiet, $phuongxa, $quanhuyen, $tinhthanh]);

                $conn->commit();

                echo "<script>
                    alert('Đăng ký tài khoản thành công!');
                    window.location='trangdangnhap.php';
                </script>";

            } catch (Exception $e) {
                $conn->rollBack();
                echo "<script>alert('Lỗi đăng ký: " . $e->getMessage() . "');</script>";
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
    <title>Đăng Ký - TechZone</title>
    <link rel="stylesheet" href="trangdangki.css">
</head>
<body>

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

                    <label>Địa chỉ chi tiết (Số nhà, Tên đường):</label>
                    <input type="text" name="diachichitiet" placeholder="Vd: 123 Nguyễn Huệ" required>

                    <div class="address-group">
                        <div>
                            <label>Phường/Xã:</label>
                            <input type="text" name="phuongxa" placeholder="Vd: Bến Nghé" required>
                        </div>
                        <div>
                            <label>Quận/Huyện:</label>
                            <input type="text" name="quanhuyen" placeholder="Vd: Quận 1" required>
                        </div>
                        <div>
                            <label>Tỉnh/Thành:</label>
                            <input type="text" name="tinhthanh" placeholder="Vd: TP.HCM" required>
                        </div>
                    </div>
                    <label>Tên đăng nhập:</label>
                    <input type="text" name="username" placeholder="Nhập tên đăng nhập" required>

                    <label>Mật khẩu:</label>
                    <input type="password" name="password" placeholder="Tạo mật khẩu" required>

                    <label>Nhập lại mật khẩu:</label>
                    <input type="password" name="confirm" placeholder="Xác nhận mật khẩu" required>

                    <button type="submit" name="dangky" class="buttonsign">Đăng Ký</button>

                    <p class="register-text">
                        Đã có tài khoản? <a href="trangdangnhap.php">Đăng nhập ngay</a>
                    </p>
                </form>
            </div>

            <div class="divider"><span>Hoặc</span></div>

            <div class="register-box right">
                <a href="#" class="google"><img src="picture/google.png"> Đăng ký bằng Google</a>
                <a href="#" class="facebook"><img src="picture/facebook.png"> Đăng ký bằng Facebook</a>
                <a href="#" class="apple"><img src="picture/apple.png"> Đăng ký bằng Apple</a>
            </div>
        </div>
    </section>
</body>
</html>