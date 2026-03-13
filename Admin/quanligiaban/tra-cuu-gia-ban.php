<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// Lấy danh sách loại sản phẩm
$stmtLoai = $conn->query("SELECT * FROM LoaiSanPham");
$loaiSanPhams = $stmtLoai->fetchAll(PDO::FETCH_ASSOC);

$maLoaiActive = isset($_GET['maloai']) ? (int)$_GET['maloai'] : ($loaiSanPhams[0]['MaLoai'] ?? 0);

// Lấy danh sách sản phẩm theo loại đang chọn
$stmtSP = $conn->prepare("SELECT * FROM SanPham WHERE MaLoai = ? ORDER BY MaSP DESC");
$stmtSP->execute([$maLoaiActive]);
$sanPhams = $stmtSP->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra Cứu Giá Bán</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style.css">
</head>
<body>
    <div class="container">
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/Do_an_Web/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Tra cứu giá bán sản phẩm</h1>
            </div>
            
            <nav class="category-nav">
                <?php foreach($loaiSanPhams as $loai): ?>
                    <a href="tra-cuu-gia-ban.php?maloai=<?= $loai['MaLoai'] ?>" 
                       class="category-link <?= ($loai['MaLoai'] == $maLoaiActive) ? 'active' : '' ?>">
                        <?= htmlspecialchars($loai['TenLoai']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <table class="price-lookup-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="text-align: right;">Giá vốn (Bình quân)</th>
                        <th style="text-align: right;">Tỉ lệ lợi nhuận</th>
                        <th style="text-align: right;">Giá bán (dự kiến)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($sanPhams) > 0): ?>
                        <?php foreach($sanPhams as $sp): 
                            // Công thức tính giá bán theo yêu cầu đề bài
                            $giaVon = $sp['GiaNhapBinhQuan'];
                            $tiLe = $sp['TiLeLoiNhuan'];
                            $giaBan = $giaVon * (1 + $tiLe);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($sp['TenSP']) ?></td>
                            <td class="text-right"><?= number_format($giaVon, 0, ',', '.') ?>đ</td>
                            <td class="text-right"><?= $tiLe * 100 ?>%</td>
                            <td class="text-right strong-price" style="color: #dc3545; font-weight: bold;">
                                <?= number_format($giaBan, 0, ',', '.') ?>đ
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">Chưa có sản phẩm.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="form-actions" style="margin-top: 20px;">
                <a href="quanligiaban.php?maloai=<?= $maLoaiActive ?>" class="btn btn-deleteback">← Quay lại trang cài đặt</a>
            </div>
        </main>
    </div>
</body>
</html>