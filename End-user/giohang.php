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
  <link rel="stylesheet" href="style.css">
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
            <td>
            <?php 
                // Kiểm tra xem sản phẩm này có nằm trong danh sách đang được chọn không
                $is_checked = "";
                if (isset($_SESSION['sp_chon'])) {
                    // Nếu có session lưu trạng thái, kiểm tra xem ID này có trong đó không
                    if (in_array($id_sp, $_SESSION['sp_chon'])) {
                        $is_checked = "checked";
                    }
                } else {
                    // Nếu mới vào giỏ hàng lần đầu (chưa bấm gì), mặc định chọn hết cho tiện
                    $is_checked = "checked";
                }
            ?>
<input type="checkbox" name="sp_chon[]" value="<?php echo $id_sp; ?>" class="chon-sp" data-price="<?php echo $thanh_tien; ?>" <?php echo $is_checked; ?>>
              </td> 
              <td><img src="picture/<?php echo $row['HinhAnh']; ?>" alt="" style="width: 50px;"></td>
              <td data-label="Tên"><?php echo htmlspecialchars($row['TenSP']); ?></td>
              <td data-label="Giá"><?php echo number_format($gia_ban, 0, ',', '.'); ?> ₫</td>
              
              <td data-label="Số lượng">
                <input type="number" name="soluong[<?php echo $id_sp; ?>]" value="<?php echo $so_luong; ?>" min="1" onchange="this.form.submit()">
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
          <p class="total">Tổng cộng: <span id="tong-tien-hien-thi"><?php echo number_format($tong_tien_gio_hang, 0, ',', '.'); ?></span> ₫</p>
          <?php if($tong_tien_gio_hang > 0): ?>
          <button type="submit" formaction="thanhtoan.php" class="checkout-btn" style="color: white; text-decoration: none;">Thanh toán</button>
          <?php else: ?>
          <button type="button" class="checkout-btn" style="background: #ccc; cursor: not-allowed;" disabled>Thanh toán</button>
          <?php endif; ?>
        </div>
    </form> </section>

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
<script>
  // Tìm tất cả các ô checkbox và chỗ hiển thị tổng tiền
  const checkboxes = document.querySelectorAll('.chon-sp');
  const tongTienEl = document.getElementById('tong-tien-hien-thi');

  // Hàm tính lại tiền dựa trên những ô đang được tích
  function tinhTongTien() {
      let tong = 0;
      checkboxes.forEach(cb => {
          if(cb.checked) {
              // Cộng dồn tiền của món đó (lấy từ thuộc tính data-price)
              tong += parseInt(cb.getAttribute('data-price'));
          }
      });
      // Định dạng lại thành số tiền VNĐ và in ra màn hình
      tongTienEl.innerText = tong.toLocaleString('vi-VN');
  }

  // Lắng nghe sự kiện: hễ khách bấm tích/bỏ tích là tính lại tiền ngay
  checkboxes.forEach(cb => {
      cb.addEventListener('change', tinhTongTien);
  });

  // Gọi hàm 1 lần khi trang vừa load xong để hiển thị đúng số tiền của các ô đang tích sẵn
  tinhTongTien();
</script>
</body>
</html>