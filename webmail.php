<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * CỔNG HÒM THƯ ĐIỆN TỬ NỘI BỘ (TUD WEBMAIL - @daihocthudo.edu.vn)
 * ==============================================================================
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Services/InternalMailboxService.php';

use App\Services\InternalMailboxService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('get_app_base_url')) {
    function get_app_base_url(): string {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $appRoot = str_replace('\\', '/', realpath(__DIR__) ?: __DIR__);
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

// Xác định hòm thư người dùng hiện tại
$pdo = db();
$targetParam = trim($_GET['email'] ?? ($_GET['user'] ?? ''));
$targetUser = null;

if (!empty($targetParam)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id1 OR user_code = :id2 LIMIT 1");
    $stmt->execute(['id1' => $targetParam, 'id2' => $targetParam]);
    $targetUser = $stmt->fetch();
}

if (!$targetUser) {
    if (!empty($_SESSION['user'])) {
        $targetUser = $_SESSION['user'];
    } elseif (!empty($_SESSION['reset_otp_data'])) {
        $rUid = (int)$_SESSION['reset_otp_data']['user_id'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $rUid]);
        $targetUser = $stmt->fetch();
    }
}

// Mặc định là SV Trưởng nhóm Phạm Thị Thanh Ngân nếu chưa có ai
if (!$targetUser) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_code = '224001736' LIMIT 1");
    $stmt->execute();
    $targetUser = $stmt->fetch();
}

$currentEmail = strtolower($targetUser['email'] ?? '224001736@daihocthudo.edu.vn');
$currentName  = $targetUser['name'] ?? 'Phạm Thị Thanh Ngân';
$currentCode  = $targetUser['user_code'] ?? '224001736';

// Lấy danh sách thư của email này
$mails = InternalMailboxService::getMails($currentEmail);

// Thư đang xem chi tiết
$activeMailId = trim($_GET['mail_id'] ?? '');
$activeMail = null;

if (!empty($activeMailId)) {
    foreach ($mails as $m) {
        if ($m['id'] === $activeMailId) {
            $activeMail = $m;
            break;
        }
    }
}
if (!$activeMail && !empty($mails)) {
    $activeMail = $mails[0];
}

