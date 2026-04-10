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
                    <form method="POST" onsubmit="return validateDangKy()" name="formDangKy">
                        <label>Họ và tên:</label>
                        <div class="input-group">
                            <input type="text" name="fullname" id="fullname" placeholder="Nhập họ và tên" required oninput="validateField('fullname')">
                            <span id="err-fullname" class="error-msg"></span>
                        </div>

                        <label>Email:</label>
                        <div class="input-group">
                            <input type="email" name="email" id="email" placeholder="Nhập email" required oninput="validateField('email')">
                            <span id="err-email" class="error-msg"></span>
                        </div>

                        <label>Số điện thoại:</label>
                        <div class="input-group">
                            <input type="text" name="phone" id="phone" placeholder="Nhập số điện thoại" required oninput="validateField('phone')">
                            <span id="err-phone" class="error-msg"></span>
                        </div>

                        <label>Địa chỉ chi tiết (Số nhà, Tên đường):</label>
                        <div class="input-group">
                            <input type="text" name="diachichitiet" id="diachichitiet" placeholder="Vd: 123 Nguyễn Huệ" required oninput="validateField('diachichitiet')">
                            <span id="err-diachichitiet" class="error-msg"></span>
                        </div>

                        <div class="address-group">
                            <div>
                                <label>Phường/Xã:</label>
                                <div class="input-group">
                                    <input type="text" name="phuongxa" id="phuongxa" placeholder="Vd: Bến Nghé" required oninput="validateField('phuongxa')">
                                    <span id="err-phuongxa" class="error-msg"></span>
                                </div>
                            </div>
                            <div>
                                <label>Quận/Huyện:</label>
                                <div class="input-group">
                                    <input type="text" name="quanhuyen" id="quanhuyen" placeholder="Vd: Quận 1" required oninput="validateField('quanhuyen')">
                                    <span id="err-quanhuyen" class="error-msg"></span>
                                </div>
                            </div>
                            <div>
                                <label>Tỉnh/Thành:</label>
                                <div class="input-group">
                                    <input type="text" name="tinhthanh" id="tinhthanh" placeholder="Vd: TP.HCM" required oninput="validateField('tinhthanh')">
                                    <span id="err-tinhthanh" class="error-msg"></span>
                                </div>
                            </div>
                        </div>
                        
                        <label>Tên đăng nhập:</label>
                        <div class="input-group">
                            <input type="text" name="username" id="username" placeholder="Nhập tên đăng nhập" required oninput="validateField('username')">
                            <span id="err-username" class="error-msg"></span>
                        </div>

                        <label>Mật khẩu:</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" placeholder="Tạo mật khẩu" required oninput="validateField('password')">
                            <span id="err-password" class="error-msg"></span>
                        </div>

                        <label>Nhập lại mật khẩu:</label>
                        <div class="input-group">
                            <input type="password" name="confirm" id="confirm" placeholder="Xác nhận mật khẩu" required oninput="validateField('confirm')">
                            <span id="err-confirm" class="error-msg"></span>
                        </div>

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

    <script>
        function validateField(fieldId) {
            const input = document.getElementById(fieldId);
            const errorSpan = document.getElementById('err-' + fieldId);
            const value = (input?.value || '').trim();
            let message = '';

            const regexPhone = /^0[0-9]{9}$/;

            if (fieldId === 'fullname') {
                if (value === '') message = '* Vui lòng nhập Họ & tên.';
            } else if (fieldId === 'email') {
                if (value === '') message = '* Vui lòng nhập Email.';
            } else if (fieldId === 'phone') {
                if (value === '') message = '* Vui lòng nhập SĐT.';
                else if (!regexPhone.test(value)) message = '* SĐT phải là 10 số (Bắt đầu bằng 0).';
            } else if (fieldId === 'diachichitiet') {
                if (value === '') message = '* Vui lòng nhập địa chỉ chi tiết.';
            } else if (fieldId === 'phuongxa') {
                if (value === '') message = '* Vui lòng nhập Phường/Xã.';
            } else if (fieldId === 'quanhuyen') {
                if (value === '') message = '* Vui lòng nhập Quận/Huyện.';
            } else if (fieldId === 'tinhthanh') {
                if (value === '') message = '* Vui lòng nhập Tỉnh/Thành phố.';
            } else if (fieldId === 'username') {
                if (value === '') message = '* Vui lòng nhập Tên đăng nhập.';
            } else if (fieldId === 'password') {
                if (value === '') message = '* Vui lòng nhập Mật khẩu.';
                else if (value.length < 6) message = '* Mật khẩu phải có ít nhất 6 ký tự.';
            } else if (fieldId === 'confirm') {
                const pass = (document.getElementById('password')?.value || '');
                if (value === '') message = '* Vui lòng nhập lại mật khẩu.';
                else if (value !== pass) message = '* Mật khẩu nhập lại không khớp.';
            }

            if (errorSpan) errorSpan.innerText = message;
            if (input) {
                if (message) input.classList.add('invalid');
                else input.classList.remove('invalid');
            }
            return message === '';
        }

        function validateDangKy() {
            const fields = ['fullname', 'email', 'phone', 'diachichitiet', 'phuongxa', 'quanhuyen', 'tinhthanh', 'username', 'password', 'confirm'];
            for (const f of fields) {
                const ok = validateField(f);
                if (!ok) {
                    document.getElementById(f)?.focus();
                    alert('Vui lòng sửa các lỗi nhập liệu trước khi đăng ký!');
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>