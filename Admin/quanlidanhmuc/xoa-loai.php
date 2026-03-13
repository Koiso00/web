<?php
session_start();
require_once '../config.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: ../Trang-dang-nhap.php");
    exit();
}

// 2. Lấy ID loại cần xóa
$idLoai = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idLoai > 0) {
    try {
        // 3. KIỂM TRA RÀNG BUỘC: Nếu có sản phẩm thuộc loại này thì không cho xóa
        // Điều này giúp tránh lỗi logic trong CSDL (Foreign Key Constraint)
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM SanPham WHERE MaLoai = ?");
        $stmtCheck->execute([$idLoai]);
        $count = $stmtCheck->fetchColumn();

        if ($count > 0) {
            echo "<script>
                alert('Không thể xóa! Đang có $count sản phẩm thuộc loại này. Bạn hãy xóa hoặc đổi loại cho các sản phẩm đó trước.');
                window.location.href='sanpham.php';
            </script>";
        } else {
            // 4. Tiến hành xóa nếu không có sản phẩm nào liên quan
            $stmtDelete = $conn->prepare("DELETE FROM LoaiSanPham WHERE MaLoai = ?");
            $stmtDelete->execute([$idLoai]);
            echo "<script>
                alert('Đã xóa loại sản phẩm thành công!');
                window.location.href='sanpham.php';
            </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
            alert('Lỗi hệ thống: Không thể xóa loại này.');
            window.location.href='sanpham.php';
        </script>";
    }
} else {
    header("Location: sanpham.php");
}
