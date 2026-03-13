<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// Lấy danh sách loại sản phẩm
$stmt = $conn->query("SELECT * FROM LoaiSanPham ORDER BY MaLoai DESC");
$danhSachLoai = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Loại Sản phẩm</title>
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Danh sách loại sản phẩm</h1>
            </div>

            <div class="page-toolbar">
                <a href="them-loai-san-pham.php" class="btn btn-add">➕ Thêm loại mới</a>
            </div>

            <div class="product-list">
                <?php foreach ($danhSachLoai as $loai): ?>
                    <div class="product-item">
                        <img src="../Image/<?= htmlspecialchars($loai['HinhAnh'] ?? 'product0.png') ?>" alt="Icon" class="product-image">

                        <div class="product-details">
                            <h3><?= htmlspecialchars($loai['TenLoai']) ?></h3>
                            <p>Mã Loại: <?= $loai['MaLoai'] ?></p>
                        </div>
                        <div class="product-actions">
                            <a href="sua-loai-san-pham.php?id=<?= $loai['MaLoai'] ?>" class="btn btn-edit" style="text-decoration: none;">Sửa</a>
                            <a href="xoa-loai.php?id=<?= $loai['MaLoai'] ?>" class="btn btn-delete" onclick="return confirm('Xoá loại này sẽ ảnh hưởng đến sản phẩm bên trong. Chắc chắn?')">Xoá</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>

</html>