// Lấy danh sách tài khoản mẫu để ban giám khảo / sinh viên chuyển đổi hòm thư
$sampleUsers = $pdo->query("SELECT user_code, name, email, role, user_type FROM users WHERE user_code IN ('224001736', '224001729', 'GV001', 'KTV001', 'ADM001') ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hòm Thư Nội Bộ (@daihocthudo.edu.vn) - Đại Học Thủ Đô Hà Nội</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📬</text></svg>">
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden">

    <!-- THANH TIÊU ĐỀ WEBMAIL (HEADER) -->
    <header class="bg-indigo-900 text-white px-4 py-2.5 flex items-center justify-between border-b border-indigo-950/60 shadow-sm shrink-0">
        <div class="flex items-center gap-3">
            <a href="<?php echo $appBase; ?>index.php" class="flex items-center gap-2 hover:opacity-90 transition">
                <span class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center font-black text-sm text-white shadow-xs">TUD</span>
                <div>
                    <h1 class="text-sm font-black tracking-wide leading-none flex items-center gap-1.5">
                        <span>Webmail Đại Học Thủ Đô</span>
                        <span class="text-[10px] bg-indigo-800 text-indigo-200 px-1.5 py-0.5 rounded font-normal">Hòm Thư Nội Bộ</span>
                    </h1>
                    <p class="text-[10.5px] text-indigo-300 leading-tight mt-0.5">Hệ thống thư điện tử học thuật @daihocthudo.edu.vn</p>
                </div>
            </a>
        </div>

        <!-- Bộ chuyển đổi hòm thư tài khoản -->
        <div class="flex items-center gap-2">
            <div class="hidden sm:flex items-center gap-2 bg-indigo-950/70 border border-indigo-700/60 px-3 py-1 rounded-xl text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="text-indigo-200 text-[11px]">Hòm thư:</span>
                <strong class="text-white font-mono"><?php echo htmlspecialchars($currentEmail); ?></strong>
                <span class="text-indigo-300 font-bold">(<?php echo htmlspecialchars($currentName); ?>)</span>
            </div>

            <!-- Dropdown chọn hòm thư khác -->
            <form method="GET" action="webmail.php" class="flex items-center gap-1">
                <select name="user" onchange="this.form.submit()" class="bg-indigo-950 border border-indigo-700 text-indigo-100 text-xs rounded-xl px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-400 cursor-pointer">
                    <?php foreach ($sampleUsers as $su): ?>
                        <option value="<?php echo htmlspecialchars($su['user_code']); ?>" <?php echo ($su['user_code'] === $currentCode) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($su['name'] . ' (' . $su['user_code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <a href="<?php echo $appBase; ?>index.php" class="px-3 py-1.5 bg-indigo-700 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1">
                <span>←</span> Cổng Helpdesk
            </a>
        </div>
    </header>

    <!-- GIAO DIỆN CHÍNH 3 CỘT (WEBMAIL INTERFACE) -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- CỘT 1: THANH ĐIỀU HƯỚNG THƯ MỤC TRÁI -->
        <aside class="w-56 bg-white border-r border-slate-200 p-3 flex flex-col justify-between shrink-0 hidden md:flex">
            <div class="space-y-3">
                <button type="button" onclick="alert('Tính năng gửi thư mới từ Webmail sinh viên đang hoạt động ở chế độ nhận tự động từ hệ thống Helpdesk!');" 
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold text-xs shadow-md shadow-indigo-100 flex items-center justify-center gap-2 transition">
                    <span>✏️</span> Soạn Thư Mới
                </button>

                <nav class="space-y-1 text-xs">
                    <a href="webmail.php?email=<?php echo urlencode($currentEmail); ?>" class="flex items-center justify-between px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 font-bold">
                        <span class="flex items-center gap-2"><span>📥</span> Hộp Thư Đến</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-600 text-white font-mono"><?php echo count($mails); ?></span>
                    </a>
                    <a href="#" onclick="alert('Không có thư quan trọng'); return false;" class="flex items-center justify-between px-3 py-2 rounded-xl text-slate-600 hover:bg-slate-50 font-medium">
                        <span class="flex items-center gap-2"><span>⭐</span> Có Gắn Sao</span>
                        <span class="text-slate-400 text-[10px]">0</span>
                    </a>
                    <a href="#" onclick="alert('Chưa có thư đã gửi'); return false;" class="flex items-center justify-between px-3 py-2 rounded-xl text-slate-600 hover:bg-slate-50 font-medium">
                        <span class="flex items-center gap-2"><span>📤</span> Đã Gửi</span>
                        <span class="text-slate-400 text-[10px]">0</span>
                    </a>
                    <a href="#" onclick="alert('Thùng rác trống'); return false;" class="flex items-center justify-between px-3 py-2 rounded-xl text-slate-600 hover:bg-slate-50 font-medium">
                        <span class="flex items-center gap-2"><span>🗑️</span> Thùng Rác</span>
                        <span class="text-slate-400 text-[10px]">0</span>
                    </a>
                </nav>
            </div>

            <!-- Quota Dung lượng lưu trữ -->
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-[11px] space-y-1.5">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Dung lượng:</span>
                    <strong class="font-mono text-slate-800">14.8 MB / 50 GB</strong>
                </div>
                <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-indigo-600 h-full w-[2%]"></div>
                </div>
                <p class="text-[10px] text-slate-400">Gói Office 365 Education TUD</p>
            </div>
        </aside>

        <!-- CỘT 2: DANH SÁCH THƯ (INBOX LIST) -->
        <section class="w-full md:w-80 lg:w-96 bg-white border-r border-slate-200 flex flex-col shrink-0 overflow-y-auto">
            <div class="p-3.5 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Hộp Thư Đến</h2>
                    <p class="text-[11px] text-slate-500 font-mono"><?php echo htmlspecialchars($currentEmail); ?></p>
                </div>
                <span class="text-[10.5px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                    <?php echo count($mails); ?> thư
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                <?php foreach ($mails as $m): 
                    $isSelected = ($activeMail && $activeMail['id'] === $m['id']);
                ?>
                    <a href="webmail.php?email=<?php echo urlencode($currentEmail); ?>&mail_id=<?php echo urlencode($m['id']); ?>" 
                       class="block p-3.5 transition <?php echo $isSelected ? 'bg-indigo-50/80 border-l-4 border-indigo-600' : 'hover:bg-slate-50'; ?>">
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <strong class="<?php echo $isSelected ? 'text-indigo-900' : 'text-slate-800'; ?> truncate max-w-[170px]">
                                <?php echo htmlspecialchars($m['from_name']); ?>
                            </strong>
                            <span class="text-[10px] text-slate-400 whitespace-nowrap"><?php echo htmlspecialchars($m['sent_time'] ?? date('H:i')); ?></span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900 truncate mb-1">
                            <?php if (!empty($m['otp_code'])): ?>
                                <span class="text-amber-600">🔑 [MÃ OTP]</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($m['subject']); ?>
                        </h4>
                        <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                            <?php echo strip_tags($m['body_html']); ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CỘT 3: NỘI DUNG THƯ CHI TIẾT (MAIL VIEWER) -->
        <main class="flex-1 bg-slate-50/60 p-4 sm:p-6 overflow-y-auto flex flex-col justify-between">
            <?php if ($activeMail): ?>
                <div class="max-w-3xl mx-auto w-full bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 space-y-6">
                    
                    <!-- Header của Email -->
                    <div class="border-b border-slate-100 pb-5 space-y-3">
                        <div class="flex items-start justify-between flex-wrap gap-2">
                            <h2 class="text-lg font-black text-slate-900">
                                <?php echo htmlspecialchars($activeMail['subject']); ?>
                            </h2>
                            <span class="text-[11px] text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full font-mono">
                                <?php echo htmlspecialchars($activeMail['sent_time'] ?? date('H:i:s d/m/Y')); ?>
                            </span>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                                TUD
                            </div>
                            <div class="text-xs space-y-0.5">
                                <div class="font-bold text-slate-900">
                                    <?php echo htmlspecialchars($activeMail['from_name']); ?> 
                                    <span class="font-normal text-slate-400 font-mono">&lt;<?php echo htmlspecialchars($activeMail['from_email']); ?>&gt;</span>
                                </div>
                                <div class="text-slate-500 text-[11px]">
                                    Gửi tới: <strong class="text-slate-800"><?php echo htmlspecialchars($activeMail['to_name']); ?></strong> 
                                    <span class="font-mono">&lt;<?php echo htmlspecialchars($activeMail['to_email']); ?>&gt;</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nội dung Email -->
                    <div class="text-xs text-slate-700 leading-relaxed space-y-4">
                        <?php echo $activeMail['body_html']; ?>
                    </div>

                    <!-- Nút hành động quay lại Helpdesk -->
                    <?php if (!empty($activeMail['otp_code'])): ?>
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3">
                            <div class="text-[11px] text-slate-500">
                                💡 Bạn có thể sao chép mã OTP trên và quay lại Cổng Helpdesk để hoàn tất đổi mật khẩu.
                            </div>
                            <a href="<?php echo $appBase; ?>index.php?action=forgot_password&step=enter_otp" 
                               class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-indigo-100 flex items-center gap-2">
                                <span>🔑</span> Nhập Mã OTP Vào Cổng Helpdesk Ngay ➔
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            <?php else: ?>
                <div class="flex-1 flex items-center justify-center text-slate-400 text-xs">
                    Vui lòng chọn 1 thư để đọc nội dung.
                </div>
            <?php endif; ?>

            <!-- Footer nhỏ -->
            <footer class="text-center text-[11px] text-slate-400 pt-4">
                © 2026 Hòm thư nội bộ Trường Đại Học Thủ Đô Hà Nội · Bảo mật đa tầng theo tiêu chuẩn Office 365 Education
            </footer>
        </main>

    </div>

</body>
</html>
