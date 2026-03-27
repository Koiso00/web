<?php
session_start();
include 'connect.php';

// Kiểm tra đăng nhập (Giả sử session lưu MaTK khi login)
if (!isset($_SESSION['MaTK'])) {
    echo "<script>alert('Vui lòng đăng nhập để thanh toán!'); window.location.href='dangnhap.php';</script>";
    exit;
}

$maTK = $_SESSION['MaTK'];

// Lấy danh sách địa chỉ đã có của khách hàng
$sql_diachi = "SELECT * FROM diachikhachhang WHERE MaTK = $maTK";
$result_diachi = mysqli_query($conn, $sql_diachi);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán - TechZone</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-box { max-width: 800px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .section-title { color: #0f75ff; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        .hidden { display: none; }
        .bank-info { background: #f0f8ff; padding: 15px; border-left: 4px solid #0f75ff; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="checkout-box">
        <h1>Xác nhận thanh toán</h1>
        <form action="xulydathang.php" method="POST">
            
            <h2 class="section-title">1. Địa chỉ giao hàng</h2>
            <?php if (mysqli_num_rows($result_diachi) > 0) {
                while ($dc = mysqli_fetch_assoc($result_diachi)) { ?>
                    <div class="form-group">
                        <input type="radio" name="chon_diachi" value="<?php echo $dc['MaDC']; ?>" class="radio-diachi" checked>
                        <label>
                            <b><?php echo $dc['TenNguoiNhan']; ?> (<?php echo $dc['SDTNhan']; ?>)</b> - 
                            <?php echo $dc['DiaChiChiTiet'] . ", " . $dc['PhuongXa'] . ", " . $dc['QuanHuyen'] . ", " . $dc['TinhThanh']; ?>
                        </label>
                    </div>
            <?php } } ?>
            
            <div class="form-group">
                <input type="radio" name="chon_diachi" value="new" class="radio-diachi" <?php echo (mysqli_num_rows($result_diachi) == 0) ? 'checked' : ''; ?>>
                <label><b>+ Thêm địa chỉ giao hàng mới</b></label>
            </div>

            <div id="form_diachimoi" class="<?php echo (mysqli_num_rows($result_diachi) > 0) ? 'hidden' : ''; ?>">
                <div class="form-group"><input type="text" name="ten_nhan" placeholder="Tên người nhận" class="form-control"></div>
                <div class="form-group"><input type="text" name="sdt_nhan" placeholder="Số điện thoại" class="form-control"></div>
                <div class="form-group"><input type="text" name="tinh_thanh" placeholder="Tỉnh/Thành phố" class="form-control"></div>
                <div class="form-group"><input type="text" name="quan_huyen" placeholder="Quận/Huyện" class="form-control"></div>
                <div class="form-group"><input type="text" name="phuong_xa" placeholder="Phường/Xã" class="form-control"></div>
                <div class="form-group"><input type="text" name="diachi_chitiet" placeholder="Số nhà, Tên đường..." class="form-control"></div>
            </div>

            <h2 class="section-title">2. Phương thức thanh toán</h2>
            <div class="form-group">
                <select name="phuongthuc_tt" id="phuongthuc_tt" style="width:100%; padding: 10px;">
                    <option value="Tiền mặt">Thanh toán khi nhận hàng (COD)</option>
                    <option value="Chuyển khoản">Chuyển khoản ngân hàng</option>
                    <option value="Trực tuyến">Thanh toán trực tuyến (VNPay / Momo)</option>
                </select>
            </div>
            
            <div id="thongtin_chuyenkhoan" class="hidden bank-info">
                <b>Thông tin chuyển khoản:</b><br>
                Ngân hàng: Vietcombank<br>
                Số tài khoản: <b>123456789</b><br>
                Chủ tài khoản: TECHZONE VIETNAM<br>
                Nội dung CK: <i>Thanh toan don hang - [Số điện thoại của bạn]</i>
            </div>

            <button type="submit" style="background:#0f75ff; color:white; padding:15px; width:100%; border:none; font-size:18px; margin-top:20px; cursor:pointer;">
                Hoàn tất đặt hàng
            </button>
        </form>
    </div>

    <script>
        // JS Ẩn/hiện form nhập địa chỉ mới
        const radiosDiachi = document.querySelectorAll('.radio-diachi');
        const formDiachiMoi = document.getElementById('form_diachimoi');
        radiosDiachi.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'new') formDiachiMoi.classList.remove('hidden');
                else formDiachiMoi.classList.add('hidden');
            });
        });

        // JS Ẩn/hiện thông tin chuyển khoản
        const selectThanhToan = document.getElementById('phuongthuc_tt');
        const infoChuyenKhoan = document.getElementById('thongtin_chuyenkhoan');
        selectThanhToan.addEventListener('change', function() {
            if(this.value === 'Chuyển khoản') infoChuyenKhoan.classList.remove('hidden');
            else infoChuyenKhoan.classList.add('hidden');
        });
    </script>
</body>
</html>