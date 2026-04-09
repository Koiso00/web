<?php
session_start();
include 'connect.php';

// Kiểm tra xem JavaScript có gửi mã sản phẩm (id) lên không
if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    if ($id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Sản phẩm không hợp lệ.'
        ]);
        exit();
    }

    // Nếu giỏ hàng chưa tồn tại, tạo mới
    if (!isset($_SESSION['giohang'])) {
        $_SESSION['giohang'] = array();
    }

    // Kiểm tra tồn kho hiện tại
    $stmt = mysqli_prepare($conn, "SELECT SoLuongTon FROM sanpham WHERE MaSP = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);

    if (!$row) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Không tìm thấy sản phẩm.'
        ]);
        exit();
    }

    $ton = (int)$row['SoLuongTon'];
    $currentQty = isset($_SESSION['giohang'][$id]) ? (int)$_SESSION['giohang'][$id] : 0;

    if ($ton <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Sản phẩm tạm hết hàng.'
        ]);
        exit();
    }

    if ($currentQty + 1 > $ton) {
        echo json_encode([
            'status' => 'error',
            'message' => "Trong kho chỉ còn $ton sản phẩm."
        ]);
        exit();
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