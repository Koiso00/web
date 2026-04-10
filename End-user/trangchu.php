<?php
session_start();
include 'connect.php'; // Đổi lại dùng connect.php cho đồng bộ với các trang khác

// 1. Hàm tính giá bán theo chuẩn FIFO (Có fallback về giá bình quân như đã thống nhất)
function getGiaBanFIFO($conn, $maSP, $tiLeLoiNhuan, $giaNhapBinhQuan)
{
    $sql_fifo = "SELECT ctpn.GiaNhap 
                 FROM chitietphieunhap ctpn 
                 JOIN phieunhap pn ON ctpn.MaPN = pn.MaPN 
                 WHERE ctpn.MaSP = $maSP 
                 AND ctpn.SoLuongNhap > 0 
                 ORDER BY pn.NgayNhap ASC 
                 LIMIT 1";

    $result_fifo = mysqli_query($conn, $sql_fifo);

    if ($row_fifo = mysqli_fetch_assoc($result_fifo)) {
        $giaNhapLieu = $row_fifo['GiaNhap'];
        return $giaNhapLieu * (1 + $tiLeLoiNhuan);
    }

    if ($giaNhapBinhQuan > 0) {
        return $giaNhapBinhQuan * (1 + $tiLeLoiNhuan);
    }
    return 0;
}

// 2. --- ĐẾM SỐ LƯỢNG GIỎ HÀNG KHI LOAD TRANG ---
$tong_gio_hang = 0;
if (isset($_SESSION['giohang'])) {
    foreach ($_SESSION['giohang'] as $soluong) {
        $tong_gio_hang += $soluong;
    }
}

// 3. Lấy 12 sản phẩm mới nhất đang bán (Chuyển sang dùng mysqli)
$sql = "SELECT * FROM SanPham WHERE HienTrang = 1 ORDER BY MaSP DESC LIMIT 12";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechZone - Phụ kiện máy tính</title>
    <link rel="shortcut icon" href="picture/logo.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<body>
<?php 
    // Gọi file header vào đây
    include 'header.php'; 
    ?>

    <section class="home" id="home">
        <div class="content">
            <h3>TechZone</h3>
            <span>Chất lượng , chuyên nghiệp</span>
            <p>Techzone với chất lượng sản phẩm luôn được đặt lên hàng đầu. Chúng tôi cam kết không bán hàng kém chất lượng.</p>
            <a href="#products" class="btn">Mua ngay</a>
        </div>
    </section>

    <section class="icons-container">
        <div class="icon">
            <img src="picture/hinh1.png" style="width:20%">
            <div class="info">
                <h3>Miễn phí vận chuyển</h3>
                <span>trên toàn bộ đơn hàng</span>
            </div>
        </div>
        <div class="icon">
            <img src="picture/hinh2.png" style="width:20%">
            <div class="info">
                <h3>Trả hàng trong 30 ngày</h3>
                <span>đảm bảo hoàn tiền</span>
            </div>
        </div>
        <div class="icon">
            <img src="picture/hinh3.png" style="width:20%">
            <div class="info">
                <h3>Voucher hấp dẫn</h3>
                <span>cho mọi đơn hàng</span>
            </div>
        </div>
        <div class="icon">
            <img src="picture/hinh4.png" style="width:20%">
            <div class="info">
                <h3>Hàng chính hãng</h3>
                <span>đảm bảo 100%</span>
            </div>
        </div>
    </section>

    <section class="products" id="products">
        <h1 class="heading">Latest <span>Products</span></h1>
        <br>

        <div class="box-content">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // SỬ DỤNG HÀM TÍNH GIÁ FIFO
                    $gia_ban = getGiaBanFIFO($conn, $row['MaSP'], $row['TiLeLoiNhuan'], $row['GiaNhapBinhQuan']);
                    $con_hang = ((int)($row['SoLuongTon'] ?? 0) > 0);
            ?>
                    <div class="box">
                        <div class="image">
                            <a href="thongtinsanpham.php?id=<?php echo $row['MaSP']; ?>">
                                <img src="../Admin/Image/<?php echo $row['HinhAnh']; ?>" alt="">
                            </a>
                        </div>

                        <div class="name-product">
                            <h3><?php echo htmlspecialchars($row['TenSP']); ?></h3>

                            <div class="price">
                                <?php
                                if ($con_hang) {
                                    echo number_format($gia_ban, 0, ',', '.') . ' ₫';
                                } else {
                                    echo '<span style="color:red; font-size: 14px; font-weight: bold;">Tạm hết hàng</span>';
                                }
                                ?>
                            </div>
                        </div>
                        
                    </div>
            <?php
                }
            } else {
                echo "<p>Hiện tại chưa có sản phẩm nào trong danh mục này.</p>";
            }
            ?>
        </div>
    </section>

    <footer id="bottom">
        <section class="footer">
            <div class="footer-box">
                <ul>
                    <li><b>Dịch vụ khách hàng</b></li>
                    <li>Trung tâm trợ giúp</li>
                    <li>Hướng dẫn mua hàng</li>
                    <li>Đơn hàng</li>
                    <li>Trả hàng / hoàn tiền</li>
                    <li>Chính sách bảo hành</li>
                </ul>
            </div>

            <div class="footer-box">
                <ul>
                    <li><b>TechZone Việt Nam</b></li>
                    <li>Về TechZone</li>
                    <li>Tuyển dụng</li>
                    <li>Điều khoản</li>
                    <li>Chính sách bảo mật</li>
                </ul>
            </div>

            <div class="footer-box">
                <ul>
                    <li><b>Thanh toán</b></li>
                </ul>
                <div class="payment">
                    <table>
                        <tr>
                            <td><img src="picture/thanhtoan1.png"></td>
                            <td><img src="picture/thanhtoan2.png"></td>
                            <td><img src="picture/thanhtoan3.png"></td>
                            <td><img src="picture/thanhtoan7.png"></td>
                        </tr>
                        <tr>
                            <td><img src="picture/thanhtoan4.png"></td>
                            <td><img src="picture/thanhtoan5.png"></td>
                            <td><img src="picture/thanhtoan6.png"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </section>
    </footer>

</body>

</html>