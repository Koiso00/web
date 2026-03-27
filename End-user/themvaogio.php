<?php
session_start();

if (isset($_POST['id']) && $_POST['id'] != "") {
    $id_sp = $_POST['id'];
    $so_luong = 1; 

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['giohang'])) {
        $_SESSION['giohang'] = array();
    }

    // Nếu sản phẩm đã có, tăng số lượng. Nếu chưa, thêm mới.
    if (array_key_exists($id_sp, $_SESSION['giohang'])) {
        $_SESSION['giohang'][$id_sp] += $so_luong;
    } else {
        $_SESSION['giohang'][$id_sp] = $so_luong;
    }

    header("Location: giohang.php");
    exit();
} else {
    header("Location: trangchu.php");
    exit();
}
?>