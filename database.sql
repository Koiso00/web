-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 27, 2026 at 08:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_banhang`
--

-- --------------------------------------------------------

--
-- Table structure for table `chitietdonhang`
--

CREATE TABLE `chitietdonhang` (
  `MaDH` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `SoLuongMua` int(11) NOT NULL CHECK (`SoLuongMua` > 0),
  `GiaBan` decimal(15,2) NOT NULL COMMENT 'Lưu cứng giá bán tại thời điểm mua'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitietdonhang`
--

INSERT INTO `chitietdonhang` (`MaDH`, `MaSP`, `SoLuongMua`, `GiaBan`) VALUES
(1, 1, 1, 1500000.00),
(2, 11, 2, 585000.00),
(3, 20, 1, 2437500.00);

-- --------------------------------------------------------

--
-- Table structure for table `chitietphieunhap`
--

CREATE TABLE `chitietphieunhap` (
  `MaPN` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `SoLuongNhap` int(11) NOT NULL CHECK (`SoLuongNhap` > 0),
  `GiaNhap` decimal(15,2) NOT NULL CHECK (`GiaNhap` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chitietphieunhap`
--

INSERT INTO `chitietphieunhap` (`MaPN`, `MaSP`, `SoLuongNhap`, `GiaNhap`) VALUES
(1, 1, 10, 1100000.00),
(1, 2, 5, 1400000.00),
(2, 11, 20, 400000.00),
(2, 13, 30, 300000.00),
(3, 8, 15, 800000.00),
(3, 20, 10, 1850000.00),
(4, 12, 5, 550000.00),
(5, 1, 1235, 52345245.00),
(5, 20, 500, 99999999.00);

-- --------------------------------------------------------

--
-- Table structure for table `diachikhachhang`
--

CREATE TABLE `diachikhachhang` (
  `MaDC` int(11) NOT NULL,
  `MaTK` int(11) NOT NULL,
  `TenNguoiNhan` varchar(100) NOT NULL,
  `SDTNhan` varchar(20) NOT NULL,
  `DiaChiChiTiet` varchar(255) NOT NULL,
  `PhuongXa` varchar(100) NOT NULL,
  `QuanHuyen` varchar(100) NOT NULL,
  `TinhThanh` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diachikhachhang`
--

INSERT INTO `diachikhachhang` (`MaDC`, `MaTK`, `TenNguoiNhan`, `SDTNhan`, `DiaChiChiTiet`, `PhuongXa`, `QuanHuyen`, `TinhThanh`) VALUES
(7, 2, 'Nguyễn Văn A', '0901234567', '123 Nguyễn Huệ', 'Bến Nghé', 'Quận 1', 'TP.HCM'),
(8, 3, 'Trần Thị B', '0912345678', '456 Lê Lợi', 'Phường 7', 'Quận 3', 'TP.HCM'),
(9, 4, 'Lê Văn C', '0923456789', '111 Lý Thái Tổ', 'Phường 1', 'Quận 10', 'TP.HCM'),
(10, 5, 'Phạm Thị D', '0934567890', '789 Quang Trung', 'Phường 10', 'Gò Vấp', 'TP.HCM');

-- --------------------------------------------------------

--
-- Table structure for table `donhang`
--

CREATE TABLE `donhang` (
  `MaDH` int(11) NOT NULL,
  `MaTK` int(11) NOT NULL,
  `NgayDat` datetime DEFAULT current_timestamp(),
  `TongTien` decimal(15,2) NOT NULL,
  `PhuongThucThanhToan` varchar(50) NOT NULL,
  `TrangThai` tinyint(1) DEFAULT 0 COMMENT '0: Chưa xử lý, 1: Đã xác nhận, 2: Đã giao, 3: Đã hủy',
  `DiaChiGiaoHang` text NOT NULL COMMENT 'Lưu cứng địa chỉ lúc đặt hàng',
  `PhuongXaGiao` varchar(100) NOT NULL COMMENT 'Tách riêng để Admin dễ lọc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donhang`
--

INSERT INTO `donhang` (`MaDH`, `MaTK`, `NgayDat`, `TongTien`, `PhuongThucThanhToan`, `TrangThai`, `DiaChiGiaoHang`, `PhuongXaGiao`) VALUES
(1, 2, '2026-03-10 10:30:00', 1500000.00, 'Tiền mặt', 0, '123 Nguyễn Huệ', 'Bến Nghé'),
(2, 3, '2026-03-11 14:20:00', 1170000.00, 'Chuyển khoản', 2, '456 Lê Lợi', 'Phường 7'),
(3, 5, '2026-03-12 09:15:00', 2437500.00, 'Ví điện tử', 1, '789 Quang Trung', 'Phường 10');

-- --------------------------------------------------------

--
-- Table structure for table `loaisanpham`
--

CREATE TABLE `loaisanpham` (
  `MaLoai` int(11) NOT NULL,
  `TenLoai` varchar(100) NOT NULL,
  `HinhAnh` varchar(255) DEFAULT 'folder-icon.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loaisanpham`
--

INSERT INTO `loaisanpham` (`MaLoai`, `TenLoai`, `HinhAnh`) VALUES
(1, 'Bàn phím cơ', 'loai_1.png'),
(2, 'Chuột Gaming', 'loai_2.jpg'),
(3, 'Tai nghe & Âm thanh', 'loai_3.png');

-- --------------------------------------------------------

--
-- Table structure for table `phieunhap`
--

CREATE TABLE `phieunhap` (
  `MaPN` int(11) NOT NULL,
  `NgayNhap` datetime DEFAULT current_timestamp(),
  `MaAdmin` int(11) NOT NULL,
  `TrangThai` tinyint(1) DEFAULT 0 COMMENT '0: Đang tạo/Chưa hoàn thành, 1: Đã hoàn thành'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phieunhap`
--

INSERT INTO `phieunhap` (`MaPN`, `NgayNhap`, `MaAdmin`, `TrangThai`) VALUES
(1, '2026-03-01 08:00:00', 1, 1),
(2, '2026-03-05 09:30:00', 1, 1),
(3, '2026-03-10 15:00:00', 1, 1),
(4, '2026-03-13 10:00:00', 1, 0),
(5, '2026-03-13 00:00:00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sanpham`
--

CREATE TABLE `sanpham` (
  `MaSP` int(11) NOT NULL,
  `TenSP` varchar(200) NOT NULL,
  `MaLoai` int(11) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `DonViTinh` varchar(50) NOT NULL,
  `HinhAnh` varchar(255) DEFAULT NULL,
  `SoLuongTon` int(11) DEFAULT 0,
  `TiLeLoiNhuan` decimal(5,2) DEFAULT 0.00 COMMENT 'Ví dụ: 0.20 là 20%',
  `GiaNhapBinhQuan` decimal(15,2) DEFAULT 0.00,
  `HienTrang` tinyint(1) DEFAULT 1 COMMENT '0: Ẩn (Xóa mềm), 1: Đang bán'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sanpham`
--

INSERT INTO `sanpham` (`MaSP`, `TenSP`, `MaLoai`, `MoTa`, `DonViTinh`, `HinhAnh`, `SoLuongTon`, `TiLeLoiNhuan`, `GiaNhapBinhQuan`, `HienTrang`) VALUES
(1, 'Bàn phím cơ AULA F99', 1, 'Bàn phím cơ không dây 3 mode, mạch xuôi, cấu trúc gasket mount gõ êm ái, pin trâu.', 'Cái', 'aula-f99.png', 15, 0.25, 1200000.00, 1),
(2, 'Bàn phím cơ Akko 5108S', 1, 'Bàn phím fullsize 108 phím, LED RGB rực rỡ, switch custom êm ái, keycap PBT siêu bền.', 'Cái', 'ban-phim-co-akko-5108s-black-pink-ava-510x631.jpg', 10, 0.20, 1500000.00, 1),
(3, 'Bàn phím cơ Keychron K2 V2', 1, 'Layout 75% nhỏ gọn, hỗ trợ Mac/Windows hoàn hảo, kết nối Bluetooth tiện lợi cho dân văn phòng.', 'Cái', 'Keychron-K2-V2-vo-nhua.jpg', 25, 0.30, 1650000.00, 1),
(4, 'Bàn phím cơ DareU EK87', 1, 'Bàn phím cơ quốc dân giá rẻ cho học sinh sinh viên, switch D độc quyền siêu bền bỉ.', 'Cái', 'Bàn phím cơ DareU EK87.png', 50, 0.35, 450000.00, 1),
(5, 'Bàn phím cơ Logitech G Pro X', 1, 'Bàn phím TKL chuyên dụng cho game thủ eSports, khả năng thay nóng switch (hot-swap) linh hoạt.', 'Cái', 'Bàn phím cơ Logitech G Pro X.png', 8, 0.25, 2800000.00, 1),
(6, 'Bàn phím cơ Razer BlackWidow V3', 1, 'Switch xanh lá đặc trưng của Razer, tiếng clicky đã tai, kê tay nam châm êm ái, LED Chroma.', 'Cái', 'Razer BlackWidow V3.png', 12, 0.20, 3100000.00, 1),
(7, 'Bàn phím cơ Corsair K70 RGB', 1, 'Khung nhôm xước phay nguyên khối sang trọng, switch Cherry MX chuẩn Đức, phím media cuộn tiện lợi.', 'Cái', 'n.com-products-corsair-k70-rgb.png', 5, 0.15, 3800000.00, 1),
(8, 'Bàn phím cơ RK Royal Kludge RK61', 1, 'Bàn phím mini 60% siêu nhỏ gọn, kết nối 3 chế độ (Dây, Bluetooth, 2.4Ghz), dễ dàng mang theo.', 'Cái', 'RK Royal Kludge RK61.png', 30, 0.40, 850000.00, 1),
(9, 'Bàn phím cơ E-Dra EK387', 1, 'Thiết kế cổ điển, build cực kỳ chắc chắn, giá cả phải chăng, phù hợp cho phòng net.', 'Cái', 'n.com-products-ban-phim-e-dra-ek387.png', 40, 0.30, 550000.00, 1),
(10, 'Bàn phím cơ FL-Esports CMK87', 1, 'Bàn phím cận cao cấp, build kim loại đầm tay, âm thanh gõ cực thock không cần mod lại. Phiên bản CPM Metal Heart ', 'Cái', 'ban-phim-co-fl-esports-cmk87cpm-metal-heart-3-mode.jpg', 7, 0.30, 2500000.00, 1),
(11, 'Chuột VXE R1 SE+', 2, 'Chuột gaming siêu nhẹ chỉ khoảng 50g, form đối xứng dễ cầm, cảm biến cực nhạy.', 'Cái', 'chuot-gaming-vgn-vxe-dragonfly-r1-se-plus-black.jpg', 20, 0.30, 450000.00, 1),
(12, 'Chuột Logitech G402', 2, 'Huyền thoại chuột gaming form công thái học, tích hợp nhiều phím macro tiện dụng cho FPS và MOBA.', 'Cái', 'chuot-logitech-g402.jpg', 12, 0.20, 600000.00, 1),
(13, 'Chuột Logitech G102 Lightsync', 2, 'Chuột quốc dân form nhỏ gọn, LED RGB đẹp mắt, mắt đọc chuẩn xác cho mọi tựa game.', 'Cái', 'logitech g102.jpg', 60, 0.35, 350000.00, 1),
(14, 'Chuột Razer DeathAdder V2', 2, 'Thiết kế công thái học huyền thoại, switch quang học Razer siêu tốc không lo double-click.', 'Cái', 'razer-deathAdder-v2.jpg', 15, 0.25, 1100000.00, 1),
(15, 'Chuột Razer Viper Mini', 2, 'Trọng lượng siêu nhẹ chỉ 61g, feet chuột PTFE trượt êm ái, dành cho tay vừa và nhỏ.', 'Cái', 'razer-viper-ultralight.jpg', 25, 0.30, 750000.00, 1),
(16, 'Chuột Zowie EC2-C', 2, 'Dòng chuột chuyên nghiệp dành riêng cho dân bắn súng CS:GO/Valorant, không cần phần mềm.', 'Cái', 'chuot-gaming-zowie-ec2-c.jpg', 10, 0.15, 1650000.00, 1),
(17, 'Chuột SteelSeries Rival 3', 2, 'Cảm biến TrueMove Core chính xác, chất liệu nhựa nhám chống mồ hôi cực tốt.', 'Cái', 'steelseries-rival-3.png', 18, 0.25, 650000.00, 1),
(18, 'Chuột Corsair Harpoon RGB', 2, 'Chuột không dây giá mềm của Corsair, công nghệ Slipstream độ trễ siêu thấp.', 'Cái', 'chuot-corsair-harpoon-rgb-pro.jpg', 22, 0.20, 1150000.00, 1),
(19, 'Chuột Asus ROG Gladius II', 2, 'Có thể tự thay switch dễ dàng với socket push-fit, LED Aura Sync đồng bộ hệ sinh thái Asus.', 'Cái', 'asus-rog-gladius-ii.jpg', 8, 0.20, 1450000.00, 1),
(20, 'Tai nghe HyperX Cloud II', 3, 'Huyền thoại tai nghe gaming, giả lập âm thanh vòm 7.1, đệm tai mút hoạt tính cực êm đeo cả ngày không đau.', 'Cái', 'tai-nghe-hyperx-cloud-ii.png', 15, 0.25, 1950000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `taikhoan`
--

CREATE TABLE `taikhoan` (
  `MaTK` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `HoTen` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Avatar` varchar(255) DEFAULT NULL,
  `SoDienThoai` varchar(20) NOT NULL,
  `VaiTro` tinyint(1) DEFAULT 0 COMMENT '0: Khách hàng, 1: Admin',
  `TrangThai` tinyint(1) DEFAULT 1 COMMENT '0: Bị khóa, 1: Hoạt động'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taikhoan`
--

INSERT INTO `taikhoan` (`MaTK`, `Username`, `Password`, `HoTen`, `Email`, `Avatar`, `SoDienThoai`, `VaiTro`, `TrangThai`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'Quản trị viên', 'admin@gmail.com', NULL, '0123456789', 1, 1),
(2, 'khachhang1', 'e10adc3949ba59abbe56e057f20f883e', 'Nguyễn Văn A', 'nva@gmail.com', NULL, '0901234567', 0, 1),
(3, 'khachhang2', 'e10adc3949ba59abbe56e057f20f883e', 'Trần Thị B', 'ttb@gmail.com', NULL, '0912345678', 0, 1),
(4, 'khachhang3', 'e10adc3949ba59abbe56e057f20f883e', 'Lê Văn C', 'lvc@gmail.com', NULL, '0923456789', 0, 0),
(5, 'khachhang4', 'e10adc3949ba59abbe56e057f20f883e', 'Phạm Thị D', 'ptd@gmail.com', NULL, '0934567890', 0, 1),
(6, 'nhanvien1', 'e10adc3949ba59abbe56e057f20f883e', 'Nhân viên test', 'nv1@techzone.vn', NULL, '0988888888', 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`MaDH`,`MaSP`),
  ADD KEY `MaSP` (`MaSP`);

--
-- Indexes for table `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  ADD PRIMARY KEY (`MaPN`,`MaSP`),
  ADD KEY `MaSP` (`MaSP`);

--
-- Indexes for table `diachikhachhang`
--
ALTER TABLE `diachikhachhang`
  ADD PRIMARY KEY (`MaDC`),
  ADD KEY `MaTK` (`MaTK`);

--
-- Indexes for table `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`MaDH`),
  ADD KEY `MaTK` (`MaTK`);

--
-- Indexes for table `loaisanpham`
--
ALTER TABLE `loaisanpham`
  ADD PRIMARY KEY (`MaLoai`),
  ADD UNIQUE KEY `TenLoai` (`TenLoai`);

--
-- Indexes for table `phieunhap`
--
ALTER TABLE `phieunhap`
  ADD PRIMARY KEY (`MaPN`),
  ADD KEY `MaAdmin` (`MaAdmin`);

--
-- Indexes for table `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`MaSP`),
  ADD KEY `MaLoai` (`MaLoai`);

--
-- Indexes for table `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`MaTK`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diachikhachhang`
--
ALTER TABLE `diachikhachhang`
  MODIFY `MaDC` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `donhang`
--
ALTER TABLE `donhang`
  MODIFY `MaDH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loaisanpham`
--
ALTER TABLE `loaisanpham`
  MODIFY `MaLoai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `phieunhap`
--
ALTER TABLE `phieunhap`
  MODIFY `MaPN` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `MaSP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `taikhoan`
--
ALTER TABLE `taikhoan`
  MODIFY `MaTK` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`MaDH`) REFERENCES `donhang` (`MaDH`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`);

--
-- Constraints for table `chitietphieunhap`
--
ALTER TABLE `chitietphieunhap`
  ADD CONSTRAINT `chitietphieunhap_ibfk_1` FOREIGN KEY (`MaPN`) REFERENCES `phieunhap` (`MaPN`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitietphieunhap_ibfk_2` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`);

--
-- Constraints for table `diachikhachhang`
--
ALTER TABLE `diachikhachhang`
  ADD CONSTRAINT `diachikhachhang_ibfk_1` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`) ON DELETE CASCADE;

--
-- Constraints for table `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`MaTK`) REFERENCES `taikhoan` (`MaTK`);

--
-- Constraints for table `phieunhap`
--
ALTER TABLE `phieunhap`
  ADD CONSTRAINT `phieunhap_ibfk_1` FOREIGN KEY (`MaAdmin`) REFERENCES `taikhoan` (`MaTK`);

--
-- Constraints for table `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`MaLoai`) REFERENCES `loaisanpham` (`MaLoai`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
