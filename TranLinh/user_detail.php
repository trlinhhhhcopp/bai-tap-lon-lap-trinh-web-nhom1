<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['flash'] = [
        'type'    => 'danger',
        'message' => 'Bạn phải đăng nhập với vai trò Quản trị viên (Admin) để truy cập chức năng này!'
    ];
    header('Location: ../index.php');
    exit;
}
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - BÀI 5: PHP–MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI - KHOA: TOÁN-CNTT
 * ------------------------------------------------------------------------------
 * PHÂN HỆ: USER MANAGEMENT & AUTH SECURITY
 * SINH VIÊN: TRẦN THỤC LINH - MÃ SINH VIÊN: 224001729
 * ------------------------------------------------------------------------------
 * TẬP TIN: TranLinh/user_detail.php (Chi tiết một bản ghi - Slide 7: "SELECT một bản ghi")
 * ==============================================================================
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/UserRepository.php';

$id = (int)($_GET['id'] ?? 0);
$user = UserRepository::findById($id);

// Nếu không tìm thấy người dùng (Slide 7: "Xử lý trường hợp không tìm thấy")
if (!$user) {
    set_flash('danger', "Không tìm thấy hồ sơ người dùng #{$id} trong hệ thống!");
    header('Location: users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ người dùng: <?= htmlspecialchars($user['name']) ?> - Helpdesk ĐH Thủ Đô</title>
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --navy-dark: #0f172a;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #cbd5e1;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            padding: 30px 20px;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .card {
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%);
            color: #fff;
            padding: 24px;
            border-left: 5px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h2 { font-size: 19px; color: #38bdf8; }
        .card-body { padding: 25px; }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .info-item {
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .info-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
        .info-value { font-size: 14.5px; font-weight: 600; color: #0f172a; word-break: break-all; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-locked { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <div>
                <h2>HỒ SƠ NGƯỜI DÙNG: <?= htmlspecialchars($user['name']) ?></h2>
                <p style="font-size: 12.5px; color: #cbd5e1; margin-top: 4px;">Định danh: <code><?= htmlspecialchars($user['user_code']) ?></code> · ID CSDL: #<?= $user['id'] ?></p>
            </div>
            <div>
                <?php if ($user['is_active'] == 1): ?>
                    <span class="badge badge-active">● Đang hoạt động</span>
                <?php else: ?>
                    <span class="badge badge-locked">🔒 Bị khóa (<?= htmlspecialchars($user['lock_reason']) ?>)</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Mã số sinh viên / Cán bộ:</div>
                    <div class="info-value"><?= htmlspecialchars($user['user_code']) ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Họ và tên tiếng Việt:</div>
                    <div class="info-value"><?= htmlspecialchars($user['name']) ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email trường học (@daihocthudo.edu.vn):</div>
                    <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Số điện thoại liên hệ:</div>
                    <div class="info-value"><?= htmlspecialchars($user['phone'] ?: 'Chưa cập nhật') ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Vai trò hệ thống (RBAC):</div>
                    <div class="info-value"><?= strtoupper($user['role']) ?> (<?= htmlspecialchars($user['user_type']) ?>)</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Trạng thái học vụ (Vòng đời SV):</div>
                    <div class="info-value"><?= htmlspecialchars($user['academic_status']) ?> (Khóa <?= htmlspecialchars($user['cohort'] ?: 'N/A') ?>)</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Lớp sinh hoạt chính:</div>
                    <div class="info-value"><?= htmlspecialchars($user['class_name']) ?> (Mã lớp: <?= htmlspecialchars($user['class_code']) ?>)</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Chuyên môn kỹ thuật (KTV):</div>
                    <div class="info-value"><?= htmlspecialchars($user['specialty_name']) ?></div>
                </div>

                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">Chuỗi băm mật khẩu một chiều (Bcrypt Hash 60 ký tự):</div>
                    <div class="info-value" style="font-family: monospace; font-size: 12px; color: #0369a1;">
                        <?= htmlspecialchars($user['password_hash']) ?>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <a href="users.php" class="btn btn-secondary">← Quay lại Danh sách</a>
                <span style="font-size: 12px; color: var(--text-muted);">Hệ thống Quản lý Người dùng & Phân quyền · ĐH Thủ Đô Hà Nội</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>

