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

// Lấy ngày cần tra cứu (nếu có)
$selected_date = isset($_GET['date_view']) ? $_GET['date_view'] : '';

if ($selected_date !== '') {
    // TÍNH TỒN KHO TRONG QUÁ KHỨ (Tính ngược từ hiện tại)
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
    $raw_inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $inventory = [];
    foreach ($raw_inventory as $row) {
        // Công thức: Tồn quá khứ = Tồn hiện tại - Nhập (sau đó) + Xuất (sau đó)
        $tonKhoLichSu = $row['TonHienTai'] - $row['NhapSauNgay'] + $row['XuatSauNgay'];
        
        $inventory[] = [
            'MaSP' => $row['MaSP'],
            'TenSP' => $row['TenSP'],
            'TenLoai' => $row['TenLoai'],
            'SoLuongTon' => $tonKhoLichSu 
        ];
    }
    // Sắp xếp mảng theo số lượng tồn tăng dần
    usort($inventory, function($a, $b) {
        return $a['SoLuongTon'] <=> $b['SoLuongTon'];
    });

} else {
    // TỒN KHO HIỆN TẠI (Mặc định)
    $sql = "SELECT s.MaSP, s.TenSP, s.SoLuongTon, l.TenLoai FROM SanPham s JOIN LoaiSanPham l ON s.MaLoai = l.MaLoai ORDER BY s.SoLuongTon ASC";
    $inventory = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// Lọc các sản phẩm dưới ngưỡng báo động
$lowStock = array_filter($inventory, function ($i) use ($threshold) {
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
                <h1 class="page-header-title">
                    <?= $selected_date !== '' ? 'Tồn kho tính đến cuối ngày: ' . date('d/m/Y', strtotime($selected_date)) : 'Quản lí số lượng tồn hiện tại' ?>
                </h1>
            </div>

            <nav class="category-nav">
                <a href="quanlitonkho.php" class="category-link active">Tồn kho hiện tại</a>
                <a href="bao-cao-nhap-xuat-ton.php" class="category-link">Báo cáo Nhập-Xuất-Tồn</a>
            </nav>

            <form method="GET" action="">
                <div class="inventory-filter-bar">
                    <div class="date-range-filter">
                        <label style="font-weight:bold;">Tra cứu tồn kho tại ngày:</label>
                        <input type="date" name="date_view" value="<?= htmlspecialchars($selected_date) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-edit">🔍 Tra cứu</button>
                    
                    <?php if ($selected_date !== ''): ?>
                        <a href="quanlitonkho.php" class="btn btn-hide" style="text-decoration:none; padding:10px;">← Về hiện tại</a>
                    <?php endif; ?>
                </div>
            </form>

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
                    <?php if(count($inventory) > 0): ?>
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
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center">Chưa có dữ liệu sản phẩm.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>

</html>