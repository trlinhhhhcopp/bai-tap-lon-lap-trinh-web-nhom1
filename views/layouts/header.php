<?php
if (!function_exists('get_app_base_url')) {
    function get_app_base_url(): string {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $appRoot = str_replace('\\', '/', realpath(__DIR__ . '/../..') ?: dirname(dirname(__DIR__)));
        if ($docRoot && str_starts_with($appRoot, $docRoot)) {
            $rel = substr($appRoot, strlen($docRoot));
            $base = '/' . trim(str_replace('\\', '/', $rel), '/') . '/';
            return str_replace('//', '/', $base);
        }
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $folder = 'bai-tap-lon-lap-trinh-web-nhom1';
        $pos = strpos($script, $folder);
        if ($pos !== false) {
            return substr($script, 0, $pos + strlen($folder)) . '/';
        }
        return '/';
    }
}
$appBase = get_app_base_url();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Helpdesk CNTT - Đại Học Thủ Đô Hà Nội') ?></title>
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
            --info: #0369a1;
            --info-bg: #e0f2fe;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }
        .brand-logo {
            width: 38px;
            height: 38px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            color: #fff;
        }
        .brand-text h1 { font-size: 15px; color: #38bdf8; font-weight: 700; text-transform: uppercase; }
        .brand-text p { font-size: 11px; color: #94a3b8; }
        .navbar-menu { display: flex; align-items: center; gap: 14px; list-style: none; }
        .navbar-menu a {
            color: #e2e8f0;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .navbar-menu a:hover { background: rgba(255,255,255,0.1); color: #38bdf8; }
        .user-pill {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-pill strong { color: #38bdf8; }
        .container { max-width: 1200px; margin: 24px auto; padding: 0 16px; flex: 1; width: 100%; }
        
        .alert {
            position: relative;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid;
            overflow: hidden;
            animation: flashSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes flashSlideIn {
            0% {
                opacity: 0;
                transform: translateY(-12px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #166534;
            border-color: #bbf7d0;
            box-shadow: 0 10px 20px -4px rgba(34, 197, 94, 0.15);
        }
        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #991b1b;
            border-color: #fecaca;
            box-shadow: 0 10px 20px -4px rgba(239, 68, 68, 0.15);
        }
        .alert-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            color: #92400e;
            border-color: #fde68a;
            box-shadow: 0 10px 20px -4px rgba(245, 158, 11, 0.15);
        }
        .alert-info {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            color: #075985;
            border-color: #bae6fd;
            box-shadow: 0 10px 20px -4px rgba(14, 165, 233, 0.15);
        }

        .alert-icon-wrapper {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 15px;
            font-weight: 800;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }
        .alert-close-btn {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.45;
            line-height: 1;
            padding: 4px;
            border-radius: 50%;
            transition: all 0.2s;
            color: inherit;
            margin-left: 8px;
        }
        .alert-close-btn:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.06);
        }
        .alert-progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.3;
            border-radius: 0 0 12px 12px;
            animation: alertTimer 3s linear forwards;
        }
        @keyframes alertTimer {
            from { width: 100%; }
            to { width: 0%; }
        }

        .footer {
            background: #0f172a;
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
            padding: 16px;
            border-top: 1px solid #1e293b;
            margin-top: auto;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="<?= $appBase ?>index.php" class="navbar-brand">
        <div class="brand-logo">TUD</div>
        <div class="brand-text">
            <h1>Helpdesk CNTT Online</h1>
            <p>Trường Đại học Thủ Đô Hà Nội · Nhóm 1</p>
        </div>
    </a>

    <div class="navbar-menu" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <a href="<?= $appBase ?>index.php">🏠 Tổng Quan</a>
        <a href="<?= $appBase ?>agent/index.php">👨‍💻 KTV Workspace</a>
        <a href="<?= $appBase ?>tickets/index.php">🎫 Quản Lý Phiếu</a>
        <a href="<?= $appBase ?>views/student/dashboard.php">🎓 Cổng Sinh Viên</a>
        <a href="<?= $appBase ?>NguyenAnhNhi_SinhVien/views/student/ratings/index.php">⭐ CSAT</a>
        <a href="<?= $appBase ?>LeHaMy_GiangVien/views/lecturer/assignments/index.php">👨‍🏫 Giảng Viên</a>
        <a href="<?= $appBase ?>TranLinh/users.php">👥 Người Dùng</a>

        <?php if ($currentUser): ?>
            <div class="user-pill" style="display: flex; align-items: center; gap: 8px;">
                <?php if (!empty($currentUser['avatar_path'])): ?>
                    <img src="<?= $appBase ?>tickets/attachment?file=<?= urlencode($currentUser['avatar_path']) ?>" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; border: 1.5px solid #38bdf8;">
                <?php else: ?>
                    <span>👤</span>
                <?php endif; ?>
                <span><?= htmlspecialchars($currentUser['name']) ?></span>
                <span style="opacity: 0.6;">|</span>
                <span style="color: #cbd5e1;"><?= strtoupper($currentUser['role']) ?></span>
            </div>
            <a href="<?= $appBase ?>public/index.php/logout" style="color: #f87171;" onclick="return confirm('Bạn có chắc muốn đăng xuất?');">🚪 Đăng xuất</a>
        <?php else: ?>
            <a href="<?= $appBase ?>public/index.php/login" style="background: var(--primary); color: #fff; padding: 6px 12px; border-radius: 6px;">🔑 Đăng nhập</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <?php if (\App\Core\Database::isFallback()): ?>
        <div class="alert alert-warning" style="margin-bottom: 16px;">
            <div class="alert-icon-wrapper">⚠️</div>
            <div>
                <strong>Lưu ý về CSDL:</strong> Bạn đang tạm thời ở <em>Chế độ dữ liệu dự phòng cục bộ</em> do MySQL trong XAMPP chưa được bật. 
                Để xem lại đầy đủ tất cả các phiếu bạn đã tạo trên phpMyAdmin, bạn chỉ cần mở <strong>XAMPP Control Panel</strong> và bấm <strong>Start tại ô MySQL</strong> rồi nhấn <strong>F5</strong> nhé!
            </div>
        </div>
    <?php endif; ?>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" id="systemFlashAlert">
            <div class="alert-icon-wrapper">
                <?php if ($flash['type'] === 'success'): ?>
                    <span style="color: #16a34a;">✓</span>
                <?php elseif ($flash['type'] === 'danger'): ?>
                    <span style="color: #dc2626;">✕</span>
                <?php elseif ($flash['type'] === 'warning'): ?>
                    <span>⚠️</span>
                <?php else: ?>
                    <span style="color: #0284c7;">ℹ️</span>
                <?php endif; ?>
            </div>
            <div style="flex: 1; font-weight: 500;"><?= $flash['message'] ?></div>
            <button type="button" onclick="closeSystemFlash()" class="alert-close-btn" title="Đóng thông báo">✕</button>
            <?php if (empty($flash['message']) || !str_contains($flash['message'], 'resetCountdownBox')): ?>
                <div class="alert-progress-bar"></div>
            <?php endif; ?>
        </div>
        <script>
        function closeSystemFlash() {
            const flash = document.getElementById('systemFlashAlert');
            if (!flash) return;
            flash.style.transition = 'opacity 0.4s ease, transform 0.4s ease, margin-bottom 0.4s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-12px) scale(0.98)';
            flash.style.marginBottom = '0';
            setTimeout(function() {
                if (flash && flash.parentNode) flash.parentNode.removeChild(flash);
            }, 400);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const flash = document.getElementById('systemFlashAlert');
            // Nếu không phải là flash message đặc biệt có đồng hồ đếm ngược 15 phút của quên mật khẩu
            if (flash && !flash.querySelector('#resetCountdownBox')) {
                setTimeout(function() {
                    closeSystemFlash();
                }, 3000); // Tự động biến mất sau đúng 3 giây (3000ms)
            }
        });
        </script>
    <?php endif; ?>

    <!-- Nội dung view con sẽ chèn vào đây -->

