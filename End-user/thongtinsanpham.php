<?php
session_start();
include "config.php";

if(!isset($_GET['id'])){
    echo "Không tìm thấy sản phẩm";
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM SanPham WHERE MaSP=?");
$stmt->execute([$id]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$sp){
    echo "Sản phẩm không tồn tại";
    exit();
}

/* tính giá bán */
$gia = $sp['GiaNhapBinhQuan'] + ($sp['GiaNhapBinhQuan'] * $sp['TiLeLoiNhuan']);

/* sản phẩm liên quan */
$stmt2 = $conn->prepare("SELECT * FROM SanPham 
WHERE MaLoai=? AND MaSP!=? LIMIT 4");
$stmt2->execute([$sp['MaLoai'],$id]);
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

<a href="trangchu.php">Trang chủ</a>
<a href="trangchu.php#products">Sản phẩm</a>
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
<span>Đăng nhập </span>
</a>

<a href="trangdangki.php" class="dangky"> Đăng ký</a>

<?php } ?>

</div>

</div>

</header>



<section class="product-container">

<div class="product-left">

<img src="picture/<?php echo $sp['HinhAnh']; ?>">

</div>

<div class="product-right">

<h1><?php echo $sp['TenSP']; ?></h1>

<div class="price">
<?php echo number_format($gia); ?>đ
</div>

<p class="status">

<?php
if($sp['SoLuongTon'] > 0){
echo "<span class='conhang'>Còn hàng</span>";
}else{
echo "<span class='hethang'>Hết hàng</span>";
}
?>

</p>

<div class="buttons">

<form action="themvaogio.php" method="POST">

<input type="hidden" name="id" value="<?php echo $sp['MaSP']; ?>">

<button class="cart">Thêm vào giỏ</button>

</form>

<button class="buy">Mua ngay</button>

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

<?php foreach($lienquan as $sp2){

$gia2 = $sp2['GiaNhapBinhQuan'] + ($sp2['GiaNhapBinhQuan'] * $sp2['TiLeLoiNhuan']);

?>

<div class="card">

<a href="thongtinsanpham.php?id=<?php echo $sp2['MaSP']; ?>">

<img src="picture/<?php echo $sp2['HinhAnh']; ?>">

<h3><?php echo $sp2['TenSP']; ?></h3>

<p><?php echo number_format($gia2); ?>đ</p>

</a>

</div>

<?php } ?>

</div>

</section>

</body>

</html>