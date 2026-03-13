<?php
session_start();
require_once 'config.php'; // Không có ../ vì ở thư mục gốc

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: Trang-dang-nhap.php");
    exit();
}

// Lấy tên Admin để hiển thị
$adminName = $_SESSION['admin']['HoTen'];

// Lấy dữ liệu thống kê thật từ Database
$tongKhachHang = $conn->query("SELECT COUNT(*) FROM TaiKhoan WHERE VaiTro = 0")->fetchColumn();
$tongSanPham = $conn->query("SELECT COUNT(*) FROM SanPham")->fetchColumn();
$tongDonHang = $conn->query("SELECT COUNT(*) FROM DonHang")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang Chủ - Quản trị</title>
    <link rel="stylesheet" href="Style.css">
</head>

<body>
    <div class="container">
        <?php include_once 'sidebar.php'; ?>

        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-column"></div>
                <div class="header-title">
                    <h1>Tổng quan hệ thống</h1>
                    <p>Chào mừng Admin: <strong><?= htmlspecialchars($adminName) ?></strong></p>
                </div>
                <div class="header-column header-profile">
                    <details class="profile-dropdown">
                        <summary class="admin-avatar">
                            <img src="Image/Frog.gif" alt="Avatar">
                        </summary>
                        <div class="dropdown-menu">
                            <a href="logout.php">Đăng xuất</a>
                        </div>
                    </details>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="card-info">
                        <span class="stat-number"><?= $tongKhachHang ?></span>
                        <span class="stat-label">Khách hàng</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-info">
                        <span class="stat-number"><?= $tongSanPham ?></span>
                        <span class="stat-label">Sản phẩm</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="card-info">
                        <span class="stat-number"><?= $tongDonHang ?></span>
                        <span class="stat-label">Đơn hàng</span>
                    </div>
                </div>
            </div>

            <div class="chart-container">
                <h2>Biểu đồ doanh thu dự kiến</h2>
                <div class="chart-placeholder">
                    <div class="chart-bars">
                        <div class="bar" style="height: 80%;"><span class="bar-value">160tr</span>
                            <p>Mar</p>
                        </div>
                        <div class="bar" style="height: 100%;"><span class="bar-value">200tr</span>
                            <p>Nov</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>