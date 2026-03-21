<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy dữ liệu sản phẩm hiện tại để hiện lên form
$stmt = $conn->prepare("SELECT * FROM SanPham WHERE MaSP = ?");
$stmt->execute([$id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sp) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='quanlidanhmuc.php';</script>";
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

    $hinhAnhMoi = $sp['HinhAnh']; // Giữ ảnh cũ nếu không chọn ảnh mới

    if (isset($_FILES['product-image']) && $_FILES['product-image']['error'] == 0) {
        $ext = pathinfo($_FILES['product-image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $hinhAnhMoi = 'sp_' . $id . '.' . strtolower($ext);
            move_uploaded_file($_FILES['product-image']['tmp_name'], '../Image/' . $hinhAnhMoi);
        }
    }

    $sql = "UPDATE SanPham SET TenSP=?, MaLoai=?, MoTa=?, DonViTinh=?, HinhAnh=?, TiLeLoiNhuan=?, HienTrang=? WHERE MaSP=?";
    $stmtUpdate = $conn->prepare($sql);
    $stmtUpdate->execute([$tenSP, $maLoai, $moTa, $donViTinh, $hinhAnhMoi, $tiLeLoiNhuan, $hienTrang, $id]);

    echo "<script>alert('Cập nhật thành công!'); window.location.href='quanlidanhmuc.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chỉnh Sửa Sản Phẩm</title>
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="form-container">
                <h1>Chỉnh sửa sản phẩm: <?= htmlspecialchars($sp['TenSP']) ?></h1>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Loại sản phẩm</label>
                        <select name="product-category" required>
                            <?php foreach ($loaiSanPhams as $loai): ?>
                                <option value="<?= $loai['MaLoai'] ?>" <?= ($loai['MaLoai'] == $sp['MaLoai']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loai['TenLoai']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tên sản phẩm</label>
                        <input type="text" name="product-name" value="<?= htmlspecialchars($sp['TenSP']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Đơn vị tính</label>
                        <input type="text" name="product-unit" value="<?= htmlspecialchars($sp['DonViTinh']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tỉ lệ lợi nhuận (%)</label>
                        <input type="number" step="0.1" name="product-margin" value="<?= $sp['TiLeLoiNhuan'] * 100 ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Hiện trạng</label>
                        <select name="product-status">
                            <option value="1" <?= $sp['HienTrang'] == 1 ? 'selected' : '' ?>>Hiển thị (Đang bán)</option>
                            <option value="0" <?= $sp['HienTrang'] == 0 ? 'selected' : '' ?>>Ẩn (Chưa bán)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Hình ảnh (Để trống nếu không đổi)</label><br>
                        <img src="../Image/<?= htmlspecialchars($sp['HinhAnh']) ?>" width="100" style="margin-bottom:10px;">
                        <input type="file" name="product-image" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="product-description" rows="4"><?= htmlspecialchars($sp['MoTa']) ?></textarea>
                    </div>
                    <div class="form-actions">
                        <a href="quanlidanhmuc.php" class="btn btn-deleteback">← Quay lại</a>
                        <button type="submit" class="btn btn-save">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>