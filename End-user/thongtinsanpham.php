<?php
session_start();
include "config.php";

if (!isset($_GET['id'])) {
    echo "Không tìm thấy sản phẩm";
    exit();
}

$id = $_GET['id'];

// Lấy thông tin sản phẩm chính
$stmt = $conn->prepare("SELECT * FROM SanPham WHERE MaSP=?");
$stmt->execute([$id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sp) {
    echo "Sản phẩm không tồn tại";
    exit();
}

/* Tính giá bán dựa trên tỉ lệ lợi nhuận */
$gia = $sp['GiaNhapBinhQuan'] + ($sp['GiaNhapBinhQuan'] * $sp['TiLeLoiNhuan']);

/* Lấy 4 sản phẩm liên quan cùng loại */
$stmt2 = $conn->prepare("SELECT * FROM SanPham WHERE MaLoai=? AND MaSP!=? LIMIT 4");
$stmt2->execute([$sp['MaLoai'], $id]);
$lienquan = $stmt2->fetchAll(PDO::FETCH_ASSOC);

/* Logic tính tổng số lượng sản phẩm trong giỏ hàng để hiển thị trên Header */
$tong_so_luong = 0;
if (isset($_SESSION['giohang'])) {
    foreach ($_SESSION['giohang'] as $sl) {
        $tong_so_luong += $sl;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sp['TenSP']); ?></title>
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
                <input type="search" name="q" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
                <button type="submit" class="search-submit">
                    <img src="picture/magnifying-glass.png" alt="Tìm kiếm">
                </button>
            </form>
        </div>

        <nav class="navbar">
            <a href="trangchu.php">Trang chủ</a>
            <a href="sanpham.php">Sản Phẩm</a>
            <a href="#bottom">Liên hệ</a>
        </nav>

        <div class="icon" style="display: flex; align-items: center;">
            <div style="position: relative; margin-right: 15px;">
                <a href="giohang.php" class="shopping-cart">
                    <img src="picture/shopping.png" alt="Giỏ hàng">
                </a>
                <span id="cart-count" style="position: absolute; top: -5px; right: -10px; background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; line-height: 1;">
                    <?php echo $tong_so_luong; ?>
                </span>
            </div>

            <div class="user">
                <?php if(isset($_SESSION['user'])): ?>
                    <a href="#">
                        <img src="picture/user.png">
                        <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
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
            <img src="../Admin/Image/<?php echo $sp['HinhAnh']; ?>" alt="<?php echo $sp['TenSP']; ?>">
        </div>

        <div class="product-right">
            <h1><?php echo htmlspecialchars($sp['TenSP']); ?></h1>
            <div class="price"><?php echo number_format($gia, 0, ',', '.'); ?>đ</div>
            
            <p class="status">
                <?php echo ($sp['SoLuongTon'] > 0) ? "<span class='conhang'>Còn hàng</span>" : "<span class='hethang'>Hết hàng</span>"; ?>
            </p>

            <div class="buttons">
                <form action="themvaogio.php" method="POST" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $sp['MaSP']; ?>">
                    <button type="submit" class="cart">Thêm vào giỏ</button>
                </form>
                <button class="buy">Mua ngay</button>
            </div>

            <div class="info">
                <h3>Thông số kỹ thuật</h3>
                <ul>
                    <li>Đơn vị: <?php echo htmlspecialchars($sp['DonViTinh']); ?></li>
                    <li>Số lượng tồn: <?php echo $sp['SoLuongTon']; ?></li>
                    <li>Mô tả: <?php echo nl2br(htmlspecialchars($sp['MoTa'])); ?></li>
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
                        <h3><?php echo htmlspecialchars($sp2['TenSP']); ?></h3>
                        <p><?php echo number_format($gia2, 0, ',', '.'); ?>đ</p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer id="bottom">
        <p style="text-align: center; padding: 20px;">&copy; 2026 TechZone - Hệ thống cửa hàng công nghệ</p>
    </footer>
</body>
</html>