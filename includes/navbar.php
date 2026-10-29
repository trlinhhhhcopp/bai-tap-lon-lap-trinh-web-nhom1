<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1 - ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * ------------------------------------------------------------------------------
 * THANH ĐIỀU HƯỚNG TỔNG HỢP DUY NHẤT (UNIFIED MASTER NAVIGATION BAR)
 * Tích hợp toàn diện 5 phân hệ thành 1 ứng dụng web thống nhất, đồng bộ CSDL & Session
 * Trưởng nhóm phụ trách: PHẠM THỊ THANH NGÂN (MSSV: 224001736)
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Tự động tính toán đường dẫn gốc Base URL linh hoạt cho mọi môi trường (XAMPP / PHP Server)
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

// 2. Cơ chế chuyển đổi vai trò nhanh (Persona Quick Switcher) hỗ trợ kiểm thử đồ án
if (isset($_GET['switch_persona'])) {
    $switchId = (int)$_GET['switch_persona'];
    try {
        if (function_exists('db')) {
            $stmt = db()->prepare("SELECT id, user_code, name, email, role, user_type, avatar_path FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $switchId]);
            $switchedUser = $stmt->fetch();
            if ($switchedUser) {
                $_SESSION['user'] = $switchedUser;
            }
        }
    } catch (\Throwable $e) {}
}

// Người dùng hiện tại trong Session (Mặc định là Trưởng nhóm Ngân nếu chưa đăng nhập)
$currentUser = $_SESSION['user'] ?? [
    'id'        => 13,
    'user_code' => '224001736',
    'name'      => 'Phạm Thị Thanh Ngân',
    'role'      => 'requester',
    'user_type' => 'student',
    'email'     => '224001736@daihocthudo.edu.vn'
];

// Xác định trang hiện tại để làm sáng Tab đang chọn (Active Tab)
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$activeNav = 'home';
if (str_contains($currentScript, 'agent/')) {
    $activeNav = 'agent';
} elseif (str_contains($currentScript, 'tickets/')) {
    $activeNav = 'tickets';
} elseif (str_contains($currentScript, 'student/') || str_contains($currentScript, 'Student')) {
    if (str_contains($currentScript, 'rating')) {
        $activeNav = 'ratings';
    } else {
        $activeNav = 'student';
    }
} elseif (str_contains($currentScript, 'assignments') || str_contains($currentScript, 'GiangVien') || str_contains($currentScript, 'lecturer')) {
    $activeNav = 'lecturer';
} elseif (str_contains($currentScript, 'users.php') || str_contains($currentScript, 'admin/users') || str_contains($currentScript, 'TranLinh')) {
    $activeNav = 'users';
} elseif (str_contains($currentScript, 'index.php') && !str_contains($currentScript, '/agent/') && !str_contains($currentScript, '/tickets/')) {
    $activeNav = 'home';
}
?>

<!-- Tailwind CSS nếu trang chưa nhúng -->
<script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>

<!-- UNIFIED MASTER NAVBAR -->
<header class="bg-slate-900 border-b border-slate-800 text-slate-100 sticky top-0 z-50 shadow-md">
    <!-- Top Bar: Nhận diện trường & Thanh đổi vai trò -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex flex-wrap items-center justify-between gap-3 text-xs border-b border-slate-800/80">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-7 h-7 bg-indigo-600 text-white font-black rounded-lg text-sm shadow-sm">TUD</span>
            <div>
                <span class="font-bold text-white tracking-wide">TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI</span>
                <span class="text-slate-400 hidden sm:inline"> • Khoa CNTT • BTL Lập Trình Web (Nhóm 1 - D24-CNTT1)</span>
            </div>
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
                        <option value="13" <?php echo ($currentUser['id'] == 13) ? 'selected' : ''; ?>>👑 SV Thanh Ngân (Trưởng nhóm - 224001736)</option>
                        <option value="5" <?php echo ($currentUser['id'] == 5) ? 'selected' : ''; ?>>👨‍💻 KTV Phạm Hoàng Dũng (Canvas - KTV004)</option>
                        <option value="2" <?php echo ($currentUser['id'] == 2) ? 'selected' : ''; ?>>👨‍💻 KTV Nguyễn Văn An (SSO - KTV001)</option>
                        <option value="7" <?php echo ($currentUser['id'] == 7) ? 'selected' : ''; ?>>👨‍🏫 TS. Nguyễn Văn Giảng (Giảng viên - GV001)</option>
                        <option value="14" <?php echo ($currentUser['id'] == 14) ? 'selected' : ''; ?>>🎓 SV Trần Thục Linh (224001729)</option>
                        <option value="16" <?php echo ($currentUser['id'] == 16) ? 'selected' : ''; ?>>🎓 SV Nguyễn Ánh Nhi (224001742)</option>
                        <option value="15" <?php echo ($currentUser['id'] == 15) ? 'selected' : ''; ?>>🎓 SV Lê Hà My (224001737)</option>
                        <option value="1" <?php echo ($currentUser['id'] == 1) ? 'selected' : ''; ?>>🛡️ Quản Trị Viên (Admin - ADM001)</option>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar: 7 Phân Hệ Đồng Nhất -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
            <div class="flex items-center gap-1 overflow-x-auto py-1 scrollbar-none">
                <!-- 1. Trang chủ -->
                <a href="<?php echo $appBase; ?>index.php" 
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
                    <span>🎫</span> Quản Lý Phiếu (Bài 5)
                </a>

                <!-- 4. Cổng Sinh Viên -->
                <a href="<?php echo $appBase; ?>views/student/dashboard.php" 
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
                    <span>👥</span> Quản Trị Người Dùng
                </a>
            </div>

            <!-- Nút tạo phiếu nhanh & Đăng xuất -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="<?php echo $appBase; ?>tickets/create.php" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1 shadow-sm">
                    <span>➕</span> Báo Sự Cố
                </a>
            </div>
        </div>
    </div>
</header>
