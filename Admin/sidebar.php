<?php
// Tự động tính URL dựa trên vị trí thực tế
$base_url = str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', __DIR__)
) . '/';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar">
    <h1 class="logo">TechZone</h1>

    <a href="<?php echo $base_url; ?>Trang-chu.php" class="<?= ($current_page == 'Trang-chu.php') ? 'active' : '' ?>">📈 Trang Chủ</a>

    <a href="<?php echo $base_url; ?>quanlidanhmuc/sanpham.php" class="<?= ($current_page == 'sanpham.php') ? 'active' : '' ?>">📦 Quản lý loại sản phẩm</a>

    <a href="<?php echo $base_url; ?>taikhoan.php" class="<?= ($current_page == 'taikhoan.php') ? 'active' : '' ?>">👥 Quản lý tài khoản</a>

    <a href="<?php echo $base_url; ?>quanlidanhmuc/quanlidanhmuc.php" class="<?= ($current_page == 'quanlidanhmuc.php') ? 'active' : '' ?>">🎁 Quản lí danh mục</a>

    <a href="<?php echo $base_url; ?>quanlinhaphang/quanlinhaphang.php" class="<?= ($current_page == 'quanlinhaphang.php') ? 'active' : '' ?>">📥 Quản lí nhập hàng</a>

    <a href="<?php echo $base_url; ?>quanligiaban/quanligiaban.php" class="<?= ($current_page == 'quanligiaban.php') ? 'active' : '' ?>">💲 Quản lí giá bán</a>

    <a href="<?php echo $base_url; ?>quanlidonhang/quanlidonhang.php" class="<?= ($current_page == 'quanlidonhang.php') ? 'active' : '' ?>">🚚 Quản lí đơn hàng</a>

    <a href="<?php echo $base_url; ?>quanlitonkho/quanlitonkho.php" class="<?= ($current_page == 'quanlitonkho.php') ? 'active' : '' ?>">📊 Quản lí tồn kho</a>
</nav>