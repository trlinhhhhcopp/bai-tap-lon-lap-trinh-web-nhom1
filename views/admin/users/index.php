<?php require __DIR__ . '/../../layouts/header.php'; ?>

<style>
    .admin-bar {
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
    .search-box { display: flex; gap: 8px; flex: 1; max-width: 480px; }
    .table-container {
        background: #fff;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 20px;
    }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th {
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        padding: 12px 14px;
        border-bottom: 2px solid var(--border-color);
    }
    td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    tr:hover td { background: #f8fafc; }
    .badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .badge-student  { background: #e0f2fe; color: #0284c7; }
    .badge-agent    { background: #fef3c7; color: #b45309; }
    .badge-admin    { background: #fce7f3; color: #be185d; }
    .badge-lecturer { background: #ede9fe; color: #6d28d9; }
    .badge-active   { background: #dcfce7; color: #15803d; }
    .badge-locked   { background: #fee2e2; color: #b91c1c; }
    .btn {
        padding: 6px 12px;
        border-radius: 5px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-main); }
    .btn-outline:hover { background: #f1f5f9; }
    .btn-danger { background: #ef4444; color: #fff; }
    .btn-success { background: #10b981; color: #fff; }

    /* Modal */
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
    .modal-footer {
        padding: 14px 20px;
        background: #f8fafc;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 20px 24px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="font-size: 18px; color: #38bdf8; margin-bottom: 4px;">🏛️ ADMIN CENTER: QUẢN TRỊ TÀI KHOẢN & PHÂN QUYỀN</h2>
        <p style="font-size: 12.5px; color: #cbd5e1;">Phân hệ Quản trị Người dùng toàn trường (Sinh viên, Giảng viên, Kỹ thuật viên)</p>
    </div>
    <div style="text-align: right; font-size: 12.5px;">
        Phụ trách lập trình: <strong style="color: #38bdf8;">Trần Thục Linh</strong><br>
        MSV: <strong>224001729</strong> (Toán Ứng Dụng D24)
    </div>
</div>

<div class="admin-bar">
    <form class="search-box" method="GET" action="/admin/users">
        <input type="text" name="kw" value="<?= htmlspecialchars($kw) ?>" placeholder="Tìm kiếm theo Mã định danh, Họ tên, Email..." style="flex:1; padding:8px 12px; border:1px solid var(--border-color); border-radius:6px; font-size:13px;">
        <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
        <?php if ($kw !== ''): ?>
            <a href="/admin/users" class="btn btn-outline">✕ Xóa lọc</a>
        <?php endif; ?>
    </form>

    <button type="button" class="btn btn-primary" onclick="document.getElementById('createModal').style.display='flex'">
        ➕ Thêm Người Dùng Mới
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Mã định danh</th>
                <th>Họ và tên</th>
                <th>Email trường</th>
                <th>Vai trò</th>
                <th>Lớp sinh hoạt</th>
                <th>Trạng thái</th>
                <th style="text-align: center; width: 220px;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: #94a3b8;">
                        Không có dữ liệu phù hợp với từ khóa "<strong><?= htmlspecialchars($kw) ?></strong>".
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong>#<?= $u['id'] ?></strong></td>
                        <td><code><?= htmlspecialchars($u['user_code']) ?></code></td>
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
                            <div style="display: inline-flex; gap: 4px;">
                                <a href="/admin/users/detail?id=<?= $u['id'] ?>" class="btn btn-outline" title="Xem hồ sơ">👁️ Xem</a>
                                <button type="button" class="btn btn-outline" onclick='openEdit(<?= json_encode($u) ?>)'>✏️ Sửa</button>
                                <form method="POST" action="/admin/users/toggle-lock" onsubmit="return confirm('Xác nhận đổi trạng thái khóa tài khoản?');" style="display:inline;">
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

<!-- Phân trang -->
<div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--text-muted);">
    <div>Hiển thị <strong><?= count($users) ?></strong> / tổng số <strong><?= $totalUsers ?></strong> người dùng (Trang <?= $page ?> / <?= $totalPages ?>)</div>
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; gap: 4px;">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?kw=<?= urlencode($kw) ?>&page=<?= $p ?>" style="padding: 5px 10px; border: 1px solid var(--border-color); border-radius: 4px; text-decoration: none; color: <?= ($p == $page) ? '#fff' : 'var(--text-main)' ?>; background: <?= ($p == $page) ? 'var(--primary)' : '#fff' ?>; font-weight: 600;"><?= $p ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Thêm mới -->
<div id="createModal" class="modal-overlay">
    <div class="modal-card">
        <form method="POST" action="/admin/users/create">
            <div class="modal-header">
                <h3>➕ Thêm Tài Khoản Người Dùng Mới</h3>
                <button type="button" onclick="document.getElementById('createModal').style.display='none'" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;">✕</button>
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
                        <input type="email" name="email" required class="form-control" placeholder="VD: nam224@daihocthudo.edu.vn">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vai trò hệ thống:</label>
                        <select name="role" class="form-control">
                            <option value="requester">Sinh viên / Giảng viên (Requester)</option>
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
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('createModal').style.display='none'">Hủy</button>
                <button type="submit" class="btn btn-primary">Xác nhận Thêm</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa -->
<div id="editModal" class="modal-overlay">
    <div class="modal-card">
        <form method="POST" action="/admin/users/update">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header">
                <h3>✏️ Cập Nhật Hồ Sơ Người Dùng</h3>
                <button type="button" onclick="document.getElementById('editModal').style.display='none'" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;">✕</button>
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
                        <input type="email" name="email" id="edit_email" required class="form-control">
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
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal').style.display='none'">Đóng</button>
                <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(u) {
    document.getElementById('edit_id').value = u.id;
    document.getElementById('edit_code').value = u.user_code;
    document.getElementById('edit_name').value = u.name;
    document.getElementById('edit_email').value = u.email;
    document.getElementById('edit_phone').value = u.phone || '';
    document.getElementById('edit_class_id').value = u.class_id || '';
    document.getElementById('editModal').style.display = 'flex';
}
</script>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

