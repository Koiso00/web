<?php
session_start();
require_once 'config.php'; // Ở thư mục gốc nên không dùng ../

// Bảo mật: Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin'])) { 
    header("Location: Trang-dang-nhap.php"); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenLoai = trim($_POST['product-name']);
    
    // Kiểm tra tên loại đã tồn tại chưa
    $check = $conn->prepare("SELECT COUNT(*) FROM LoaiSanPham WHERE TenLoai = ?");
    $check->execute([$tenLoai]);
    
    if($check->fetchColumn() > 0) {
        echo "<script>alert('Tên loại này đã tồn tại!');</script>";
    } else {
        // Lưu vào bảng LoaiSanPham
        $stmt = $conn->prepare("INSERT INTO LoaiSanPham (TenLoai) VALUES (?)");
        $stmt->execute([$tenLoai]);
        echo "<script>alert('Thêm loại thành công!'); window.location.href='sanpham.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Loại Sản Phẩm Mới</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>
    <div class="container">
        <?php include_once 'sidebar.php'; ?>
        <main class="main-content">
            <div class="form-container">
                <h1>Thêm loại sản phẩm mới</h1>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="product-name">Tên loại sản phẩm</label>
                        <input type="text" id="product-name" name="product-name" placeholder="VD: Bàn phím, Chuột..." required>
                    </div>
                    <div class="form-actions">
                        <a href="sanpham.php" class="btn btn-deleteback">← Quay lại</a>
                        <button type="submit" class="btn btn-save">Lưu lại</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>