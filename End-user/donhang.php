<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user'])) {
    header('location:trangdangnhap.php');
    exit();
}

$user_session = $_SESSION['user'];
// Tìm MaTK từ Session (Dùng Email hoặc HoTen tùy dữ liệu của bạn)
$sql_user = "SELECT MaTK FROM taikhoan WHERE Email = '$user_session' OR HoTen = '$user_session' LIMIT 1";
$res_user = mysqli_query($conn, $sql_user);
$row_user = mysqli_fetch_assoc($res_user);
$maTK = $row_user['MaTK'];

// Truy vấn danh sách đơn hàng
$sql_dh = "SELECT dh.MaDH, dh.NgayDat, dh.TongTien, dh.TrangThai 
           FROM donhang dh 
           WHERE dh.MaTK = '$maTK' 
           ORDER BY dh.NgayDat DESC";
$res_dh = mysqli_query($conn, $sql_dh);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đơn hàng của tôi - TechZone</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="donhang.css"> </head>
<body>
    <?php include 'header.php'; ?>

    <section class="order-container">
        <h1 class="title">Đơn hàng của tôi</h1>

        <?php if (mysqli_num_rows($res_dh) > 0): ?>
            <?php while ($dh = mysqli_fetch_assoc($res_dh)): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span>Mã đơn: #<?php echo $dh['MaDH']; ?></span>
                        <span class="order-date">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($dh['NgayDat'])); ?></span>
                        <span class="order-status"><?php echo $dh['TrangThai']; ?></span>
                    </div>
                    
                    <div class="order-body">
                        <?php
                        $maDH = $dh['MaDH'];
                        $sql_ct = "SELECT sp.TenSP, sp.HinhAnh, ctdh.SoLuongMua, ctdh.GiaBan 
                                   FROM chitietdonhang ctdh 
                                   JOIN sanpham sp ON ctdh.MaSP = sp.MaSP 
                                   WHERE ctdh.MaDH = $maDH";
                        $res_ct = mysqli_query($conn, $sql_ct);
                        while ($ct = mysqli_fetch_assoc($res_ct)):
                        ?>
                        <div class="product-item">
                            <img src="../Admin/Image/<?php echo $ct['HinhAnh']; ?>" alt="">
                            <div class="product-info">
                                <h3><?php echo $ct['TenSP']; ?></h3>
                                <p>Số lượng: x<?php echo $ct['SoLuongMua']; ?></p>
                            </div>
                            <div class="product-price">
                                <?php echo number_format($ct['GiaBan'], 0, ',', '.'); ?> ₫
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="order-footer">
                        <div class="total-label">Tổng số tiền:</div>
                        <div class="total-amount"><?php echo number_format($dh['TongTien'], 0, ',', '.'); ?> ₫</div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-msg">Bạn chưa có đơn hàng nào. <a href="trangchu.php">Mua sắm ngay!</a></div>
        <?php endif; ?>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>