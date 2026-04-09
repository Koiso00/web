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
    $tongTien = (float)($_POST['TongTien'] ?? 0);
    $payment = $_POST['payment'] ?? '';
    $diachi_option = $_POST['diachi_option'] ?? '';

    $diaChiGiaoHang = "";
    $phuongXaGiao = "";

    $is_success = false;
    $ds_sanpham = [];

    // Lấy MaTK của user bằng prepared statement
    $stmtUser = mysqli_prepare($conn, "SELECT MaTK, HoTen, Email FROM taikhoan WHERE Email = ? OR Username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtUser, "ss", $user_session, $user_session);
    mysqli_stmt_execute($stmtUser);
    $res_user = mysqli_stmt_get_result($stmtUser);
    $row_user = $res_user ? mysqli_fetch_assoc($res_user) : null;
    mysqli_stmt_close($stmtUser);

    if (!$row_user) {
        header("location:index.php");
        exit();
    }
    $maTK = (int)$row_user['MaTK'];

    // Dùng transaction để: tạo đơn + tạo chi tiết + trừ tồn kho (atomic)
    mysqli_begin_transaction($conn);
    try {
        // Xử lý Địa chỉ
        if ($diachi_option === 'cu' && isset($_POST['diachi_cu'])) {
            $maDC = (int)$_POST['diachi_cu'];
            $stmtDC = mysqli_prepare($conn, "SELECT TenNguoiNhan, SDTNhan, DiaChiChiTiet, PhuongXa, QuanHuyen, TinhThanh FROM diachikhachhang WHERE MaDC = ? AND MaTK = ? LIMIT 1");
            mysqli_stmt_bind_param($stmtDC, "ii", $maDC, $maTK);
            mysqli_stmt_execute($stmtDC);
            $res_dc = mysqli_stmt_get_result($stmtDC);
            $row_dc = $res_dc ? mysqli_fetch_assoc($res_dc) : null;
            mysqli_stmt_close($stmtDC);

            if (!$row_dc) {
                throw new Exception("Không tìm thấy địa chỉ giao hàng hợp lệ.");
            }

            $diaChiGiaoHang = $row_dc['TenNguoiNhan'] . " - " . $row_dc['SDTNhan'] . " | " . $row_dc['DiaChiChiTiet'] . ", " . $row_dc['PhuongXa'] . ", " . $row_dc['QuanHuyen'] . ", " . $row_dc['TinhThanh'];
            $phuongXaGiao = $row_dc['PhuongXa'];
        } else {
            $tenNguoiNhan = trim($_POST['TenNguoiNhan'] ?? '');
            $sdtNhan = trim($_POST['SDTNhan'] ?? '');
            $diaChiChiTiet = trim($_POST['DiaChiChiTiet'] ?? '');
            $phuongXa = trim($_POST['PhuongXa'] ?? '');
            $quanHuyen = trim($_POST['QuanHuyen'] ?? '');
            $tinhThanh = trim($_POST['TinhThanh'] ?? '');

            if ($tenNguoiNhan === '' || $sdtNhan === '' || $diaChiChiTiet === '' || $phuongXa === '' || $quanHuyen === '' || $tinhThanh === '') {
                throw new Exception("Thiếu thông tin địa chỉ giao hàng.");
            }

            $stmtInsertDC = mysqli_prepare($conn, "INSERT INTO diachikhachhang (MaTK, TenNguoiNhan, SDTNhan, DiaChiChiTiet, PhuongXa, QuanHuyen, TinhThanh) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmtInsertDC, "issssss", $maTK, $tenNguoiNhan, $sdtNhan, $diaChiChiTiet, $phuongXa, $quanHuyen, $tinhThanh);
            if (!mysqli_stmt_execute($stmtInsertDC)) {
                mysqli_stmt_close($stmtInsertDC);
                throw new Exception("Không thể lưu địa chỉ mới.");
            }
            mysqli_stmt_close($stmtInsertDC);

            $diaChiGiaoHang = "$tenNguoiNhan - $sdtNhan | $diaChiChiTiet, $phuongXa, $quanHuyen, $tinhThanh";
            $phuongXaGiao = $phuongXa;
        }

        // Lưu Đơn Hàng
        $trangThai = 0;
        $stmtOrder = mysqli_prepare($conn, "INSERT INTO donhang (MaTK, TongTien, PhuongThucThanhToan, TrangThai, DiaChiGiaoHang, PhuongXaGiao) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtOrder, "idsiss", $maTK, $tongTien, $payment, $trangThai, $diaChiGiaoHang, $phuongXaGiao);
        if (!mysqli_stmt_execute($stmtOrder)) {
            mysqli_stmt_close($stmtOrder);
            throw new Exception("Không thể tạo đơn hàng.");
        }
        mysqli_stmt_close($stmtOrder);

        $maDH = mysqli_insert_id($conn);

        // Chuẩn bị statement chi tiết đơn hàng
        $stmtSP = mysqli_prepare($conn, "SELECT TenSP, GiaNhapBinhQuan, TiLeLoiNhuan FROM sanpham WHERE MaSP = ? LIMIT 1");
        $stmtDetail = mysqli_prepare($conn, "INSERT INTO chitietdonhang (MaDH, MaSP, SoLuongMua, GiaBan) VALUES (?, ?, ?, ?)");
        // Trừ tồn kho: chỉ trừ khi đủ hàng, tránh âm kho
        $stmtTruKho = mysqli_prepare($conn, "UPDATE sanpham SET SoLuongTon = SoLuongTon - ? WHERE MaSP = ? AND SoLuongTon >= ?");

        foreach ($_SESSION['giohang'] as $id_sp => $so_luong) {
            $id_sp = (int)$id_sp;
            $so_luong = (int)$so_luong;
            if ($id_sp <= 0 || $so_luong <= 0) {
                throw new Exception("Dữ liệu giỏ hàng không hợp lệ.");
            }

            // Kiểm tra và trừ kho
            mysqli_stmt_bind_param($stmtTruKho, "iii", $so_luong, $id_sp, $so_luong);
            if (!mysqli_stmt_execute($stmtTruKho) || mysqli_stmt_affected_rows($stmtTruKho) !== 1) {
                throw new Exception("Sản phẩm đã hết hàng hoặc không đủ số lượng trong kho.");
            }

            // Lấy thông tin SP để lưu vào chi tiết đơn
            mysqli_stmt_bind_param($stmtSP, "i", $id_sp);
            mysqli_stmt_execute($stmtSP);
            $res_sp = mysqli_stmt_get_result($stmtSP);
            $row_sp = $res_sp ? mysqli_fetch_assoc($res_sp) : null;
            if (!$row_sp) {
                throw new Exception("Không tìm thấy sản phẩm trong hệ thống.");
            }

            $giaBan = (float)$row_sp['GiaNhapBinhQuan'] * (1 + (float)$row_sp['TiLeLoiNhuan']);
            $tenSP = $row_sp['TenSP'];

            mysqli_stmt_bind_param($stmtDetail, "iiid", $maDH, $id_sp, $so_luong, $giaBan);
            if (!mysqli_stmt_execute($stmtDetail)) {
                throw new Exception("Không thể lưu chi tiết đơn hàng.");
            }

            $ds_sanpham[] = [
                'ten' => $tenSP,
                'sl' => $so_luong,
                'gia' => $giaBan,
                'thanhtien' => $giaBan * $so_luong
            ];
        }

        mysqli_stmt_close($stmtSP);
        mysqli_stmt_close($stmtDetail);
        mysqli_stmt_close($stmtTruKho);

        mysqli_commit($conn);

        unset($_SESSION['giohang']);
        $is_success = true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $is_success = false;
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