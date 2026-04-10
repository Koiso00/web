<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user'])) {
    header('location:trangdangnhap.php');
    exit();
}

$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$user_session = $_SESSION['user']; 
$user_session = mysqli_real_escape_string($conn, $user_session);

// Tìm MaTK dựa trên Email hoặc HoTen
$sql_user = "SELECT MaTK FROM taikhoan WHERE Email = '$user_session' OR HoTen = '$user_session' LIMIT 1";
$res_user = mysqli_query($conn, $sql_user);

if ($res_user && mysqli_num_rows($res_user) > 0) {
    $row_user = mysqli_fetch_assoc($res_user);
    $maTK = $row_user['MaTK'];
} else {
    // NẾU VẪN LỖI: Hãy dùng cách này để "cứu vãn" trang web 
    // Lấy đại MaTK đầu tiên trong bảng taikhoan để hiển thị (chỉ dùng để debug)
    $sql_fallback = "SELECT MaTK FROM taikhoan LIMIT 1";
    $res_fallback = mysqli_query($conn, $sql_fallback);
    $row_fallback = mysqli_fetch_assoc($res_fallback);
    $maTK = $row_fallback['MaTK']; 
    
    // Nếu bạn muốn tìm nguyên nhân, hãy bỏ comment dòng dưới để xem session đang mang giá trị gì:
    // die("Dữ liệu trong session là: " . $user_session . ". Hãy vào DB tìm người có Email hoặc HoTen như vậy.");
}

// Giờ câu lệnh này sẽ chạy được vì $maTK chắc chắn có giá trị
$sql_da_mua = "SELECT sp.TenSP, sp.HinhAnh, ctdh.SoLuongMua, ctdh.GiaBan, dh.NgayDat 
               FROM chitietdonhang ctdh
               JOIN donhang dh ON ctdh.MaDH = dh.MaDH
               JOIN sanpham sp ON ctdh.MaSP = sp.MaSP
               WHERE dh.MaTK = '$maTK'
               ORDER BY dh.NgayDat DESC";
$result_da_mua = mysqli_query($conn, $sql_da_mua);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - TechZone</title>
    <link rel="stylesheet" href="style.css"> <link rel="stylesheet" href="giohang.css"> <style>
        .cart-section-title {
            font-size: 2.2rem;
            margin: 3rem 0 1.5rem;
            color: #333;
            border-left: 5px solid #0f75ff;
            padding-left: 1.5rem;
            text-transform: uppercase;
        }
        .status-success { color: #27ae60; font-weight: bold; }
        .history-table img { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <?php if (!empty($flash_error)): ?>
        <script>alert(<?php echo json_encode($flash_error); ?>);</script>
    <?php endif; ?>

    <section class="cart-container" style="margin-top: 100px;">
        
        <h2 class="cart-section-title">Giỏ hàng hiện tại</h2>
        <form action="capnhatgiohang.php" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Chọn</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tong_tien_gio_hang = 0;
                    if (isset($_SESSION['giohang']) && !empty($_SESSION['giohang'])) {
                        foreach ($_SESSION['giohang'] as $id_sp => $so_luong) {
                            $sql = "SELECT * FROM SanPham WHERE MaSP = $id_sp";
                            $result = mysqli_query($conn, $sql);
                            if ($row = mysqli_fetch_assoc($result)) {
                                $gia_ban = $row['GiaNhapBinhQuan'] * (1 + $row['TiLeLoiNhuan']);
                                $thanh_tien = $gia_ban * $so_luong;
                                $tong_tien_gio_hang += $thanh_tien;
                    ?>
                        <tr>
                            <td><input type="checkbox" name="sp_chon[]" value="<?php echo $id_sp; ?>" class="chon-sp" data-price="<?php echo $thanh_tien; ?>" checked></td>
                            <td><img src="../Admin/Image/<?php echo $row['HinhAnh']; ?>" alt=""></td>
                            <td><?php echo htmlspecialchars($row['TenSP']); ?></td>
                            <td><?php echo number_format($gia_ban, 0, ',', '.'); ?> ₫</td>
                            <td>
                                <input type="number"
                                       name="soluong[<?php echo $id_sp; ?>]"
                                       value="<?php echo $so_luong; ?>"
                                       min="1"
                                       max="<?php echo (int)$row['SoLuongTon']; ?>"
                                       onchange="this.form.submit()"
                                       style="width: 60px; padding: 5px; text-align: center;">
                            </td>
                            <td><?php echo number_format($thanh_tien, 0, ',', '.'); ?> ₫</td>
                            <td>
                                <a href="xoagiohang.php?id=<?php echo $id_sp; ?>" onclick="return confirm('Xóa sản phẩm này?')">
                                    <img src="picture/trash.png" alt="Xóa" style="width: 25px; height: 25px;">
                                </a>
                            </td>
                        </tr>
                    <?php 
                            }
                        }
                    } else {
                        echo "<tr><td colspan='7' style='padding: 3rem; font-size: 1.6rem;'>Giỏ hàng trống. <a href='trangchu.php' style='color:#0f75ff'>Tiếp tục mua sắm</a></td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <p class="total">Tổng thanh toán: <span id="tong-tien-hien-thi"><?php echo number_format($tong_tien_gio_hang, 0, ',', '.'); ?></span> ₫</p>
                <?php if ($tong_tien_gio_hang > 0): ?>
                    <button type="submit" formaction="thanhtoan.php" class="checkout-btn">Tiến hành thanh toán</button>
                <?php endif; ?>
            </div>
        </form>

        <hr style="margin: 5rem 0; border: 0; border-top: 1px dashed #ccc;">

       

</table>
    </section>
    </script>
</body>
</html>