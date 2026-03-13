<?php
session_start();
require_once 'config.php';

// Kiểm tra bảo mật: Phải đăng nhập admin mới được vào
if (!isset($_SESSION['admin'])) {
    // Tạm thời comment lại để bạn test giao diện nếu chưa làm trang login
    // header("Location: login.php"); 
    // exit();
}

// Lấy danh sách loại sản phẩm
$stmtLoai = $conn->prepare("SELECT * FROM LoaiSanPham");
$stmtLoai->execute();
$loaiSanPhams = $stmtLoai->fetchAll(PDO::FETCH_ASSOC);

// Lấy mã loại từ URL, mặc định loại đầu tiên
$maLoaiActive = isset($_GET['maloai']) ? (int)$_GET['maloai'] : ($loaiSanPhams[0]['MaLoai'] ?? 0);

// Phân trang
$limit = 8;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số sản phẩm
$stmtCount = $conn->prepare("SELECT COUNT(*) FROM SanPham WHERE MaLoai = ?");
$stmtCount->execute([$maLoaiActive]);
$totalProducts = $stmtCount->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Lấy danh sách sản phẩm
$stmtSP = $conn->prepare("SELECT * FROM SanPham WHERE MaLoai = ? ORDER BY MaSP DESC LIMIT ? OFFSET ?");
$stmtSP->bindValue(1, $maLoaiActive, PDO::PARAM_INT);
$stmtSP->bindValue(2, $limit, PDO::PARAM_INT);
$stmtSP->bindValue(3, $offset, PDO::PARAM_INT);
$stmtSP->execute();
$sanPhams = $stmtSP->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h1 class="logo">TechZone</h1>
            <a href="../Trang-chu.php">📈 Trang Chủ</a>
            <a href="../sanpham.php">📦 Quản lý loại sản phẩm</a>
            <a href="../taikhoan.php">👥 Quản lý tài khoản</a>
            <a href="quanlidanhmuc.php" class="active">🎁 Quản lí danh mục</a>
            <a href="../quanlinhaphang/quan-li-nhap-hang.php">📥 Quản lí nhập hàng</a>
            <a href="../quanligiaban/quanligiaban.php">💲 Quản lí giá bán</a>
            <a href="../quanlidonhang/quan-li-don-hang.php">🚚 Quản lí đơn hàng</a>
            <a href="../quanlitonkho/quan-li-ton-kho.php">📊 Quản lí tồn kho</a>
        </nav>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Quản lí danh mục sản phẩm</h1>
            </div>

            <nav class="category-nav">
                <?php foreach($loaiSanPhams as $loai): ?>
                    <a href="quanlidanhmuc.php?maloai=<?= $loai['MaLoai'] ?>" 
                       class="category-link <?= ($loai['MaLoai'] == $maLoaiActive) ? 'active' : '' ?>">
                        <?= htmlspecialchars($loai['TenLoai']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="page-toolbar">
                <a href="them-san-pham.php" class="btn btn-add">➕ Thêm sản phẩm</a>
            </div>

            <div class="product-list">
                <?php if(count($sanPhams) > 0): ?>
                    <?php foreach($sanPhams as $sp): ?>
                        <div class="product-item <?= $sp['HienTrang'] == 0 ? 'hidden-item' : '' ?>">
                            <img src="../Image/<?= htmlspecialchars($sp['HinhAnh']) ?>" alt="Hình ảnh" class="product-image">
                            <div class="product-details">
                                <h3><?= htmlspecialchars($sp['TenSP']) ?></h3>
                                <p>Mã SP: <?= $sp['MaSP'] ?></p>
                                <p>Tồn kho: <?= $sp['SoLuongTon'] ?> <?= htmlspecialchars($sp['DonViTinh']) ?></p>
                                <p>Trạng thái: <?= $sp['HienTrang'] == 1 ? '<span style="color:green; font-weight:bold;">Đang bán</span>' : '<span style="color:red; font-weight:bold;">Đang ẩn</span>' ?></p>
                            </div>
                            <div class="product-actions">
                                <a href="sua-san-pham.php?id=<?= $sp['MaSP'] ?>" class="btn btn-edit">Sửa</a>
                                <a href="action_sanpham.php?action=delete&id=<?= $sp['MaSP'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa/ẩn sản phẩm này?');">Xoá</a>
                                <?php if($sp['HienTrang'] == 1): ?>
                                    <a href="action_sanpham.php?action=hide&id=<?= $sp['MaSP'] ?>" class="btn btn-hide">Ẩn</a>
                                <?php else: ?>
                                    <a href="action_sanpham.php?action=show&id=<?= $sp['MaSP'] ?>" class="btn btn-hide" style="background-color: #28a745; color: white;">Hiện</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="padding: 20px;">Chưa có sản phẩm nào trong danh mục này.</p>
                <?php endif; ?>
            </div>

            <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="quanlidanhmuc.php?maloai=<?= $maLoaiActive ?>&page=<?= $i ?>" 
                       class="page-number <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>