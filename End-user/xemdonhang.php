<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đơn hàng của tôi - TechZone</title>
  <link rel="stylesheet" href="xemdonhang.css">
</head>
<body>

  <header class="header">
    <div class="logo">TechZone | Đơn hàng</div>
    <nav>
      <a href="sau-khi-dang-nhap.php">Trang chủ</a>
      <a href="giohang.php">Giỏ hàng</a>
      <a href="thanhtoan.php">Thanh toán</a>
    </nav>
  </header>

  <main class="order-container">
    <h2>Chi tiết đơn hàng</h2>

    <!-- 🧾 Thông tin đơn hàng -->
    <section class="order-info">
      <div>
        <p><strong>Mã đơn:</strong> DH001</p>
        <p><strong>Ngày đặt:</strong> 25/10/2025</p>
      </div>
      <div>
        <p><strong>Trạng thái:</strong> <span class="status processing">Đang xử lý</span></p>
      </div>
    </section>

    <!-- 🏠 Địa chỉ nhận hàng -->
    <section class="address-info">
      <h3>Địa chỉ giao hàng</h3>
      <p><strong>Nguyễn Văn A</strong> | 0901 234 567</p>
      <p>123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh</p>
    </section>

    <!-- 📦 Danh sách sản phẩm -->
    <section class="order-items">
      <h3>Sản phẩm trong đơn</h3>
      <table>
        <thead>
          <tr>
            <th>Hình</th>
            <th>Tên sản phẩm</th>
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

    <!-- 💳 Nút thao tác -->
    <section class="order-actions">
      <button class="btn btn-outline" onclick="window.location.href='sanpham-banphim.php'" >Tiếp tục mua hàng</button>
      <button class="btn btn-primary">In hóa đơn</button>
    </section>

  </main>

</body>
</html>
