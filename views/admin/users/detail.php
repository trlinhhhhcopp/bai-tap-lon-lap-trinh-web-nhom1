<?php require __DIR__ . '/../../layouts/header.php'; ?>

<div style="max-width: 800px; margin: 20px auto; background: #fff; border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
    <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 22px 26px; border-left: 5px solid var(--primary); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="font-size: 18px; color: #38bdf8; margin-bottom: 4px;">HỒ SƠ TÀI KHOẢN: <?= htmlspecialchars($user['name']) ?></h2>
            <p style="font-size: 12.5px; color: #cbd5e1;">Mã định danh: <code><?= htmlspecialchars($user['user_code']) ?></code> · ID CSDL: #<?= $user['id'] ?></p>
        </div>
        <div>
            <?php if ($user['is_active'] == 1): ?>
                <span style="background: #dcfce7; color: #15803d; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 12px;">● Hoạt động</span>
            <?php else: ?>
                <span style="background: #fee2e2; color: #b91c1c; padding: 4px 12px; border-radius: 12px; font-weight: 700; font-size: 12px;">🔒 Bị khóa (<?= htmlspecialchars($user['lock_reason']) ?>)</span>
            <?php endif; ?>
        </div>
    </div>

    <div style="padding: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px;">
            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Họ và tên:</div>
                <div style="font-size: 14.5px; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($user['name']) ?></div>
            </div>

            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Mã sinh viên / Cán bộ:</div>
                <div style="font-size: 14.5px; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($user['user_code']) ?></div>
            </div>

            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Email trường cấp:</div>
                <div style="font-size: 14.5px; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($user['email']) ?></div>
            </div>

            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Số điện thoại:</div>
                <div style="font-size: 14.5px; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($user['phone'] ?: 'Chưa cập nhật') ?></div>
            </div>

            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Vai trò hệ thống:</div>
                <div style="font-size: 14.5px; font-weight: 600; color: #0f172a;"><?= strtoupper($user['role']) ?> (<?= htmlspecialchars($user['user_type']) ?>)</div>
            </div>

            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Lớp sinh hoạt:</div>
                <div style="font-size: 14.5px; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($user['class_name']) ?></div>
            </div>

            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0; grid-column: span 2;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Mật khẩu băm một chiều (Bcrypt 60 ký tự):</div>
                <div style="font-family: monospace; font-size: 12px; color: #0369a1;"><?= htmlspecialchars($user['password_hash']) ?></div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 16px;">
            <a href="/admin/users" style="padding: 8px 16px; background: #e2e8f0; color: #334155; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 13px;">← Quay lại Quản trị</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>

