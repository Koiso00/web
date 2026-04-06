<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$stmtLoai = $conn->query("SELECT * FROM LoaiSanPham ORDER BY MaLoai ASC");
$loaiSanPhams = $stmtLoai->fetchAll(PDO::FETCH_ASSOC);

$maLoaiActive = isset($_GET['maloai']) ? (int)$_GET['maloai'] : ($loaiSanPhams[0]['MaLoai'] ?? 0);

// Chế độ xem: 'binh_quan' (mặc định) hoặc 'lo_hang'
$viewMode = isset($_GET['view']) && $_GET['view'] === 'lo_hang' ? 'lo_hang' : 'binh_quan';

// --- Lấy dữ liệu theo chế độ ---
if ($viewMode === 'lo_hang') {
    // Tra cứu theo từng lô hàng đã nhập (từng phiếu nhập)
    $sql = "SELECT s.MaSP, s.TenSP, s.TiLeLoiNhuan,
                   pn.MaPN, pn.NgayNhap,
                   ct.SoLuongNhap, ct.GiaNhap,
                   ct.GiaNhap * (1 + s.TiLeLoiNhuan) AS GiaBanLo
            FROM SanPham s
            JOIN ChiTietPhieuNhap ct ON s.MaSP = ct.MaSP
            JOIN PhieuNhap pn ON ct.MaPN = pn.MaPN
            WHERE s.MaLoai = ? AND pn.TrangThai = 1
            ORDER BY s.MaSP ASC, pn.NgayNhap DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$maLoaiActive]);
    $loHangs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nhóm theo sản phẩm
    $groupedData = [];
    foreach ($loHangs as $row) {
        $groupedData[$row['MaSP']]['TenSP'] = $row['TenSP'];
        $groupedData[$row['MaSP']]['TiLeLoiNhuan'] = $row['TiLeLoiNhuan'];
        $groupedData[$row['MaSP']]['loHang'][] = $row;
    }
} else {
    // Tra cứu theo giá bình quân (mặc định)
    $stmtSP = $conn->prepare("SELECT * FROM SanPham WHERE MaLoai = ? ORDER BY MaSP DESC");
    $stmtSP->execute([$maLoaiActive]);
    $sanPhams = $stmtSP->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra Cứu Giá Bán</title>
    <link rel="stylesheet" href="../Style.css">
    <style>
        .view-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .view-toggle a {
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid #ddd;
            color: #333;
            background: #f5f5f5;
        }
        .view-toggle a.active {
            background: #1a73e8;
            color: #fff;
            border-color: #1a73e8;
        }
        .lo-hang-group { margin-bottom: 24px; }
        .lo-hang-title {
            font-weight: 700;
            font-size: 15px;
            padding: 10px 14px;
            background: #f0f7ff;
            border-left: 4px solid #1a73e8;
            border-radius: 4px;
            margin-bottom: 6px;
        }
        .lo-hang-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .lo-hang-table th, .lo-hang-table td {
            padding: 8px 12px; border: 1px solid #eee; font-size: 13px;
        }
        .lo-hang-table th { background: #fafafa; font-weight: 600; }
        .no-data { text-align:center; padding:20px; color:#888; font-size:14px; }
    </style>
</head>
<body>
<div class="container">
    <?php include_once '../sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-header-title">Tra cứu giá bán sản phẩm</h1>
        </div>

        <!-- Tab danh mục -->
        <nav class="category-nav">
            <?php foreach ($loaiSanPhams as $loai): ?>
                <a href="tra-cuu-gia-ban.php?maloai=<?= $loai['MaLoai'] ?>&view=<?= $viewMode ?>"
                   class="category-link <?= ($loai['MaLoai'] == $maLoaiActive) ? 'active' : '' ?>">
                    <?= htmlspecialchars($loai['TenLoai']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Toggle chế độ xem -->
        <div class="view-toggle">
            <a href="tra-cuu-gia-ban.php?maloai=<?= $maLoaiActive ?>&view=binh_quan"
               class="<?= $viewMode === 'binh_quan' ? 'active' : '' ?>">
                📊 Giá bình quân
            </a>
            <a href="tra-cuu-gia-ban.php?maloai=<?= $maLoaiActive ?>&view=lo_hang"
               class="<?= $viewMode === 'lo_hang' ? 'active' : '' ?>">
                📦 Theo lô hàng nhập
            </a>
        </div>

        <?php if ($viewMode === 'binh_quan'): ?>
            <!-- CHẾ ĐỘ: GIÁ BÌNH QUÂN -->
            <table class="price-lookup-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="text-align:right">Giá vốn (Bình quân)</th>
                        <th style="text-align:right">Tỉ lệ lợi nhuận</th>
                        <th style="text-align:right">Giá bán (dự kiến)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sanPhams)): ?>
                        <?php foreach ($sanPhams as $sp):
                            $giaVon = $sp['GiaNhapBinhQuan'];
                            $tiLe   = $sp['TiLeLoiNhuan'];
                            $giaBan = $giaVon * (1 + $tiLe);
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($sp['TenSP']) ?></td>
                                <td class="text-right"><?= number_format($giaVon, 0, ',', '.') ?>đ</td>
                                <td class="text-right"><?= number_format($tiLe * 100, 1) ?>%</td>
                                <td class="text-right" style="color:#dc3545; font-weight:bold;">
                                    <?= number_format($giaBan, 0, ',', '.') ?>đ
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="no-data">Chưa có sản phẩm trong danh mục này.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php else: ?>
            <!-- CHẾ ĐỘ: THEO LÔ HÀNG -->
            <?php if (!empty($groupedData)): ?>
                <?php foreach ($groupedData as $maSP => $data): ?>
                    <div class="lo-hang-group">
                        <div class="lo-hang-title">
                            <?= htmlspecialchars($data['TenSP']) ?>
                            <span style="font-weight:400; color:#666; font-size:13px; margin-left:10px;">
                                (Tỉ lệ lợi nhuận: <?= number_format($data['TiLeLoiNhuan'] * 100, 1) ?>%)
                            </span>
                        </div>
                        <table class="lo-hang-table">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Ngày nhập</th>
                                    <th style="text-align:right">Số lượng nhập</th>
                                    <th style="text-align:right">Giá vốn lô</th>
                                    <th style="text-align:right">Giá bán theo lô</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['loHang'] as $lo): ?>
                                    <tr>
                                        <td>PN<?= str_pad($lo['MaPN'], 3, '0', STR_PAD_LEFT) ?></td>
                                        <td><?= date('d/m/Y', strtotime($lo['NgayNhap'])) ?></td>
                                        <td style="text-align:right"><?= $lo['SoLuongNhap'] ?></td>
                                        <td style="text-align:right"><?= number_format($lo['GiaNhap'], 0, ',', '.') ?>đ</td>
                                        <td style="text-align:right; color:#dc3545; font-weight:bold;">
                                            <?= number_format($lo['GiaBanLo'], 0, ',', '.') ?>đ
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">Chưa có lô hàng nào được nhập cho danh mục này.</p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="form-actions" style="margin-top:20px;">
            <a href="quanligiaban.php?maloai=<?= $maLoaiActive ?>" class="btn btn-deleteback">← Quay lại cài đặt</a>
        </div>
    </main>
</div>
</body>
</html>
