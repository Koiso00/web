<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: Trang-dang-nhap.php");
    exit();
}

$thongBao = '';
$loaiThongBao = '';

// -----------------------------------------------------------------------
// XỬ LÝ: KHÓA / MỞ KHÓA
// -----------------------------------------------------------------------
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] == 'lock') {
        $conn->prepare("UPDATE TaiKhoan SET TrangThai = 0 WHERE MaTK = ?")->execute([$id]);
    } elseif ($_GET['action'] == 'unlock') {
        $conn->prepare("UPDATE TaiKhoan SET TrangThai = 1 WHERE MaTK = ?")->execute([$id]);
    }
    header("Location: taikhoan.php");
    exit();
}

// -----------------------------------------------------------------------
// XỬ LÝ: RESET MẬT KHẨU
// -----------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'reset') {
    $id = (int)$_POST['id'];
    $matKhauMoi = trim($_POST['mat_khau_moi']);

    if (strlen($matKhauMoi) < 6) {
        $thongBao = 'Mật khẩu phải có ít nhất 6 ký tự!';
        $loaiThongBao = 'error';
    } else {
        $matKhauBam = md5($matKhauMoi);
        $conn->prepare("UPDATE TaiKhoan SET Password = ? WHERE MaTK = ?")->execute([$matKhauBam, $id]);
        $thongBao = 'Reset mật khẩu thành công!';
        $loaiThongBao = 'success';
    }
}

