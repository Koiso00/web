<?php
session_start();
include 'connect.php';

// 1. GHI NHỚ CÁC Ô CHECKBOX ĐANG ĐƯỢC CHỌN
if (isset($_POST['sp_chon'])) {
    $_SESSION['sp_chon'] = $_POST['sp_chon'];
} else {
    // Nếu khách bỏ tích hết sạch, thì lưu mảng rỗng
    $_SESSION['sp_chon'] = []; 
}

// 2. CẬP NHẬT SỐ LƯỢNG (Code cũ giữ nguyên)
if (isset($_POST['soluong'])) {
    foreach ($_POST['soluong'] as $id_sp => $so_luong_moi) {
        $id_sp = (int)$id_sp;
        $so_luong_moi = (int)$so_luong_moi;

        if ($id_sp <= 0) continue;

        if ($so_luong_moi > 0) {
            // Không cho cập nhật vượt tồn kho
            $stmt = mysqli_prepare($conn, "SELECT SoLuongTon FROM sanpham WHERE MaSP = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $id_sp);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);

            $ton = $row ? (int)$row['SoLuongTon'] : 0;
            if ($ton <= 0) {
                unset($_SESSION['giohang'][$id_sp]);
                $_SESSION['flash_error'] = "Sản phẩm đã hết hàng và đã được xóa khỏi giỏ.";
                continue;
            }

            if ($so_luong_moi > $ton) {
                $_SESSION['giohang'][$id_sp] = $ton;
                $_SESSION['flash_error'] = "Số lượng vượt tồn kho. Hệ thống đã điều chỉnh về tối đa $ton.";
            } else {
                $_SESSION['giohang'][$id_sp] = $so_luong_moi;
            }
        } else {
            unset($_SESSION['giohang'][$id_sp]);
        }
    }
}

header("Location: giohang.php");
exit();
?>