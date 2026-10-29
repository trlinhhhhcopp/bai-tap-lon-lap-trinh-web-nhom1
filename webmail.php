<?php
/**
 * ==============================================================================
 * CỔNG HÒM THƯ ĐIỆN TỬ (@daihocthudo.edu.vn) - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * Giao diện nhận thư chính thức từ hệ thống (Sender: nga469704@gmail.com)
 * ==============================================================================
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Core/Database.php';
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

$pdo = \App\Core\Database::getConnection();
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

if (!$targetUser) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_code = '224001736' LIMIT 1");
    $stmt->execute();
    $targetUser = $stmt->fetch();
}

$currentEmail = strtolower($targetUser['email'] ?? '224001736@daihocthudo.edu.vn');
$currentName  = $targetUser['name'] ?? 'Sinh viên';
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

$sampleUsers = $pdo->query("SELECT user_code, name, email, role, user_type FROM users WHERE user_code IN ('224001736', '224001729', 'GV001', 'KTV001', 'ADM001') ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hòm Thư (@daihocthudo.edu.vn) - Đại Học Thủ Đô Hà Nội</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📬</text></svg>">
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased h-screen flex flex-col overflow-hidden">

    <!-- THANH TIÊU ĐỀ WEBMAIL (HEADER) -->
    <header class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between border-b border-slate-800 shadow-md shrink-0">
        <div class="flex items-center gap-3">
            <a href="<?php echo $appBase; ?>index.php" class="flex items-center gap-2.5 hover:opacity-90 transition">
                <span class="inline-flex items-center justify-center w-8 h-8 bg-gradient-to-tr from-sky-500 to-indigo-600 text-white font-black rounded-lg text-xs shadow-sm">TUD</span>
                <div>
                    <h1 class="font-extrabold text-sm tracking-wide leading-tight">HÒM THƯ ĐIỆN TỬ (@daihocthudo.edu.vn)</h1>
                    <p class="text-[10.5px] text-sky-400 font-semibold leading-tight">Trường Đại Học Thủ Đô Hà Nội · Mail Server Nội Bộ</p>
                </div>
            </a>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-xl text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-slate-300">Đang xem:</span>
                <strong class="text-white"><?php echo htmlspecialchars($currentName); ?></strong>
                <span class="font-mono text-sky-400">&lt;<?php echo htmlspecialchars($currentEmail); ?>&gt;</span>
            </div>

            <a href="<?php echo $appBase; ?>index.php" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <span>←</span> Cổng Helpdesk
            </a>
        </div>
    </header>

    <!-- KHUNG CHÍNH 3 CỘT (LAYOUT) -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- CỘT 1: THANH ĐIỀU HƯỚNG & DANH SÁCH TÀI KHOẢN (SIDEBAR) -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 p-4 hidden md:flex flex-col justify-between shrink-0">
            <div class="space-y-4">
                <!-- Thư mục hòm thư -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-2 mb-1">Thư Mục</span>
                    <a href="?email=<?php echo urlencode($currentEmail); ?>" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold bg-sky-500/15 text-sky-300 border border-sky-500/30">
                        <span class="flex items-center gap-2"><span>📥</span> Hộp thư đến (Inbox)</span>
                        <span class="px-2 py-0.5 rounded-full bg-sky-500 text-white text-[10px]"><?php echo count($mails); ?></span>
                    </a>
                </div>

                <!-- Chuyển đổi hòm thư -->
                <div class="pt-3 border-t border-slate-800 space-y-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-2">Chuyển Đổi Hòm Thư:</span>
                    <div class="space-y-1 text-xs">
                        <?php foreach ($sampleUsers as $su): ?>
                            <?php $isAct = (strtolower($su['email']) === $currentEmail); ?>
                            <a href="?email=<?php echo urlencode($su['email']); ?>" 
                               class="block px-3 py-2 rounded-xl transition <?php echo $isAct ? 'bg-slate-800 text-sky-300 font-bold border border-slate-700' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200'; ?>">
                                <div class="flex items-center justify-between">
                                    <span class="truncate"><?php echo htmlspecialchars($su['name']); ?></span>
                                    <span class="text-[10px] font-mono text-slate-500"><?php echo htmlspecialchars($su['user_code']); ?></span>
                                </div>
                                <div class="text-[10.5px] font-mono text-slate-500 truncate"><?php echo htmlspecialchars($su['email']); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Chú thích người gửi -->
            <div class="p-3 bg-slate-800/80 rounded-2xl border border-slate-700/80 text-[11px] text-slate-400 space-y-1">
                <div class="font-bold text-slate-200 flex items-center gap-1.5">
                    <span class="text-sky-400">✉️</span> Người gửi hệ thống:
                </div>
                <div class="font-mono text-sky-300 text-[10.5px] break-all">nga469704@gmail.com</div>
                <div class="text-[10px] text-slate-500">Thư khôi phục tài khoản gửi tự động từ máy chủ Gmail SMTP của trường.</div>
            </div>
        </aside>

        <!-- CỘT 2: DANH SÁCH THƯ ĐẾN (MAIL LIST) -->
        <section class="w-full md:w-80 lg:w-96 bg-slate-900/60 border-r border-slate-800 flex flex-col shrink-0">
            <div class="p-3.5 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider">Hộp Thư Đến</h2>
                    <span class="text-[11px] text-slate-400"><?php echo count($mails); ?> thư từ hệ thống</span>
                </div>
                <a href="?email=<?php echo urlencode($currentEmail); ?>" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-xs" title="Làm mới">
                    🔄
                </a>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-slate-800/80">
                <?php if (empty($mails)): ?>
                    <div class="p-8 text-center text-slate-500 text-xs space-y-2">
                        <span class="text-3xl block">📭</span>
                        <p>Hòm thư hiện đang trống.</p>
                        <p class="text-[11px] text-slate-600">Khi bạn bấm "Quên Mật Khẩu", thư chứa mã OTP từ <strong>nga469704@gmail.com</strong> sẽ xuất hiện tại đây.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($mails as $m): ?>
                    <?php $isActive = ($activeMail && $activeMail['id'] === $m['id']); ?>
                    <a href="?email=<?php echo urlencode($currentEmail); ?>&mail_id=<?php echo urlencode($m['id']); ?>" 
                       class="block p-3.5 transition <?php echo $isActive ? 'bg-sky-500/10 border-l-4 border-sky-400 text-white' : 'hover:bg-slate-800/50 text-slate-300'; ?>">
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <span class="text-xs font-bold text-sky-400 truncate"><?php echo htmlspecialchars($m['from_name']); ?></span>
                            <span class="text-[10px] text-slate-400 shrink-0 font-mono"><?php echo htmlspecialchars($m['sent_time'] ?? date('H:i')); ?></span>
                        </div>
                        <h3 class="text-xs font-bold text-white truncate mb-1">
                            <?php if (!empty($m['otp_code'])): ?>
                                <span class="text-amber-400">🔑 [MÃ OTP]</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($m['subject']); ?>
                        </h3>
                        <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed">
                            <?php echo strip_tags($m['body_html']); ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CỘT 3: NỘI DUNG THƯ CHI TIẾT (MAIL VIEWER RENDER THEO ẢNH 1) -->
        <main class="flex-1 bg-slate-950 p-4 sm:p-6 overflow-y-auto flex flex-col justify-between">
            <?php if ($activeMail): ?>
                <div class="max-w-xl mx-auto w-full space-y-4">
                    
                    <!-- Header tóm tắt nguồn gửi -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex items-center justify-between flex-wrap gap-2 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-md">
                                TUD
                            </div>
                            <div>
                                <div class="font-bold text-white">
                                    <?php echo htmlspecialchars($activeMail['from_name']); ?>
                                    <span class="font-mono text-sky-400 text-[11px]">&lt;<?php echo htmlspecialchars($activeMail['from_email']); ?>&gt;</span>
                                </div>
                                <div class="text-slate-400 text-[11px]">
                                    Gửi tới: <strong class="text-slate-200"><?php echo htmlspecialchars($activeMail['to_name']); ?></strong> 
                                    <span class="font-mono text-slate-400">&lt;<?php echo htmlspecialchars($activeMail['to_email']); ?>&gt;</span>
                                </div>
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono bg-slate-800 px-2.5 py-1 rounded-full border border-slate-700">
                            <?php echo htmlspecialchars($activeMail['sent_time'] ?? date('H:i:s d/m/Y')); ?>
                        </span>
                    </div>

                    <!-- Nội dung Email (Format chuẩn Ảnh 1) -->
                    <div class="rounded-3xl overflow-hidden shadow-2xl border border-slate-800">
                        <?php echo $activeMail['body_html']; ?>
                    </div>

                    <!-- Nút hành động nhanh -->
                    <?php if (!empty($activeMail['otp_code'])): ?>
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex items-center justify-between flex-wrap gap-3 text-xs">
                            <div class="text-[11.5px] text-slate-300">
                                💡 Mã OTP của bạn là: <strong class="font-mono text-amber-400 text-sm tracking-widest"><?php echo htmlspecialchars($activeMail['otp_code']); ?></strong>
                            </div>
                            <a href="<?php echo $appBase; ?>index.php?action=forgot_password&step=enter_otp" 
                               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center gap-1.5">
                                <span>🔑</span> Nhập Mã OTP Vào Helpdesk Ngay ➔
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            <?php else: ?>
                <div class="flex-1 flex items-center justify-center text-slate-500 text-xs">
                    Vui lòng chọn 1 thư bên trái để đọc nội dung.
                </div>
            <?php endif; ?>

            <!-- Footer nhỏ -->
            <footer class="text-center text-[11px] text-slate-600 pt-6">
                © 2026 Hòm thư nội bộ Trường Đại Học Thủ Đô Hà Nội · Gửi từ Sender: nga469704@gmail.com
            </footer>
        </main>

    </div>

</body>
</html>
