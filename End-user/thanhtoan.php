<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user'])) {
    header("location:trangdangnhap.php");
    exit();
}

$user_session = $_SESSION['user'];

$sql_user = "SELECT MaTK FROM taikhoan WHERE Email='$user_session' OR HoTen='$user_session' LIMIT 1";
$res_user = mysqli_query($conn,$sql_user);
$row_user = mysqli_fetch_assoc($res_user);
$maTK = $row_user['MaTK'];

$sql_dc = "SELECT * FROM diachikhachhang WHERE MaTK='$maTK'";
$res_dc = mysqli_query($conn,$sql_dc);

$tong_tien = 0;

if(isset($_SESSION['giohang'])){
    foreach($_SESSION['giohang'] as $id_sp=>$so_luong){
        $sql = "SELECT * FROM sanpham WHERE MaSP=$id_sp";
        $res = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($res);

        $gia = $row['GiaNhapBinhQuan']*(1+$row['TiLeLoiNhuan']);
        $tong_tien += $gia*$so_luong;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thanh toán</title>
<link rel="stylesheet" href="thanhtoan.css">
</head>

<body>

<?php include 'header.php'; ?>

<form action="process_order.php" method="POST">

<div class="container">

<div class="box left">

<h2>Địa chỉ giao hàng</h2>

<label>
<input type="radio" name="diachi_option" value="cu" checked onclick="toggleAddress()"> 
Sử dụng địa chỉ đã lưu
</label>

<select name="diachi_cu" id="diachi_cu">

<?php
while($row=mysqli_fetch_assoc($res_dc)){
echo "<option value='".$row['MaDC']."'>
".$row['TenNguoiNhan']." - ".$row['SDTNhan']." - ".
$row['DiaChiChiTiet'].", ".$row['PhuongXa'].", ".$row['QuanHuyen']."
</option>";
}
?>

</select>

<label>
<input type="radio" name="diachi_option" value="moi" onclick="toggleAddress()"> 
Nhập địa chỉ mới
</label>

<div id="diachi_moi" style="display:none">

<input type="text" name="TenNguoiNhan" placeholder="Tên người nhận">

<input type="text" name="SDTNhan" placeholder="Số điện thoại">

<input type="text" name="DiaChiChiTiet" placeholder="Địa chỉ chi tiết">

<input type="text" name="PhuongXa" placeholder="Phường/Xã">

<input type="text" name="QuanHuyen" placeholder="Quận/Huyện">

<input type="text" name="TinhThanh" placeholder="Tỉnh/Thành">

</div>


<h2>Phương thức thanh toán</h2>

<label>
<input type="radio" name="payment" value="tienmat" checked onclick="togglePayment()">
Thanh toán tiền mặt khi nhận hàng
</label>

<br>

<label>
<input type="radio" name="payment" value="chuyenkhoan" onclick="togglePayment()">
Chuyển khoản ngân hàng
</label>

<div class="payment-info" id="bank-info">

<p><b>Ngân hàng:</b> Vietcombank</p>
<p><b>Chủ tài khoản:</b> TECHZONE</p>
<p><b>Số tài khoản:</b> 123456789</p>
<p><b>Nội dung:</b> Thanh toán đơn hàng</p>

</div>

<br>

<label>
<input type="radio" name="payment" value="online" onclick="togglePayment()">
Thanh toán trực tuyến
</label>

<div class="payment-info" id="online-info">

<p>Chức năng thanh toán online đang phát triển.</p>

</div>

</div>


<div class="box right">

<h2>Đơn hàng của bạn</h2>

<p>Tổng tiền:</p>

<h3><?php echo number_format($tong_tien,0,",","."); ?> ₫</h3>

<input type="hidden" name="TongTien" value="<?php echo $tong_tien; ?>">

<button class="order-btn">
Đặt hàng
</button>

</div>

</div>

</form>

<script>

function toggleAddress(){

let option=document.querySelector('input[name="diachi_option"]:checked').value;

if(option=="moi"){
document.getElementById("diachi_moi").style.display="block";
document.getElementById("diachi_cu").style.display="none";
}else{
document.getElementById("diachi_moi").style.display="none";
document.getElementById("diachi_cu").style.display="block";
}

}

function togglePayment(){

let p=document.querySelector('input[name="payment"]:checked').value;

document.getElementById("bank-info").style.display="none";
document.getElementById("online-info").style.display="none";

if(p=="chuyenkhoan"){
document.getElementById("bank-info").style.display="block";
}

if(p=="online"){
document.getElementById("online-info").style.display="block";
}

}

</script>

</body>
</html>