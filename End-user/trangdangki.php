<?php 
session_start();
include "config.php"; // File kết nối PDO của bạn

if(isset($_POST['dangky'])){

    $hoten = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $sdt = $_POST['phone']; 
    $diachi = $_POST['address']; // Nhận từ ô input địa chỉ mới

    // 1. Kiểm tra mật khẩu khớp
    if($password != $confirm){
        echo "<script>alert('Mật khẩu nhập lại không chính xác!');</script>";
    } else {

        $pass = md5($password); // Database của bạn đang dùng md5

        // 2. Kiểm tra Username hoặc Email đã tồn tại chưa
        $check = $conn->prepare("SELECT * FROM taikhoan WHERE Username=? OR Email=?");
        $check->execute([$username, $email]);

        if($check->rowCount() > 0){
            echo "<script>alert('Tên đăng nhập hoặc Email này đã được sử dụng!');</script>";
        } else {
            
            try {
                // Bắt đầu Transaction để lưu vào 2 bảng cùng lúc
                $conn->beginTransaction();

                // 3. Chèn vào bảng taikhoan
                // VaiTro = 0 (Khách hàng), TrangThai = 1 (Hoạt động)
                $sql_tk = $conn->prepare("INSERT INTO taikhoan 
                    (Username, Password, HoTen, Email, SoDienThoai, VaiTro, TrangThai) 
                    VALUES (?, ?, ?, ?, ?, 0, 1)");
                
                $sql_tk->execute([$username, $pass, $hoten, $email, $sdt]);

                // Lấy MaTK vừa tự động tăng của tài khoản này
                $last_id = $conn->lastInsertId();

                // 4. Chèn vào bảng diachikhachhang
                // Theo cấu trúc database của bạn: MaDC tự tăng, MaTK là khóa ngoại
                // Các trường PhuongXa, QuanHuyen, TinhThanh để tạm rỗng nếu form chưa chia nhỏ
                $sql_dc = $conn->prepare("INSERT INTO diachikhachhang 
                    (MaTK, TenNguoiNhan, SDTNhan, DiaChiChiTiet, PhuongXa, QuanHuyen, TinhThanh) 
                    VALUES (?, ?, ?, ?, '', '', '')");
                
                $sql_dc->execute([$last_id, $hoten, $sdt, $diachi]);

                // Xác nhận lưu mọi thay đổi vào Database
                $conn->commit();

                echo "<script>
                    alert('Đăng ký tài khoản thành công!');
                    window.location='trangdangnhap.php';
                </script>";

            } catch (Exception $e) {
                // Nếu có bất kỳ lỗi nào, hủy bỏ toàn bộ quá trình (không tạo tài khoản lỗi)
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
    <header>
        <a href="trangchu.php" class="logo">TechZone</a>
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

                    <label>Địa chỉ giao hàng:</label>
                    <input type="text" name="address" placeholder="Số nhà, tên đường, phường, quận..." required>

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