<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechZone | Phụ kiện máy tính gần đây</title>
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
                <a href="sanpham-banphim-chuadangnhap.php">Bàn phím</a>
                <a href="sanpham-chuot-chuadangnhap.php">Chuột</a>
                <a href="sanpham-banphim-chuadangnhap.php">Tai nghe</a>
                <a href="sanpham-chuot-chuadangnhap.php">Màn hình</a>
            </div>

            <form action="timkiembanphim.php" method="GET" class="search-form">
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

        <div class="icon">
            <div><a href="#" class="shopping-cart"><img src="picture/shopping.png" alt="Giỏ hàng"></a></div>

            <div class="user"> <a href="trangdangnhap.php" ><img src="picture/user.png" alt="Người dùng"><span>Đăng nhập</span></a><a href="trangdangki.php" class="dangky">Đăng ký</a></div>
        </div>
    </header>
    <main class="container">
        <br>

        <div class="layout">
            <!-- Thanh bên trái -->
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
            <h1>Keyboard | Bàn phím máy tính</h1>
            <br>
            
            <?php
            // 1. Nhúng file kết nối
            include 'connect.php';

            // 2. Cấu hình phân trang
            $limit = 6; // Số sản phẩm 1 trang
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($page < 1) $page = 1;
            $offset = ($page - 1) * $limit;

            // 3. Đếm tổng số sản phẩm Bàn phím (Giả sử Tên loại có chứa chữ 'Bàn phím' và đang được bán)
            $sql_count = "SELECT COUNT(*) as total FROM SanPham sp 
                          JOIN LoaiSanPham lsp ON sp.MaLoai = lsp.MaLoai 
                          WHERE lsp.TenLoai LIKE '%Bàn phím%' AND sp.HienTrang = 1";
            $result_count = mysqli_query($conn, $sql_count);
            $row_count = mysqli_fetch_assoc($result_count);
            $total_records = $row_count['total'];
            $total_pages = ceil($total_records / $limit);

            // 4. Lấy dữ liệu sản phẩm cho trang hiện tại
            $sql = "SELECT sp.* FROM SanPham sp 
                    JOIN LoaiSanPham lsp ON sp.MaLoai = lsp.MaLoai 
                    WHERE lsp.TenLoai LIKE '%Bàn phím%' AND sp.HienTrang = 1 
                    LIMIT $limit OFFSET $offset";
            $result = mysqli_query($conn, $sql);
            ?>

            <div class="grid">
                <?php 
                // 5. Vòng lặp in sản phẩm ra HTML
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        // Tính giá bán theo công thức: Giá nhập * (100% + Tỉ lệ lợi nhuận)
                        // Trong DB của bạn, TiLeLoiNhuan lưu kiểu 0.20 (tức là 20%)
                        $gia_nhap = $row['GiaNhapBinhQuan'];
                        $ti_le = $row['TiLeLoiNhuan'];
                        $gia_ban = $gia_nhap * (1 + $ti_le);
                ?>
                <article class="card">
                    <div class="img">
                        <a href="thongtinsanpham.php?id=<?php echo $row['MaSP']; ?>">
                            <img src="picture/<?php echo $row['HinhAnh']; ?>" alt="<?php echo htmlspecialchars($row['TenSP']); ?>">
                        </a>
                    </div>
                    <h3><?php echo htmlspecialchars($row['TenSP']); ?></h3>
                    
                    <div class="price">
                        <?php echo number_format($gia_ban, 0, ',', '.'); ?> ₫ 
                    </div>
                    
                    <div class="actions">
                        <a class="btn btn-primary" href="thongtinsanpham-chuadangnhap.php?id=<?php echo $row['MaSP']; ?>">Chi tiết</a>
                        <a class="btn btn-outline" href="themgiohang.php?id=<?php echo $row['MaSP']; ?>">Thêm vào giỏ hàng</a>
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
                    <a href="?page=<?php echo $page - 1; ?>" class="prev">« Trở về </a>
                <?php endif; ?>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="next">Tiếp tục »</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </section>
    </main>

</body>
</html>