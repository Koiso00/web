<?php
session_start();
include 'connect.php';

// KIỂM TRA ĐÚNG BIẾN MaTK
if (!isset($_SESSION['MaTK'])) {
    header("location:trangdangnhap.php");
    exit();
}

$maTK = $_SESSION['MaTK'];

// 1. Lấy thông tin tài khoản
$sql_user = "SELECT * FROM taikhoan WHERE MaTK='$maTK' LIMIT 1";
$res_user = mysqli_query($conn, $sql_user);
$userInfo = mysqli_fetch_assoc($res_user);

// 2. Lấy danh sách đơn hàng (Gần nhất hiện lên đầu)
$sql_orders = "SELECT * FROM donhang WHERE MaTK='$maTK' ORDER BY NgayDat DESC";
$res_orders = mysqli_query($conn, $sql_orders);

// Xác định tab mặc định
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

function getTrangThai($status_code) {
    switch ($status_code) {
        case 0: return '<span class="badge status-0">Chưa xử lý</span>';
        case 1: return '<span class="badge status-1">Đã xác nhận</span>';
        case 2: return '<span class="badge status-2">Đã giao</span>';
        case 3: return '<span class="badge status-3">Đã hủy</span>';
        default: return '<span class="badge">Không xác định</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin tài khoản | TechZone</title>
    <link rel="stylesheet" href="trangthongtinsanpham.css">
    <link rel="stylesheet" href="thongtintaikhoan.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="wrapper">
    <div class="sidebar">
        <div class="user-card">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userInfo['HoTen']); ?>&background=0f75ff&color=fff&size=80" alt="Avatar" style="border-radius: 50%;">
            <h3><?php echo htmlspecialchars($userInfo['HoTen']); ?></h3>
            <p>Thành viên TechZone</p>
        </div>
        <ul>
            <li><a id="nav-profile" class="<?php echo ($activeTab == 'profile') ? 'active' : ''; ?>" onclick="switchTab('profile')">Thông tin cá nhân</a></li>
            <li><a id="nav-history" class="<?php echo ($activeTab == 'history') ? 'active' : ''; ?>" onclick="switchTab('history')">Lịch sử mua hàng</a></li>
            <li><a href="xuly_dangxuat.php" style="color: #e81500; background: #fff5f5; margin-top: 20px;">Đăng xuất</a></li>
        </ul>
    </div>

    <div class="content">
        
        <div id="tab-profile" class="tab-pane <?php echo ($activeTab == 'profile') ? 'active' : ''; ?>">
            <h2>Hồ Sơ</h2>
            <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
            <form>
                <div class="profile-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" value="<?php echo htmlspecialchars($userInfo['Username']); ?>" readonly>
                </div>
                <div class="profile-group">
                    <label>Họ và Tên</label>
                    <input type="text" value="<?php echo htmlspecialchars($userInfo['HoTen']); ?>" readonly>
                </div>
                <div class="profile-group">
                    <label>Email</label>
                    <input type="text" value="<?php echo htmlspecialchars($userInfo['Email']); ?>" readonly>
                </div>
                <div class="profile-group">
                    <label>Số điện thoại</label>
                    <input type="text" value="<?php echo htmlspecialchars($userInfo['SoDienThoai']); ?>" readonly>
                </div>
            </form>
        </div>

        <div id="tab-history" class="tab-pane <?php echo ($activeTab == 'history') ? 'active' : ''; ?>">
            <h2>Lịch Sử Đơn Hàng</h2>
            <p>Danh sách các đơn hàng bạn đã đặt mua</p>
            
            <?php 
            if (mysqli_num_rows($res_orders) > 0) {
                while ($order = mysqli_fetch_assoc($res_orders)) { 
                    $maDH = $order['MaDH'];
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <span class="order-id">Mã đơn: #DH<?php echo sprintf('%05d', $maDH); ?></span>
                            <span class="order-date">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['NgayDat'])); ?></span>
                        </div>
                        <div>
                            <?php echo getTrangThai($order['TrangThai']); ?>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <?php
                        $sql_detail = "SELECT ct.SoLuongMua, ct.GiaBan, sp.TenSP 
                                       FROM chitietdonhang ct 
                                       JOIN sanpham sp ON ct.MaSP = sp.MaSP 
                                       WHERE ct.MaDH = '$maDH'";
                        $res_detail = mysqli_query($conn, $sql_detail);
                        
                        while ($item = mysqli_fetch_assoc($res_detail)) {
                        ?>
                            <div class="order-item">
                                <div class="order-item-name">
                                    <strong><?php echo htmlspecialchars($item['TenSP']); ?></strong><br>
                                    <small style="color: #888;">Số lượng: <?php echo $item['SoLuongMua']; ?></small>
                                </div>
                                <div class="order-item-price">
                                    <?php echo number_format($item['GiaBan'], 0, ",", "."); ?> ₫
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    
                    <div class="order-footer">
                        <span style="font-size: 1.3rem; color: #666;">Thanh toán: <?php echo $order['PhuongThucThanhToan']; ?></span>
                        <div class="order-total">
                            Tổng tiền: <?php echo number_format($order['TongTien'], 0, ",", "."); ?> ₫
                        </div>
                    </div>
                </div>
            <?php 
                } 
            } else { 
            ?>
                <div style="text-align: center; padding: 40px 0;">
                    <h3 style="color: #666; font-size: 1.6rem;">Bạn chưa có đơn hàng nào</h3>
                    <a href="trangchu.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #0f75ff; color: white; text-decoration: none; border-radius: 5px; font-size: 1.4rem;">Mua sắm ngay</a>
                </div>
            <?php } ?>

        </div>
    </div>
</div>


<script>
function switchTab(tabId) {
    // Ẩn tất cả tab
    document.querySelectorAll('.tab-pane').forEach(function(pane) {
        pane.classList.remove('active');
    });
    
    // Bỏ highlight menu
    document.querySelectorAll('.sidebar ul li a').forEach(function(nav) {
        nav.classList.remove('active');
    });
    
    // Hiện tab được chọn
    document.getElementById('tab-' + tabId).classList.add('active');
    document.getElementById('nav-' + tabId).classList.add('active');

    // Cập nhật URL để F5 không bị mất dấu
    history.pushState(null, null, '?tab=' + tabId);
}
</script>

</body>
</html>