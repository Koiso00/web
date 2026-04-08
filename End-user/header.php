<link rel="stylesheet" href="style.css">
<header>
    <a href="trangchu.php" class="logo">TechZone</a>
    

    <div class="search-bar" role="search">
        <input type="checkbox" id="menu-toggle" hidden>
        <label for="menu-toggle" class="menu-btn">
            <img src="picture/menu-burger.png" class="menu-icon" alt="menu">
        </label>

        <div class="dropdown-content" aria-hidden="true">
            <?php
            // Truy vấn lấy tất cả các danh mục từ CSDL
            $sql_menu = "SELECT * FROM LoaiSanPham";
            $result_menu = mysqli_query($conn, $sql_menu);
            
            // Dùng vòng lặp in ra từng danh mục
            if ($result_menu && mysqli_num_rows($result_menu) > 0) {
                while ($row_menu = mysqli_fetch_assoc($result_menu)) {
                    // Tự động gắn MaLoai vào link và in TenLoai ra màn hình
                    echo '<a href="sanpham.php?loai=' . $row_menu['MaLoai'] . '">' . htmlspecialchars($row_menu['TenLoai']) . '</a>';
                }
            } else {
                echo '<a href="#">Đang cập nhật...</a>';
            }
            ?>
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
        <a href="sanpham.php">Sản Phẩm</a>
        <a href="#bottom">Liên hệ</a>
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
        <?php if(isset($_SESSION['user'])): ?>
        <a href="thongtintaikhoan.php" title="Xem thông tin cá nhân">
            <img src="picture/user.png" alt="Avatar">
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