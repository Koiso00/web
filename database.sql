-- Tạo cơ sở dữ liệu (Bạn có thể đổi tên db_banhang theo ý muốn)
CREATE DATABASE IF NOT EXISTS db_banhang DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_banhang;

-- 1. Bảng Tài Khoản (Dùng chung cho cả Admin và Khách hàng)
CREATE TABLE TaiKhoan (
    MaTK INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL, -- Lưu mật khẩu đã mã hóa (MD5/Bcrypt)
    HoTen VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    SoDienThoai VARCHAR(20) NOT NULL,
    VaiTro TINYINT(1) DEFAULT 0 COMMENT '0: Khách hàng, 1: Admin',
    TrangThai TINYINT(1) DEFAULT 1 COMMENT '0: Bị khóa, 1: Hoạt động'
) ENGINE=InnoDB;

-- 2. Bảng Địa Chỉ Khách Hàng (Để khách chọn khi mua và Admin lọc theo Phường)
CREATE TABLE DiaChiKhachHang (
    MaDC INT AUTO_INCREMENT PRIMARY KEY,
    MaTK INT NOT NULL,
    TenNguoiNhan VARCHAR(100) NOT NULL,
    SDTNhan VARCHAR(20) NOT NULL,
    DiaChiChiTiet VARCHAR(255) NOT NULL,
    PhuongXa VARCHAR(100) NOT NULL,
    QuanHuyen VARCHAR(100) NOT NULL,
    TinhThanh VARCHAR(100) NOT NULL,
    FOREIGN KEY (MaTK) REFERENCES TaiKhoan(MaTK) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Bảng Loại Sản Phẩm (Danh mục)
CREATE TABLE LoaiSanPham (
    MaLoai INT AUTO_INCREMENT PRIMARY KEY,
    TenLoai VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 4. Bảng Sản Phẩm
CREATE TABLE SanPham (
    MaSP INT AUTO_INCREMENT PRIMARY KEY,
    TenSP VARCHAR(200) NOT NULL,
    MaLoai INT NOT NULL,
    MoTa TEXT,
    DonViTinh VARCHAR(50) NOT NULL,
    HinhAnh VARCHAR(255),
    SoLuongTon INT DEFAULT 0,
    TiLeLoiNhuan DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Ví dụ: 0.20 là 20%',
    GiaNhapBinhQuan DECIMAL(15,2) DEFAULT 0.00,
    HienTrang TINYINT(1) DEFAULT 1 COMMENT '0: Ẩn (Xóa mềm), 1: Đang bán',
    FOREIGN KEY (MaLoai) REFERENCES LoaiSanPham(MaLoai) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. Bảng Phiếu Nhập
CREATE TABLE PhieuNhap (
    MaPN INT AUTO_INCREMENT PRIMARY KEY,
    NgayNhap DATETIME DEFAULT CURRENT_TIMESTAMP,
    MaAdmin INT NOT NULL,
    TrangThai TINYINT(1) DEFAULT 0 COMMENT '0: Đang tạo/Chưa hoàn thành, 1: Đã hoàn thành',
    FOREIGN KEY (MaAdmin) REFERENCES TaiKhoan(MaTK) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. Bảng Chi Tiết Phiếu Nhập
CREATE TABLE ChiTietPhieuNhap (
    MaPN INT NOT NULL,
    MaSP INT NOT NULL,
    SoLuongNhap INT NOT NULL CHECK (SoLuongNhap > 0),
    GiaNhap DECIMAL(15,2) NOT NULL CHECK (GiaNhap >= 0),
    PRIMARY KEY (MaPN, MaSP),
    FOREIGN KEY (MaPN) REFERENCES PhieuNhap(MaPN) ON DELETE CASCADE,
    FOREIGN KEY (MaSP) REFERENCES SanPham(MaSP) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Bảng Đơn Hàng
CREATE TABLE DonHang (
    MaDH INT AUTO_INCREMENT PRIMARY KEY,
    MaTK INT NOT NULL,
    NgayDat DATETIME DEFAULT CURRENT_TIMESTAMP,
    TongTien DECIMAL(15,2) NOT NULL,
    PhuongThucThanhToan VARCHAR(50) NOT NULL,
    TrangThai TINYINT(1) DEFAULT 0 COMMENT '0: Chưa xử lý, 1: Đã xác nhận, 2: Đã giao, 3: Đã hủy',
    DiaChiGiaoHang TEXT NOT NULL COMMENT 'Lưu cứng địa chỉ lúc đặt hàng',
    PhuongXaGiao VARCHAR(100) NOT NULL COMMENT 'Tách riêng để Admin dễ lọc',
    FOREIGN KEY (MaTK) REFERENCES TaiKhoan(MaTK) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 8. Bảng Chi Tiết Đơn Hàng
CREATE TABLE ChiTietDonHang (
    MaDH INT NOT NULL,
    MaSP INT NOT NULL,
    SoLuongMua INT NOT NULL CHECK (SoLuongMua > 0),
    GiaBan DECIMAL(15,2) NOT NULL COMMENT 'Lưu cứng giá bán tại thời điểm mua',
    PRIMARY KEY (MaDH, MaSP),
    FOREIGN KEY (MaDH) REFERENCES DonHang(MaDH) ON DELETE CASCADE,
    FOREIGN KEY (MaSP) REFERENCES SanPham(MaSP) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- INSERT sẵn 1 tài khoản Admin mặc định để bạn test đăng nhập (Password là: 123456 - giả sử dùng MD5)
-- Lưu ý: Trong thực tế bạn dùng hàm băm nào (MD5, password_hash của PHP) thì thay chuỗi hash vào đây.
INSERT INTO TaiKhoan (Username, Password, HoTen, Email, SoDienThoai, VaiTro, TrangThai) 
VALUES ('admin', 'e10adc3949ba59abbe56e057f20f883e', 'Quản trị viên', 'admin@gmail.com', '0123456789', 1, 1);