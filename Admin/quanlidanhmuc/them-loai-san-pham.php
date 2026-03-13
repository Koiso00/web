<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenLoai = trim($_POST['ten-loai']);
    $hinhAnh = "folder-icon.png"; // Mặc định

    if (isset($_FILES['hinh-anh']) && $_FILES['hinh-anh']['error'] == 0) {
        $ext = pathinfo($_FILES['hinh-anh']['name'], PATHINFO_EXTENSION);
        $hinhAnh = time() . '_' . $_FILES['hinh-anh']['name'];
        move_uploaded_file($_FILES['hinh-anh']['tmp_name'], '../Image/' . $hinhAnh);
    }

    if (!empty($tenLoai)) {
        try {
            $stmt = $conn->prepare("INSERT INTO LoaiSanPham (TenLoai, HinhAnh) VALUES (?, ?)");
            $stmt->execute([$tenLoai, $hinhAnh]);
            echo "<script>alert('Thêm loại thành công!'); window.location.href='sanpham.php';</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Lỗi: Tên loại đã tồn tại hoặc lỗi CSDL!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Loại Mới</title>
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>
        <main class="main-content">
            <div class="form-container">
                <h1>Thêm loại sản phẩm mới</h1>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Tên loại sản phẩm</label>
                        <input type="text" name="ten-loai" required placeholder="Nhập tên loại...">
                    </div>
                    <div class="form-group">
                        <label>Hình ảnh đại diện loại</label>
                        <input type="file" name="hinh-anh" accept="image/*">
                    </div>
                    <div class="form-actions">
                        <a href="sanpham.php" class="btn btn-deleteback">← Quay lại</a>
                        <button type="submit" class="btn btn-save">Lưu loại sản phẩm</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>