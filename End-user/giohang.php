<?php 
session_start(); 
include 'connect.php'; // Kết nối cơ sở dữ liệu
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Giỏ hàng - TechZone</title>
  <link rel="stylesheet" href="giohang.css">
</head>
<body>

   <header class="header">
    <div class="logo">TechZone | Giỏ hàng</div>
    <nav>
      <a href="sanpham.php">Trang chủ</a>
      <a href="giohang.php">Giỏ hàng</a>
    </nav>
  </header>

  <section class="cart-container">
    <h1>Giỏ hàng của bạn</h1>

    <form action="capnhatgiohang.php" method="POST">
        <table>
          <thead>
            <tr>
              <th>Chọn</th>
              <th>Sản phẩm</th>
              <th>Tên</th>
              <th>Giá</th>
              <th>Số lượng</th>
              <th>Tổng</th>
              <th>Xóa</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $tong_tien_gio_hang = 0; // Biến cộng dồn tổng tiền

            // Kiểm tra xem giỏ hàng có tồn tại và có đồ không
            if(isset($_SESSION['giohang']) && !empty($_SESSION['giohang'])) {
                
                // Duyệt qua từng món trong SESSION
                foreach($_SESSION['giohang'] as $id_sp => $so_luong) {
                    
                    // Truy vấn lấy thông tin sản phẩm từ DB
                    $sql = "SELECT * FROM SanPham WHERE MaSP = $id_sp";
                    $result = mysqli_query($conn, $sql);
                    
                    if($row = mysqli_fetch_assoc($result)) {
                        // Tính giá bán thực tế
                        $gia_ban = $row['GiaNhapBinhQuan'] * (1 + $row['TiLeLoiNhuan']);
                        // Tính thành tiền cho dòng này
                        $thanh_tien = $gia_ban * $so_luong;
                        // Cộng dồn vào tổng tiền giỏ hàng
                        $tong_tien_gio_hang += $thanh_tien;
            ?>
            <tr>
               <td><input type="checkbox"></td> 
              <td><img src="picture/<?php echo $row['HinhAnh']; ?>" alt="" style="width: 50px;"></td>
              <td data-label="Tên"><?php echo htmlspecialchars($row['TenSP']); ?></td>
              <td data-label="Giá"><?php echo number_format($gia_ban, 0, ',', '.'); ?> ₫</td>
              
              <td data-label="Số lượng">
                  <input type="number" name="soluong[<?php echo $id_sp; ?>]" value="<?php echo $so_luong; ?>" min="1">
              </td>
              
              <td data-label="Tổng"><?php echo number_format($thanh_tien, 0, ',', '.'); ?> ₫</td>
              <td data-label="Xóa">
                  <a href="xoagiohang.php?id=<?php echo $id_sp; ?>">
                      <i class="remove"><img src="picture/trash.png" alt="Xóa" style="width: 20px;"></i>
                  </a>
              </td>
            </tr>
            <?php 
                    } // Kết thúc if
                } // Kết thúc foreach
            } else {
                // NẾU GIỎ HÀNG TRỐNG
                echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>Giỏ hàng của bạn đang trống. <a href='sanpham.php'>Tiếp tục mua sắm</a></td></tr>";
            }
            ?>
          </tbody>
        </table>

        <div class="cart-summary">
          <p class="total">Tổng cộng: <?php echo number_format($tong_tien_gio_hang, 0, ',', '.'); ?> ₫</p>
          
          <?php if($tong_tien_gio_hang > 0): ?>
              
              <button type="button" class="checkout-btn"><a href="thanhtoan.php" style="color: white; text-decoration: none;">Thanh toán</a></button>
          <?php else: ?>
              <button type="button" class="checkout-btn" style="background: #ccc; cursor: not-allowed;" disabled>Thanh toán</button>
          <?php endif; ?>
        </div>
    </form> </section>

</body>
</html>