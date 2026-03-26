<?php
session_start();
include 'connect.php'; // Nhúng file kết nối DB

// 1. Hàm tính giá bán theo chuẩn FIFO (Kèm fallback)
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

// 3. --- LẤY TỪ KHÓA TÌM KIẾM TỪ URL ---
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$keyword_safe = mysqli_real_escape_string($conn, $keyword);

// 4. --- XỬ LÝ PHÂN TRANG VÀ ĐIỀU KIỆN LỌC TÌM KIẾM ---
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Điều kiện cơ bản: Đang bán
$where_clause = "HienTrang = 1";

// RẤT QUAN TRỌNG: Lọc theo Tên Sản Phẩm chứa từ khóa
if ($keyword_safe != '') {
    $where_clause .= " AND TenSP LIKE '%$keyword_safe%'";
}

// Nếu người dùng có chọn Hãng (Mảng hang[]) từ Sidebar
if (!empty($_GET['hang'])) {
    $hang_conditions = [];
    foreach ($_GET['hang'] as $hang) {
        $hang_safe = mysqli_real_escape_string($conn, $hang);
        $hang_conditions[] = "TenSP LIKE '%$hang_safe%'";
    }
    $where_clause .= " AND (" . implode(" OR ", $hang_conditions) . ")";
}

// 3. XỬ LÝ LỌC GIÁ (Kết hợp cả Radio Button và Ô nhập tay)
$min_price = -1;
$max_price = -1;

// Ưu tiên 1: Kiểm tra xem người dùng CÓ TỰ NHẬP số vào ô "Từ... Đến..." hay không
if ((isset($_GET['gia_min']) && $_GET['gia_min'] !== '') || (isset($_GET['gia_max']) && $_GET['gia_max'] !== '')) {
    
    $min_price = (isset($_GET['gia_min']) && $_GET['gia_min'] !== '') ? (int)$_GET['gia_min'] : 0;
    $max_price = (isset($_GET['gia_max']) && $_GET['gia_max'] !== '') ? (int)$_GET['gia_max'] : 999999999;

} 
// Ưu tiên 2: Nếu khách không nhập tay, thì kiểm tra xem khách có bấm nút Radio không
elseif (!empty($_GET['gia'])) {
    
    $range = explode('-', $_GET['gia']); 
    if (count($range) == 2) {
        $min_price = (int)$range[0];
        $max_price = (int)$range[1];
    }
}

// Nếu lấy được mức giá (từ 1 trong 2 cách trên) thì đưa vào câu lệnh SQL
if ($min_price >= 0 && $max_price >= 0) {
    $where_clause .= " AND (GiaNhapBinhQuan * (1 + TiLeLoiNhuan)) BETWEEN $min_price AND $max_price";
}

// Đếm tổng số sản phẩm SAU KHI LỌC để chia trang cho đúng
$sql_count = "SELECT COUNT(*) as total FROM SanPham WHERE $where_clause";
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

