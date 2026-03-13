<?php
session_start();

// Kiểm tra xem có nhận được ID sản phẩm cần xóa không
if (isset($_GET['id'])) {
    $id_cantoan = $_GET['id'];
    
    // Nếu sản phẩm đó có trong giỏ hàng (SESSION) thì xóa nó đi
    if (isset($_SESSION['giohang'][$id_cantoan])) {
        unset($_SESSION['giohang'][$id_cantoan]);
    }
}

// Xóa xong thì tự động "đá" khách hàng quay lại trang giỏ hàng
header("Location: giohang.php");
exit();
?>