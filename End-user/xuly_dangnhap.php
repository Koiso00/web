<?php
session_start();
include("config.php");

$username = $_POST['username'];
$password = md5($_POST['password']);

$sql = "SELECT * FROM TaiKhoan WHERE Username=? AND Password=?";
$stmt = $conn->prepare($sql);
$stmt->execute([$username,$password]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user){

    $_SESSION['user_id'] = $user['MaTK'];
    $_SESSION['username'] = $user['Username'];
    $_SESSION['hoten'] = $user['HoTen'];

    header("Location: trangchu.php");
}else{

    echo "Sai tài khoản hoặc mật khẩu";

}
?>