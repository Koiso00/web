<?php
session_start();
// Chú ý đường dẫn file config vì file này nằm trong thư mục con quanlidonhang
require_once '../config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// ------------------------------------------------------------------------
// XỬ LÝ: CẬP NHẬT TRẠNG THÁI NHIỀU ĐƠN HÀNG CÙNG LÚC
// ------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_update_status'])) {
    if (isset($_POST['trang_thai']) && is_array($_POST['trang_thai'])) {
        $stmtUpdate = $conn->prepare("UPDATE DonHang SET TrangThai = ? WHERE MaDH = ?");
        foreach ($_POST['trang_thai'] as $maDH => $trangThaiMoi) {
            $stmtUpdate->execute([$trangThaiMoi, $maDH]);
        }
        echo "<script>alert('Cập nhật trạng thái các đơn hàng thành công!'); window.location.href='quanlidonhang.php';</script>";
    }
}

// ------------------------------------------------------------------------
// XỬ LÝ: LỌC & TÌM KIẾM DỮ LIỆU
// ------------------------------------------------------------------------
$where = [];
$params = [];

// 1. Tìm theo từ khóa (Mã đơn hoặc Tên khách)
$keyword = $_GET['keyword'] ?? '';
if ($keyword !== '') {
    $where[] = "(d.MaDH LIKE ? OR t.HoTen LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

// 2. Lọc theo trạng thái
$statusFilter = $_GET['status'] ?? '';
if ($statusFilter !== '') {
    $where[] = "d.TrangThai = ?";
    $params[] = $statusFilter;
}

// 3. Lọc theo ngày
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
if ($dateFrom !== '') {
    $where[] = "DATE(d.NgayDat) >= ?";
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $where[] = "DATE(d.NgayDat) <= ?";
    $params[] = $dateTo;
}

// Chuỗi WHERE hoàn chỉnh
$whereSQL = "";
if (count($where) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

// 4. Sắp xếp (Hỗ trợ sắp xếp theo Phường/Xã như đề yêu cầu)
$orderBy = "ORDER BY d.MaDH DESC"; // Mặc định đơn mới nhất lên đầu
$sort = $_GET['sort'] ?? '';
if ($sort === 'phuongxa') {
    $orderBy = "ORDER BY d.PhuongXaGiao ASC, d.MaDH DESC";
}

// Câu lệnh Query lấy danh sách đơn hàng (Join với bảng TaiKhoan để lấy tên)
$sql = "SELECT d.*, t.HoTen 
        FROM DonHang d 
        JOIN TaiKhoan t ON d.MaTK = t.MaTK 
        $whereSQL 
        $orderBy";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$danhSachDonHang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mảng ánh xạ trạng thái để hiển thị cho đẹp
$arrTrangThai = [
    0 => 'Mới đặt',
    1 => 'Đã xác nhận',
    2 => 'Đã giao',
    3 => 'Hủy'
];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn Hàng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style.css">
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Quản lí đơn đặt hàng</h1>
            </div>

            <form method="GET" action="">
                <div class="inventory-filter-bar">
                    <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Mã đơn hàng, tên khách hàng...">

                    <select name="status" class="status-select" style="width: 150px;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Mới đặt</option>
                        <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="2" <?= $statusFilter === '2' ? 'selected' : '' ?>>Đã giao</option>
                        <option value="3" <?= $statusFilter === '3' ? 'selected' : '' ?>>Hủy</option>
                    </select>

                    <select name="sort" class="status-select" style="width: 170px;">
                        <option value="">Sắp xếp: Mới nhất</option>
                        <option value="phuongxa" <?= $sort === 'phuongxa' ? 'selected' : '' ?>>Sắp xếp: Phường/Xã</option>
                    </select>

                    <div class="date-range-filter">
                        <label>Từ:</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
                        <label>Đến:</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
                    </div>

                    <button type="submit" class="btn btn-edit">Tra cứu</button>
                    <a href="quanlidonhang.php" class="btn btn-hide" style="text-decoration: none; padding: 10px;">Xóa lọc</a>
                </div>
            </form>

            <form method="POST" action="">
                <div class="order-list">
                    <?php if (count($danhSachDonHang) > 0): ?>
                        <?php foreach ($danhSachDonHang as $dh): ?>
                            <div class="order-item">
                                <div class="order-details">
                                    <h4>Mã đơn: DH<?= str_pad($dh['MaDH'], 3, '0', STR_PAD_LEFT) ?></h4>
                                    <p>Khách hàng: <strong><?= htmlspecialchars($dh['HoTen']) ?></strong></p>
                                    <p>Phường/Xã giao: <?= htmlspecialchars($dh['PhuongXaGiao']) ?></p>
                                    <p>Ngày đặt: <?= date('d/m/Y H:i', strtotime($dh['NgayDat'])) ?></p>
                                    <p class="order-total">Tổng tiền: <?= number_format($dh['TongTien'], 0, ',', '.') ?>đ</p>
                                </div>
                                <div class="order-actions">
                                    <a href="chi-tiet-don-hang.php?id=<?= $dh['MaDH'] ?>" class="btn btn-view" style="text-decoration:none;">Chi tiết</a>

                                    <select name="trang_thai[<?= $dh['MaDH'] ?>]" class="status-select" <?= $dh['TrangThai'] == 2 || $dh['TrangThai'] == 3 ? 'disabled' : '' ?>>
                                        <option value="0" <?= $dh['TrangThai'] == 0 ? 'selected' : '' ?>>Mới đặt</option>
                                        <option value="1" <?= $dh['TrangThai'] == 1 ? 'selected' : '' ?>>Đã xác nhận</option>
                                        <option value="2" <?= $dh['TrangThai'] == 2 ? 'selected' : '' ?>>Đã giao</option>
                                        <option value="3" <?= $dh['TrangThai'] == 3 ? 'selected' : '' ?>>Hủy</option>
                                    </select>

                                    <?php if ($dh['TrangThai'] == 2 || $dh['TrangThai'] == 3): ?>
                                        <input type="hidden" name="trang_thai[<?= $dh['MaDH'] ?>]" value="<?= $dh['TrangThai'] ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align:center; padding: 20px;">Không tìm thấy đơn hàng nào phù hợp.</p>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" name="btn_update_status" class="btn btn-save">Lưu tình trạng tất cả</button>
                    </div>
                </div>
            </form>

        </main>
    </div>
</body>

</html>