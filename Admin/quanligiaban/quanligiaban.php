<?php
session_start();
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// ------------------------------------------------------------------------
// XỬ LÝ LƯU TỈ LỆ LỢI NHUẬN (Lưu nhiều sản phẩm cùng lúc)
// ------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_update_profit'])) {
    if (isset($_POST['ti_le']) && is_array($_POST['ti_le'])) {
        $stmtUpdate = $conn->prepare("UPDATE SanPham SET TiLeLoiNhuan = ? WHERE MaSP = ?");
        foreach ($_POST['ti_le'] as $maSP => $tiLePhanTram) {
            // Đổi từ % sang số thập phân để lưu (VD: nhập 20 -> lưu 0.2)
            $tiLeThapPhan = $tiLePhanTram / 100;
            $stmtUpdate->execute([$tiLeThapPhan, $maSP]);
        }
        $thongBao = "Cập nhật tỉ lệ lợi nhuận thành công!";
        echo "<script>alert('$thongBao');</script>";
    }
}

// Lấy danh sách loại sản phẩm để làm Menu Tabs
$stmtLoai = $conn->query("SELECT * FROM LoaiSanPham");
$loaiSanPhams = $stmtLoai->fetchAll(PDO::FETCH_ASSOC);

// Lấy mã loại đang chọn (mặc định là loại đầu tiên nếu không có trên URL)
$maLoaiActive = isset($_GET['maloai']) ? (int)$_GET['maloai'] : ($loaiSanPhams[0]['MaLoai'] ?? 0);

// Lấy danh sách sản phẩm thuộc loại đang chọn
$stmtSP = $conn->prepare("SELECT * FROM SanPham WHERE MaLoai = ? ORDER BY MaSP DESC");
$stmtSP->execute([$maLoaiActive]);
$sanPhams = $stmtSP->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Giá Bán</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/Do_an_Web/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Quản lí tỉ lệ lợi nhuận</h1>
            </div>

            <nav class="category-nav">
                <?php foreach ($loaiSanPhams as $loai): ?>
                    <a href="quanligiaban.php?maloai=<?= $loai['MaLoai'] ?>"
                        class="category-link <?= ($loai['MaLoai'] == $maLoaiActive) ? 'active' : '' ?>">
                        <?= htmlspecialchars($loai['TenLoai']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="page-toolbar">
                <a href="tra-cuu-gia-ban.php?maloai=<?= $maLoaiActive ?>" class="btn btn-edit">📊 Tra cứu giá bán</a>
            </div>

            <form method="POST" action="">
                <div class="product-list">
                    <?php if (count($sanPhams) > 0): ?>
                        <?php foreach ($sanPhams as $sp): ?>
                            <div class="product-item">
                                <img src="../Image/<?= htmlspecialchars($sp['HinhAnh']) ?>" alt="Hình ảnh" class="product-image">
                                <div class="product-details">
                                    <h3><?= htmlspecialchars($sp['TenSP']) ?></h3>
                                    <p>Mã SP: SP<?= str_pad($sp['MaSP'], 3, '0', STR_PAD_LEFT) ?></p>
                                </div>
                                <div class="product-profit-input">
                                    <label>Tỉ lệ lợi nhuận:</label>
                                    <input type="number" name="ti_le[<?= $sp['MaSP'] ?>]" step="0.1" value="<?= $sp['TiLeLoiNhuan'] * 100 ?>" required>
                                    <span>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 20px;">Chưa có sản phẩm nào trong danh mục này.</p>
                    <?php endif; ?>

                    <?php if (count($sanPhams) > 0): ?>
                        <div class="form-actions">
                            <button type="submit" name="btn_update_profit" class="btn btn-save">Lưu tình trạng tất cả</button>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </main>
    </div>
</body>

</html>