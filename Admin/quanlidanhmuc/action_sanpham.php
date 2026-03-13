<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) {} // Bật lại sau

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if ($id > 0) {
    try {
        if ($action === 'hide') {
            $conn->query("UPDATE SanPham SET HienTrang = 0 WHERE MaSP = $id");
        } elseif ($action === 'show') {
            $conn->query("UPDATE SanPham SET HienTrang = 1 WHERE MaSP = $id");
        } elseif ($action === 'delete') {
            // Kiểm tra lịch sử nhập hàng
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM ChiTietPhieuNhap WHERE MaSP = ?");
            $stmtCheck->execute([$id]);
            $count = $stmtCheck->fetchColumn();

            if ($count > 0) {
                $conn->query("UPDATE SanPham SET HienTrang = 0 WHERE MaSP = $id");
                echo "<script>alert('Sản phẩm đã có lịch sử nhập kho. Hệ thống tự động chuyển sang trạng thái ẨN để bảo toàn dữ liệu.');</script>";
            } else {
                $conn->query("DELETE FROM SanPham WHERE MaSP = $id");
                echo "<script>alert('Đã xóa cứng sản phẩm thành công!');</script>";
            }
        }
    } catch (PDOException $e) {
        echo "<script>alert('Có lỗi xảy ra, không thể thao tác!');</script>";
    }
}
echo "<script>window.location.href='quanlidanhmuc.php';</script>";
?>