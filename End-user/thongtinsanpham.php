<?php
session_start();
include "connect.php";

// 1. Hàm tính giá bán theo chuẩn FIFO (Giống trang chủ)
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

// 2. --- ĐẾM SỐ LƯỢNG GIỎ HÀNG KHI LOAD TRANG (Sửa lỗi hiển thị bằng 0) ---
$tong_gio_hang = 0;
if (isset($_SESSION['giohang'])) {
    foreach ($_SESSION['giohang'] as $soluong) {
        $tong_gio_hang += (int)$soluong;
    }
}

// 3. --- KIỂM TRA ID SẢN PHẨM ---
if (!isset($_GET['id'])) {
    echo "Không tìm thấy sản phẩm";
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM SanPham WHERE MaSP = $id";
$result = mysqli_query($conn, $sql);
$sp = mysqli_fetch_assoc($result);

if (!$sp) {
    echo "Sản phẩm không tồn tại";
    exit();
}

/* tính giá bán */
$gia = getGiaBanFIFO($conn, $id, $sp['TiLeLoiNhuan'], $sp['GiaNhapBinhQuan']);

/* sản phẩm liên quan */
$maLoai = $sp['MaLoai'];
$sql2 = "SELECT * FROM SanPham WHERE MaLoai = $maLoai AND MaSP != $id LIMIT 4";
$result2 = mysqli_query($conn, $sql2);
$lienquan = [];
while ($row = mysqli_fetch_assoc($result2)) {
    $lienquan[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sp['TenSP']; ?></title>
    <link rel="stylesheet" href="trangthongtinsanpham.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <section class="product-container">
        <div class="product-left">
            <img src="../Admin/Image/<?php echo $sp['HinhAnh']; ?>">
        </div>

        <div class="product-right">
            <h1><?php echo $sp['TenSP']; ?></h1>
            <div class="price">
                <?php echo number_format($gia); ?>đ
            </div>

            <p class="status">
                <?php
                if ($sp['SoLuongTon'] > 0) {
                    echo "<span class='conhang'>Còn hàng</span>";
                } else {
                    echo "<span class='hethang'>Hết hàng</span>";
                }
                ?>
            </p>

            <div class="buttons">
                <button class="cart btn-add-cart" data-id="<?php echo $sp['MaSP']; ?>">Thêm vào giỏ</button>
                <button class="buy btn-buy-now" data-id="<?php echo $sp['MaSP']; ?>">Mua ngay</button>
            </div>

            <div class="info">
                <h3>Thông số kỹ thuật</h3>
                <ul>
                    <li>Đơn vị: <?php echo $sp['DonViTinh']; ?></li>
                    <li>Số lượng tồn: <?php echo $sp['SoLuongTon']; ?></li>
                    <li>Mô tả: <?php echo $sp['MoTa']; ?></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="related">
        <h2>Sản Phẩm Liên Quan</h2>
        <div class="grid">
            <?php foreach ($lienquan as $sp2): 
                $gia2 = getGiaBanFIFO($conn, $sp2['MaSP'], $sp2['TiLeLoiNhuan'], $sp2['GiaNhapBinhQuan']);
            ?>
                <div class="card">
                    <a href="thongtinsanpham.php?id=<?php echo $sp2['MaSP']; ?>">
                        <img src="../Admin/Image/<?php echo $sp2['HinhAnh']; ?>">
                        <h3><?php echo $sp2['TenSP']; ?></h3>
                        <p><?php echo $gia2 > 0 ? number_format($gia2, 0, ',', '.') . 'đ' : '<span style="color:red; font-size:14px; font-weight:bold;">Tạm hết hàng</span>'; ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
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
                <ul><li><b>Thanh toán</b></li></ul>
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

    <script>
        // Hàm xử lý chung cho cả 2 nút
        function handleAddToCart(productId, isRedirect) {
            let formData = new FormData();
            formData.append('id', productId);

            fetch('themgiohang.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Cập nhật số lượng trên Header
                        document.getElementById('cart-count').innerText = data.tong_mon;
                        
                        if (isRedirect) {
                            // Nếu là "Mua ngay" -> Chuyển hướng sang trang giỏ hàng
                            window.location.href = 'giohang.php';
                        } else {
                            // Nếu là "Thêm vào giỏ" -> Thông báo thành công
                            alert('Đã thêm sản phẩm vào giỏ hàng thành công!');
                        }
                    } else {
                        alert('Có lỗi xảy ra, vui lòng thử lại.');
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    alert('Không thể kết nối tới máy chủ.');
                });
        }

        // Sự kiện cho nút THÊM VÀO GIỎ
        document.querySelectorAll('.btn-add-cart').forEach(button => {
            button.addEventListener('click', function() {
                const sanPhamId = this.getAttribute('data-id');
                handleAddToCart(sanPhamId, false);
            });
        });

        // Sự kiện cho nút MUA NGAY
        document.querySelectorAll('.btn-buy-now').forEach(button => {
            button.addEventListener('click', function() {
                const sanPhamId = this.getAttribute('data-id');
                handleAddToCart(sanPhamId, true);
            });
        });
    </script>
</body>
</html>