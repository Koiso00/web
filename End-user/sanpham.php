<?php
session_start();
include 'connect.php'; // Nhúng file kết nối DB

// Hàm tính giá bán theo chuẩn FIFO
function getGiaBanFIFO($conn, $maSP, $tiLeLoiNhuan)
{
    $sql_fifo = "SELECT ctpn.GiaNhap 
                 FROM chitietphieunhap ctpn 
                 JOIN phieunhap pn ON ctpn.MaPN = pn.MaPN 
                 WHERE ctpn.MaSP = $maSP 
                 -- Sửa lại đúng tên cột trong DB của bạn là SoLuongNhap
                 AND ctpn.SoLuongNhap > 0 
                 ORDER BY pn.NgayNhap ASC 
                 LIMIT 1";

    $result_fifo = mysqli_query($conn, $sql_fifo);

    if ($row_fifo = mysqli_fetch_assoc($result_fifo)) {
        $giaNhapLieu = $row_fifo['GiaNhap'];
        return $giaNhapLieu * (1 + $tiLeLoiNhuan);
    }

    return 0;
}

// --- ĐẾM SỐ LƯỢNG GIỎ HÀNG KHI LOAD TRANG ---
$tong_gio_hang = 0;
if (isset($_SESSION['giohang'])) {
    foreach ($_SESSION['giohang'] as $soluong) {
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

// --- XỬ LÝ PHÂN TRANG VÀ ĐIỀU KIỆN LỌC ---
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 1. Xây dựng câu lệnh điều kiện (WHERE clause) cơ bản
$where_clause = "MaLoai = $ma_loai AND HienTrang = 1";

// 2. Nếu người dùng có chọn Hãng (Mảng hang[])
if (!empty($_GET['hang'])) {
    $hang_conditions = [];
    foreach ($_GET['hang'] as $hang) {
        $hang_safe = mysqli_real_escape_string($conn, $hang);
        $hang_conditions[] = "TenSP LIKE '%$hang_safe%'";
    }
    // Ghép các hãng lại (VD: TenSP LIKE '%Logitech%' OR TenSP LIKE '%Razer%')
    $where_clause .= " AND (" . implode(" OR ", $hang_conditions) . ")";
}

// 3. Nếu người dùng TỰ NHẬP khoảng giá
// Kiểm tra xem có nhập giá Min hoặc Max không
if ((isset($_GET['gia_min']) && $_GET['gia_min'] !== '') || (isset($_GET['gia_max']) && $_GET['gia_max'] !== '')) {
    
    // Nếu để trống ô Min thì mặc định là 0
    $min_price = (isset($_GET['gia_min']) && $_GET['gia_min'] !== '') ? (int)$_GET['gia_min'] : 0;
    
    // Nếu để trống ô Max thì mặc định là 1 con số cực lớn
    $max_price = (isset($_GET['gia_max']) && $_GET['gia_max'] !== '') ? (int)$_GET['gia_max'] : 999999999;
    
    // Ghép vào câu truy vấn
    $where_clause .= " AND (GiaNhapBinhQuan * (1 + TiLeLoiNhuan)) BETWEEN $min_price AND $max_price";
}

// 4. Đếm tổng số sản phẩm SAU KHI LỌC để chia trang cho đúng
$sql_count = "SELECT COUNT(*) as total FROM SanPham WHERE $where_clause";
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

// 5. Câu truy vấn lấy dữ liệu để hiển thị
$sql = "SELECT * FROM SanPham WHERE $where_clause LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
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
<?php 
    // Gọi file header vào đây
    include 'header.php'; 
    ?>

    <main class="container">
        <br>
        <div class="layout">
            <aside class="sidebar">
                <form action="sanpham.php" method="GET" id="filter-form">
                    <input type="hidden" name="loai" value="<?php echo $ma_loai; ?>">

                    <h3>Hãng sản xuất</h3>
                    <label><input type="checkbox" name="hang[]" value="Logitech" <?php echo (isset($_GET['hang']) && in_array('Logitech', $_GET['hang'])) ? 'checked' : ''; ?>> Logitech</label><br>
                    <label><input type="checkbox" name="hang[]" value="Razer" <?php echo (isset($_GET['hang']) && in_array('Razer', $_GET['hang'])) ? 'checked' : ''; ?>> Razer</label><br>
                    <label><input type="checkbox" name="hang[]" value="Corsair" <?php echo (isset($_GET['hang']) && in_array('Corsair', $_GET['hang'])) ? 'checked' : ''; ?>> Corsair</label><br>
                    <label><input type="checkbox" name="hang[]" value="AKKO" <?php echo (isset($_GET['hang']) && in_array('AKKO', $_GET['hang'])) ? 'checked' : ''; ?>> AKKO</label><br>
                    <label><input type="checkbox" name="hang[]" value="MSI" <?php echo (isset($_GET['hang']) && in_array('MSI', $_GET['hang'])) ? 'checked' : ''; ?>> MSI</label><br><br>

                    <h3>Mức giá</h3>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                        <input type="number" name="gia_min" placeholder="Từ..." min="0" value="<?php echo isset($_GET['gia_min']) ? htmlspecialchars($_GET['gia_min']) : ''; ?>" style="width: 45%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
                        <span> - </span>
                        <input type="number" name="gia_max" placeholder="Đến..." min="0" value="<?php echo isset($_GET['gia_max']) ? htmlspecialchars($_GET['gia_max']) : ''; ?>" style="width: 45%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; cursor: pointer;">Lọc sản phẩm</button>
                </form>
            </aside>

            <section class="products">
                <h1>Danh mục | <?php echo $ten_danh_muc; ?></h1>
                <br>

                <div class="grid">
                    <?php

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $gia_ban = getGiaBanFIFO($conn, $row['MaSP'], $row['TiLeLoiNhuan']);
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
                        echo "<p>Hiện tại chưa có sản phẩm nào trong danh mục này.</p>";
                    }
                    ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        // Tự động giữ lại các bộ lọc (hang, gia, loai) trên URL
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
                        if (data.status === 'success') {
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