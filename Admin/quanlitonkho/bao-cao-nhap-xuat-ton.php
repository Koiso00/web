<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// Mặc định xem báo cáo trong tháng hiện tại
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');

// Câu lệnh SQL lấy Tổng Nhập (Phiếu đã Hoàn thành) và Tổng Xuất (Đơn đã Giao)
$sql = "SELECT s.MaSP, s.TenSP, s.SoLuongTon as TonCuoiKy,
        (SELECT COALESCE(SUM(ctpn.SoLuongNhap), 0) 
         FROM ChiTietPhieuNhap ctpn 
         JOIN PhieuNhap pn ON ctpn.MaPN = pn.MaPN 
         WHERE ctpn.MaSP = s.MaSP AND pn.TrangThai = 1 AND DATE(pn.NgayNhap) BETWEEN ? AND ?) as TongNhap,
        
        (SELECT COALESCE(SUM(ctdh.SoLuongMua), 0) 
         FROM ChiTietDonHang ctdh 
         JOIN DonHang dh ON ctdh.MaDH = dh.MaDH 
         WHERE ctdh.MaSP = s.MaSP AND dh.TrangThai = 2 AND DATE(dh.NgayDat) BETWEEN ? AND ?) as TongXuat
        
        FROM SanPham s 
        ORDER BY s.TenSP ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([$dateFrom, $dateTo, $dateFrom, $dateTo]);
$report = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Báo cáo Nhập-Xuất-Tồn</title>
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Thống kê Nhập - Xuất - Tồn</h1>
            </div>

            <nav class="category-nav">
                <a href="quanlitonkho.php" class="category-link">Tồn kho hiện tại</a>
                <a href="bao-cao-nhap-xuat-ton.php" class="category-link active">Báo cáo Nhập-Xuất-Tồn</a>
            </nav>

            <form method="GET" action="">
                <div class="inventory-filter-bar">
                    <div class="date-range-filter">
                        <label>Từ ngày:</label>
                        <input type="date" name="date_from" value="<?= $dateFrom ?>">
                        <label>Đến ngày:</label>
                        <input type="date" name="date_to" value="<?= $dateTo ?>">
                    </div>
                    <button type="submit" class="btn btn-edit">Xem báo cáo</button>
                </div>
            </form>

            <table class="price-lookup-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Mã SP</th>
                        <th style="text-align: right;">Tổng nhập (trong kỳ)</th>
                        <th style="text-align: right;">Tổng xuất (trong kỳ)</th>
                        <th style="text-align: right;">Tồn cuối thực tế</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['TenSP']) ?></td>
                            <td>SP<?= str_pad($row['MaSP'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td class="text-right" style="color: green; font-weight: bold;">+ <?= $row['TongNhap'] ?></td>
                            <td class="text-right" style="color: red; font-weight: bold;">- <?= $row['TongXuat'] ?></td>
                            <td class="text-right strong-price"><?= $row['TonCuoiKy'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>

</html>