<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<script>alert('Không tìm thấy đơn hàng!'); window.location.href='quan-li-don-hang.php';</script>";
    exit();
}

// 1. Lấy thông tin chung của đơn hàng
$sqlDonHang = "SELECT d.*, t.HoTen, t.SoDienThoai 
               FROM DonHang d 
               JOIN TaiKhoan t ON d.MaTK = t.MaTK 
               WHERE d.MaDH = ?";
$stmt = $conn->prepare($sqlDonHang);
$stmt->execute([$id]);
$donHang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donHang) {
    echo "<script>alert('Đơn hàng không tồn tại!'); window.location.href='quan-li-don-hang.php';</script>";
    exit();
}

// 2. Lấy danh sách sản phẩm trong đơn hàng đó
$sqlChiTiet = "SELECT c.*, s.TenSP 
               FROM ChiTietDonHang c 
               JOIN SanPham s ON c.MaSP = s.MaSP 
               WHERE c.MaDH = ?";
$stmtCT = $conn->prepare($sqlChiTiet);
$stmtCT->execute([$id]);
$chiTiet = $stmtCT->fetchAll(PDO::FETCH_ASSOC);

// Cấu hình hiển thị trạng thái
$statusText = '';
$statusClass = '';
switch ($donHang['TrangThai']) {
    case 0: $statusText = 'Mới đặt'; $statusClass = 'status-pending'; break;
    case 1: $statusText = 'Đã xác nhận'; $statusClass = 'status-confirmed'; break;
    case 2: $statusText = 'Đã giao thành công'; $statusClass = 'status-completed'; break;
    case 3: $statusText = 'Đã hủy'; $statusClass = 'status-cancelled'; break;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style.css">
</head>
<body>
    <div class="container">
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/Do_an_Web/sidebar.php'; ?>

        <main class="main-content">
            <div class="form-container">
                <h1>Chi tiết đơn hàng: DH<?= str_pad($donHang['MaDH'], 3, '0', STR_PAD_LEFT) ?></h1>
                
                <div class="order-customer-info">
                    <p><strong>Khách hàng:</strong> <?= htmlspecialchars($donHang['HoTen']) ?></p>
                    <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($donHang['SoDienThoai']) ?></p>
                    <p><strong>Địa chỉ giao:</strong> <?= htmlspecialchars($donHang['DiaChiGiaoHang']) ?></p>
                    <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i:s', strtotime($donHang['NgayDat'])) ?></p>
                    <p><strong>Thanh toán:</strong> <?= htmlspecialchars($donHang['PhuongThucThanhToan']) ?></p>
                    <p><strong>Trạng thái:</strong> <span class="status-dot <?= $statusClass ?>"></span> <strong><?= $statusText ?></strong></p>
                </div>

                <h2 class="form-section-title">Các sản phẩm trong đơn</h2>
                
                <table class="price-lookup-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Mã SP</th>
                            <th style="text-align: right;">Số lượng</th>
                            <th style="text-align: right;">Đơn giá</th>
                            <th style="text-align: right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $tongCong = 0;
                        foreach ($chiTiet as $item): 
                            $thanhTien = $item['SoLuongMua'] * $item['GiaBan'];
                            $tongCong += $thanhTien;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['TenSP']) ?></td>
                            <td>SP<?= str_pad($item['MaSP'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td style="text-align: right;"><?= $item['SoLuongMua'] ?></td>
                            <td style="text-align: right;"><?= number_format($item['GiaBan'], 0, ',', '.') ?>đ</td>
                            <td style="text-align: right; font-weight: bold;"><?= number_format($thanhTien, 0, ',', '.') ?>đ</td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <tr style="background-color: #f8f9fa;">
                            <td colspan="4" style="text-align: right; font-weight: bold; font-size: 1.1rem; color: #dc3545;">TỔNG CỘNG ĐƠN HÀNG</td>
                            <td style="text-align: right; font-weight: bold; font-size: 1.1rem; color: #dc3545;"><?= number_format($tongCong, 0, ',', '.') ?>đ</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="form-actions" style="margin-top: 30px;">
                    <a href="quan-li-don-hang.php" class="btn btn-deleteback">← Quay lại danh sách</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>