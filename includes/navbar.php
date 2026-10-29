<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1 - ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * ------------------------------------------------------------------------------
 * THANH ĐIỀU HƯỚNG TỔNG HỢP HỢP NHẤT TOÀN HỆ THỐNG (UNIFIED MASTER NAVBAR)
 * ==============================================================================
 */

// Đồng bộ tên Session chuẩn HELPDESK_TUD_SESS cho toàn hệ thống
if (session_status() === PHP_SESSION_NONE) {
    session_name('HELPDESK_TUD_SESS');
    @session_start();
}

if (!function_exists('get_app_base_url')) {
    function get_app_base_url(): string {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $appRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: dirname(__DIR__));
        
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

// Cơ chế chuyển đổi vai trò nhanh (Persona Quick Switcher) hỗ trợ kiểm thử đồ án
if (isset($_GET['switch_persona'])) {
    $switchId = (int)$_GET['switch_persona'];
    try {
        if (!function_exists('db')) {
            require_once __DIR__ . '/../config/database.php';
        }
        $stmt = db()->prepare("SELECT id, user_code, name, email, role, user_type, avatar_path FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $switchId]);
        $switchedUser = $stmt->fetch();
        if ($switchedUser) {
            $_SESSION['user'] = $switchedUser;
        }
    } catch (\Throwable $e) {}
}

// Người dùng hiện tại trong Session
$currentUser = $_SESSION['user'] ?? null;
$isLoggedIn  = !empty($currentUser);

// Xác định trang hiện tại để làm sáng Tab đang chọn (Active Tab)
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$activeNav = 'home';

if (str_contains($currentUri, 'agent/') || str_contains($currentScript, 'agent/')) {
    $activeNav = 'agent';
} elseif (str_contains($currentUri, 'tickets/') || str_contains($currentScript, 'tickets/')) {
    $activeNav = 'tickets';
} elseif (str_contains($currentUri, 'student/dashboard') || str_contains($currentScript, 'student/dashboard') || str_contains($currentScript, 'views/student/')) {
    $activeNav = 'student';
} elseif (str_contains($currentUri, 'rating') || str_contains($currentScript, 'rating') || str_contains($currentScript, 'NguyenAnhNhi')) {
    $activeNav = 'ratings';
} elseif (str_contains($currentUri, 'assignments') || str_contains($currentScript, 'assignments') || str_contains($currentScript, 'LeHaMy')) {
    $activeNav = 'lecturer';
} elseif (str_contains($currentUri, 'TranLinh') || str_contains($currentScript, 'TranLinh') || str_contains($currentUri, 'admin/users')) {
    $activeNav = 'users';
} elseif (str_contains($currentUri, 'holidays') || str_contains($currentScript, 'holidays') || str_contains($currentScript, 'PhanCongThanh')) {
    $activeNav = 'admin';
} elseif ($currentUri === '/' || $currentUri === '/index.php' || $currentUri === '/hub') {
    $activeNav = 'home';
}
?>

<!-- Tailwind CSS -->
<script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>

<!-- UNIFIED MASTER NAVBAR -->
<header class="bg-slate-900 border-b border-slate-800 text-slate-100 sticky top-0 z-50 shadow-md">
    <!-- Top Bar: Nhận diện trường & Thanh đổi vai trò -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-wrap items-center justify-between gap-3 text-xs border-b border-slate-800/80">
        <div class="flex items-center gap-3">
            <a href="<?php echo $appBase; ?>" class="inline-flex items-center gap-2 text-white hover:text-sky-300 transition">
                <span class="inline-flex items-center justify-center w-7 h-7 bg-indigo-600 text-white font-black rounded-lg text-sm shadow-sm">TUD</span>
                <div>
                    <span class="font-bold tracking-wide">TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI</span>
                    <span class="text-slate-400 hidden sm:inline"> • Khoa CNTT • BTL Lập Trình Web (Nhóm 1 - D24-CNTT1)</span>
                </div>
            </a>
        </div>

        <!-- Trạng thái CSDL & Persona Quick Switcher -->
        <div class="flex items-center gap-3 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-950/60 text-emerald-400 border border-emerald-800/40 text-[11px] font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>CSDL: helpdesk_thudo</span>
            </span>

            <!-- Dropdown chuyển nhanh vai trò để kiểm thử 4 nhóm quyền -->
            <div class="relative inline-block text-left">
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ''); ?>" class="inline-flex items-center gap-1.5 bg-slate-800 border border-slate-700 rounded-xl px-2.5 py-1 text-[11px]">
                    <span class="text-slate-400">Đóng vai:</span>
                    <select name="switch_persona" onchange="this.form.submit()" class="bg-transparent text-indigo-300 font-bold focus:outline-none cursor-pointer">
                        <option value="" disabled <?php echo !$currentUser ? 'selected' : ''; ?>>-- Chọn vai trò đóng vai --</option>
                        <option value="14" <?php echo ($currentUser && $currentUser['id'] == 14) ? 'selected' : ''; ?>>🎓 SV Trần Thục Linh (224001729)</option>
                        <option value="13" <?php echo ($currentUser && $currentUser['id'] == 13) ? 'selected' : ''; ?>>👑 SV Thanh Ngân (Trưởng nhóm - 224001736)</option>
                        <option value="5" <?php echo ($currentUser && $currentUser['id'] == 5) ? 'selected' : ''; ?>>👨‍💻 KTV Phạm Hoàng Dũng (Canvas - KTV004)</option>
                        <option value="2" <?php echo ($currentUser && $currentUser['id'] == 2) ? 'selected' : ''; ?>>👨‍💻 KTV Nguyễn Văn An (SSO - KTV001)</option>
                        <option value="7" <?php echo ($currentUser && $currentUser['id'] == 7) ? 'selected' : ''; ?>>👨‍🏫 TS. Nguyễn Văn Giảng (Giảng viên - GV001)</option>
                        <option value="16" <?php echo ($currentUser && $currentUser['id'] == 16) ? 'selected' : ''; ?>>🎓 SV Nguyễn Ánh Nhi (224001742)</option>
                        <option value="15" <?php echo ($currentUser && $currentUser['id'] == 15) ? 'selected' : ''; ?>>🎓 SV Lê Hà My (224001737)</option>
                        <option value="1" <?php echo ($currentUser && $currentUser['id'] == 1) ? 'selected' : ''; ?>>🛡️ Quản Trị Viên (Admin - ADM001)</option>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar: 7 Phân Hệ Đồng Nhất -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
            <div class="flex items-center gap-1 overflow-x-auto py-1 scrollbar-none">
                <!-- 1. Trang chủ Hub -->
                <a href="<?php echo $appBase; ?>" 
                   class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 <?php echo $activeNav === 'home' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800'; ?>">
                    <span>🏠</span> Tổng Quan
                </a>

                <!-- 2. KTV Workspace (Thanh Ngân) -->
                <a href="<?php echo $appBase; ?>agent/index.php" 
                   class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 <?php echo $activeNav === 'agent' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800'; ?>">
                    <span>👨‍💻</span> KTV Workspace <span class="bg-indigo-500/30 text-indigo-300 text-[10px] px-1.5 py-0.5 rounded-md font-mono">SLA · Tràn 15</span>
                </a>

                <!-- 3. Quản Lý Phiếu Sự Cố (Bài 5 - Thanh Ngân) -->
                <a href="<?php echo $appBase; ?>tickets/index.php" 
                   class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 <?php echo $activeNav === 'tickets' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800'; ?>">
                    <span>🎫</span> Quản Lý Phiếu
                </a>

                <!-- 4. Cổng Sinh Viên (Trần Thục Linh) -->
                <a href="<?php echo $appBase; ?>student/dashboard" 
                   class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 <?php echo $activeNav === 'student' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800'; ?>">
                    <span>🎓</span> Cổng Sinh Viên
                </a>

                <!-- 5. Đánh Giá CSAT (Ánh Nhi) -->
                <a href="<?php echo $appBase; ?>NguyenAnhNhi_SinhVien/views/student/ratings/index.php" 
                   class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 <?php echo $activeNav === 'ratings' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800'; ?>">
                    <span>⭐</span> Đánh Giá CSAT
                </a>

                <!-- 6. Cổng Giảng Viên (Hà My) -->
                <a href="<?php echo $appBase; ?>LeHaMy_GiangVien/views/lecturer/assignments/index.php" 
                   class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 <?php echo $activeNav === 'lecturer' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800'; ?>">
                    <span>👨‍🏫</span> Giảng Viên & Lớp HP
                </a>

                <!-- 7. Quản Trị Người Dùng (Thục Linh) -->
                <a href="<?php echo $appBase; ?>TranLinh/users.php" 
                   class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 <?php echo $activeNav === 'users' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800'; ?>">
                    <span>👥</span> Quản Trị User
                </a>

                <!-- 8. Quản Trị Admin Ngày Lễ (Phan Công Thành) -->
                <a href="<?php echo $appBase; ?>PhanCongThanh_Admin/views/Admin/holidays/index.php" 
                   class="px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 <?php echo $activeNav === 'admin' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800'; ?>">
                    <span>📅</span> Quản Trị Ngày Lễ
                </a>
            </div>

            <!-- Góc phải: Hòm thư Webmail, Cứu hộ, Báo sự cố, Đăng xuất -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Nút mở Hòm thư nội bộ Webmail -->
                <a href="<?php echo $appBase; ?>webmail.php<?php echo $currentUser ? '?email=' . urlencode($currentUser['email']) : ''; ?>" 
                   target="_blank" 
                   title="Mở Hòm thư điện tử nội bộ @daihocthudo.edu.vn" 
                   class="px-2.5 py-1.5 bg-sky-950/70 hover:bg-sky-900 border border-sky-800/60 text-sky-300 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs">
                    <span>📬</span> <span class="hidden sm:inline">Hòm Thư Webmail</span>
                </a>

                <?php if (!$currentUser): ?>
                    <a href="<?php echo $appBase; ?>index.php?action=guest_ticket" class="hidden sm:inline-flex px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-950/40 transition">
                        🆘 Cứu Hộ Tài Khoản
                    </a>
                <?php endif; ?>

                <a href="<?php echo $appBase; ?>tickets/create.php" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1 shadow-sm">
                    <span>➕</span> Báo Sự Cố
                </a>

                <?php if ($currentUser): ?>
                    <div class="hidden md:flex items-center gap-2 bg-slate-800/80 border border-slate-700/80 px-2.5 py-1 rounded-xl text-xs">
                        <?php if (!empty($currentUser['avatar_path'])): ?>
                            <img src="<?php echo $appBase; ?>tickets/attachment?file=<?php echo urlencode($currentUser['avatar_path']); ?>" alt="Avatar" class="w-5 h-5 rounded-full object-cover border border-sky-400">
                        <?php else: ?>
                            <span class="w-5 h-5 rounded-full bg-indigo-500/30 text-indigo-300 flex items-center justify-center text-[10px] font-bold">
                                <?php echo mb_substr($currentUser['name'] ?? 'U', 0, 1, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                        <span class="text-slate-200 font-semibold max-w-[120px] truncate"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                    </div>

                    <a href="<?php echo $appBase; ?>index.php?action=logout" 
                       onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');" 
                       class="px-2.5 py-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 rounded-xl text-xs font-bold transition flex items-center gap-1">
                        <span>🚪</span> <span class="hidden sm:inline">Đăng Xuất</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $appBase; ?>index.php" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1 shadow-sm">
                        <span>🔑</span> Đăng Nhập
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
