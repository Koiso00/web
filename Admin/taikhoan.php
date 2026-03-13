<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: Trang-dang-nhap.php");
    exit();
}

// Xử lý Khóa / Mở Khóa tài khoản
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] == 'lock') {
        $conn->query("UPDATE TaiKhoan SET TrangThai = 0 WHERE MaTK = $id");
    } elseif ($_GET['action'] == 'unlock') {
        $conn->query("UPDATE TaiKhoan SET TrangThai = 1 WHERE MaTK = $id");
    }
    header("Location: taikhoan.php");
    exit();
}

// Lấy danh sách tài khoản
$stmt = $conn->query("SELECT * FROM TaiKhoan ORDER BY VaiTro DESC, MaTK DESC");
$danhSachTK = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tài khoản</title>
    <link rel="stylesheet" href="Style.css">
</head>

<body>
    <div class="container">
        <?php include_once 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Quản lý Tài khoản Hệ thống</h1>
            </div>

            <div class="account-list">
                <?php foreach ($danhSachTK as $tk): ?>
                    <div class="account-item">
                        <?php if (!empty($tk['Avatar'])): ?>
                            <img src="Image/<?= htmlspecialchars($tk['Avatar']) ?>" alt="Avatar" class="account-avatar" style="object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($tk['HoTen']) ?>&background=random&color=fff&size=100" alt="Avatar" class="account-avatar" style="border-radius: 50%;">
                        <?php endif; ?>
                        <div class="account-info">
                            <h4><?= htmlspecialchars($tk['HoTen']) ?> (<?= htmlspecialchars($tk['Username']) ?>)</h4>
                            <p><?= htmlspecialchars($tk['Email']) ?></p>
                            <p>Vai trò:
                                <?php if ($tk['VaiTro'] == 1): ?>
                                    <span class="role admin">Quản trị viên</span>
                                <?php else: ?>
                                    <span class="role customer">Khách hàng</span>
                                <?php endif; ?>

                                <?php if ($tk['TrangThai'] == 0): ?>
                                    <span style="color:red; font-size: 12px; margin-left: 10px;">(Đang bị khóa)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="account-actions">
                            <button class="btn btn-reset">Reset Mật khẩu</button>

                            <?php if ($tk['MaTK'] != $_SESSION['admin']['MaTK']): ?>
                                <?php if ($tk['TrangThai'] == 1): ?>
                                    <a href="taikhoan.php?action=lock&id=<?= $tk['MaTK'] ?>" class="btn btn-lock" style="text-decoration:none;">Khoá</a>
                                <?php else: ?>
                                    <a href="taikhoan.php?action=unlock&id=<?= $tk['MaTK'] ?>" class="btn btn-unlock" style="text-decoration:none;">Mở khoá</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>

</html>