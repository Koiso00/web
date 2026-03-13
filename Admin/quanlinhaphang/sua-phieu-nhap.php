<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Lấy thông tin phiếu nhập
$stmtPN = $conn->prepare("SELECT * FROM PhieuNhap WHERE MaPN = ?");
$stmtPN->execute([$id]);
$phieu = $stmtPN->fetch(PDO::FETCH_ASSOC);

// Nếu phiếu đã hoàn thành thì không cho sửa
if (!$phieu || $phieu['TrangThai'] == 1) {
    echo "<script>alert('Phiếu không tồn tại hoặc đã chốt, không thể sửa!'); window.location.href='quanlinhaphang.php';</script>";
    exit();
}

// 2. Lấy chi tiết các sản phẩm trong phiếu đó
$stmtCT = $conn->prepare("SELECT ct.*, s.TenSP FROM ChiTietPhieuNhap ct JOIN SanPham s ON ct.MaSP = s.MaSP WHERE ct.MaPN = ?");
$stmtCT->execute([$id]);
$chiTiet = $stmtCT->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách sản phẩm để nếu muốn thêm dòng mới
$allProducts = $conn->query("SELECT MaSP, TenSP FROM SanPham WHERE HienTrang = 1")->fetchAll(PDO::FETCH_ASSOC);

// 3. Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ngayNhap = $_POST['import-date'];

    // Cập nhật ngày nhập
    $conn->prepare("UPDATE PhieuNhap SET NgayNhap = ? WHERE MaPN = ?")->execute([$ngayNhap, $id]);

    // Xóa chi tiết cũ để chèn lại chi tiết mới (cách làm đơn giản nhất cho sửa bảng chi tiết)
    $conn->prepare("DELETE FROM ChiTietPhieuNhap WHERE MaPN = ?")->execute([$id]);

    if (isset($_POST['sp_ids'])) {
        $stmtInsert = $conn->prepare("INSERT INTO ChiTietPhieuNhap (MaPN, MaSP, SoLuongNhap, GiaNhap) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($_POST['sp_ids']); $i++) {
            $stmtInsert->execute([
                $id,
                $_POST['sp_ids'][$i],
                $_POST['quantities'][$i],
                $_POST['prices'][$i]
            ]);
        }
    }
    echo "<script>alert('Cập nhật phiếu nhập thành công!'); window.location.href='quanlinhaphang.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa Phiếu Nhập</title>
    <link rel="stylesheet" href="../Style.css">
    <script>
        function addRow() {
            let table = document.getElementById("import-table").getElementsByTagName('tbody')[0];
            let row = table.insertRow();
            row.innerHTML = `
                <td>
                    <select name="sp_ids[]" required style="width:100%; padding:8px;">
                        <?php foreach ($allProducts as $p): ?>
                            <option value="<?= $p['MaSP'] ?>"><?= htmlspecialchars($p['TenSP']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" name="quantities[]" min="1" required></td>
                <td><input type="number" name="prices[]" min="0" required></td>
                <td><button type="button" onclick="this.parentElement.parentElement.remove()" style="color:red">Xóa</button></td>
            `;
        }
    </script>
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>
        <main class="main-content">
            <div class="form-container">
                <h1>Sửa phiếu nhập: PN<?= str_pad($id, 3, '0', STR_PAD_LEFT) ?></h1>
                <form method="POST">
                    <div class="form-group">
                        <label>Ngày nhập</label>
                        <input type="date" name="import-date" value="<?= date('Y-m-d', strtotime($phieu['NgayNhap'])) ?>" required>
                    </div>

                    <div class="form-section-header">
                        <h2 class="form-section-title">Chi tiết sản phẩm</h2>
                        <button type="button" class="btn btn-add-item" onclick="addRow()">➕ Thêm dòng</button>
                    </div>

                    <table class="product-entry-table" id="import-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá nhập (VNĐ)</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chiTiet as $item): ?>
                                <tr>
                                    <td>
                                        <select name="sp_ids[]" required style="width:100%; padding:8px;">
                                            <?php foreach ($allProducts as $p): ?>
                                                <option value="<?= $p['MaSP'] ?>" <?= ($p['MaSP'] == $item['MaSP']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($p['TenSP']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="number" name="quantities[]" value="<?= $item['SoLuongNhap'] ?>" min="1" required></td>
                                    <td><input type="number" name="prices[]" value="<?= $item['GiaNhap'] ?>" min="0" required></td>
                                    <td><button type="button" onclick="this.parentElement.parentElement.remove()" style="color:red">Xóa</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="form-actions">
                        <a href="quanlinhaphang.php" class="btn btn-deleteback">Quay lại</a>
                        <button type="submit" class="btn btn-save">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>