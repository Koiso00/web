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
    $tenSP       = trim($_POST['product-name']);
    $maLoai      = (int)$_POST['product-category'];
    $moTa        = trim($_POST['product-description']);
    $donViTinh   = trim($_POST['product-unit']);
    $tiLeLoiNhuan = (float)$_POST['product-margin'] / 100;
    $hienTrang   = (int)$_POST['product-status'];
    $soLuongBanDau = (int)$_POST['product-quantity'];

    // Validate phía server
    if (empty($tenSP) || empty($donViTinh)) {
        echo "<script>alert('Vui lòng điền đầy đủ thông tin bắt buộc!'); window.history.back();</script>";
        exit();
    }
    if ($soLuongBanDau < 0) {
        echo "<script>alert('Số lượng ban đầu không được âm!'); window.history.back();</script>";
        exit();
    }
    if ($tiLeLoiNhuan < 0) {
        echo "<script>alert('Tỉ lệ lợi nhuận không được âm!'); window.history.back();</script>";
        exit();
    }
    if (!isset($_FILES['product-image']) || $_FILES['product-image']['error'] != 0) {
        echo "<script>alert('Vui lòng chọn ảnh sản phẩm!'); window.history.back();</script>";
        exit();
    }
    if ($_FILES['product-image']['size'] > 2097152) {
        echo "<script>alert('File ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.'); window.history.back();</script>";
        exit();
    }
    $ext = strtolower(pathinfo($_FILES['product-image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        echo "<script>alert('Chỉ cho phép upload ảnh (jpg, png, gif, webp)!'); window.history.back();</script>";
        exit();
    }

    // 1. INSERT sản phẩm với ảnh tạm và số lượng ban đầu
    $sql = "INSERT INTO SanPham (TenSP, MaLoai, MoTa, DonViTinh, HinhAnh, TiLeLoiNhuan, HienTrang, SoLuongTon) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tenSP, $maLoai, $moTa, $donViTinh, 'product0.png', $tiLeLoiNhuan, $hienTrang, $soLuongBanDau]);

    // 2. Lấy MaSP vừa tạo
    $maSP = $conn->lastInsertId();

    // 3. Đặt tên file theo MaSP và upload
    $hinhAnh = preg_replace('/\s+/', '-', $_FILES['product-image']['name']);
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
    <style>
        .required {
            color: red;
        }

        .form-hint {
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }

        .preview-img {
            display: none;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px dashed #ccc;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="form-container">
                <h1>Thêm sản phẩm mới</h1>

                <form action="" method="POST" enctype="multipart/form-data"
                    onsubmit="return validateForm()">

                    <div class="form-group">
                        <label for="product-category">Loại sản phẩm <span class="required">*</span></label>
                        <select id="product-category" name="product-category" required>
                            <?php foreach ($loaiSanPhams as $loai): ?>
                                <option value="<?= $loai['MaLoai'] ?>">
                                    <?= htmlspecialchars($loai['TenLoai']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="product-name">Tên sản phẩm <span class="required">*</span></label>
                        <input type="text" id="product-name" name="product-name"
                            required placeholder="Nhập tên sản phẩm...">
                    </div>

                    <div class="form-group">
                        <label for="product-unit">Đơn vị tính <span class="required">*</span></label>
                        <input type="text" id="product-unit" name="product-unit"
                            required placeholder="Cái, hộp, chiếc...">
                    </div>

                    <div class="form-group">
                        <label for="product-quantity">Số lượng ban đầu <span class="required">*</span></label>
                        <input type="number" id="product-quantity" name="product-quantity"
                            min="0" value="0" required>
                        <p class="form-hint">Số lượng tồn kho khi mới tạo sản phẩm (nhập 0 nếu chưa có hàng).</p>
                    </div>

                    <div class="form-group">
                        <label>Giá vốn ban đầu (VNĐ) <span style="color:red">*</span></label>
                        <input type="number" name="gia_von" required min="0" placeholder="Ví dụ: 500000" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                        <span style="font-size: 0.85rem; color: #6c757d;">Nhập giá gốc nhập hàng. Nếu số lượng ban đầu là 0 thì nhập 0.</span>
                    </div>

                    <div class="form-group">
                        <label for="product-margin">Tỉ lệ lợi nhuận (%) <span class="required">*</span></label>
                        <input type="number" step="0.1" min="0" id="product-margin"
                            name="product-margin" required placeholder="VD: 20">
                        <p class="form-hint">Nhập số phần trăm lợi nhuận mong muốn. VD: 20 = 20%</p>
                    </div>

                    <div class="form-group">
                        <label for="product-status">Hiện trạng <span class="required">*</span></label>
                        <select id="product-status" name="product-status">
                            <option value="1">Hiển thị (Đang bán)</option>
                            <option value="0">Ẩn (Chưa bán)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="product-image">Hình ảnh sản phẩm <span class="required">*</span></label>
                        <input type="file" id="product-image" name="product-image"
                            accept="image/*" required onchange="previewImage(this)">
                        <p class="form-hint">Định dạng: jpg, png, gif, webp. Tối đa 2MB.</p>
                        <img id="img-preview" class="preview-img" src="#" alt="Preview">
                    </div>

                    <div class="form-group">
                        <label for="product-description">Mô tả sản phẩm <span class="required">*</span></label>
                        <textarea id="product-description" name="product-description"
                            rows="4" required
                            placeholder="Nhập mô tả chi tiết cho sản phẩm..."></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="quanlidanhmuc.php" class="btn btn-deleteback">← Quay lại danh sách</a>
                        <button type="submit" class="btn btn-save">💾 Lưu sản phẩm</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Preview ảnh trước khi upload
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

        // Validate form phía client trước khi submit
        function validateForm() {
            const ten = document.getElementById('product-name').value.trim();
            const dvt = document.getElementById('product-unit').value.trim();
            const margin = parseFloat(document.getElementById('product-margin').value);
            const qty = parseInt(document.getElementById('product-quantity').value);
            const img = document.getElementById('product-image');

            if (!ten) {
                alert('Vui lòng nhập tên sản phẩm!');
                return false;
            }
            if (!dvt) {
                alert('Vui lòng nhập đơn vị tính!');
                return false;
            }
            if (isNaN(margin) || margin < 0) {
                alert('Tỉ lệ lợi nhuận phải là số không âm!');
                return false;
            }
            if (isNaN(qty) || qty < 0) {
                alert('Số lượng ban đầu phải là số không âm!');
                return false;
            }
            if (!img.files || img.files.length === 0) {
                alert('Vui lòng chọn ảnh sản phẩm!');
                return false;
            }
            const size = img.files[0].size;
            if (size > 2097152) {
                alert('File ảnh quá lớn! Vui lòng chọn ảnh dưới 2MB.');
                return false;
            }
            return true;
        }
    </script>
</body>

</html>