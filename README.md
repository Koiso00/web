# 🛒 TechZone - Website bán phụ kiện công nghệ

**TechZone** là một website thương mại điện tử chuyên bán phụ kiện máy tính (bàn phím cơ, chuột gaming, tai nghe, lót chuột...), được phát triển bằng PHP thuần (Core PHP) và MySQL như một đồ án môn học **Lập trình Web và Ứng dụng nâng cao** tại Trường Đại học Sài Gòn.

---

## 📌 Tính năng chính

### 👥 Dành cho khách hàng (End-user)

- Xem danh sách sản phẩm theo danh mục, hỗ trợ phân trang
- Tìm kiếm sản phẩm theo từ khóa
- Lọc sản phẩm theo khoảng giá và danh mục
- Xem chi tiết sản phẩm (hình ảnh, thông số, tồn kho, giá bán)
- Thêm/sửa/xóa sản phẩm vào giỏ hàng **(AJAX - không reload trang)**
- Đặt hàng, thanh toán khi nhận hàng (COD)
- Đăng ký / Đăng nhập tài khoản
- Quản lý hồ sơ cá nhân (thông tin, sổ địa chỉ)
- Xem lịch sử đơn hàng và theo dõi trạng thái

### 🔧 Dành cho quản trị viên (Admin)

- Đăng nhập khu vực quản trị riêng biệt
- Dashboard thống kê: tổng doanh thu, số đơn hàng, số sản phẩm, số khách hàng
- Biểu đồ doanh thu trực quan **(Chart.js)**
- Quản lý danh mục sản phẩm (thêm/sửa/xóa)
- Quản lý sản phẩm (thêm/sửa/xóa mềm/ẩn hiện)
- Quản lý nhập hàng theo lô, tự động tính giá vốn bình quân gia quyền **(FIFO)**
- Quản lý đơn hàng, cập nhật trạng thái đơn (xác nhận, giao hàng, hủy)
- Quản lý tài khoản khách hàng (khóa/mở khóa)
- Báo cáo Nhập – Xuất – Tồn theo thời gian

---

## 🛠 Công nghệ sử dụng

| Thành phần | Công nghệ |
|------------|------------|
| Backend | PHP (Core PHP, hướng thủ tục) |
| Database | MySQL |
| Frontend | HTML5, CSS3, Bootstrap 5 |
| JavaScript | jQuery, AJAX, Fetch API |
| Biểu đồ | Chart.js |
| Môi trường phát triển | XAMPP (Apache, MySQL, PHP) |
| Trình soạn thảo | Visual Studio Code |


## 📁 Cấu trúc thư mục dự án

web/
│   database.sql              # File CSQL database (import vào phpMyAdmin)
│
├───Admin                     # 🛠️ Khu vực quản trị (chỉ admin mới vào được)
│   │   config.php            # Cấu hình kết nối database
│   │   logout.php            # Đăng xuất
│   │   sidebar.php           # Menu bên trái của admin
│   │   Style.css             # Giao diện admin
│   │   taikhoan.php          # Quản lý tài khoản người dùng
│   │   Trang-chu.php         # Trang chủ admin
│   │   Trang-dang-nhap.php   # Cửa đăng nhập dành cho admin
│   │
│   ├───Image                 # 🖼️ Ảnh sản phẩm, logo, icon dùng riêng cho admin
│   │
│   ├───quanlidanhmuc         # 📂 Quản lý danh mục & sản phẩm (thêm/sửa/xóa)
│   │
│   ├───quanlidonhang         # 📦 Quản lý đơn hàng (xem, cập nhật trạng thái)
│   │
│   ├───quanligiaban          # 💰 Quản lý giá bán, tra cứu giá
│   │
│   ├───quanlinhaphang        # 🚚 Quản lý nhập hàng (phiếu nhập kho)
│   │
│   └───quanlitonkho          # 📊 Báo cáo tồn kho, nhập - xuất - tồn
│
└───End-user                  # 🛒 Khu vực khách hàng (giao diện chính)
    │   *.php                 # Các trang chức năng: giỏ hàng, thanh toán, đăng nhập...
    │   *.css                 # File định dạng giao diện
    │
    ├───.vscode               # Cấu hình cho VS Code (không ảnh hưởng web)
    │
    └───picture               # 🖼️ Ảnh giao diện (icon, banner, logo) dùng cho người dùng
    
