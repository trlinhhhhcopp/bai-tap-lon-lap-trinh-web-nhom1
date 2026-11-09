<?php 
require_once __DIR__ . '/../../layouts/header.php'; 
$appBase = get_app_base_url();
$type = $type ?? '';
$classId = (int)($classId ?? 0);
$kw = $kw ?? '';
?>

<style>
    .admin-bar {
        background: #ffffff;
        padding: 16px 20px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .search-box { 
        display: flex; 
        gap: 8px; 
        flex: 1; 
        max-width: 680px; 
        flex-wrap: wrap;
        align-items: center;
    }
    .filter-buttons {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }
    .table-container {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
        padding: 13px 16px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }
    td { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    tr:hover td { background: #f8fafc; }
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
    }
    .badge-student  { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-agent    { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-admin    { background: #fce7f3; color: #be185d; border: 1px solid #fbcfe8; }
    .badge-lecturer { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
    .badge-active   { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-locked   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .btn {
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease-in-out;
    }
    .btn-primary { background: #0284c7; color: #fff; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2); }
    .btn-primary:hover { background: #0369a1; color: #fff; }
    .btn-outline { background: #fff; border: 1px solid #cbd5e1; color: #334155; }
    .btn-outline:hover { background: #f1f5f9; border-color: #94a3b8; }
    .btn-danger { background: #ef4444; color: #fff; }
    .btn-danger:hover { background: #dc2626; color: #fff; }
    .btn-success { background: #10b981; color: #fff; }
    .btn-success:hover { background: #059669; color: #fff; }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(3px);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    .modal-card {
        background: #fff;
        width: 100%;
        max-width: 600px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        border: 1px solid #e2e8f0;
    }
    .modal-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 18px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-body { padding: 22px 24px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-group.full { grid-column: span 2; }
    .form-label { font-size: 12px; font-weight: 700; color: #334155; }
    .form-control {
        padding: 8.5px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 13px;
        outline: none;
        background: #fff;
        color: #1e293b;
        transition: border-color 0.15s ease;
    }
    .form-control:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }
    .modal-footer {
        padding: 16px 24px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<!-- 1. HEADER BANNER ĐỒNG BỘ TOÀN HỆ THỐNG - KHÔNG CÓ KHOẢNG TRẮNG Ở ĐẦU TIÊU ĐỀ -->
<div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div class="space-y-1">
        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
            🛡️ TRUNG TÂM QUẢN TRỊ VIÊN (ADMIN CONTROL CENTER)
        </span>
        <h1 class="text-2xl font-black text-white tracking-normal">Quản Trị Tài Khoản & Phân Quyền</h1>
        <p class="text-xs text-indigo-200/80">Phân hệ Quản trị Người dùng toàn trường (Sinh viên, Giảng viên, Kỹ thuật viên) - Trường Đại học Thủ Đô Hà Nội</p>
    </div>
    <div class="text-right text-xs bg-slate-800/80 border border-slate-700/80 px-4 py-3 rounded-2xl shrink-0 shadow-inner">
        <span class="text-slate-400 block text-[11px]">Phụ trách lập trình:</span>
        <strong class="text-sky-400 font-bold text-sm">Trần Thục Linh</strong>
        <div class="text-[11.5px] text-slate-300 mt-0.5 font-mono">MSV: 224001729 (Toán Ứng Dụng D24)</div>
    </div>
</div>

<!-- 2. CONTROL BAR: TÌM KIẾM, BỘ LỌC ĐỐI TƯỢNG (SV, GV, KTV), LỌC THEO LỚP & NÚT THÊM -->
<div class="admin-bar">
    <form class="search-box" method="GET" action="<?= $appBase ?>admin/users">
        <!-- Ô nhập từ khóa tìm kiếm -->
        <input type="text" name="kw" value="<?= htmlspecialchars($kw) ?>" placeholder="Tìm kiếm theo Mã định danh, Họ tên, Email..." class="form-control" style="flex: 1; min-width: 220px;">

        <!-- Dropdown lọc theo lớp sinh hoạt -->
        <select name="class_id" class="form-control" style="max-width: 175px;" onchange="this.form.submit()">
            <option value="0">-- Lọc theo Lớp --</option>
            <?php foreach ($classes as $cl): ?>
                <option value="<?= $cl['id'] ?>" <?= ($classId === (int)$cl['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cl['class_code']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($type !== ''): ?>
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>

        <?php if ($kw !== '' || $type !== '' || $classId > 0): ?>
            <a href="<?= $appBase ?>admin/users" class="btn btn-outline" title="Xóa toàn bộ bộ lọc">✕ Xóa lọc</a>
        <?php endif; ?>
    </form>

    <!-- Nút Lọc theo Đối tượng: Tất cả, SV, GV, KTV -->
    <nav class="filter-buttons" aria-label="Lọc theo đối tượng">
        <a class="btn <?= $type === '' ? 'btn-primary' : 'btn-outline' ?>" href="<?= $appBase ?>admin/users?kw=<?= urlencode($kw) ?>&amp;class_id=<?= $classId ?>">
            Tất cả (<?= $counts['all'] ?? $totalUsers ?>)
        </a>
        <a class="btn <?= $type === 'student' ? 'btn-primary' : 'btn-outline' ?>" href="<?= $appBase ?>admin/users?kw=<?= urlencode($kw) ?>&amp;type=student&amp;class_id=<?= $classId ?>">
            🎓 SV (<?= $counts['student'] ?? 0 ?>)
        </a>
        <a class="btn <?= $type === 'lecturer' ? 'btn-primary' : 'btn-outline' ?>" href="<?= $appBase ?>admin/users?kw=<?= urlencode($kw) ?>&amp;type=lecturer&amp;class_id=<?= $classId ?>">
            👨‍🏫 GV (<?= $counts['lecturer'] ?? 0 ?>)
        </a>
        <a class="btn <?= $type === 'agent' ? 'btn-primary' : 'btn-outline' ?>" href="<?= $appBase ?>admin/users?kw=<?= urlencode($kw) ?>&amp;type=agent&amp;class_id=<?= $classId ?>">
            🛠️ KTV (<?= $counts['agent'] ?? 0 ?>)
        </a>
    </nav>

    <!-- Nhóm Nút Thao tác: Ẩn/Hiện Bảng và Thêm người dùng mới -->
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-outline" data-target-table="admin_users_table_body" onclick="toggleTableCollapse('admin_users_table_body', this)">
            <span class="toggle-table-icon">✕</span> <span class="toggle-table-text">Ẩn bảng</span>
        </button>
        <button type="button" class="btn btn-primary" onclick="openCreateModal()">
            ➕ Thêm Người Dùng Mới
        </button>
    </div>
</div>

<!-- 3. KHỐI BỌC BẢNG DỮ LIỆU CÓ THỂ ẨN / HIỆN -->
<div id="admin_users_table_body" data-collapsible-table>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 55px;">ID</th>
                    <th style="width: 120px;">Mã định danh</th>
                    <th>Họ và tên</th>
                    <th>Email trường</th>
                    <th>Vai trò</th>
                    <th>Lớp sinh hoạt</th>
                    <th>Trạng thái</th>
                    <th style="text-align: center; width: 230px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                            Không có dữ liệu người dùng nào phù hợp với bộ lọc hiện tại.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong>#<?= $u['id'] ?></strong></td>
                            <td><code style="background: #e2e8f0; padding: 2px 6px; border-radius: 6px; font-weight: bold;"><?= htmlspecialchars($u['user_code']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($u['name']) ?></strong>
                                <?php if ($u['is_first_login'] == 1): ?>
                                    <span style="font-size: 10px; color: #ea580c; display: block; font-weight: 600;">(Chưa đổi pass lần đầu)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge badge-admin">Quản trị viên</span>
                                <?php elseif ($u['role'] === 'agent'): ?>
                                    <span class="badge badge-agent">Kỹ thuật viên</span>
                                    <?php if (($u['is_on_duty'] ?? 1) == 1): ?>
                                        <span style="font-size: 10px; color: #16a34a; font-weight: bold; display: block; margin-top: 3px;" title="KTV đang trong ca trực (sẵn sàng tiếp nhận vé)">🟢 Đang trực</span>
                                    <?php else: ?>
                                        <span style="font-size: 10px; color: #dc2626; font-weight: bold; display: block; margin-top: 3px;" title="KTV đang nghỉ ca hoặc báo ốm (tạm ngưng nhận vé)">⏸️ Nghỉ ca / Ốm</span>
                                    <?php endif; ?>
                                <?php elseif ($u['user_type'] === 'lecturer'): ?>
                                    <span class="badge badge-lecturer">Giảng viên</span>
                                <?php else: ?>
                                    <span class="badge badge-student">Sinh viên</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['class_name'] ?? 'Chưa xếp lớp') ?></td>
                            <td>
                                <?php if ($u['is_active'] == 1): ?>
                                    <span class="badge badge-active">● Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge badge-locked">🔒 Bị khóa (<?= htmlspecialchars($u['lock_reason'] ?? 'none') ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 4px; align-items: center; justify-content: center; flex-wrap: wrap;">
                                    <!-- Xem chi tiết hồ sơ -->
                                    <a href="<?= $appBase ?>admin/users/detail?id=<?= $u['id'] ?>" class="btn btn-outline" style="padding: 5px 9px; font-size: 11.5px;" title="Xem hồ sơ">👁️ Xem</a>
                                    
                                    <!-- Nút Sửa modal -->
                                    <button type="button" class="btn btn-outline" style="padding: 5px 9px; font-size: 11.5px;" onclick='openEdit(<?= json_encode($u) ?>)'>✏️ Sửa</button>

                                    <!-- Bật/Tắt ca trực KTV (nếu là Agent) -->
                                    <?php if ($u['role'] === 'agent'): ?>
                                        <form method="POST" action="<?= $appBase ?>admin/users/toggle-duty" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <?php if (($u['is_on_duty'] ?? 1) == 1): ?>
                                                <button type="submit" class="btn btn-outline" style="color: #b45309; border-color: #f59e0b; padding: 5px 8px; font-size: 11px;" title="Chuyển sang trạng thái Nghỉ ca / Báo ốm">⏸️ Nghỉ</button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-outline" style="color: #15803d; border-color: #22c55e; padding: 5px 8px; font-size: 11px;" title="Bật lại ca trực (Sẵn sàng tiếp nhận vé)">▶️ Trực</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Khóa / Mở khóa tài khoản -->
                                    <form method="POST" action="<?= $appBase ?>admin/users/toggle-lock" onsubmit="return confirm('Xác nhận đổi trạng thái khóa tài khoản?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <?php if ($u['is_active'] == 1): ?>
                                            <button type="submit" class="btn btn-danger" style="padding: 5px 9px; font-size: 11.5px;" title="Khóa tài khoản">🔒 Khóa</button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-success" style="padding: 5px 9px; font-size: 11.5px;" title="Mở khóa tài khoản">🔓 Mở</button>
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

    <!-- 4. THANH PHÂN TRANG (PAGINATION) -->
    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #64748b; flex-wrap: wrap; gap: 10px;">
        <div>Hiển thị <strong><?= count($users) ?></strong> / tổng số <strong><?= $totalUsers ?></strong> người dùng (Trang <?= $page ?> / <?= $totalPages ?>)</div>
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; gap: 5px;">
                <?php if ($page > 1): ?>
                    <a href="?kw=<?= urlencode($kw) ?>&amp;type=<?= urlencode($type) ?>&amp;class_id=<?= $classId ?>&amp;page=<?= $page - 1 ?>" style="padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #334155; background: #fff; font-weight: 700;">« Trước</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?kw=<?= urlencode($kw) ?>&amp;type=<?= urlencode($type) ?>&amp;class_id=<?= $classId ?>&amp;page=<?= $p ?>" style="padding: 5px 11px; border: 1px solid <?= ($p == $page) ? '#0284c7' : '#cbd5e1' ?>; border-radius: 6px; text-decoration: none; color: <?= ($p == $page) ? '#fff' : '#334155' ?>; background: <?= ($p == $page) ? '#0284c7' : '#fff' ?>; font-weight: 700;"><?= $p ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?kw=<?= urlencode($kw) ?>&amp;type=<?= urlencode($type) ?>&amp;class_id=<?= $classId ?>&amp;page=<?= $page + 1 ?>" style="padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #334155; background: #fff; font-weight: 700;">Sau »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================================== -->
<!-- MODAL THÊM MỚI NGƯỜI DÙNG -->
<!-- ============================================================================== -->
<div id="createModal" class="modal-overlay">
    <div class="modal-card">
        <form method="POST" action="<?= $appBase ?>admin/users/create">
            <div class="modal-header">
                <h3 style="font-size: 15px; font-weight: 800; color: #fff;">➕ Thêm Tài Khoản Người Dùng Mới</h3>
                <button type="button" onclick="closeModal('createModal')" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Mã SV / Mã Cán bộ (*):</label>
                        <input type="text" name="user_code" required class="form-control" placeholder="VD: 224001799">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Họ và tên (*):</label>
                        <input type="text" name="name" required class="form-control" placeholder="VD: Hoàng Văn Nam">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Email trường học (*):</label>
                        <input type="email" name="email" required class="form-control" placeholder="VD: nam224@daihocthudo.edu.vn" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Vui lòng nhập đúng email có tên miền và dấu chấm hợp lệ">
                        <small style="font-size: 11px; color: #64748b;">Mật khẩu ban đầu mặc định là: <strong>123456</strong> (Yêu cầu đổi pass lần đầu)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vai trò hệ thống:</label>
                        <select name="role" class="form-control">
                            <option value="requester">Sinh viên (Requester)</option>
                            <option value="lecturer">Giảng viên (Lecturer)</option>
                            <option value="agent">Kỹ thuật viên IT (Agent)</option>
                            <option value="admin">Quản trị viên (Admin)</option>
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
                        <label class="form-label">Khoa / Đơn vị:</label>
                        <input type="text" name="department" class="form-control" value="Khoa: Toán-CNTT" placeholder="VD: Khoa: Toán-CNTT">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số điện thoại:</label>
                        <input type="text" name="phone" class="form-control" placeholder="VD: 0987654321">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('createModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Xác nhận Thêm</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================================== -->
<!-- MODAL SỬA THÔNG TIN NGƯỜI DÙNG -->
<!-- ============================================================================== -->
<div id="editModal" class="modal-overlay">
    <div class="modal-card">
        <form method="POST" action="<?= $appBase ?>admin/users/update">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header">
                <h3 style="font-size: 15px; font-weight: 800; color: #fff;">✏️ Cập Nhật Hồ Sơ Người Dùng</h3>
                <button type="button" onclick="closeModal('editModal')" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Mã định danh (Cố định):</label>
                        <input type="text" id="edit_code" readonly style="background:#f1f5f9;" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Họ và tên (*):</label>
                        <input type="text" name="name" id="edit_name" required class="form-control">
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Email trường (*):</label>
                        <input type="email" name="email" id="edit_email" required class="form-control" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số điện thoại:</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
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
                    <div class="form-group full">
                        <label class="form-label">Khoa / Phòng ban:</label>
                        <input type="text" name="department" id="edit_department" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Đóng</button>
                <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function openEdit(u) {
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_code').value = u.user_code;
    document.getElementById('edit_name').value = u.name;
    document.getElementById('edit_email').value = u.email;
    document.getElementById('edit_phone').value = u.phone || '';
    document.getElementById('edit_class_id').value = u.class_id || '';
    document.getElementById('edit_department').value = u.department || '';
    document.getElementById('editModal').style.display = 'flex';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
};

// Ẩn / hiện bảng dữ liệu
function toggleTableCollapse(wrapperId, buttonElement) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;
    const isHidden = wrapper.classList.toggle('hidden');
    const textSpan = buttonElement.querySelector('.toggle-table-text');
    const iconSpan = buttonElement.querySelector('.toggle-table-icon');
    if (textSpan) textSpan.textContent = isHidden ? 'Hiện bảng' : 'Ẩn bảng';
    if (iconSpan) iconSpan.textContent = isHidden ? '👁️' : '✕';
}
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
