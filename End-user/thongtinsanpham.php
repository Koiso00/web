<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết sản phẩm - AKKO 5075B Plus Naruto</title>
  <link rel="shortcut icon" href="picture/png-transparent-laptop-computer-icons-computer-desktop-pc-electronics-rectangle-computer.png" type="image/x-icon">
  <link rel="stylesheet" href="trangthongtinsanpham.css">
</head>
<body>

  <!-- HEADER -->
<header>
        <a href="sau-khi-dang-nhap.php" class="logo">TechZone</a>

        <div class="search-bar" role="search" aria-label="Search site">
            <input type="checkbox" id="menu-toggle" hidden>
            <label for="menu-toggle" class="menu-btn" aria-hidden="true">
                <img src="picture/menu-burger.png" class="menu-icon" alt="menu">
            </label>

            <div class="dropdown-content" aria-hidden="true">
                <a href="sanpham-banphim.php">Bàn phím</a>
                <a href="sanpham-chuot.php">Chuột</a>
                <a href="sanpham-banphim.php">Tai nghe</a>
                <a href="sanpham-chuot.php">Màn hình</a>
            </div>

            <form action="timkiembanphim.php" method="GET" class="search-form">
                <label for="search-box" class="visually-hidden">Tìm kiếm</label>
                <input type="search" id="search-box" name="q" placeholder="Tìm kiếm sản phẩm, thương hiệu..." aria-label="Tìm kiếm" autocomplete="off">
                <button type="submit" class="search-submit" aria-label="Tìm kiếm">
                    <img src="picture/magnifying-glass.png" alt="">
                </button>
            </form>
        </div>
        <nav class="navbar">
        <a href="trangchu.php">Trang chủ</a> 
        <a href="sanpham-banphim.php">Sản Phẩm</a> 
        <a href="#contact">Liên hệ</a>     
        </nav>

        <div class="icon">
            <div><a href="giohang.php" class="shopping-cart"><img src="picture/shopping.png" alt="Giỏ hàng"></a></div>

            <div class="user"> <a href="suathongtin.php" ><img class="avatar" src="picture/avatar-con-gian-cute.jpg" alt="Người dùng"><span>TÈO</span></a></div>
        </div>
    </header>

  <!-- PHẦN CHI TIẾT SẢN PHẨM -->
  <section class="product-details">
    <div class="right-column">
      <div class="product-image">
        <div class="image-container">
          <input type="radio" name="img" id="img1" checked>
          <input type="radio" name="img" id="img2">
          <input type="radio" name="img" id="img3">

          <div class="images">
            <img src="picture/product1.1.png" class="m1">
            <img src="picture/product1.2.png" class="m2">
            <img src="picture/product1.3.png" class="m3">
          </div>

          <div class="nav nav1">
            <label for="img3">&#10094;</label>
            <label for="img2">&#10095;</label>
          </div>
          <div class="nav nav2">
            <label for="img1">&#10094;</label>
            <label for="img3">&#10095;</label>
          </div>
          <div class="nav nav3">
            <label for="img2">&#10094;</label>
            <label for="img1">&#10095;</label>
          </div>

          <div class="dots">
            <label for="img1"></label>
            <label for="img2"></label>
            <label for="img3"></label>
          </div>
        </div>
      </div>

      <div class="thongsokithuat">
        <p>Thông số kỹ thuật:</p><br>
        <ul>
          <li>Hãng sản xuất: Akko</li>
          <li>Model: 5075B Plus Naruto</li>
          <li>Kích thước: 75% (84 phím)</li>
          <li>Kết nối: Bluetooth 5.1, 2.4Ghz, Type-C có dây</li>
          <li>Pin: 1900mAh</li>
          <li>Switch: Akko CS Jelly Pink</li>
          <li>Thời gian sử dụng: 15-20 giờ</li>
          <li>Đèn nền: RGB</li>
          <li>Keycap: PBT Double-shot</li>
        </ul>
      </div>
    </div>

    <div class="left-column">
<div class="product-info">
  <h1>Bàn phím cơ AKKO 5075B Plus Naruto (Multi-modes / RGB / Hotswap / Gasket mount)</h1>

  <div class="gia-container">
    <p class="pricesale">2.490.000đ</p>
    <p class="price">2.990.000đ</p>
    <span class="sale">-7%</span>
  </div>

  <p class="tt">Tình trạng: <span class="status">Còn hàng</span></p>
