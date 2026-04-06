<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// Thiết lập ngưỡng cảnh báo (mặc định 10, lưu vào session để giữ nguyên khi chuyển tab)
if (isset($_GET['threshold'])) {
    $_SESSION['tonkho_threshold'] = max(1, (int)$_GET['threshold']);
}
$threshold = isset($_SESSION['tonkho_threshold']) ? $_SESSION['tonkho_threshold'] : 10;

// Lấy ngày cần tra cứu (nếu có)
$selected_date = isset($_GET['date_view']) ? $_GET['date_view'] : '';

if ($selected_date !== '') {
    // TÍNH TỒN KHO TẠI THỜI ĐIỂM QUÁ KHỨ
    $sql = "SELECT s.MaSP, s.TenSP, s.SoLuongTon as TonHienTai, l.TenLoai,
            (SELECT COALESCE(SUM(ctpn.SoLuongNhap), 0) 
             FROM ChiTietPhieuNhap ctpn 
             JOIN PhieuNhap pn ON ctpn.MaPN = pn.MaPN 
             WHERE ctpn.MaSP = s.MaSP AND pn.TrangThai = 1 AND DATE(pn.NgayNhap) > ?) AS NhapSauNgay,
            (SELECT COALESCE(SUM(ctdh.SoLuongMua), 0) 
             FROM ChiTietDonHang ctdh 
             JOIN DonHang dh ON ctdh.MaDH = dh.MaDH 
             WHERE ctdh.MaSP = s.MaSP AND dh.TrangThai = 2 AND DATE(dh.NgayDat) > ?) AS XuatSauNgay
            FROM SanPham s 
            JOIN LoaiSanPham l ON s.MaLoai = l.MaLoai";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$selected_date, $selected_date]);
    $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $inventory = [];
    foreach ($raw as $row) {
        $tonKhoLichSu = $row['TonHienTai'] - $row['NhapSauNgay'] + $row['XuatSauNgay'];
        $inventory[] = [
            'MaSP'      => $row['MaSP'],
            'TenSP'     => $row['TenSP'],
            'TenLoai'   => $row['TenLoai'],
            'SoLuongTon'=> $tonKhoLichSu
        ];
    }
    usort($inventory, fn($a, $b) => $a['SoLuongTon'] <=> $b['SoLuongTon']);
} else {
    $sql = "SELECT s.MaSP, s.TenSP, s.SoLuongTon, l.TenLoai 
            FROM SanPham s JOIN LoaiSanPham l ON s.MaLoai = l.MaLoai 
            ORDER BY s.SoLuongTon ASC";
    $inventory = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

$lowStock = array_filter($inventory, fn($i) => $i['SoLuongTon'] < $threshold);
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
            <h1 class="page-header-title">
                <?= $selected_date !== '' 
                    ? 'Tồn kho tính đến cuối ngày: ' . date('d/m/Y', strtotime($selected_date)) 
                    : 'Quản lí số lượng tồn hiện tại' ?>
            </h1>
        </div>

        <nav class="category-nav">
            <a href="quanlitonkho.php" class="category-link active">Tồn kho hiện tại</a>
            <a href="bao-cao-nhap-xuat-ton.php" class="category-link">Báo cáo Nhập-Xuất-Tồn</a>
        </nav>

        <!-- Thanh filter: tra cứu ngày + ngưỡng cảnh báo -->
        <form method="GET" action="" onsubmit="return validateFilter()">
            <div class="inventory-filter-bar">
                <div class="date-range-filter">
                    <label><strong>Tra cứu tại ngày:</strong></label>
                    <input type="date" name="date_view"
                           value="<?= htmlspecialchars($selected_date) ?>"
                           max="<?= date('Y-m-d') ?>">
                </div>

                <div class="date-range-filter">
                    <label><strong>Ngưỡng cảnh báo hết hàng:</strong></label>
                    <input type="number" name="threshold" id="threshold-input"
                           min="1" value="<?= $threshold ?>"
                           style="width:80px; padding:8px; border:1px solid #ddd; border-radius:6px;">
                    <span style="font-size:13px; color:#666;">sản phẩm</span>
                </div>

                <button type="submit" class="btn btn-edit">🔍 Áp dụng</button>

                <?php if ($selected_date !== ''): ?>
                    <a href="quanlitonkho.php?threshold=<?= $threshold ?>"
                       class="btn btn-hide" style="text-decoration:none; padding:10px;">
                        ← Về hiện tại
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (count($lowStock) > 0): ?>
            <div class="warning-box">
                <h4>⚠️ Cảnh báo sắp hết hàng (Dưới <?= $threshold ?> sản phẩm)</h4>
                <p>Cần nhập thêm: <strong>
                    <?= implode(', ', array_map(
                        fn($i) => htmlspecialchars($i['TenSP']) . ' (' . $i['SoLuongTon'] . ')',
                        $lowStock
                    )) ?>
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
                <?php if (count($inventory) > 0): ?>
                    <?php foreach ($inventory as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['TenSP']) ?></td>
                            <td>SP<?= str_pad($item['MaSP'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($item['TenLoai']) ?></td>
                            <td class="text-right <?= $item['SoLuongTon'] < $threshold ? 'status-low-stock' : '' ?>"
                                style="font-weight:bold;">
                                <?= $item['SoLuongTon'] ?>
                                <?php if ($item['SoLuongTon'] < $threshold): ?>
                                    <span style="color:red; font-size:12px;"> ⚠️</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:20px;">Chưa có dữ liệu.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>

<script>
function validateFilter() {
    const threshold = parseInt(document.getElementById('threshold-input').value);
    if (isNaN(threshold) || threshold < 1) {
        alert('Ngưỡng cảnh báo phải là số lớn hơn 0!');
        return false;
    }
    return true;
}
</script>
</body>
</html>
