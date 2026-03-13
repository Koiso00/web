<?php 
session_start(); 
include 'connect.php'; // Nhúng file kết nối DB

// --- ĐẾM SỐ LƯỢNG GIỎ HÀNG KHI LOAD TRANG ---
$tong_gio_hang = 0;
if(isset($_SESSION['giohang'])) {
    foreach($_SESSION['giohang'] as $soluong) {
        $tong_gio_hang += $soluong;
    }
}

// --- XỬ LÝ URL: Lấy mã loại sản phẩm ---
$ma_loai = isset($_GET['loai']) ? (int)$_GET['loai'] : 1;

// Lấy tên danh mục để in ra tiêu đề
$sql_tenloai = "SELECT TenLoai FROM LoaiSanPham WHERE MaLoai = $ma_loai";
$result_tenloai = mysqli_query($conn, $sql_tenloai);
$row_tenloai = mysqli_fetch_assoc($result_tenloai);
$ten_danh_muc = $row_tenloai ? $row_tenloai['TenLoai'] : "Sản phẩm";

// --- XỬ LÝ PHÂN TRANG ---
$limit = 6; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total FROM SanPham WHERE MaLoai = $ma_loai AND HienTrang = 1";
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechZone | <?php echo $ten_danh_muc; ?></title>
    <link rel="shortcut icon" href="picture/png-transparent-laptop-computer-icons-computer-desktop-pc-electronics-rectangle-computer.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="styleforpr.css">
</head>
<body>
    <header>
        <a href="trangchu.php" class="logo">TechZone</a>

        <div class="search-bar" role="search" aria-label="Search site">
            <input type="checkbox" id="menu-toggle" hidden>
            <label for="menu-toggle" class="menu-btn" aria-hidden="true">
                <img src="picture/menu-burger.png" class="menu-icon" alt="menu">
            </label>

            <div class="dropdown-content" aria-hidden="true">
                <a href="sanpham.php?loai=1">Bàn phím</a>
                <a href="sanpham.php?loai=2">Chuột</a>
                <a href="sanpham.php?loai=3">Màn hình</a>
                <a href="sanpham.php?loai=4">Tai nghe</a>
            </div>

            <form action="timkiem.php" method="GET" class="search-form">
                <label for="search-box" class="visually-hidden">Tìm kiếm</label>
                <input type="search" id="search-box" name="q" placeholder="Tìm kiếm sản phẩm, thương hiệu..." aria-label="Tìm kiếm" autocomplete="off">
                <button type="submit" class="search-submit" aria-label="Tìm kiếm">
                    <img src="picture/magnifying-glass.png" alt="">
                </button>
            </form>
        </div>

        <nav class="navbar">
            <a href="trangchu.php">Trang chủ</a> 
            <a href="#">Sản Phẩm</a> 
            <a href="#">Liên hệ</a>     
        </nav>

        <div class="icon" style="display: flex; align-items: center;">
        <div style="position: relative; margin-right: 15px;">
            <a href="giohang.php" class="shopping-cart">
            <img src="picture/shopping.png" alt="Giỏ hàng">
            </a>
            <span id="cart-count" style="position: absolute; top: -5px; right: -10px; background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold; line-height: 1;">
            <?php echo isset($tong_gio_hang) ? $tong_gio_hang : 0; ?>
            </span>
        </div>

        <div class="user"><?php if(isset($_SESSION['MaTK'])): ?><a href="suathongtin.php"><img src="picture/user.png" alt="Người dùng" style="margin-right: 5px;"><span><?php echo htmlspecialchars($_SESSION['HoTen']); ?></span></a><a href="dangxuat.php" class="dangky" style="margin-left: 10px;">Thoát</a><?php else: ?><a href="trangdangnhap.php"><img src="picture/user.png" alt="Người dùng" style="margin-right: 5px;"><span>Đăng nhập</span></a><a href="trangdangki.php" class="dangky" style="margin-left: 10px;">Đăng ký</a><?php endif; ?></div>
        </div>
        </div>
    </header>

    <main class="container">
        <br>
        <div class="layout">
            <aside class="sidebar">
                <h3>Mức giá</h3>
                <label><input type="checkbox"> Dưới 500k</label><br>
                <label><input type="checkbox"> 500k - 1 triệu</label><br>
                <label><input type="checkbox"> 1 triệu - 2 triệu</label><br>
                <label><input type="checkbox"> Trên 2 triệu</label><br><br>
          
                <h3>Hãng</h3>
                <label><input type="checkbox"> Logitech</label><br>
                <label><input type="checkbox"> Razer</label><br>
                <label><input type="checkbox"> Corsair</label><br>
                <label><input type="checkbox"> AKKO</label><br>
                <label><input type="checkbox"> MSI</label><br>
            </aside>
    
            <section class="products">
                <h1>Keyboard | <?php echo $ten_danh_muc; ?></h1>
                <br>
                
                <div class="grid">
                    <?php 
                    $sql = "SELECT * FROM SanPham WHERE MaLoai = $ma_loai AND HienTrang = 1 LIMIT $limit OFFSET $offset";
                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $gia_ban = $row['GiaNhapBinhQuan'] * (1 + $row['TiLeLoiNhuan']);
                    ?>
                    <article class="card">
                        <div class="img">
                            <a href="thongtinsanpham.php?id=<?php echo $row['MaSP']; ?>">
                                <img src="picture/<?php echo $row['HinhAnh']; ?>" alt="">
                            </a>
                        </div>
                        <h3><?php echo htmlspecialchars($row['TenSP']); ?></h3>
                        <div class="price"><?php echo number_format($gia_ban, 0, ',', '.'); ?> ₫</div>
                        <div class="actions">
                            <a class="btn btn-primary" href="thongtinsanpham.php?id=<?php echo $row['MaSP']; ?>">Chi tiết</a>
                            <button class="btn btn-outline btn-add-cart" data-id="<?php echo $row['MaSP']; ?>">Thêm vào giỏ</button>
                        </div>
                    </article>
                    <?php 
                        } 
                    } else {
                        echo "<p>Hiện tại chưa có sản phẩm nào trong danh mục này.</p>";
                    }
                    ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?loai=<?php echo $ma_loai; ?>&page=<?php echo $page - 1; ?>" class="prev">« Trở về </a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?loai=<?php echo $ma_loai; ?>&page=<?php echo $i; ?>" class="page <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?loai=<?php echo $ma_loai; ?>&page=<?php echo $page + 1; ?>" class="next">Tiếp tục »</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <script>
    // Tìm tất cả các nút "Thêm vào giỏ"
    const addCartBtns = document.querySelectorAll('.btn-add-cart');

    addCartBtns.forEach(button => {
        button.addEventListener('click', function() {
            // Lấy mã sản phẩm từ thuộc tính data-id
            const sanPhamId = this.getAttribute('data-id');

            // Đóng gói dữ liệu để gửi đi
            let formData = new FormData();
            formData.append('id', sanPhamId);

            // Gửi yêu cầu ngầm đến file themgiohang.php
            fetch('themgiohang.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Chờ PHP trả lời và dịch nó ra
            .then(data => {
                if(data.status === 'success') {
                    // CẬP NHẬT CON SỐ LÊN CÁI BONG BÓNG ĐỎ
                    document.getElementById('cart-count').innerText = data.tong_mon;
                    
                    // Hiện một thông báo nhỏ cho vui (k vui thì tắt)
                    alert('Đã thêm sản phẩm vào giỏ hàng!');
                }
            })
            .catch(error => console.error('Lỗi:', error));
        });
    });
</script>
</body>
</html>