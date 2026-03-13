<?php
session_start();

// Kiểm tra xem JavaScript có gửi mã sản phẩm (id) lên không
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Nếu giỏ hàng chưa tồn tại, tạo mới
    if (!isset($_SESSION['giohang'])) {
        $_SESSION['giohang'] = array();
    }

    // Nếu sản phẩm đã có trong giỏ, tăng số lượng lên 1
    if (isset($_SESSION['giohang'][$id])) {
        $_SESSION['giohang'][$id]++;
    } else {
        // Nếu chưa có, thêm vào giỏ với số lượng là 1
        $_SESSION['giohang'][$id] = 1;
    }

    // Đếm tổng số lượng món đang có trong giỏ
    $tong_soluong = 0;
    foreach ($_SESSION['giohang'] as $soluong) {
        $tong_soluong += $soluong;
    }

    // Trả về kết quả cho JavaScript đọc (Định dạng JSON)
    echo json_encode([
        'status' => 'success',
        'tong_mon' => $tong_soluong
    ]);
}
?>