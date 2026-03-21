<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$stmtLoai = $conn->query("SELECT * FROM LoaiSanPham");
$loaiSanPhams = $stmtLoai->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenSP = $_POST['product-name'];
    $maLoai = $_POST['product-category'];
    $moTa = $_POST['product-description'];
    $donViTinh = $_POST['product-unit'];
    $tiLeLoiNhuan = $_POST['product-margin'] / 100;
    $hienTrang = $_POST['product-status'];

    if (!isset($_FILES['product-image']) || $_FILES['product-image']['error'] != 0) {
        echo "<script>alert('Vui lòng chọn ảnh!'); window.history.back();</script>";
        exit();
    }
    if ($_FILES['product-image']['size'] > 2097152) {
        echo "<script>alert('File ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.'); window.history.back();</script>";
        exit();
    }
    $ext = pathinfo($_FILES['product-image']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array(strtolower($ext), $allowed)) {
        echo "<script>alert('Chỉ cho phép upload ảnh (jpg, png, gif, webp)!'); window.history.back();</script>";
        exit();
    }

    // 1. INSERT trước với ảnh tạm
    $sql = "INSERT INTO SanPham (TenSP, MaLoai, MoTa, DonViTinh, HinhAnh, TiLeLoiNhuan, HienTrang) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tenSP, $maLoai, $moTa, $donViTinh, 'product0.png', $tiLeLoiNhuan, $hienTrang]);

    // 2. Lấy MaSP vừa tạo
    $maSP = $conn->lastInsertId();

    // 3. Đặt tên file theo MaSP và upload
    $hinhAnh = 'sp_' . $maSP . '.' . strtolower($ext);
    move_uploaded_file($_FILES['product-image']['tmp_name'], '../Image/' . $hinhAnh);

    // 4. Cập nhật lại tên ảnh
    $conn->prepare("UPDATE SanPham SET HinhAnh = ? WHERE MaSP = ?")->execute([$hinhAnh, $maSP]);

    echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href='quanlidanhmuc.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Sản Phẩm Mới</title>
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="form-container">
                <h1>Thêm sản phẩm mới</h1>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="product-category">Loại sản phẩm</label>
                        <select id="product-category" name="product-category" required>
                            <?php foreach ($loaiSanPhams as $loai): ?>
                                <option value="<?= $loai['MaLoai'] ?>"><?= htmlspecialchars($loai['TenLoai']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="product-name">Tên sản phẩm</label>
                        <input type="text" id="product-name" name="product-name" required>
                    </div>
                    <div class="form-group">
                        <label for="product-unit">Đơn vị tính</label>
                        <input type="text" id="product-unit" name="product-unit" placeholder="Cái, hộp..." required>
                    </div>
                    <div class="form-group">
                        <label for="product-margin">Tỉ lệ lợi nhuận (%)</label>
                        <input type="number" step="0.1" id="product-margin" name="product-margin" required>
                    </div>
                    <div class="form-group">
                        <label for="product-status">Hiện trạng</label>
                        <select id="product-status" name="product-status">
                            <option value="1">Hiển thị (Đang bán)</option>
                            <option value="0">Ẩn (Chưa bán)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="product-image">Hình ảnh sản phẩm</label>
                        <input type="file" id="product-image" name="product-image" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label for="product-description">Mô tả</label>
                        <textarea id="product-description" name="product-description" rows="4" required placeholder="Nhập mô tả chi tiết cho sản phẩm..."></textarea>
                    </div>
                    <div class="form-actions">
                        <a href="quanlidanhmuc.php" class="btn btn-deleteback">← Quay lại danh sách</a>
                        <button type="submit" class="btn btn-save">Lưu sản phẩm</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>