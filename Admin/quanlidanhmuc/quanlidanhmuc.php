<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// 1. Lấy danh sách loại sản phẩm để làm Tab điều hướng
$stmtLoai = $conn->query("SELECT * FROM LoaiSanPham ORDER BY MaLoai ASC");
$loaiSanPhams = $stmtLoai->fetchAll(PDO::FETCH_ASSOC);

// 2. Xác định Loại đang chọn (Mặc định là loại đầu tiên)
$maLoaiActive = isset($_GET['maloai']) ? (int)$_GET['maloai'] : (count($loaiSanPhams) > 0 ? $loaiSanPhams[0]['MaLoai'] : 0);

// 3. Lấy danh sách sản phẩm theo loại
$sanPhams = [];
if ($maLoaiActive > 0) {
    $stmtSP = $conn->prepare("SELECT * FROM SanPham WHERE MaLoai = ? ORDER BY MaSP DESC");
    $stmtSP->execute([$maLoaiActive]);
    $sanPhams = $stmtSP->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục</title>
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Quản lý mặt hàng</h1>
            </div>

            <nav class="category-nav">
                <?php foreach ($loaiSanPhams as $loai): ?>
                    <a href="quanlidanhmuc.php?maloai=<?= $loai['MaLoai'] ?>"
                        class="category-link <?= ($loai['MaLoai'] == $maLoaiActive) ? 'active' : '' ?>">
                        <?= htmlspecialchars($loai['TenLoai']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="page-toolbar">
                <a href="them-san-pham.php" class="btn btn-add">➕ Thêm sản phẩm mới</a>
            </div>

            <div class="product-list">
                <?php if (!empty($sanPhams)): ?>
                    <?php foreach ($sanPhams as $sp): ?>
                        <div class="product-item">
                            <img src="../Image/<?= htmlspecialchars($sp['HinhAnh'] ?? 'product0.png') ?>" alt="Hình" class="product-image">

                            <div class="product-details">
                                <h3><?= htmlspecialchars($sp['TenSP']) ?></h3>
                                <p>Mã SP: SP<?= str_pad($sp['MaSP'], 4, '0', STR_PAD_LEFT) ?></p>
                                <p>Giá vốn: <strong><?= number_format($sp['GiaNhapBinhQuan'], 0, ',', '.') ?> đ</strong></p>
                                <p>Tồn kho: <?= $sp['SoLuongTon'] ?></p>
                            </div>

                            <div class="product-actions" style="display: flex; gap: 10px;">
                                <a href="sua-san-pham.php?id=<?= $sp['MaSP'] ?>" class="btn btn-edit" style="text-decoration: none;">Sửa</a>
                                <a href="action_sanpham.php?action=delete&id=<?= $sp['MaSP'] ?>"
                                    class="btn btn-delete"
                                    style="text-decoration: none;"
                                    onclick="return confirm('Chắc chắn xóa sản phẩm này?');">Xóa</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center;">Danh mục này chưa có sản phẩm nào.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>