// Câu truy vấn lấy dữ liệu để hiển thị
$sql = "SELECT * FROM SanPham WHERE $where_clause LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm: <?php echo htmlspecialchars($keyword); ?> | TechZone</title>
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
                <input type="search" id="search-box" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tìm kiếm sản phẩm, thương hiệu..." aria-label="Tìm kiếm" autocomplete="off">
                <button type="submit" class="search-submit" aria-label="Tìm kiếm">
                    <img src="picture/magnifying-glass.png" alt="">
                </button>
            </form>
        </div>

        <nav class="navbar">
            <a href="trangchu.php">Trang chủ</a>
            <a href="sanpham.php?loai=1">Sản Phẩm</a>
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

            <div class="user">
                <?php if (isset($_SESSION['MaTK'])): ?>
                    <a href="suathongtin.php"><img src="picture/user.png" alt="Người dùng" style="margin-right: 5px;"><span><?php echo htmlspecialchars($_SESSION['HoTen']); ?></span></a>
                    <a href="dangxuat.php" class="dangky" style="margin-left: 10px;">Thoát</a>
                <?php else: ?>
                    <a href="trangdangnhap.php"><img src="picture/user.png" alt="Người dùng" style="margin-right: 5px;"><span>Đăng nhập</span></a>
                    <a href="trangdangki.php" class="dangky" style="margin-left: 10px;">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <br>
        <div class="layout">

            <aside class="sidebar">
                <form action="timkiem.php" method="GET" id="filter-form">
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($keyword); ?>">

                    <h3>Hãng sản xuất</h3>
                    <label><input type="checkbox" name="hang[]" value="Logitech" <?php echo (isset($_GET['hang']) && in_array('Logitech', $_GET['hang'])) ? 'checked' : ''; ?>> Logitech</label><br>
                    <label><input type="checkbox" name="hang[]" value="Razer" <?php echo (isset($_GET['hang']) && in_array('Razer', $_GET['hang'])) ? 'checked' : ''; ?>> Razer</label><br>
                    <label><input type="checkbox" name="hang[]" value="Corsair" <?php echo (isset($_GET['hang']) && in_array('Corsair', $_GET['hang'])) ? 'checked' : ''; ?>> Corsair</label><br>
                    <label><input type="checkbox" name="hang[]" value="AKKO" <?php echo (isset($_GET['hang']) && in_array('AKKO', $_GET['hang'])) ? 'checked' : ''; ?>> AKKO</label><br>
                    <label><input type="checkbox" name="hang[]" value="MSI" <?php echo (isset($_GET['hang']) && in_array('MSI', $_GET['hang'])) ? 'checked' : ''; ?>> MSI</label><br><br>

                    <h3>Mức giá</h3>
                    <label><input type="radio" name="gia" value="0-500000" <?php echo (isset($_GET['gia']) && $_GET['gia'] == '0-500000') ? 'checked' : ''; ?>> Dưới 500k</label><br>
                    <label><input type="radio" name="gia" value="500000-1000000" <?php echo (isset($_GET['gia']) && $_GET['gia'] == '500000-1000000') ? 'checked' : ''; ?>> 500k - 1 triệu</label><br>
                    <label><input type="radio" name="gia" value="1000000-2000000" <?php echo (isset($_GET['gia']) && $_GET['gia'] == '1000000-2000000') ? 'checked' : ''; ?>> 1 triệu - 2 triệu</label><br>
                    <label><input type="radio" name="gia" value="2000000-999999999" <?php echo (isset($_GET['gia']) && $_GET['gia'] == '2000000-999999999') ? 'checked' : ''; ?>> Trên 2 triệu</label><br>
                    <label><input type="radio" name="gia" value="" <?php echo (empty($_GET['gia'])) ? 'checked' : ''; ?>> <i>Tất cả mức giá</i></label><br><br>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                        <input type="number" name="gia_min" placeholder="Từ..." min="0" value="<?php echo isset($_GET['gia_min']) ? htmlspecialchars($_GET['gia_min']) : ''; ?>" style="width: 45%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
                        <span> - </span>
                        <input type="number" name="gia_max" placeholder="Đến..." min="0" value="<?php echo isset($_GET['gia_max']) ? htmlspecialchars($_GET['gia_max']) : ''; ?>" style="width: 45%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; cursor: pointer;">Lọc kết quả</button>
                </form>
            </aside>

            <section class="products">
                <h1 style="font-size: 24px; padding-bottom: 15px; border-bottom: 2px solid #eee;">
                    Kết quả tìm kiếm cho: <span style="color: #007bff;">"<?php echo htmlspecialchars($keyword); ?>"</span>
                </h1>
                <br>

                <div class="grid">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $gia_ban = getGiaBanFIFO($conn, $row['MaSP'], $row['TiLeLoiNhuan'], $row['GiaNhapBinhQuan']);
                    ?>
                            <article class="card">
                                <div class="img">
                                    <a href="thongtinsanpham.php?id=<?php echo $row['MaSP']; ?>">
                                        <img src="../Admin/Image/<?php echo $row['HinhAnh']; ?>" alt="">
                                    </a>
                                </div>
                                <h3><?php echo htmlspecialchars($row['TenSP']); ?></h3>
                                <div class="price">
                                    <?php
                                    if ($gia_ban > 0) {
                                        echo number_format($gia_ban, 0, ',', '.') . ' ₫';
                                    } else {
                                        echo '<span style="color:red; font-size: 14px; font-weight: bold;">Tạm hết hàng</span>';
                                    }
                                    ?>
                                </div>
                                <div class="actions">
                                    <a class="btn btn-primary" href="thongtinsanpham.php?id=<?php echo $row['MaSP']; ?>">Chi tiết</a>
                                    <button class="btn btn-outline btn-add-cart" data-id="<?php echo $row['MaSP']; ?>">Thêm vào giỏ</button>
                                </div>
                            </article>
                    <?php
                        }
                    } else {
                        // GIAO DIỆN KHÔNG TÌM THẤY RẤT ĐẸP MẮT
                        echo '<div style="text-align: center; width: 100%; grid-column: 1 / -1; padding: 60px 20px; background: #f9f9f9; border-radius: 8px;">';
                        echo '<h3 style="color: #333; font-size: 22px; margin-bottom: 10px;">Rất tiếc, không tìm thấy sản phẩm nào!</h3>';
                        echo '<p style="color: #666; font-size: 16px;">Chúng tôi không tìm thấy kết quả nào phù hợp với từ khóa <b>"' . htmlspecialchars($keyword) . '"</b> của bạn.</p>';
                        echo '<p style="color: #888; font-size: 14px; margin-top: 5px;">Vui lòng thử lại bằng từ khóa chung chung hơn (ví dụ: chuột, bàn phím, akko...)</p>';
                        echo '<a href="sanpham.php?loai=1" class="btn btn-primary" style="margin-top: 20px; display: inline-block; padding: 10px 20px; text-decoration: none;">Xem các sản phẩm khác</a>';
                        echo '</div>';
                    }
                    ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        // Tự động giữ lại các bộ lọc (q, hang, gia) trên URL
                        $current_get = $_GET;
                        unset($current_get['page']);
                        $url_query = http_build_query($current_get);
                        ?>

                        <?php if ($page > 1): ?>
                            <a href="?<?php echo $url_query; ?>&page=<?php echo $page - 1; ?>" class="prev">« Trở về </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?<?php echo $url_query; ?>&page=<?php echo $i; ?>" class="page <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo $url_query; ?>&page=<?php echo $page + 1; ?>" class="next">Tiếp tục »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <script>
        // Xử lý thêm vào giỏ hàng
        const addCartBtns = document.querySelectorAll('.btn-add-cart');
        addCartBtns.forEach(button => {
            button.addEventListener('click', function() {
                const sanPhamId = this.getAttribute('data-id');
                let formData = new FormData();
                formData.append('id', sanPhamId);

                fetch('themgiohang.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById('cart-count').innerText = data.tong_mon;
                            alert('Đã thêm sản phẩm vào giỏ hàng!');
                        }
                    })
                    .catch(error => console.error('Lỗi:', error));
            });
        });
    </script>
</body>

</html>