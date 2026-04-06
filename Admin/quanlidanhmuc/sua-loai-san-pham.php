<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM LoaiSanPham WHERE MaLoai = ?");
$stmt->execute([$id]);
$loai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$loai) {
    echo "<script>alert('Loại sản phẩm không tồn tại!'); window.location.href='sanpham.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenMoi = trim($_POST['ten-loai']);
    $hinhAnhMoi = $loai['HinhAnh'];

    if (empty($tenMoi)) {
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
        $hinhAnhMoi = preg_replace('/\s+/', '-', $_FILES['hinh-anh']['name']);
        move_uploaded_file($_FILES['hinh-anh']['tmp_name'], '../Image/' . $hinhAnhMoi);
    }

    try {
        $update = $conn->prepare("UPDATE LoaiSanPham SET TenLoai = ?, HinhAnh = ? WHERE MaLoai = ?");
        $update->execute([$tenMoi, $hinhAnhMoi, $id]);
        echo "<script>alert('Cập nhật thành công!'); window.location.href='sanpham.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Lỗi: Tên loại này đã tồn tại!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Loại Sản Phẩm</title>
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
            <h1>Chỉnh sửa loại: <?= htmlspecialchars($loai['TenLoai']) ?></h1>

            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>Tên loại sản phẩm <span class="required">*</span></label>
                    <input type="text" name="ten-loai" id="ten-loai"
                           value="<?= htmlspecialchars($loai['TenLoai']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Hình ảnh hiện tại</label><br>
                    <img src="../Image/<?= htmlspecialchars($loai['HinhAnh'] ?? 'product0.png') ?>"
                         width="100" style="margin-bottom:10px; border-radius:5px;" id="current-img">
                    <br>
                    <label>Thay đổi ảnh mới (Để trống nếu giữ nguyên)</label>
                    <input type="file" name="hinh-anh" accept="image/*" onchange="previewImage(this)">
                    <p class="form-hint">Định dạng: jpg, png, gif, webp. Tối đa 2MB.</p>
                    <img id="img-preview" class="preview-img" src="#" alt="Preview">
                </div>

                <div class="form-actions">
                    <a href="sanpham.php" class="btn btn-deleteback">← Quay lại</a>
                    <button type="submit" class="btn btn-save">💾 Lưu thay đổi</button>
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
            document.getElementById('current-img').style.display = 'none';
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
