<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user'])) {
    header("location:trangdangnhap.php");
    exit();
}

$user_session = $_SESSION['user'];

// Lấy MaTK (Kiểm tra bằng Email hoặc Username cho chính xác với database)
$sql_user = "SELECT MaTK FROM taikhoan WHERE Email='$user_session' OR Username='$user_session' LIMIT 1";
$res_user = mysqli_query($conn, $sql_user);
$row_user = mysqli_fetch_assoc($res_user);
$maTK = $row_user['MaTK'];

$sql_dc = "SELECT * FROM diachikhachhang WHERE MaTK='$maTK'";
$res_dc = mysqli_query($conn, $sql_dc);

$tong_tien = 0;

if(isset($_SESSION['giohang'])){
    foreach($_SESSION['giohang'] as $id_sp => $so_luong){
        $sql = "SELECT * FROM sanpham WHERE MaSP=$id_sp";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($res);

        $gia = $row['GiaNhapBinhQuan'] * (1 + $row['TiLeLoiNhuan']);
        $tong_tien += $gia * $so_luong;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán | TechZone</title>
    <link rel="stylesheet" href="thanhtoan.css">
</head>
<body>

<?php include 'header.php'; ?>

<form action="process_order.php" method="POST">
    <div class="container">
        <div class="box left">
            <h2>Địa chỉ giao hàng</h2>
            
            <?php if(mysqli_num_rows($res_dc) > 0) { ?>
            <label>
                <input type="radio" name="diachi_option" value="cu" checked onclick="toggleAddress()"> 
                Sử dụng địa chỉ đã lưu
            </label>
            <select name="diachi_cu" id="diachi_cu">
                <?php
                while($row = mysqli_fetch_assoc($res_dc)){
                    echo "<option value='".$row['MaDC']."'>
                    ".$row['TenNguoiNhan']." - ".$row['SDTNhan']." | ".
                    $row['DiaChiChiTiet'].", P ".$row['PhuongXa'].", Q ".$row['QuanHuyen'].", ".$row['TinhThanh']."
                    </option>";
                }
                ?>
            </select>
            <?php } else { echo "<input type='hidden' name='diachi_option' value='moi'>"; } ?>

            <label>
                <input type="radio" name="diachi_option" value="moi" <?php echo (mysqli_num_rows($res_dc) == 0) ? 'checked' : ''; ?> onclick="toggleAddress()"> 
                Nhập địa chỉ mới
            </label>

            <div id="diachi_moi" style="display: <?php echo (mysqli_num_rows($res_dc) == 0) ? 'block' : 'none'; ?>;">
    <div class="input-group">
        <input type="text" name="TenNguoiNhan" id="TenNguoiNhan" placeholder="Họ & Tên" oninput="validateField('TenNguoiNhan')">
        <span id="err-TenNguoiNhan" class="error-msg"></span>
    </div>

    <div class="input-group">
        <input type="text" name="SDTNhan" id="SDTNhan" placeholder="SĐT Liên hệ" oninput="validateField('SDTNhan')">
        <span id="err-SDTNhan" class="error-msg"></span>
    </div>

    <div class="input-group">
        <input type="text" name="DiaChiChiTiet" id="DiaChiChiTiet" placeholder="Số nhà, Tên đường" oninput="validateField('DiaChiChiTiet')">
        <span id="err-DiaChiChiTiet" class="error-msg"></span>
    </div>
    
    <!-- Các trường khác giữ nguyên -->
    <input type="text" name="PhuongXa" id="PhuongXa" placeholder="Phường/Xã">
    <input type="text" name="QuanHuyen" id="QuanHuyen" placeholder="Quận/Huyện">
    <input type="text" name="TinhThanh" id="TinhThanh" placeholder="Tỉnh/Thành phố">
</div>

            <hr style="margin: 2rem 0; border: 0.5px solid #eee;">

            <h2>Phương thức thanh toán</h2>
            <label>
                <input type="radio" name="payment" value="Tiền mặt" checked onclick="togglePayment()">
                Thanh toán tiền mặt khi nhận hàng (COD)
            </label>
            <br>
            <label>
                <input type="radio" name="payment" value="Chuyển khoản" onclick="togglePayment()">
                Chuyển khoản ngân hàng
            </label>
            <div class="payment-info" id="bank-info">
                <p><b>Ngân hàng:</b> Vietcombank</p>
                <p><b>Chủ tài khoản:</b> TECHZONE VIETNAM</p>
                <p><b>Số tài khoản:</b> 123456789</p>
                <p><b>Nội dung:</b> Thanh toán đơn hàng</p>
            </div>
            <br>
            <label>
                <input type="radio" name="payment" value="Ví điện tử" onclick="togglePayment()">
                Ví điện tử (Momo, ZaloPay)
            </label>
            <div class="payment-info" id="online-info">
                <p>Chức năng thanh toán trực tuyến đang được cập nhật.</p>
            </div>
        </div>

        <div class="box right">
            <h2>Đơn hàng của bạn</h2>
            <p>Tổng tiền thanh toán:</p>
            <h3><?php echo number_format($tong_tien, 0, ",", "."); ?> ₫</h3>
            <input type="hidden" name="TongTien" value="<?php echo $tong_tien; ?>">
            <button type="submit" class="order-btn" onclick="return validateForm()">
                Xác nhận đặt hàng
            </button>
        </div>
    </div>
</form>

<script>
function toggleAddress(){
    let option = document.querySelector('input[name="diachi_option"]:checked').value;
    let dcMoi = document.getElementById("diachi_moi");
    let dcCu = document.getElementById("diachi_cu");
    
    if(option == "moi"){
        dcMoi.style.display = "block";
        if(dcCu) dcCu.disabled = true;
    } else {
        dcMoi.style.display = "none";
        if(dcCu) dcCu.disabled = false;
    }
}

function togglePayment(){
    let p = document.querySelector('input[name="payment"]:checked').value;
    document.getElementById("bank-info").style.display = "none";
    document.getElementById("online-info").style.display = "none";

    if(p == "Chuyển khoản"){
        document.getElementById("bank-info").style.display = "block";
    }
    if(p == "Ví điện tử"){
        document.getElementById("online-info").style.display = "block";
    }
}

// Hàm kiểm tra từng trường một cách chi tiết
function validateField(fieldId) {
    const input = document.getElementById(fieldId);
    const errorSpan = document.getElementById('err-' + fieldId);
    const value = input.value.trim();
    let message = "";

    // Regex tiếng Việt không chứa số/kí tự đặc biệt
    const regexName = /^[a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪỬỮỰỲỴÝỶỸửữựỳỵỷỹ\s]+$/;
    // Regex SĐT 10 số bắt đầu bằng 0
    const regexPhone = /^0[0-9]{9}$/;
    // Regex địa chỉ chỉ cho phép / và -
    const regexAddress = /^[a-zA-Z0-9ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪỬỮỰỲỴÝỶỸửữựỳỵỷỹ\s\/\-]+$/;

    if (fieldId === 'TenNguoiNhan') {
        if (!value.includes(' ')) message = "* Phải có ít nhất 1 khoảng trắng.";
        else if (!regexName.test(value)) message = "* Không chứa số/ký tự lạ.";
    } 
    else if (fieldId === 'SDTNhan') {
        if (!regexPhone.test(value)) message = "* SĐT phải là 10 số (Bắt đầu bằng 0).";
    } 
    else if (fieldId === 'DiaChiChiTiet') {
        if (!value.includes(' ')) message = "* Phải có ít nhất 1 khoảng trắng.";
        else if (!regexAddress.test(value)) message = "* Không chứa ký tự lạ (ngoại trừ / và -).";
    }

    // Hiển thị kết quả[cite: 1]
    if (message !== "") {
        errorSpan.innerText = message;
        input.classList.add('invalid');
        return false;
    } else {
        errorSpan.innerText = "";
        input.classList.remove('invalid');
        return true;
    }
}

// Cập nhật lại hàm validateForm khi nhấn Submit
function validateForm() {
    let option = document.querySelector('input[name="diachi_option"]:checked').value;
    if (option === 'moi') {
        const isTenValid = validateField('TenNguoiNhan');
        const isSdtValid = validateField('SDTNhan');
        const isDcValid = validateField('DiaChiChiTiet');

        if (!isTenValid || !isSdtValid || !isDcValid) {
            alert('Vui lòng sửa các lỗi nhập liệu trước khi đặt hàng!');
            return false;
        }
    }
    return true;
}
</script>

</body>
</html>