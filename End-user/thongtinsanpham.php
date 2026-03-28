<?php
session_start();
include "config.php";

// 1. --- ĐẾM SỐ LƯỢNG GIỎ HÀNG KHI LOAD TRANG (Sửa lỗi hiển thị bằng 0) ---
$tong_gio_hang = 0;
if (isset($_SESSION['giohang'])) {
    foreach ($_SESSION['giohang'] as $soluong) {
        $tong_gio_hang += (int)$soluong;
    }
}

// 2. --- KIỂM TRA ID SẢN PHẨM ---
if (!isset($_GET['id'])) {
    echo "Không tìm thấy sản phẩm";
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM SanPham WHERE MaSP=?");
$stmt->execute([$id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sp) {
    echo "Sản phẩm không tồn tại";
    exit();
}

/* tính giá bán */
$gia = $sp['GiaNhapBinhQuan'] + ($sp['GiaNhapBinhQuan'] * $sp['TiLeLoiNhuan']);

/* sản phẩm liên quan */
$stmt2 = $conn->prepare("SELECT * FROM SanPham 
WHERE MaLoai=? AND MaSP!=? LIMIT 4");
$stmt2->execute([$sp['MaLoai'], $id]);
$lienquan = $stmt2->fetchAll(PDO::FETCH_ASSOC);
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
    <header>
        <a href="trangchu.php" class="logo">TechZone</a>

        <div class="search-bar" role="search">
            <input type="checkbox" id="menu-toggle" hidden>
            <label for="menu-toggle" class="menu-btn">
                <img src="picture/menu-burger.png" class="menu-icon" alt="menu">
            </label>

            <div class="dropdown-content" aria-hidden="true">
                <a href="sanpham.php?loai=1">Bàn phím</a>
                <a href="sanpham.php?loai=2">Chuột</a>
                <a href="sanpham.php?loai=3">Màn hình</a>
                <a href="sanpham.php?loai=4">Tai nghe</a>
            </div>

            <form action="timkiem.php" method="GET" class="search-form">
                <input type="search" name="q" placeholder="Tìm kiếm sản phẩm, thương hiệu..." autocomplete="off">
                <button type="submit" class="search-submit">
                    <img src="picture/magnifying-glass.png" alt="Tìm kiếm">
                </button>
            </form>
        </div>

        <nav class="navbar">
            <a href="trangchu.php">Trang chủ</a>
            <a href="sanpham.php?loai=1">Sản Phẩm</a>
            <a href="#bottom">Liên hệ</a>
        </nav>

        <div class="icon" style="display: flex; align-items: center;">
            <div style="position: relative; margin-right: 15px;">
                <a href="giohang.php" class="shopping-cart">
                    <img src="picture/shopping.png" alt="Giỏ hàng">
                </a>
                <span id="cart-count" style="position: absolute; top: -5px; right: -10px; background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; line-height: 1;">
                    <?php echo $tong_gio_hang; ?>
                </span>
            </div>

            <div class="user">
                <?php if(isset($_SESSION['user'])): ?>
                    <a href="#">
                        <img src="picture/user.png">
                        <span><?php echo $_SESSION['user']; ?></span>
                    </a>
                    <a href="xuly_dangxuat.php" class="dangky">Đăng xuất</a>
                <?php else: ?>
                    <a href="trangdangnhap.php">
                        <img src="picture/user.png">
                        <span>Đăng nhập</span>
                    </a>
                    <a href="trangdangki.php" class="dangky">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

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
                $gia2 = $sp2['GiaNhapBinhQuan'] + ($sp2['GiaNhapBinhQuan'] * $sp2['TiLeLoiNhuan']);
            ?>
                <div class="card">
                    <a href="thongtinsanpham.php?id=<?php echo $sp2['MaSP']; ?>">
                        <img src="../Admin/Image/<?php echo $sp2['HinhAnh']; ?>">
                        <h3><?php echo $sp2['TenSP']; ?></h3>
                        <p><?php echo number_format($gia2); ?>đ</p>
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