<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 5: PHP–MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
 * ------------------------------------------------------------------------------
 * PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY
 * SINH VIÊN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
 * ------------------------------------------------------------------------------
 * TẬP TIN: TranLinh/users.php (Giao diện Quản trị CRUD: Danh sách, Tìm kiếm, Phân trang)
 * ==============================================================================
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/UserRepository.php';

// 1. Tiếp nhận tham số tìm kiếm & phân trang an toàn từ URL (Slide 6 & 11)
$kw      = trim($_GET['kw'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6; // Số lượng bản ghi mỗi trang

// 2. Gọi Repository lấy dữ liệu
$filters    = ['kw' => $kw];
$totalUsers = UserRepository::countActiveUsers($filters);
$totalPages = max(1, (int)ceil($totalUsers / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$users   = UserRepository::findActiveUsers($filters, $page, $perPage);
$classes = UserRepository::getClasses();

// 3. Đọc Flash Message (nếu có sau khi POST) (Slide 2)
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài 5: CRUD Users bằng PDO - Trần Thục Linh (224001729)</title>
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-light: #e0f2fe;
            --navy-dark: #0f172a;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #cbd5e1;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --success: #15803d;
            --success-bg: #dcfce7;
            --danger: #b91c1c;
            --danger-bg: #fee2e2;
            --warning: #b45309;
            --warning-bg: #fef3c7;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            padding: 24px;
        }
        .container { max-width: 1200px; margin: 0 auto; }

        /* Header Card */
        .header-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 24px 30px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.08);
            border-left: 6px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header-title h1 {
            font-size: 20px;
            color: #38bdf8;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title p { font-size: 13px; color: #cbd5e1; }
        .student-badge {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 18px;
            border-radius: 8px;
            text-align: right;
            font-size: 13px;
        }
        .student-badge strong { color: #38bdf8; font-size: 14px; }

        /* Alert / Flash Message */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 5px solid;
            animation: fadeIn 0.3s ease;
        }
        .alert-success { background: var(--success-bg); color: var(--success); border-color: var(--success); }
        .alert-danger  { background: var(--danger-bg);  color: var(--danger);  border-color: var(--danger); }
        .alert-warning { background: var(--warning-bg); color: var(--warning); border-color: var(--warning); }

        /* Controls: Search & Add */
        .control-bar {
            background: var(--bg-card);
            padding: 16px 20px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }
        .search-form { display: flex; gap: 8px; align-items: center; flex: 1; max-width: 500px; }
        .input-text {
            flex: 1;
            padding: 9px 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 13.5px;
            outline: none;
            transition: border 0.2s;
        }
        .input-text:focus { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary-light); }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-danger { background: #ef4444; color: #fff; padding: 5px 10px; font-size: 12px; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #10b981; color: #fff; padding: 5px 10px; font-size: 12px; }
        .btn-success:hover { background: #059669; }
        .btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 5px 10px; font-size: 12px; }
        .btn-outline:hover { background: #f1f5f9; border-color: #94a3b8; }

        /* Table */
        .table-responsive {
            background: var(--bg-card);
            border-radius: 10px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 700;
            padding: 12px 14px;
            border-bottom: 2px solid var(--border-color);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        tr:hover td { background-color: #f8fafc; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 600;
        }
        .badge-student   { background: #e0f2fe; color: #0284c7; }
        .badge-agent     { background: #fef3c7; color: #b45309; }
        .badge-admin     { background: #fce7f3; color: #be185d; }
        .badge-lecturer  { background: #ede9fe; color: #6d28d9; }
        .badge-active    { background: #dcfce7; color: #15803d; }
        .badge-locked    { background: #fee2e2; color: #b91c1c; }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .pagination { display: flex; gap: 4px; list-style: none; }
        .page-link {
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-main);
            text-decoration: none;
            background: #fff;
            font-weight: 500;
        }
        .page-link:hover { background: #f1f5f9; }
        .page-item.active .page-link { background: var(--primary); color: #fff; border-color: var(--primary); }
        .page-item.disabled .page-link { opacity: 0.5; pointer-events: none; }

        /* Modal / Form */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .modal-card {
            background: #fff;
            width: 100%;
            max-width: 600px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease;
        }
        .modal-header {
            background: var(--navy-dark);
            color: #fff;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body { padding: 20px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group.full { grid-column: span 2; }
        .form-label { font-size: 12.5px; font-weight: 600; color: #334155; }
        .form-control {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 13.5px;
            outline: none;
        }
        .form-control:focus { border-color: var(--primary); }
        .modal-footer {
            padding: 14px 20px;
            background: #f8fafc;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header-card">
        <div class="header-title">
            <h1>BÀI 5: QUẢN TRỊ NGƯỜI DÙNG & BẢO MẬT BẰNG PDO CRUD</h1>
            <p>Hệ thống Helpdesk CNTT Trực tuyến Trường Đại học Thủ Đô Hà Nội (Chủ đề 9)</p>
            <div style="margin-top: 8px;">
                <a href="../index.php" style="color:#38bdf8;text-decoration:none;font-size:12.5px;font-weight:bold;">← Quay lại Cổng Tổng Hợp Nhóm 1</a>
            </div>
        </div>
        <div class="student-badge">
            Sinh viên phụ trách: <strong>Trần Thục Linh</strong><br>
            Mã SV: <strong>224001729</strong> · Lớp: Toán Ứng Dụng D2024
        </div>
    </div>

    <!-- Flash Message (Slide 2 & 8) -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span>ℹ️</span>
            <div><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    <?php endif; ?>

    <!-- Control Bar: Search & Create -->
    <div class="control-bar">
        <form class="search-form" method="GET" action="users.php">
            <input type="text" name="kw" class="input-text" placeholder="Tìm theo Mã SV/GV, Họ tên, Email..." value="<?= htmlspecialchars($kw) ?>">
            <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
            <?php if ($kw !== ''): ?>
                <a href="users.php" class="btn btn-secondary">✕ Xóa lọc</a>
            <?php endif; ?>
        </form>

        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
            ➕ Thêm tài khoản mới
        </button>
    </div>

    <!-- Users Table -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Mã định danh</th>
                    <th>Họ và tên</th>
                    <th>Email trường học</th>
                    <th>Vai trò / Đối tượng</th>
                    <th>Lớp sinh hoạt</th>
                    <th>Trạng thái</th>
                    <th style="width: 220px; text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                            Không tìm thấy người dùng nào phù hợp với từ khóa "<strong><?= htmlspecialchars($kw) ?></strong>".
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong>#<?= $u['id'] ?></strong></td>
                            <td><code style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-weight: bold;"><?= htmlspecialchars($u['user_code']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($u['name']) ?></strong>
                                <?php if ($u['is_first_login'] == 1): ?>
                                    <span style="font-size: 10px; color: #ea580c; display: block;">(Chưa đổi pass lần đầu)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge badge-admin">Quản trị viên</span>
                                <?php elseif ($u['role'] === 'agent'): ?>
                                    <span class="badge badge-agent">Kỹ thuật viên</span>
                                <?php elseif ($u['user_type'] === 'lecturer'): ?>
                                    <span class="badge badge-lecturer">Giảng viên</span>
                                <?php else: ?>
                                    <span class="badge badge-student">Sinh viên</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['class_name']) ?></td>
                            <td>
                                <?php if ($u['is_active'] == 1): ?>
                                    <span class="badge badge-active">● Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge badge-locked">🔒 Bị khóa (<?= htmlspecialchars($u['lock_reason']) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 5px;">
                                    <!-- Xem chi tiết -->
                                    <a href="user_detail.php?id=<?= $u['id'] ?>" class="btn btn-outline" title="Xem hồ sơ">👁️ Xem</a>

                                    <!-- Nút Sửa -->
                                    <button type="button" class="btn btn-outline" onclick='openEditModal(<?= json_encode($u) ?>)'>✏️ Sửa</button>

                                    <!-- Nút Khóa / Mở khóa bằng POST (Slide 15) -->
                                    <form method="POST" action="user_action.php" onsubmit="return confirm('Bạn có chắc muốn đổi trạng thái khóa tài khoản này?');" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_lock">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <?php if ($u['is_active'] == 1): ?>
                                            <button type="submit" class="btn btn-danger" title="Khóa tài khoản">🔒 Khóa</button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-success" title="Mở khóa tài khoản">🔓 Mở</button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination (Slide 11) -->
    <div class="pagination-container">
        <div>
            Hiển thị <strong><?= count($users) ?></strong> trên tổng số <strong><?= $totalUsers ?></strong> người dùng
            (Trang <?= $page ?> / <?= $totalPages ?>)
        </div>

        <?php if ($totalPages > 1): ?>
            <ul class="pagination">
                <!-- Nút Trước -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $page - 1 ?>">« Trước</a>
                </li>

                <!-- Danh sách số trang -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Nút Sau -->
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?kw=<?= urlencode($kw) ?>&page=<?= $page + 1 ?>">Sau »</a>
                </li>
            </ul>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================================== -->
<!-- MODAL THÊM MỚI NGƯỜI DÙNG (CREATE FORM) -->
<!-- ============================================================================== -->
<div id="createModal" class="modal-overlay">
    <div class="modal-card">
        <form method="POST" action="user_action.php">
            <input type="hidden" name="action" value="create">
            <div class="modal-header">
                <h3>➕ Thêm Tài Khoản Người Dùng Mới</h3>
                <button type="button" onclick="closeModal('createModal')" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Mã SV / Mã Cán bộ (*):</label>
                        <input type="text" name="user_code" class="form-control" required placeholder="VD: 224001799">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Họ và tên tiếng Việt (*):</label>
                        <input type="text" name="name" class="form-control" required placeholder="VD: Lê Hoàng Nam">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Email trường học (*):</label>
                        <input type="email" name="email" class="form-control" required placeholder="VD: nam224@daihocthudo.edu.vn">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vai trò (Role):</label>
                        <select name="role" class="form-control">
                            <option value="requester">Người dùng (Requester)</option>
                            <option value="agent">Kỹ thuật viên (Agent)</option>
                            <option value="admin">Quản trị viên (Admin)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đối tượng:</label>
                        <select name="user_type" class="form-control">
                            <option value="student">Sinh viên</option>
                            <option value="lecturer">Giảng viên</option>
                            <option value="staff">Cán bộ / KTV</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lớp sinh hoạt:</label>
                        <select name="class_id" class="form-control">
                            <option value="">-- Chọn lớp sinh hoạt --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_code'] . ' - ' . $c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số điện thoại:</label>
                        <input type="text" name="phone" class="form-control" placeholder="VD: 0912345678">
                    </div>
                </div>
                <div style="font-size: 12px; color: #64748b; background: #f8fafc; padding: 10px; border-radius: 6px;">
                    ℹ️ <em>Mật khẩu khởi tạo mặc định là <strong>123456</strong> (được băm Bcrypt an toàn). Tài khoản sẽ yêu cầu đổi mật khẩu ở lần đăng nhập đầu tiên.</em>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary">Xác nhận Lưu</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================================== -->
<!-- MODAL CẬP NHẬT NGƯỜI DÙNG (EDIT FORM) -->
<!-- ============================================================================== -->
<div id="editModal" class="modal-overlay">
    <div class="modal-card">
        <form method="POST" action="user_action.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header">
                <h3>✏️ Cập Nhật Hồ Sơ Người Dùng</h3>
                <button type="button" onclick="closeModal('editModal')" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Mã SV/Cán bộ (Cố định):</label>
                        <input type="text" id="edit_user_code" class="form-control" readonly style="background: #f1f5f9;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Họ và tên (*):</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Email trường (*):</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lớp sinh hoạt:</label>
                        <select name="class_id" id="edit_class_id" class="form-control">
                            <option value="">-- Chưa xếp lớp --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số điện thoại:</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Khoa / Phòng ban công tác:</label>
                        <input type="text" name="department" id="edit_department" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Đóng</button>
                <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
}
function openEditModal(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_user_code').value = user.user_code;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_department').value = user.department || '';
    document.getElementById('edit_class_id').value = user.class_id || '';
    document.getElementById('editModal').style.display = 'flex';
}
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
}
</script>

</body>
</html>

