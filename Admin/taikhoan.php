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
        $passHash = md5($matKhauMoi);
        $conn->prepare("UPDATE TaiKhoan SET Password = ? WHERE MaTK = ?")->execute([$passHash, $id]);
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
    $vaiTro   = (int)$_POST['vai_tro'];
    $matKhau  = trim($_POST['mat_khau']);

    if (empty($hoTen) || empty($username) || empty($email) || empty($matKhau)) {
        $thongBao = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
        $loaiThongBao = 'error';
    } elseif (strlen($matKhau) < 6) {
        $thongBao = 'Mật khẩu phải có ít nhất 6 ký tự!';
        $loaiThongBao = 'error';
    } else {
        // Kiểm tra trùng username hoặc email
        $check = $conn->prepare("SELECT COUNT(*) FROM TaiKhoan WHERE Username = ? OR Email = ?");
        $check->execute([$username, $email]);
        if ($check->fetchColumn() > 0) {
            $thongBao = 'Tên đăng nhập hoặc Email đã tồn tại!';
            $loaiThongBao = 'error';
        } else {
            $passHash = md5($matKhau);
            $conn->prepare("INSERT INTO TaiKhoan (HoTen, Username, Password, Email, SoDienThoai, VaiTro, TrangThai) VALUES (?, ?, ?, ?, ?, ?, 1)")
                ->execute([$hoTen, $username, $passHash, $email, $sdt, $vaiTro]);
            $thongBao = 'Thêm tài khoản thành công!';
            $loaiThongBao = 'success';
        }
    }
}

// -----------------------------------------------------------------------
// LẤY DANH SÁCH TÀI KHOẢN
// -----------------------------------------------------------------------
$stmt = $conn->query("SELECT * FROM TaiKhoan ORDER BY VaiTro DESC, MaTK DESC");
$danhSachTK = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Tài khoản</title>
    <link rel="stylesheet" href="Style.css">
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            width: 420px;
            max-width: 95vw;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-box h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.2rem;
        }

        .modal-box .form-group {
            margin-bottom: 14px;
        }

        .modal-box label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
        }

        .modal-box input,
        .modal-box select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn-cancel-modal {
            padding: 9px 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #f5f5f5;
            cursor: pointer;
            font-size: 14px;
        }

        .alert-box {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <div class="alert-box alert-<?= $loaiThongBao ?>">
                    <?= htmlspecialchars($thongBao) ?>
                </div>
            <?php endif; ?>

            <div class="page-toolbar">
                <button class="btn btn-add" onclick="document.getElementById('modalThem').classList.add('active')">
                    ➕ Thêm tài khoản mới
                </button>
            </div>

            <div class="account-list">
                <?php foreach ($danhSachTK as $tk): ?>
                    <div class="account-item">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($tk['HoTen']) ?>&background=random&color=fff&size=100"
                            alt="Avatar" class="account-avatar" style="border-radius: 50%;">

                        <div class="account-info">
                            <h4><?= htmlspecialchars($tk['HoTen']) ?> (<?= htmlspecialchars($tk['Username']) ?>)</h4>
                            <p><?= htmlspecialchars($tk['Email']) ?></p>
                            <p>Vai trò:
                                <?php if ($tk['VaiTro'] == 1): ?>
                                    <span class="role admin">Quản trị viên</span>
                                <?php else: ?>
                                    <span class="role customer">Khách hàng</span>
                                <?php endif; ?>
                                <?php if ($tk['TrangThai'] == 0): ?>
                                    <span style="color:red; font-size:12px; margin-left:10px;">(Đang bị khóa)</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="account-actions">
                            <!-- Nút Reset mật khẩu -->
                            <button class="btn btn-reset"
                                onclick="moModalReset(<?= $tk['MaTK'] ?>, '<?= htmlspecialchars($tk['HoTen'], ENT_QUOTES) ?>')">
                                🔑 Reset MK
                            </button>

                            <!-- Nút Khóa / Mở khóa (không cho tự khóa chính mình) -->
                            <?php if ($tk['MaTK'] != $_SESSION['admin']['MaTK']): ?>
                                <?php if ($tk['TrangThai'] == 1): ?>
                                    <a href="taikhoan.php?action=lock&id=<?= $tk['MaTK'] ?>"
                                        class="btn btn-lock" style="text-decoration:none;"
                                        onclick="return confirm('Xác nhận khóa tài khoản này?')">🔒 Khoá</a>
                                <?php else: ?>
                                    <a href="taikhoan.php?action=unlock&id=<?= $tk['MaTK'] ?>"
                                        class="btn btn-unlock" style="text-decoration:none;">🔓 Mở khoá</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- ===================== MODAL THÊM TÀI KHOẢN ===================== -->
    <div class="modal-overlay" id="modalThem">
        <div class="modal-box">
            <h3>➕ Thêm tài khoản mới</h3>
            <form method="POST">
                <input type="hidden" name="action" value="them">
                <div class="form-group">
                    <label>Họ tên <span style="color:red">*</span></label>
                    <input type="text" name="ho_ten" required placeholder="Nhập họ và tên...">
                </div>
                <div class="form-group">
                    <label>Tên đăng nhập <span style="color:red">*</span></label>
                    <input type="text" name="username" required placeholder="Nhập username...">
                </div>
                <div class="form-group">
                    <label>Email <span style="color:red">*</span></label>
                    <input type="email" name="email" required placeholder="Nhập email...">
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="sdt" placeholder="Nhập SĐT...">
                </div>
                <div class="form-group">
                    <label>Vai trò <span style="color:red">*</span></label>
                    <select name="vai_tro">
                        <option value="0">Khách hàng</option>
                        <option value="1">Quản trị viên</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mật khẩu khởi tạo <span style="color:red">*</span></label>
                    <input type="password" name="mat_khau" required placeholder="Tối thiểu 6 ký tự...">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel-modal"
                        onclick="document.getElementById('modalThem').classList.remove('active')">Hủy</button>
                    <button type="submit" class="btn btn-save">Lưu tài khoản</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== MODAL RESET MẬT KHẨU ===================== -->
    <div class="modal-overlay" id="modalReset">
        <div class="modal-box">
            <h3>🔑 Reset mật khẩu cho: <span id="tenTaiKhoan"></span></h3>
            <form method="POST">
                <input type="hidden" name="action" value="reset">
                <input type="hidden" name="id" id="resetId">
                <div class="form-group">
                    <label>Mật khẩu mới <span style="color:red">*</span></label>
                    <input type="password" name="mat_khau_moi" required placeholder="Tối thiểu 6 ký tự...">
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

        <?php if ($loaiThongBao): ?>
            // Tự động mở lại modal nếu có lỗi khi thêm tài khoản
            <?php if ($loaiThongBao == 'error' && isset($_POST['action']) && $_POST['action'] == 'them'): ?>
                document.getElementById('modalThem').classList.add('active');
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>

</html>