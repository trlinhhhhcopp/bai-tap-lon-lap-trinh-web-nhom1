<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1 - ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * ------------------------------------------------------------------------------
 * HEADER LAYOUT CHUẨN ĐỒNG NHẤT CHO MỌI VIEW TRONG HỆ THỐNG
 * ==============================================================================
 */

require_once __DIR__ . '/../../config/database.php';

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
    <!-- Tailwind CSS Engine -->
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --navy-dark: #0f172a;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* Hiệu ứng trượt vào mượt mà của Flash Message */
        @keyframes flashSlideIn {
            0% { opacity: 0; transform: translateY(-12px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .alert-system-box {
            animation: flashSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes alertTimer {
            from { width: 100%; }
            to { width: 0%; }
        }

        .alert-progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3.5px;
            background: currentColor;
            opacity: 0.35;
            border-radius: 0 0 16px 16px;
            animation: alertTimer 4.5s linear forwards;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

<!-- THANH ĐIỀU HƯỚNG TỔNG HỢP HỢP NHẤT TOÀN BỘ DỰ ÁN -->
<?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

<!-- NỘI DUNG CHÍNH CỦA TRANG -->
<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-8 flex-1 flex flex-col">

    <!-- Cảnh báo CSDL dự phòng nếu chưa bật MySQL -->
    <?php if (class_exists('App\Core\Database') && \App\Core\Database::isFallback()): ?>
        <div class="mb-5 bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3.5 rounded-2xl shadow-sm flex items-start gap-3 text-xs md:text-sm">
            <span class="text-xl">⚠️</span>
            <div>
                <strong>Lưu ý về CSDL:</strong> Hệ thống đang tạm thời hoạt động ở <em>Chế độ dữ liệu dự phòng</em> do MySQL trong XAMPP chưa được kích hoạt. 
                Để xem lại đầy đủ tất cả các phiếu bạn đã tạo trên phpMyAdmin, bạn chỉ cần mở <strong>XAMPP Control Panel</strong> và bấm <strong>Start tại ô MySQL</strong> rồi nhấn <strong>F5</strong> nhé!
            </div>
        </div>
    <?php endif; ?>

    <!-- Hệ thống Flash Alert mềm mại 2026 -->
    <?php if (!empty($flash)): ?>
        <?php
            $type = $flash['type'] ?? 'info';
            $bgClasses = [
                'success' => 'bg-emerald-50/90 border-emerald-200 text-emerald-900 shadow-emerald-500/10',
                'danger'  => 'bg-rose-50/90 border-rose-200 text-rose-900 shadow-rose-500/10',
                'warning' => 'bg-amber-50/90 border-amber-200 text-amber-900 shadow-amber-500/10',
                'info'    => 'bg-sky-50/90 border-sky-200 text-sky-900 shadow-sky-500/10',
            ];
            $iconBg = [
                'success' => 'bg-emerald-100 text-emerald-700',
                'danger'  => 'bg-rose-100 text-rose-700',
                'warning' => 'bg-amber-100 text-amber-700',
                'info'    => 'bg-sky-100 text-sky-700',
            ];
            $iconSymbol = [
                'success' => '✓',
                'danger'  => '✕',
                'warning' => '⚠️',
                'info'    => 'ℹ️',
            ];
            $cardClass = $bgClasses[$type] ?? $bgClasses['info'];
            $iBg = $iconBg[$type] ?? $iconBg['info'];
            $iSym = $iconSymbol[$type] ?? 'ℹ️';
        ?>
        <div class="alert-system-box relative mb-6 px-4 py-3.5 rounded-2xl border shadow-lg flex items-center gap-3.5 <?= $cardClass ?>" id="systemFlashAlert">
            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0 shadow-sm <?= $iBg ?>">
                <?= $iSym ?>
            </div>
            <div class="flex-1 text-xs md:text-sm font-medium leading-relaxed">
                <?= $flash['message'] ?>
            </div>
            <button type="button" onclick="closeSystemFlash()" class="opacity-40 hover:opacity-100 text-current p-1.5 rounded-full hover:bg-black/5 transition text-base leading-none" title="Đóng thông báo">✕</button>
            <?php if (empty($flash['message']) || !str_contains($flash['message'], 'resetCountdownBox')): ?>
                <div class="alert-progress-bar"></div>
            <?php endif; ?>
        </div>
        <script>
        function closeSystemFlash() {
            const flash = document.getElementById('systemFlashAlert');
            if (!flash) return;
            flash.style.transition = 'opacity 0.35s ease, transform 0.35s ease, margin-bottom 0.35s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-12px) scale(0.98)';
            flash.style.marginBottom = '0';
            setTimeout(function() {
                if (flash && flash.parentNode) flash.parentNode.removeChild(flash);
            }, 350);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const flash = document.getElementById('systemFlashAlert');
            if (flash && !flash.querySelector('#resetCountdownBox')) {
                setTimeout(function() {
                    closeSystemFlash();
                }, 4500);
            }
        });
        </script>
    <?php endif; ?>
