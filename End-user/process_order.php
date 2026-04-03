<?php
session_start();
include 'connect.php';

// Bảo vệ trang
if (!isset($_SESSION['user']) || empty($_SESSION['giohang'])) {
    header("location:index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_session = $_SESSION['user'];
    $sql_user = "SELECT MaTK, HoTen, Email FROM taikhoan WHERE Email='$user_session' OR Username='$user_session' LIMIT 1";
    $res_user = mysqli_query($conn, $sql_user);
    $row_user = mysqli_fetch_assoc($res_user);
    $maTK = $row_user['MaTK'];

    $tongTien = $_POST['TongTien'];
    $payment = $_POST['payment'];
    $diachi_option = $_POST['diachi_option'];

    $diaChiGiaoHang = "";
    $phuongXaGiao = "";

    // Xử lý Địa chỉ
    if ($diachi_option == 'cu' && isset($_POST['diachi_cu'])) {
        $maDC = $_POST['diachi_cu'];
        $sql_get_dc = "SELECT * FROM diachikhachhang WHERE MaDC='$maDC'";
        $res_dc = mysqli_query($conn, $sql_get_dc);
        $row_dc = mysqli_fetch_assoc($res_dc);
        
        $diaChiGiaoHang = $row_dc['TenNguoiNhan'] . " - " . $row_dc['SDTNhan'] . " | " . $row_dc['DiaChiChiTiet'] . ", " . $row_dc['PhuongXa'] . ", " . $row_dc['QuanHuyen'] . ", " . $row_dc['TinhThanh'];
        $phuongXaGiao = $row_dc['PhuongXa'];
    } else {
        $tenNguoiNhan = $_POST['TenNguoiNhan'];
        $sdtNhan = $_POST['SDTNhan'];
        $diaChiChiTiet = $_POST['DiaChiChiTiet'];
        $phuongXa = $_POST['PhuongXa'];
        $quanHuyen = $_POST['QuanHuyen'];
        $tinhThanh = $_POST['TinhThanh'];

        // Lưu địa chỉ mới vào Database
        $sql_insert_dc = "INSERT INTO diachikhachhang (MaTK, TenNguoiNhan, SDTNhan, DiaChiChiTiet, PhuongXa, QuanHuyen, TinhThanh) 
                          VALUES ('$maTK', '$tenNguoiNhan', '$sdtNhan', '$diaChiChiTiet', '$phuongXa', '$quanHuyen', '$tinhThanh')";
        mysqli_query($conn, $sql_insert_dc);

        $diaChiGiaoHang = "$tenNguoiNhan - $sdtNhan | $diaChiChiTiet, $phuongXa, $quanHuyen, $tinhThanh";
        $phuongXaGiao = $phuongXa;
    }

    // Lưu Đơn Hàng
    $sql_order = "INSERT INTO donhang (MaTK, TongTien, PhuongThucThanhToan, TrangThai, DiaChiGiaoHang, PhuongXaGiao) 
                  VALUES ('$maTK', '$tongTien', '$payment', 0, '$diaChiGiaoHang', '$phuongXaGiao')";
    
    $is_success = false;
    $ds_sanpham = [];

    if (mysqli_query($conn, $sql_order)) {
        $maDH = mysqli_insert_id($conn); // Lấy ID đơn hàng vừa tạo

        // Lưu Chi Tiết Đơn Hàng
        foreach ($_SESSION['giohang'] as $id_sp => $so_luong) {
            $sql_sp = "SELECT TenSP, GiaNhapBinhQuan, TiLeLoiNhuan FROM sanpham WHERE MaSP='$id_sp'";
            $res_sp = mysqli_query($conn, $sql_sp);
            $row_sp = mysqli_fetch_assoc($res_sp);
            
            $giaBan = $row_sp['GiaNhapBinhQuan'] * (1 + $row_sp['TiLeLoiNhuan']);
            $tenSP = $row_sp['TenSP'];

            $sql_detail = "INSERT INTO chitietdonhang (MaDH, MaSP, SoLuongMua, GiaBan) 
                           VALUES ('$maDH', '$id_sp', '$so_luong', '$giaBan')";
            mysqli_query($conn, $sql_detail);
            
            // Lưu mảng để in ra hóa đơn tóm tắt
            $ds_sanpham[] = [
                'ten' => $tenSP,
                'sl' => $so_luong,
                'gia' => $giaBan,
                'thanhtien' => $giaBan * $so_luong
            ];
        }

        // Xóa giỏ hàng sau khi đặt thành công
        unset($_SESSION['giohang']);
        $is_success = true;
    }
} else {
    header("location:index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công</title>
    <link rel="stylesheet" href="thanhtoan.css">
    <style>
        .summary-box { max-width: 800px; margin: 10rem auto; background: #fff; padding: 3rem; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center; font-family: sans-serif;}
        .summary-box h1 { color: #28a745; font-size: 2.5rem; margin-bottom: 1rem; }
        .summary-details { text-align: left; margin-top: 2rem; border-top: 1px solid #ddd; padding-top: 2rem; }
        .summary-details p { font-size: 1.6rem; margin-bottom: 1rem; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-top: 2rem; font-size: 1.5rem; }
        table th, table td { border: 1px solid #ddd; padding: 1.2rem; text-align: left; }
        table th { background-color: #f8f9fa; }
        .total-row { font-weight: bold; color: #d9534f; font-size: 1.8rem; }
        .btn-home { display: inline-block; margin-top: 3rem; padding: 1.2rem 2.5rem; background: #0f75ff; color: white; text-decoration: none; border-radius: 5px; font-size: 1.6rem; }
        .btn-home:hover { background: #084ea8; }
    </style>
</head>
<body style="background-color: #f9f9f9;">

    <div class="summary-box">
        <?php if($is_success) { ?>
            <h1>🎉 Đặt hàng thành công!</h1>
            <p style="font-size: 1.6rem; color: #666;">Cảm ơn bạn đã mua sắm tại TechZone. Đơn hàng của bạn đang được xử lý.</p>
            
            <div class="summary-details">
                <p><b>Mã đơn hàng:</b> #DH<?php echo sprintf('%05d', $maDH); ?></p>
                <p><b>Người nhận:</b> <?php echo $user_session; ?></p>
                <p><b>Địa chỉ giao hàng:</b> <?php echo $diaChiGiaoHang; ?></p>
                <p><b>Phương thức thanh toán:</b> <?php echo $payment; ?></p>

                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ds_sanpham as $sp) { ?>
                        <tr>
                            <td><?php echo $sp['ten']; ?></td>
                            <td><?php echo number_format($sp['gia'], 0, ",", "."); ?> ₫</td>
                            <td><?php echo $sp['sl']; ?></td>
                            <td><?php echo number_format($sp['thanhtien'], 0, ",", "."); ?> ₫</td>
                        </tr>
                        <?php } ?>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right;">Tổng cộng:</td>
                            <td><?php echo number_format($tongTien, 0, ",", "."); ?> ₫</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <a href="index.php" class="btn-home">Tiếp tục mua sắm</a>
            
        <?php } else { ?>
            <h1 style="color: red;">❌ Lỗi hệ thống</h1>
            <p style="font-size: 1.6rem;">Đã có lỗi xảy ra trong quá trình xử lý đơn hàng. Vui lòng thử lại sau.</p>
            <a href="index.php" class="btn-home">Về trang chủ</a>
        <?php } ?>
    </div>

</body>
</html>