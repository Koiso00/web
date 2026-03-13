<?php
session_start();
// Gọi file kết nối từ thư mục gốc
require_once '../config.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// Thiết lập ngưỡng cảnh báo (mặc định 10)
$threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 10;

// Lấy dữ liệu tồn kho thực tế từ Database
$sql = "SELECT s.*, l.TenLoai FROM SanPham s JOIN LoaiSanPham l ON s.MaLoai = l.MaLoai ORDER BY s.SoLuongTon ASC";
$inventory = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Lọc các sản phẩm dưới ngưỡng báo động
$lowStock = array_filter($inventory, function($i) use ($threshold) { 
    return $i['SoLuongTon'] < $threshold; 
});
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tồn Kho</title>
    <link rel="stylesheet" href="../Style.css">
</head>
<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Quản lí số lượng tồn hiện tại</h1>
            </div>
            
            <nav class="category-nav">
                <a href="quan-li-ton-kho.php" class="category-link active">Tồn kho hiện tại</a>
                <a href="bao-cao-nhap-xuat-ton.php" class="category-link">Báo cáo Nhập-Xuất-Tồn</a>
            </nav>

            <?php if (count($lowStock) > 0): ?>
            <div class="warning-box">
                <h4>⚠️ Cảnh báo sắp hết hàng (Dưới <?= $threshold ?> sản phẩm)</h4>
                <p>Cần nhập thêm: <strong>
                    <?php 
                        $names = array_map(fn($i) => $i['TenSP'] . " (" . $i['SoLuongTon'] . ")", $lowStock);
                        echo implode(', ', $names); 
                    ?>
                </strong></p>
            </div>
            <?php endif; ?>

            <table class="price-lookup-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Mã SP</th>
                        <th>Danh mục</th>
                        <th style="text-align:right">Số lượng tồn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['TenSP']) ?></td>
                        <td>SP<?= str_pad($item['MaSP'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($item['TenLoai']) ?></td>
                        <td class="text-right <?= $item['SoLuongTon'] < $threshold ? 'status-low-stock' : '' ?>" style="font-weight:bold;">
                            <?= $item['SoLuongTon'] ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>