<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenLoai = trim($_POST['ten-loai']);
    $hinhAnh = "folder-icon.png";

    if (empty($tenLoai)) {
        echo "<script>alert('Vui lòng nhập tên loại sản phẩm!'); window.history.back();</script>";
        exit();
    }

    if (isset($_FILES['hinh-anh']) && $_FILES['hinh-anh']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['hinh-anh']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            echo "<script>alert('Chỉ cho phép upload ảnh (jpg, png, gif, webp)!'); window.history.back();</script>";
            exit();
        }
        if ($_FILES['hinh-anh']['size'] > 2097152) {
            echo "<script>alert('File ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.'); window.history.back();</script>";
            exit();
        }
        $hinhAnh = preg_replace('/\s+/', '-', $_FILES['hinh-anh']['name']);
        move_uploaded_file($_FILES['hinh-anh']['tmp_name'], '../Image/' . $hinhAnh);
    }

    try {
        $stmt = $conn->prepare("INSERT INTO LoaiSanPham (TenLoai, HinhAnh) VALUES (?, ?)");
        $stmt->execute([$tenLoai, $hinhAnh]);
        echo "<script>alert('Thêm loại thành công!'); window.location.href='sanpham.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Lỗi: Tên loại đã tồn tại hoặc lỗi CSDL!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Loại Mới</title>
    <link rel="stylesheet" href="../Style.css">
    <style>
        .required { color: red; }
        .form-hint { font-size: 12px; color: #888; margin-top: 4px; }
        .preview-img {
            display: none; width: 100px; height: 100px;
            object-fit: cover; border-radius: 8px;
            border: 2px dashed #ccc; margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php include_once '../sidebar.php'; ?>
    <main class="main-content">
        <div class="form-container">
            <h1>Thêm loại sản phẩm mới</h1>
            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>Tên loại sản phẩm <span class="required">*</span></label>
                    <input type="text" name="ten-loai" id="ten-loai"
                           required placeholder="Nhập tên loại...">
                </div>
                <div class="form-group">
                    <label>Hình ảnh đại diện</label>
                    <input type="file" name="hinh-anh" accept="image/*"
                           onchange="previewImage(this)">
                    <p class="form-hint">Định dạng: jpg, png, gif, webp. Tối đa 2MB. Để trống nếu không có ảnh.</p>
                    <img id="img-preview" class="preview-img" src="#" alt="Preview">
                </div>
                <div class="form-actions">
                    <a href="sanpham.php" class="btn btn-deleteback">← Quay lại</a>
                    <button type="submit" class="btn btn-save">💾 Lưu loại sản phẩm</button>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
function previewImage(input) {
    const preview = document.getElementById('img-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function validateForm() {
    const ten = document.getElementById('ten-loai').value.trim();
    if (!ten) {
        alert('Vui lòng nhập tên loại sản phẩm!');
        return false;
    }
    if (ten.length < 2) {
        alert('Tên loại phải có ít nhất 2 ký tự!');
        return false;
    }
    const fileInput = document.querySelector('input[name="hinh-anh"]');
    if (fileInput.files.length > 0) {
        const size = fileInput.files[0].size;
        const name = fileInput.files[0].name.toLowerCase();
        const allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        const ext = name.split('.').pop();
        if (!allowed.includes(ext)) {
            alert('Chỉ cho phép upload ảnh (jpg, png, gif, webp)!');
            return false;
        }
        if (size > 2097152) {
            alert('File ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.');
            return false;
        }
    }
    return true;
}
</script>
</body>
</html>
