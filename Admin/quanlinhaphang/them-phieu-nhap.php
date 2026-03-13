<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$allProducts = $conn->query("SELECT MaSP, TenSP FROM SanPham WHERE HienTrang = 1")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ngayNhap = $_POST['import-date'];
    $maAdmin = $_SESSION['admin']['MaTK'];

    // 1. Tạo phiếu nhập cha
    $stmtPN = $conn->prepare("INSERT INTO PhieuNhap (NgayNhap, MaAdmin, TrangThai) VALUES (?, ?, 0)");
    $stmtPN->execute([$ngayNhap, $maAdmin]);
    $maPN = $conn->lastInsertId();

    // 2. Thêm các sản phẩm vào chi tiết (lấy từ các mảng input)
    if (isset($_POST['sp_ids'])) {
        $stmtCT = $conn->prepare("INSERT INTO ChiTietPhieuNhap (MaPN, MaSP, SoLuongNhap, GiaNhap) VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($_POST['sp_ids']); $i++) {
            $stmtCT->execute([
                $maPN,
                $_POST['sp_ids'][$i],
                $_POST['quantities'][$i],
                $_POST['prices'][$i]
            ]);
        }
    }
    header("Location: quanlinhaphang.php");
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm Phiếu Nhập</title>
    <link rel="stylesheet" href="../Style.css">
    <script>
        // Hàm JS để thêm dòng mới vào bảng nhập hàng
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
                <div class="container">
                    <main class="main-content" style="margin-left:260px">
                        <div class="form-container">
                            <h1>Tạo phiếu nhập hàng mới</h1>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Ngày nhập</label>
                                    <input type="date" name="import-date" value="<?= date('Y-m-d') ?>" required>
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
                                        <tr>
                                            <td>
                                                <select name="sp_ids[]" required style="width:100%; padding:8px;">
                                                    <?php foreach ($allProducts as $p): ?>
                                                        <option value="<?= $p['MaSP'] ?>"><?= htmlspecialchars($p['TenSP']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="number" name="quantities[]" min="1" required></td>
                                            <td><input type="number" name="prices[]" min="0" required></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="form-actions">
                                    <a href="quanlinhaphang.php" class="btn btn-deleteback">Quay lại</a>
                                    <button type="submit" class="btn btn-save">Lưu phiếu nhập</button>
                                </div>
                            </form>
                        </div>
                    </main>
                </div>
</body>

</html>