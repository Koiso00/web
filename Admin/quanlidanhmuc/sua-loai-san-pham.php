<?php
session_start();
require_once '../config.php';

// 1. Kiểm tra quyền Admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// 2. Lấy ID loại cần sửa từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 3. Truy vấn dữ liệu hiện tại của loại sản phẩm này
$stmt = $conn->prepare("SELECT * FROM LoaiSanPham WHERE MaLoai = ?");
$stmt->execute([$id]);
$loai = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy loại sản phẩm, quay về trang danh sách
if (!$loai) {
    echo "<script>alert('Loại sản phẩm không tồn tại!'); window.location.href='sanpham.php';</script>";
    exit();
}

// 4. Xử lý khi người dùng nhấn nút "Lưu thay đổi"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenMoi = trim($_POST['ten-loai']);
    $hinhAnhMoi = $loai['HinhAnh']; // Mặc định giữ lại ảnh cũ

    // Xử lý upload ảnh mới nếu có
    if (isset($_FILES['hinh-anh']) && $_FILES['hinh-anh']['error'] == 0) {
        $ext = pathinfo($_FILES['hinh-anh']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array(strtolower($ext), $allowed)) {
            $hinhAnhMoi = time() . '_' . $_FILES['hinh-anh']['name'];
            move_uploaded_file($_FILES['hinh-anh']['tmp_name'], '../Image/' . $hinhAnhMoi);
        }
    }

    if (!empty($tenMoi)) {
        try {
            $update = $conn->prepare("UPDATE LoaiSanPham SET TenLoai = ?, HinhAnh = ? WHERE MaLoai = ?");
            $update->execute([$tenMoi, $hinhAnhMoi, $id]);
            echo "<script>alert('Cập nhật thành công!'); window.location.href='sanpham.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Lỗi: Tên loại này đã tồn tại!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa Loại Sản Phẩm</title>
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="form-container">
                <h1>Chỉnh sửa loại: <?= htmlspecialchars($loai['TenLoai']) ?></h1>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Tên loại sản phẩm</label>
                        <input type="text" name="ten-loai" value="<?= htmlspecialchars($loai['TenLoai']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Hình ảnh hiện tại</label><br>
                        <img src="../Image/<?= htmlspecialchars($loai['HinhAnh'] ?? 'product0.png') ?>" width="100" style="margin-bottom: 10px; border-radius: 5px;">
                        <br>
                        <label>Thay đổi ảnh mới (Để trống nếu giữ nguyên)</label>
                        <input type="file" name="hinh-anh" accept="image/*">
                    </div>

                    <div class="form-actions">
                        <a href="sanpham.php" class="btn btn-deleteback">← Quay lại</a>
                        <button type="submit" class="btn btn-save">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>