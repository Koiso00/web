<?php
// Tự động tính URL dựa trên vị trí thực tế
$base_url = str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', __DIR__)
) . '/';
?>
<nav class="sidebar">
    <h1 class="logo">TechZone</h1>
    <a href="<?php echo $base_url; ?>Trang-chu.php">📈 Trang Chủ</a>
    <a href="<?php echo $base_url; ?>quanlidanhmuc/sanpham.php">📦 Quản lý loại sản phẩm</a>
    <a href="<?php echo $base_url; ?>taikhoan.php">👥 Quản lý tài khoản</a>
    <a href="<?php echo $base_url; ?>quanlidanhmuc/quanlidanhmuc.php">🎁 Quản lí danh mục</a>
    <a href="<?php echo $base_url; ?>quanlinhaphang/quanlinhaphang.php">📥 Quản lí nhập hàng</a>
    <a href="<?php echo $base_url; ?>quanligiaban/quanligiaban.php">💲 Quản lí giá bán</a>
    <a href="<?php echo $base_url; ?>quanlidonhang/quanlidonhang.php">🚚 Quản lí đơn hàng</a>
    <a href="<?php echo $base_url; ?>quanlitonkho/quanlitonkho.php">📊 Quản lí tồn kho</a>
</nav>