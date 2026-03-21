<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// Xử lý Hoàn thành phiếu nhập
if (isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['id'])) {
    $maPN = (int)$_GET['id'];

    // 1. Lấy chi tiết sản phẩm trong phiếu
    $stmtCT = $conn->prepare("SELECT * FROM ChiTietPhieuNhap WHERE MaPN = ?");
    $stmtCT->execute([$maPN]);
    $items = $stmtCT->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $maSP = $item['MaSP'];
        $soLuongNhapMoi = $item['SoLuongNhap'];
        $giaNhapMoi = $item['GiaNhap'];

        $stmtSP = $conn->prepare("SELECT SoLuongTon, GiaNhapBinhQuan FROM SanPham WHERE MaSP = ?");
        $stmtSP->execute([$maSP]);
        $product = $stmtSP->fetch(PDO::FETCH_ASSOC);

        $tonHienTai = $product['SoLuongTon'];
        $giaHienTai = $product['GiaNhapBinhQuan'];

        // Công thức bình quân gia quyền
        $tongSoLuongMoi = $tonHienTai + $soLuongNhapMoi;
        $giaNhapBinhQuanMoi = (($tonHienTai * $giaHienTai) + ($soLuongNhapMoi * $giaNhapMoi)) / $tongSoLuongMoi;

        $updateSP = $conn->prepare("UPDATE SanPham SET SoLuongTon = ?, GiaNhapBinhQuan = ? WHERE MaSP = ?");
        $updateSP->execute([$tongSoLuongMoi, $giaNhapBinhQuanMoi, $maSP]);
    }

    $conn->prepare("UPDATE PhieuNhap SET TrangThai = 1 WHERE MaPN = ?")->execute([$maPN]);
    echo "<script>alert('Hoàn thành phiếu nhập và cập nhật giá vốn thành công!'); window.location.href='quanlinhaphang.php';</script>";
}

$stmt = $conn->query("SELECT p.*, t.HoTen FROM PhieuNhap p JOIN TaiKhoan t ON p.MaAdmin = t.MaTK ORDER BY p.MaPN DESC");
$phieuNhaps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Nhập hàng</title>
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Quản lí phiếu nhập hàng</h1>
            </div>
            <div class="page-toolbar"><a href="them-phieu-nhap.php" class="btn btn-add">➕ Thêm phiếu nhập</a></div>
            <div class="slip-list">
                <?php foreach ($phieuNhaps as $pn): ?>
                    <div class="slip-item">
                        <div class="slip-info">
                            <h4>Mã phiếu: PN<?= str_pad($pn['MaPN'], 3, '0', STR_PAD_LEFT) ?></h4>
                            <p>Ngày nhập: <?= date('d/m/Y', strtotime($pn['NgayNhap'])) ?></p>
                            <p>Người lập: <?= htmlspecialchars($pn['HoTen']) ?></p>
                        </div>
                        <div class="slip-status">
                            <span class="status <?= $pn['TrangThai'] == 0 ? 'pending' : 'completed' ?>">
                                <?= $pn['TrangThai'] == 0 ? 'Chờ xử lý' : 'Đã hoàn thành' ?>
                            </span>
                        </div>
                        <div class="slip-actions">
                            <?php if ($pn['TrangThai'] == 0): ?>
                                <a href="sua-phieu-nhap.php?id=<?= $pn['MaPN'] ?>" class="btn btn-edit">Sửa</a>
                                <a href="quanlinhaphang.php?action=complete&id=<?= $pn['MaPN'] ?>" class="btn btn-complete" onclick="return confirm('Xác nhận hoàn thành?')">Hoàn thành</a>
                            <?php else: ?>
                                <button class="btn btn-view" disabled>Đã chốt</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>

</html>