// -----------------------------------------------------------------------
// XỬ LÝ: THÊM TÀI KHOẢN MỚI
// -----------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'them') {
    $hoTen    = trim($_POST['ho_ten']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $sdt      = trim($_POST['sdt']);
    $password = trim($_POST['password']);
    $vaiTro   = (int)$_POST['vai_tro'];

    // Lấy chữ cái đầu của tên để làm Avatar
    $words = explode(" ", $hoTen);
    $initials = (count($words) >= 2) ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1)) : strtoupper(substr($hoTen, 0, 2));
    $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=random&color=fff";

    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM TaiKhoan WHERE Username = ?");
    $stmtCheck->execute([$username]);

    if ($stmtCheck->fetchColumn() > 0) {
        $thongBao = "Tên đăng nhập đã tồn tại!";
        $loaiThongBao = "error";
    } elseif (strlen($password) < 6) {
        $thongBao = "Mật khẩu phải từ 6 ký tự trở lên!";
        $loaiThongBao = "error";
    } elseif (!preg_match('/^0[0-9]{9}$/', $sdt)) {
        $thongBao = "Số điện thoại không hợp lệ (Phải có 10 số và bắt đầu bằng 0)!";
        $loaiThongBao = "error";
    } else {
        try {
            $conn->beginTransaction();

            // 1. Thêm vào bảng TaiKhoan
            $stmt = $conn->prepare("INSERT INTO TaiKhoan (Username, Password, HoTen, Email, SoDienThoai, VaiTro, Avatar, TrangThai) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$username, md5($password), $hoTen, $email, $sdt, $vaiTro, $avatarUrl]);

            $maTK = $conn->lastInsertId();

            // 2. Thêm vào bảng DiaChiKhachHang nếu role là Khách Hàng (0)
            if ($vaiTro === 0) {
                $tenNhan = trim($_POST['ten_nguoi_nhan']);
                $sdtNhan = trim($_POST['sdt_nhan']);

                $diaChi = trim($_POST['dia_chi_chi_tiet']);
                $phuong = trim($_POST['phuong_xa']);
                $quan   = trim($_POST['quan_huyen']);
                $tinh   = trim($_POST['tinh_thanh']);

                // Nếu không nhập tên/SĐT nhận, lấy mặc định của tài khoản
                $tenNhan = !empty($tenNhan) ? $tenNhan : $hoTen;
                $sdtNhan = !empty($sdtNhan) ? $sdtNhan : $sdt;

                $stmtDC = $conn->prepare("INSERT INTO DiaChiKhachHang (MaTK, TenNguoiNhan, SDTNhan, DiaChiChiTiet, PhuongXa, QuanHuyen, TinhThanh) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtDC->execute([$maTK, $tenNhan, $sdtNhan, $diaChi, $phuong, $quan, $tinh]);
            }

            $conn->commit();
            $thongBao = "Thêm tài khoản thành công!";
            $loaiThongBao = "success";
        } catch (PDOException $e) {
            $conn->rollBack();
            $thongBao = "Lỗi hệ thống: Không thể thêm tài khoản.";
            $loaiThongBao = "error";
        }
    }
}

// -----------------------------------------------------------------------
// TRUY VẤN: LẤY DANH SÁCH TÀI KHOẢN (PHÂN TRANG)
// -----------------------------------------------------------------------
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$totalAccounts = $conn->query("SELECT COUNT(*) FROM TaiKhoan")->fetchColumn();
$totalPages = ceil($totalAccounts / $limit);

$stmt = $conn->prepare("SELECT * FROM TaiKhoan ORDER BY MaTK DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$danhSachTaiKhoan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Tài Khoản</title>
    <link rel="stylesheet" href="Style.css">
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .modal-content h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel-modal {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ccc;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .notification.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .notification.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Lưới form 2 cột */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            color: #007bff;
        }

        .form-hint {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: normal;
            margin-left: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include_once 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-header-title">Quản lý Tài khoản Hệ thống</h1>
            </div>

            <?php if ($thongBao): ?>
                <div class="notification <?= $loaiThongBao ?>">
                    <?= htmlspecialchars($thongBao) ?>
                </div>
            <?php endif; ?>

            <div class="page-toolbar">
                <button class="btn btn-add" onclick="document.getElementById('modalThem').classList.add('active')">
                    ➕ Thêm tài khoản mới
                </button>
            </div>

            <div class="account-list">
                <?php foreach ($danhSachTaiKhoan as $tk): ?>
                    <div class="account-item">
                        <?php
                        // Tự động tạo ảnh đại diện bằng chữ cái nếu Database chưa có ảnh
                        $avatarUrl = $tk['Avatar'];
                        if (empty($avatarUrl)) {
                            $words = explode(" ", $tk['HoTen']);
                            $initials = (count($words) >= 2) ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1)) : strtoupper(substr($tk['HoTen'], 0, 2));
                            $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=random&color=fff";
                        }
                        ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="account-avatar">

                        <div class="account-info">
                            <h4><?= htmlspecialchars($tk['HoTen']) ?> (<?= htmlspecialchars($tk['Username']) ?>)</h4>
                            <p><?= htmlspecialchars($tk['Email']) ?> | <?= htmlspecialchars($tk['SoDienThoai']) ?></p>
                            <p>Vai trò:
                                <?php if ($tk['VaiTro'] == 1): ?>
                                    <span class="role admin" style="background:#dc3545; color:white; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem;">Quản trị viên</span>
                                <?php else: ?>
                                    <span class="role customer" style="background:#28a745; color:white; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem;">Khách hàng</span>
                                <?php endif; ?>
                                <?= $tk['TrangThai'] == 0 ? '<span style="color:red; font-size: 0.85rem; margin-left: 10px; font-weight:bold;">(Đang bị khóa)</span>' : '' ?>
                            </p>
                        </div>
                        <div class="account-actions">
                            <button class="btn btn-edit" onclick="moModalReset(<?= $tk['MaTK'] ?>, '<?= htmlspecialchars($tk['Username']) ?>')">🔑 Reset MK</button>
                            <?php if ($tk['VaiTro'] != 1): ?>
                                <?php if ($tk['TrangThai'] == 1): ?>
                                    <a href="taikhoan.php?action=lock&id=<?= $tk['MaTK'] ?>" class="btn btn-delete" onclick="return confirm('Khóa tài khoản này?');">🔒 Khóa</a>
                                <?php else: ?>
                                    <a href="taikhoan.php?action=unlock&id=<?= $tk['MaTK'] ?>" class="btn btn-save" onclick="return confirm('Mở khóa tài khoản này?');">🔓 Mở khóa</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination" style="margin-top: 20px; text-align: center;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="taikhoan.php?page=<?= $i ?>" class="btn <?= ($page == $i) ? 'btn-save' : 'btn-deleteback' ?>" style="text-decoration:none; padding: 5px 10px; margin: 0 2px;">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div class="modal-overlay" id="modalThem">
        <div class="modal-content">
            <h2>Thêm Tài Khoản Mới</h2>
            <form method="POST" action="" onsubmit="return validateThem()">
                <input type="hidden" name="action" value="them">

                <div class="section-title">👤 Thông tin cơ bản</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Vai trò <span style="color:red">*</span></label>
                        <select name="vai_tro" id="inp_vaitro" required onchange="toggleAddressFields()" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                            <option value="0">Khách hàng</option>
                            <option value="1">Quản trị viên</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Họ và Tên <span style="color:red">*</span></label>
                        <input type="text" name="ho_ten" id="inp_hoten" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại <span style="color:red">*</span></label>
                        <input type="text" name="sdt" id="inp_sdt" pattern="0[0-9]{9}" maxlength="10" title="Vui lòng nhập đúng 10 số, bắt đầu bằng số 0" required style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div class="form-group full-width">
                        <label>Email <span style="color:red">*</span></label>
                        <input type="email" name="email" id="inp_email" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div class="form-group">
                        <label>Tên đăng nhập <span style="color:red">*</span></label>
                        <input type="text" name="username" id="inp_username" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu <span style="color:red">*</span></label>
                        <input type="password" name="password" id="inp_matkhau" placeholder="Ít nhất 6 ký tự" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                </div>

                <div id="address_section">
                    <div class="section-title">📍 Thông tin giao hàng <span class="form-hint">(Bỏ trống Tên/SĐT nếu người nhận là chủ tài khoản)</span></div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tên người nhận</label>
                            <input type="text" name="ten_nguoi_nhan" id="inp_tennguoinhan" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;" placeholder="Ví dụ: Nguyễn Văn B">
                        </div>
                        <div class="form-group">
                            <label>SĐT người nhận</label>
                            <input type="text" name="sdt_nhan" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;" placeholder="Ví dụ: 0987654321">
                        </div>
                        <div class="form-group full-width">
                            <label>Địa chỉ chi tiết <span style="color:red" class="req-star">*</span></label>
                            <input type="text" name="dia_chi_chi_tiet" id="inp_diachi" class="addr-input" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;" placeholder="Số nhà, tên đường...">
                        </div>
                        <div class="form-group">
                            <label>Phường/Xã <span style="color:red" class="req-star">*</span></label>
                            <input type="text" name="phuong_xa" id="inp_phuongxa" class="addr-input" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                        </div>
                        <div class="form-group">
                            <label>Quận/Huyện <span style="color:red" class="req-star">*</span></label>
                            <input type="text" name="quan_huyen" id="inp_quanhuyen" class="addr-input" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                        </div>
                        <div class="form-group full-width">
                            <label>Tỉnh/Thành phố <span style="color:red" class="req-star">*</span></label>
                            <input type="text" name="tinh_thanh" id="inp_tinhthanh" class="addr-input" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel-modal"
                        onclick="document.getElementById('modalThem').classList.remove('active')">Hủy</button>
                    <button type="submit" class="btn btn-save">💾 Tạo tài khoản</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalReset">
        <div class="modal-content" style="width: 400px;">
            <h2>Reset Mật Khẩu</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="reset">
                <input type="hidden" name="id" id="resetId">
                <p>Tài khoản: <strong id="tenTaiKhoan" style="color: #007bff;"></strong></p>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Mật khẩu mới <span style="color:red">*</span></label>
                    <input type="password" name="mat_khau_moi" required placeholder="Tối thiểu 6 ký tự..." style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel-modal"
                        onclick="document.getElementById('modalReset').classList.remove('active')">Hủy</button>
                    <button type="submit" class="btn btn-save">Xác nhận Reset</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Ẩn/Hiện form nhập địa chỉ tùy theo vai trò
        function toggleAddressFields() {
            const role = document.getElementById('inp_vaitro').value;
            const addressSection = document.getElementById('address_section');

            if (role === '0') { // 0 = Khách hàng
                addressSection.style.display = 'block';
            } else { // 1 = Admin
                addressSection.style.display = 'none';
            }
        }

        // Chạy hàm ngay khi vừa tải trang
        document.addEventListener('DOMContentLoaded', function() {
            toggleAddressFields();
        });

        // Hàm mở Modal Reset
        function moModalReset(id, ten) {
            document.getElementById('resetId').value = id;
            document.getElementById('tenTaiKhoan').innerText = ten;
            document.getElementById('modalReset').classList.add('active');
        }

        // Click ngoài modal để đóng
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });

        // Validate JavaScript trước khi Submit
        function validateThem() {
            const hoten = document.getElementById('inp_hoten').value.trim();
            const user = document.getElementById('inp_username').value.trim();
            const email = document.getElementById('inp_email').value.trim();
            const pass = document.getElementById('inp_matkhau').value.trim();
            const vaiTro = document.getElementById('inp_vaitro').value;

            if (!hoten || !user || !email || !pass) {
                alert('Vui lòng điền đầy đủ thông tin cơ bản bắt buộc!');
                return false;
            }
            if (pass.length < 6) {
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }
            
            const sdt = document.getElementById('inp_sdt').value.trim();
            const phoneRegex = /^0[0-9]{9}$/;
            if (!phoneRegex.test(sdt)) {
                alert('Số điện thoại không hợp lệ (Phải có 10 số và bắt đầu bằng 0)!');
                return false;
            }

            if (vaiTro == '0') {
                // Chỉ kiểm tra các ô địa chỉ (Bỏ qua tên và sđt người nhận)
                const dc = document.getElementById('inp_diachi').value.trim();
                const px = document.getElementById('inp_phuongxa').value.trim();
                const qh = document.getElementById('inp_quanhuyen').value.trim();
                const tt = document.getElementById('inp_tinhthanh').value.trim();

                if (!dc || !px || !qh || !tt) {
                    alert('Vui lòng điền đầy đủ thông tin địa chỉ giao hàng (Số nhà, Phường, Quận, Tỉnh)!');
                    return false;
                }
                
                const sdtNhan = document.getElementsByName('sdt_nhan')[0].value.trim();
                if (sdtNhan !== '') {
                    if (!phoneRegex.test(sdtNhan)) {
                        alert('Số điện thoại người nhận không hợp lệ (Phải có 10 số và bắt đầu bằng 0)!');
                        return false;
                    }
                }
            }
            return true;
        }

        <?php if ($loaiThongBao): ?>
            // Tự động mở lại modal nếu có lỗi khi thêm tài khoản
            <?php if ($loaiThongBao == 'error' && isset($_POST['action']) && $_POST['action'] == 'them'): ?>
                document.getElementById('modalThem').classList.add('active');
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>

</html>