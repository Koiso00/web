<?php
include("config.php");
session_start();
/* Lấy danh sách sản phẩm đang bán */
$sql = "SELECT * FROM SanPham WHERE HienTrang = 1 ORDER BY MaSP DESC LIMIT 12";
$stmt = $conn->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>TechZone - Phụ kiện máy tính</title>

<link rel="shortcut icon" href="picture/logo.png">
<link rel="stylesheet" href="style.css">

</head>

<body>

<header>

<a href="trangchu.php" class="logo">TechZone</a>

<div class="search-bar">

<input type="checkbox" id="menu-toggle" hidden>

<label for="menu-toggle" class="menu-btn">
<img src="picture/menu-burger.png" class="menu-icon">
</label>

<div class="dropdown-content">
<a href="#">Bàn phím</a>
<a href="#">Chuột</a>
<a href="#">Tai nghe</a>
<a href="#">Màn hình</a>
</div>

<form action="timkiem.php" method="GET" class="search-form">

<input type="search" name="q" placeholder="Tìm kiếm sản phẩm...">

<button type="submit">
<img src="picture/magnifying-glass.png">
</button>

</form>

</div>

<nav class="navbar">

<a href="#">Trang chủ</a>
<a href="#products">Sản phẩm</a>
<a href="#bottom">Liên hệ</a>

</nav>

<div class="icon">

<a href="#"><img src="picture/shopping.png"></a>

<div class="user">

<?php
if(isset($_SESSION['user'])){
?>

<a href="#">
<img src="picture/user.png">
<span><?php echo $_SESSION['user']; ?></span>
</a>

<a href="xuly_dangxuat.php" class="dangky">Đăng xuất</a>

<?php
}else{
?>

<a href="trangdangnhap.php">
<img src="picture/user.png">
<span>Đăng nhập</span>
</a>

<a href="trangdangki.php" class="dangky">Đăng ký</a>

<?php
}
?>

</div>

</div>

</header>


<section class="home" id="home">

<div class="content">

<h3>TechZone</h3>

<span>Chất lượng , chuyên nghiệp</span>

<p>
Techzone với chất lượng sản phẩm luôn được đặt lên hàng đầu.
Chúng tôi cam kết không bán hàng kém chất lượng.
</p>

<a href="#products" class="btn">Mua ngay</a>

</div>

</section>


<section class="icons-container">

<div class="icon">
<img src="picture/hinh1.png" style="width:20%">
<div class="info">
<h3>Miễn phí vận chuyển</h3>
<span>trên toàn bộ đơn hàng</span>
</div>
</div>

<div class="icon">
<img src="picture/hinh2.png" style="width:20%">
<div class="info">
<h3>Trả hàng trong 30 ngày</h3>
<span>đảm bảo hoàn tiền</span>
</div>
</div>

<div class="icon">
<img src="picture/hinh3.png" style="width:20%">
<div class="info">
<h3>Voucher hấp dẫn</h3>
<span>cho mọi đơn hàng</span>
</div>
</div>

<div class="icon">
<img src="picture/hinh4.png" style="width:20%">
<div class="info">
<h3>Hàng chính hãng</h3>
<span>đảm bảo 100%</span>
</div>
</div>

</section>



<section class="products" id="products">

<h1 class="heading">Latest <span>Products</span></h1>

<br>

<div class="box-content">

<?php foreach($products as $row): ?>

<div class="box">

<div class="image">

<a href="thongtinsanpham.php?id=<?php echo $row['MaSP']; ?>">

<img src="picture/<?php echo $row['HinhAnh']; ?>" alt="">

</a>

<div class="icon">
<a href="giohang.php?id=<?php echo $row['MaSP']; ?>" class="fas fa-shopping-cart">
Mua ngay
</a>
</div>

<div class="name-product">

<h3><?php echo $row['TenSP']; ?></h3>

<div class="price">

<?php echo number_format($row['GiaNhapBinhQuan'],0,',','.'); ?> ₫

</div>

</div>

</div>

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

<ul>
<li><b>Thanh toán</b></li>
</ul>

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

</body>
</html>