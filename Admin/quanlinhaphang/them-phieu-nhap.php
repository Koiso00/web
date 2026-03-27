<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$allProducts = $conn->query("SELECT MaSP, TenSP, DonViTinh FROM SanPham WHERE HienTrang = 1 ORDER BY TenSP ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ngayNhap = $_POST['import-date'];
    $maAdmin = $_SESSION['admin']['MaTK'];

    $stmtPN = $conn->prepare("INSERT INTO PhieuNhap (NgayNhap, MaAdmin, TrangThai) VALUES (?, ?, 0)");
    $stmtPN->execute([$ngayNhap, $maAdmin]);
    $maPN = $conn->lastInsertId();

    if (isset($_POST['sp_ids'])) {
        $stmtCT = $conn->prepare("INSERT INTO ChiTietPhieuNhap (MaPN, MaSP, SoLuongNhap, GiaNhap) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($_POST['sp_ids']); $i++) {
            if (!empty($_POST['sp_ids'][$i]) && $_POST['quantities'][$i] > 0 && $_POST['prices'][$i] >= 0) {
                $stmtCT->execute([$maPN, $_POST['sp_ids'][$i], $_POST['quantities'][$i], $_POST['prices'][$i]]);
            }
        }
    }
    echo "<script>alert('Tạo phiếu nhập thành công!'); window.location.href='quanlinhaphang.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Phiếu Nhập</title>
    <link rel="stylesheet" href="../Style.css">
    <style>
        .search-product-box {
            position: relative;
            margin-bottom: 16px;
        }
        .search-product-box input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .search-results {
            display: none;
            position: absolute;
            top: 100%;
            left: 0; right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .search-result-item {
            padding: 10px 14px;
            cursor: pointer;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        .search-result-item:hover { background: #f0f7ff; }
        .search-result-item span { color: #888; font-size: 12px; margin-left: 8px; }
        .product-entry-table input[type=number] { width: 100%; padding: 7px; box-sizing: border-box; }
        .product-entry-table select { width: 100%; padding: 7px; box-sizing: border-box; }
        .remove-row-btn {
            background: #dc3545; color: #fff; border: none;
            padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px;
        }
        .remove-row-btn:hover { background: #c82333; }
        .no-results { padding: 10px 14px; color: #888; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <?php include_once '../sidebar.php'; ?>

    <main class="main-content">
        <div class="form-container">
            <h1>Tạo phiếu nhập hàng mới</h1>

            <form method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>Ngày nhập <span style="color:red">*</span></label>
                    <input type="date" name="import-date" value="<?= date('Y-m-d') ?>" required>
                </div>

                <!-- Ô tìm kiếm sản phẩm -->
                <div class="form-section-header">
                    <h2 class="form-section-title">Chi tiết sản phẩm</h2>
                </div>

                <div class="search-product-box">
                    <input type="text" id="search-input" placeholder="🔍 Tìm kiếm sản phẩm để thêm vào phiếu..."
                           autocomplete="off" oninput="timKiemSanPham(this.value)">
                    <div class="search-results" id="search-results"></div>
                </div>

                <table class="product-entry-table" id="import-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th style="width:120px">Số lượng</th>
                            <th style="width:160px">Giá nhập (VNĐ)</th>
                            <th style="width:80px">Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="import-tbody">
                        <tr id="empty-row">
                            <td colspan="4" style="text-align:center; color:#aaa; padding:20px;">
                                Tìm kiếm và chọn sản phẩm ở trên để thêm vào phiếu
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-actions" style="margin-top:20px;">
                    <a href="quanlinhaphang.php" class="btn btn-deleteback">← Quay lại</a>
                    <button type="submit" class="btn btn-save">💾 Lưu phiếu nhập</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// Danh sách tất cả sản phẩm từ PHP
const allProducts = <?= json_encode($allProducts) ?>;
let selectedIds = new Set(); // Theo dõi sản phẩm đã chọn để tránh trùng

function timKiemSanPham(keyword) {
    const box = document.getElementById('search-results');
    keyword = keyword.trim().toLowerCase();

    if (!keyword) {
        box.style.display = 'none';
        return;
    }

    const filtered = allProducts.filter(p =>
        p.TenSP.toLowerCase().includes(keyword) ||
        String(p.MaSP).includes(keyword)
    );

    if (filtered.length === 0) {
        box.innerHTML = '<div class="no-results">Không tìm thấy sản phẩm nào</div>';
    } else {
        box.innerHTML = filtered.map(p => `
            <div class="search-result-item" onclick="themSanPhamVaoPhieu(${p.MaSP}, '${p.TenSP.replace(/'/g, "\\'")}', '${p.DonViTinh}')">
                ${p.TenSP} 
                ${selectedIds.has(p.MaSP) ? '<span style="color:#28a745">✓ Đã thêm</span>' : ''}
            </div>
        `).join('');
    }
    box.style.display = 'block';
}

function themSanPhamVaoPhieu(maSP, tenSP, dvt) {
    // Ẩn kết quả tìm kiếm và xóa ô tìm kiếm
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('search-input').value = '';

    // Nếu đã có rồi thì không thêm nữa
    if (selectedIds.has(maSP)) {
        alert('Sản phẩm "' + tenSP + '" đã có trong phiếu rồi!');
        return;
    }

    selectedIds.add(maSP);

    // Xóa dòng "hướng dẫn" nếu còn
    const emptyRow = document.getElementById('empty-row');
    if (emptyRow) emptyRow.remove();

    const tbody = document.getElementById('import-tbody');
    const row = document.createElement('tr');
    row.id = 'row-' + maSP;
    row.innerHTML = `
        <td>
            <input type="hidden" name="sp_ids[]" value="${maSP}">
            <strong>${tenSP}</strong><br>
        </td>
        <td>
            <input type="number" name="quantities[]" min="1" value="1" required>
        </td>
        <td>
            <input type="number" name="prices[]" min="0" value="0" required>
        </td>
        <td>
            <button type="button" class="remove-row-btn" onclick="xoaDong(${maSP})">Xóa</button>
        </td>
    `;
    tbody.appendChild(row);
}

function xoaDong(maSP) {
    selectedIds.delete(maSP);
    const row = document.getElementById('row-' + maSP);
    if (row) row.remove();

    // Nếu không còn dòng nào thì hiện lại hướng dẫn
    const tbody = document.getElementById('import-tbody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr id="empty-row">
                <td colspan="4" style="text-align:center; color:#aaa; padding:20px;">
                    Tìm kiếm và chọn sản phẩm ở trên để thêm vào phiếu
                </td>
            </tr>`;
    }
}

// Đóng dropdown khi click ra ngoài
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-product-box')) {
        document.getElementById('search-results').style.display = 'none';
    }
});

function validateForm() {
    const tbody = document.getElementById('import-tbody');
    if (document.getElementById('empty-row')) {
        alert('Vui lòng thêm ít nhất 1 sản phẩm vào phiếu!');
        return false;
    }
    // Kiểm tra số lượng và giá
    const quantities = document.querySelectorAll('input[name="quantities[]"]');
    const prices = document.querySelectorAll('input[name="prices[]"]');
    for (let i = 0; i < quantities.length; i++) {
        if (parseInt(quantities[i].value) < 1) {
            alert('Số lượng phải lớn hơn 0!');
            quantities[i].focus();
            return false;
        }
        if (parseInt(prices[i].value) < 0) {
            alert('Giá nhập không được âm!');
            prices[i].focus();
            return false;
        }
    }
    return true;
}
</script>
</body>
</html>