</div>


      <div class="diachi">
        <p>Địa điểm có hàng</p><br>
        <ul>
          <li>78-80-82 Hoàng Hoa Thám, Phường Bảy Hiền, TP.HCM</li>
          <li>496 Đường 3/2, P. Diên Hồng, TP. HCM</li>
          <li>Kiot 7, số 210 Lê Trọng Tấn, Phường Phương Liệt, TP. HN</li>
          <li>313 Võ Văn Tần, Phường 5, Quận 3, TP. HCM</li>
        </ul>
      </div>

      <div class="kn">
        <p>Khuyến mãi:</p><br>
        <ul>
          <li>Miễn phí giao hàng toàn quốc với đơn hàng từ 300.000đ <a href="#">xem thêm</a></li>
          <li>Hỗ trợ trả góp 0% lãi suất qua thẻ tín dụng <a href="#">xem thêm</a></li>
          <li>Đổi trả trong 30 ngày nếu sản phẩm lỗi do nhà sản xuất <a href="#">xem thêm</a></li>
          <li>Bảo hành chính hãng 12 tháng <a href="#">xem thêm</a></li>
        </ul>
      </div>

      <div class="action-buttons">
        <button class="buy1">🛒 Mua Ngay</button>
        <button class="buy2">💬 Tư Vấn</button>
        <button class="buy3">+ Giỏ Hàng</button>
      </div>
    </div>
  </section>

  

  <!-- ĐÁNH GIÁ SẢN PHẨM -->
  <section class="review-container">
    <div class="review-section">
      <h2>Đánh Giá Sản Phẩm</h2>
      <div class="rating">
        <input type="radio" name="star" id="star5"><label for="star5">&#9733;</label>
        <input type="radio" name="star" id="star4"><label for="star4">&#9733;</label>
        <input type="radio" name="star" id="star3"><label for="star3">&#9733;</label>
        <input type="radio" name="star" id="star2"><label for="star2">&#9733;</label>
        <input type="radio" name="star" id="star1"><label for="star1">&#9733;</label>
      </div>
      <textarea placeholder="Nhập nhận xét của bạn..."></textarea>
      <button type="submit">Gửi Đánh Giá</button>
    </div>

    <div class="review-display">
      <h2>Đánh Giá Gần Đây</h2>

      <div class="review-item">
        <p><strong>Nguyễn Văn A</strong> ⭐⭐⭐⭐☆</p>
        <p>Sản phẩm dùng tốt, giao hàng nhanh.</p>
      </div>

      <div class="review-item">
        <p><strong>Trần Thị B</strong> ⭐⭐⭐⭐⭐</p>
        <p>Rất hài lòng, sẽ ủng hộ lần sau!</p>
      </div>
    </div>
  </section>
  <!----san pham lien quan---->
  <section class="sanpham-lienquan">
  <h2>Sản Phẩm Liên Quan</h2>
  <div class="ds-sanpham">
    <div class="sp">
      <img src="picture/product2.png" alt="Akko 5075B Plus Naruto">
      <p class="ten">Bàn phím Akko 5075B Plus Dragonball</p>
      <p class="gia">2.490.000đ</p>
      <button class="xemchitiet">Xem Chi Tiết</button>
    </div>
    <div class="sp">
      <img src="picture/product2.1.png" alt="Chuột gaming">
      <p class="ten">Chuột Gaming Không Dây aula</p>
      <p class="gia">890.000đ</p>
      <button class="xemchitiet">Xem Chi Tiết</button>
    </div>
    <div class="sp">
      <img src="picture/product3.png" alt="Bàn phím trắng">
      <p class="ten">Bàn phím Akko 3108 White</p>
      <p class="gia">1.990.000đ</p>
      <button class="xemchitiet">Xem Chi Tiết</button>
    </div>
  </div>
</section>

 <footer id="bottom">
        <section class="footer">
        <div class="footer-box">
            <ul>
                <a href="#"><li><b>dịch vụ khách hàng</b></li></a>
                <br>
                <a href="#"><li>trung tâm trợ giúp techzone</li></a>
                <a href="#"><li>hướng dẫn mua hàng / đặt hàng</li></a>
                <a href="#"><li>đơn hàng</li></a>
                <a href="#"><li>trả hàng / hoàn tiền </li></a>
                <a href="#"><li>liên hệ techzone</li></a>
                <a href="#"><li>chính sách bảo hành</li></a>
                
            </ul>
            </div>
        <div class="footer-box">
            <ul>
                <a href="#"><li><b>techzone việt nam</b></li></a>
                <br>
                <a href="#"><li>về techzone</li></a>
                <a href="#"><li>tuyển dụng</li></a>
                <a href="#"><li>điều khoản techzone</li></a>
                <a href="#"><li>chính sách bảo mật</li></a>
                <a href="#"><li>tiếp thị liên hệ</li></a>
            </ul>
        </div>
        <div class="footer-box">
            <ul>
                <a href="#"><li><b>thanh toán</b></li></a>
            </ul>
            <div class="payment">
                <table>
                    <tr>
                    <td><img src="picture/thanhtoan1.png" alt=""></td>
                    <td> <img src="picture/thanhtoan2.png" alt=""></td>
                    <td><img src="picture/thanhtoan3.png" alt="">  </td>
                    <td><img src="picture/thanhtoan7.png" alt=""></td>
                    </tr>
                    <tr>
                        <td> <img src="picture/thanhtoan4.png" alt=""></td>
                        <td><img src="picture/thanhtoan5.png" alt=""></td>
                        <td> <img src="picture/thanhtoan6.png" alt=""></td>
                    </tr>
                    
                </table>
        </div>
        </div>
        </section>
    </footer>
</body>
</html>
