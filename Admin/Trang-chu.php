<?php
session_start();
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: Trang-dang-nhap.php");
    exit();
}

$adminName = $_SESSION['admin']['HoTen'];

// 1. LẤY DỮ LIỆU THỐNG KÊ TỔNG QUAN
$tongKhachHang = $conn->query("SELECT COUNT(*) FROM TaiKhoan WHERE VaiTro = 0")->fetchColumn();
$tongSanPham = $conn->query("SELECT COUNT(*) FROM SanPham")->fetchColumn();
$tongDonHang = $conn->query("SELECT COUNT(*) FROM DonHang")->fetchColumn();

// Tính tổng doanh thu (Chỉ tính các đơn hàng Đã Giao - TrangThai = 2)
$sqlTongDoanhThu = "SELECT SUM(ct.SoLuongMua * ct.GiaBan) 
                    FROM DonHang dh 
                    JOIN ChiTietDonHang ct ON dh.MaDH = ct.MaDH 
                    WHERE dh.TrangThai = 2";
$tongDoanhThu = $conn->query($sqlTongDoanhThu)->fetchColumn();
$tongDoanhThu = $tongDoanhThu ? $tongDoanhThu : 0; // Tránh null nếu chưa có đơn

// 2. LẤY DỮ LIỆU BIỂU ĐỒ DOANH THU THEO THÁNG (Năm hiện tại)
$namHienTai = date('Y');
$sqlBieuDo = "SELECT MONTH(dh.NgayDat) as Thang, SUM(ct.SoLuongMua * ct.GiaBan) as DoanhThu 
              FROM DonHang dh 
              JOIN ChiTietDonHang ct ON dh.MaDH = ct.MaDH 
              WHERE dh.TrangThai = 2 AND YEAR(dh.NgayDat) = ? 
              GROUP BY MONTH(dh.NgayDat) 
              ORDER BY Thang ASC";

$stmt = $conn->prepare($sqlBieuDo);
$stmt->execute([$namHienTai]);
$doanhThuThang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo mảng 12 tháng với giá trị mặc định là 0
$mangDoanhThu = array_fill(1, 12, 0);
foreach ($doanhThuThang as $row) {
    $mangDoanhThu[(int)$row['Thang']] = (float)$row['DoanhThu'];
}

// Chuyển mảng PHP thành chuỗi JSON để truyền cho Javascript (Chart.js)
$jsonDoanhThu = json_encode(array_values($mangDoanhThu));
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang Chủ - TechZone Dashboard</title>
    <link rel="stylesheet" href="Style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* CSS nâng cấp giao diện Dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 5px solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.blue {
            border-color: #007bff;
        }

        .stat-card.green {
            border-color: #28a745;
        }

        .stat-card.orange {
            border-color: #fd7e14;
        }

        .stat-card.purple {
            border-color: #6f42c1;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
        }

        .stat-info .number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin: 5px 0 0;
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .chart-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            height: 400px;
            /* Chiều cao cố định cho biểu đồ */
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include_once 'sidebar.php'; ?>

        <main class="main-content">
            <header class="page-header" style="border-bottom: none; margin-bottom: 10px;">
                <div class="header-title">
                    <h1 class="page-header-title">Tổng quan hệ thống</h1>
                    <p style="color: #666;">Xin chào, <strong><?= htmlspecialchars($adminName) ?></strong>! Chúc bạn một ngày làm việc hiệu quả.</p>
                </div>
            </header>

            <div class="dashboard-grid">
                <div class="stat-card blue">
                    <div class="stat-info">
                        <h3>Tổng Khách Hàng</h3>
                        <p class="number"><?= number_format($tongKhachHang) ?></p>
                    </div>
                    <div class="stat-icon">👥</div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-info">
                        <h3>Sản Phẩm</h3>
                        <p class="number"><?= number_format($tongSanPham) ?></p>
                    </div>
                    <div class="stat-icon">📦</div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-info">
                        <h3>Đơn Hàng</h3>
                        <p class="number"><?= number_format($tongDonHang) ?></p>
                    </div>
                    <div class="stat-icon">🛒</div>
                </div>

                <div class="stat-card green">
                    <div class="stat-info">
                        <h3>Doanh Thu (<?= $namHienTai ?>)</h3>
                        <p class="number" style="color: #28a745;"><?= number_format($tongDoanhThu, 0, ',', '.') ?>đ</p>
                    </div>
                    <div class="stat-icon">💰</div>
                </div>
            </div>

            <div class="chart-wrapper">
                <h2 style="margin-top: 0; margin-bottom: 20px; color: #333; font-size: 1.2rem;">📈 Biểu đồ doanh thu năm <?= $namHienTai ?></h2>
                <canvas id="revenueChart"></canvas>
            </div>
        </main>
    </div>

    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');

        // Nhận mảng dữ liệu từ PHP
        const dataDoanhThu = <?= $jsonDoanhThu ?>;

        new Chart(ctx, {
            type: 'bar', // Biểu đồ dạng cột
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: dataDoanhThu,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Màu cột (Xanh dương)
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 4, // Bo góc các cột
                    hoverBackgroundColor: 'rgba(54, 162, 235, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Ẩn chú thích (vì đã có tiêu đề)
                    },
                    tooltip: {
                        callbacks: {
                            // Định dạng số tiền khi hover vào cột
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('vi-VN', {
                                        style: 'currency',
                                        currency: 'VND'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // Rút gọn các số lớn trên trục Y (ví dụ 1000000 -> 1 Tr)
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000) + ' Tr';
                                } else if (value >= 1000) {
                                    return (value / 1000) + ' K';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>