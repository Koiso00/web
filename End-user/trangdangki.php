<?php 
session_start();
include "connect.php"; // Dùng connect.php (MySQLi) thay vì config.php (PDO)

if(isset($_POST['dangky'])){

    // Nhận và lọc dữ liệu để chống SQL Injection
    $hoten = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $sdt = mysqli_real_escape_string($conn, $_POST['phone']); 
    
    // Nhận 4 trường địa chỉ từ form
    $diachichitiet = mysqli_real_escape_string($conn, $_POST['diachichitiet']); 
    $phuongxa = mysqli_real_escape_string($conn, $_POST['phuongxa']);
    $quanhuyen = mysqli_real_escape_string($conn, $_POST['quanhuyen']);
    $tinhthanh = mysqli_real_escape_string($conn, $_POST['tinhthanh']);

    if($password != $confirm){
        echo "<script>alert('Mật khẩu nhập lại không chính xác!');</script>";
    } else {

        $pass = md5($password); 

        // Kiểm tra trùng lặp (Dùng chuẩn MySQLi)
        $sql_check = "SELECT * FROM taikhoan WHERE Username='$username' OR Email='$email'";
        $result_check = mysqli_query($conn, $sql_check);

        if(mysqli_num_rows($result_check) > 0){
            echo "<script>alert('Tên đăng nhập hoặc Email này đã được sử dụng!');</script>";
        } else {
            try {
                // Bắt đầu Transaction bằng MySQLi
                mysqli_begin_transaction($conn);

                // 1. Chèn vào bảng taikhoan
                $sql_tk = "INSERT INTO taikhoan (Username, Password, HoTen, Email, SoDienThoai, VaiTro, TrangThai) 
                           VALUES ('$username', '$pass', '$hoten', '$email', '$sdt', 0, 1)";
                mysqli_query($conn, $sql_tk);

                // Lấy ID tài khoản vừa được tạo
                $last_id = mysqli_insert_id($conn);

                // 2. Chèn 4 trường địa chỉ cụ thể vào bảng diachikhachhang
                $sql_dc = "INSERT INTO diachikhachhang (MaTK, TenNguoiNhan, SDTNhan, DiaChiChiTiet, PhuongXa, QuanHuyen, TinhThanh) 
                           VALUES ($last_id, '$hoten', '$sdt', '$diachichitiet', '$phuongxa', '$quanhuyen', '$tinhthanh')";
                mysqli_query($conn, $sql_dc);

                // Xác nhận lưu dữ liệu
                mysqli_commit($conn);

                echo "<script>
                    alert('Đăng ký tài khoản thành công!');
                    window.location='trangdangnhap.php';
                </script>";

            } catch (Exception $e) {
                // Hủy bỏ nếu có lỗi
                mysqli_rollback($conn);
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
    
    <link rel="stylesheet" href="styleforpr.css"> 
    <link rel="stylesheet" href="trangdangki.css">
</head>
<body>

    <?php include "header.php"; ?>

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