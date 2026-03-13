<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thanh toán - TechZone</title>
  <link rel="stylesheet" href="thanhtoan.css">
</head>
<body>
  <header class="header">
    <div class="logo">TechZone | Thanh toán </div>
    <nav>
      <a href="sau-khi-dang-nhap.php">Trang chủ</a>
      <a href="giohang.php">Giỏ hàng</a>
    </nav>
  </header>

  <main class="checkout-container">

    <!-- 🏠 PHẦN ĐỊA CHỈ -->
    <section class="address-section">
      <div class="address-info">
        <h3>Địa chỉ nhận hàng</h3>
        <p><strong>Nguyễn Văn A</strong> | 0901 234 567</p>
        <p>123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh</p>
      </div>

      <!-- Nút sửa dùng checkbox -->
      <label for="toggle-edit" class="edit-btn">Sửa thông tin</label>
      <input type="checkbox" id="toggle-edit" hidden>

      <!-- Form ẩn/hiện -->
      <form class="edit-form">
        <h4>Cập nhật địa chỉ</h4>
        <input type="text" placeholder="Họ và tên" value="Nguyễn Văn A">
        <input type="text" placeholder="Số điện thoại" value="0901 234 567">
        <textarea placeholder="Địa chỉ chi tiết">123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh</textarea>
        <div class="form-btns">
          <label for="toggle-edit" class="btn cancel">Hủy</label>
          <label for="toggle-edit" class="btn save">Lưu thông tin</label>
        </div>
      </form>
    </section>

    <!-- 🛒 DANH SÁCH SẢN PHẨM -->
    <section class="order-items">
      <h3>Đơn hàng của bạn</h3>
      <table>
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Tên</th>
            <th>Số lượng</th>
            <th>Giá</th>
            <th>Tổng</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><img src="picture/product1.png" alt=""></td>
            <td>AKKO TAC87 Black&Gold</td>
            <td>1</td>
            <td>1,250,000 ₫</td>
            <td>1,250,000 ₫</td>
          </tr>
          <tr>
            <td><img src="picture/product2.png" alt=""></td>
            <td>AKKO 5075B Plus Dragon Ball Super</td>
            <td>1</td>
            <td>2,000,000 ₫</td>
            <td>2,000,000 ₫</td>
          </tr>
        </tbody>
      </table>
      <div class="total">
        <p>Tổng cộng: <span>3,250,000 ₫</span></p>
      </div>
    </section>

    <!-- 💳 PHƯƠNG THỨC THANH TOÁN -->
    <section class="payment-section">
      <h3>Phương thức thanh toán</h3>
      <div class="payment-options">
        <label><input type="radio" name="pay" checked> Thanh toán khi nhận hàng (COD)</label>
        <label><input type="radio" name="pay"> Chuyển khoản ngân hàng</label>
        <label><input type="radio" name="pay"> Thanh toán trực tuyến</label>
      </div>
      <button class="confirm-btn"><a href="xemdonhang.php">Xác nhận thanh toán</a></button>
    </section>

  </main>
</body>
</html>
