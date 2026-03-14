<?php
session_start();

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
        if ($so_luong_moi > 0) {
            $_SESSION['giohang'][$id_sp] = $so_luong_moi;
        } else {
            unset($_SESSION['giohang'][$id_sp]);
        }
    }
}

header("Location: giohang.php");
exit();
?>