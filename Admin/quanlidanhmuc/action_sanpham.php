<?php
session_start();
// Gọi file kết nối từ thư mục gốc
require_once '../config.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if ($id > 0) {
    try {
        if ($action === 'hide') {
            $conn->query("UPDATE SanPham SET HienTrang = 0 WHERE MaSP = $id");
        } elseif ($action === 'show') {
            $conn->query("UPDATE SanPham SET HienTrang = 1 WHERE MaSP = $id");
        } elseif ($action === 'delete') {
            // Kiểm tra xem sản phẩm đã từng có lịch sử nhập hàng chưa
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM ChiTietPhieuNhap WHERE MaSP = ?");
            $stmtCheck->execute([$id]);
            $count = $stmtCheck->fetchColumn();

            if ($count > 0) {
                // Nếu đã có lịch sử thì không cho xóa cứng để tránh lỗi dữ liệu, chỉ chuyển sang ẩn
                $conn->query("UPDATE SanPham SET HienTrang = 0 WHERE MaSP = $id");
                echo "<script>alert('Sản phẩm đã có lịch sử nhập kho. Hệ thống tự động chuyển sang trạng thái ẨN để bảo toàn dữ liệu.');</script>";
            } else {
                // Nếu sản phẩm mới tạo, chưa nhập hàng thì cho xóa hẳn
                $conn->query("DELETE FROM SanPham WHERE MaSP = $id");
                echo "<script>alert('Đã xóa cứng sản phẩm thành công!');</script>";
            }
        }
    } catch (PDOException $e) {
        echo "<script>alert('Có lỗi xảy ra, không thể thao tác!');</script>";
    }
}
// Quay lại trang danh sách
echo "<script>window.location.href='quanlidanhmuc.php';</script>";
