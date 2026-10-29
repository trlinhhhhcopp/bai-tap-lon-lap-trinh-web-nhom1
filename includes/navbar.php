<?php
/**
 * ==============================================================================
 * THANH ĐIỀU HƯỚNG TỔNG HỢP THEO PHÂN QUYỀN (ROLE-BASED NAVBAR)
 * Tự động phân luồng hiển thị menu theo vai trò: Sinh viên, Giảng viên, KTV, Admin
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

$currentUser = $_SESSION['user'] ?? null;
$isLoggedIn  = !empty($currentUser);
$userRole    = $currentUser['role'] ?? 'guest';
$userType    = $currentUser['user_type'] ?? 'guest';

// Nhận diện active tab
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$currentQuery  = $_SERVER['QUERY_STRING'] ?? '';
?>

<!-- Tailwind CSS -->
<script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>

<!-- NAVBAR CHÍNH -->
<header class="bg-slate-900 border-b border-slate-800 text-slate-100 sticky top-0 z-50 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
            
            <!-- Logo & Tên hệ thống -->
            <div class="flex items-center gap-3">
                <a href="<?php echo $appBase; ?>index.php" class="flex items-center gap-2.5 text-white hover:opacity-90 transition">
                    <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-600 text-white font-black rounded-lg text-sm shadow-sm">TUD</span>
                    <div>
                        <span class="font-bold text-white tracking-wide text-sm block leading-tight">Helpdesk CNTT Online</span>
                        <span class="text-[10.5px] text-slate-400 block leading-tight">Đại Học Thủ Đô Hà Nội</span>
                    </div>
                </a>
            </div>

            <!-- Menu phân quyền theo vai trò -->
            <nav class="hidden md:flex items-center gap-1">
                <?php if (!$isLoggedIn): ?>
                    <!-- Menu dành cho Khách chưa đăng nhập -->
                    <a href="<?php echo $appBase; ?>views/guest/create_ticket.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-950/40 transition">
                        🆘 Cứu Hộ Tài Khoản (Bị Khóa)
                    </a>
                    <a href="<?php echo $appBase; ?>views/guest/lookup_ticket.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        🔍 Tra Cứu Phiếu Cứu Hộ
                    </a>

                <?php elseif ($userRole === 'admin'): ?>
                    <!-- Menu Quản trị viên (Admin) -->
                    <a href="<?php echo $appBase; ?>index.php" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-slate-800 transition flex items-center gap-1.5">
                        <span>🛡️</span> Tổng Quan Quản Trị
                    </a>
                    <a href="<?php echo $appBase; ?>TranLinh/users.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                        <span>👥</span> Quản Lý Tài Khoản & Ca Trực
                    </a>
                    <a href="<?php echo $appBase; ?>admin/import_excel.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/40 transition flex items-center gap-1.5">
                        <span>📊</span> Nhập Excel / CSV
                    </a>
                    <a href="<?php echo $appBase; ?>LeHaMy_GiangVien/index.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-300 hover:text-amber-200 hover:bg-amber-950/40 transition flex items-center gap-1.5">
                        <span>📚</span> Phân Công Giảng Dạy
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                        <span>🎫</span> Triage & Phân Công Phiếu
                    </a>
                    <a href="<?php echo $appBase; ?>agent/index.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                        <span>👨‍💻</span> Bàn Làm Việc KTV
                    </a>

                <?php elseif ($userRole === 'agent'): ?>
                    <!-- Menu Kỹ thuật viên (Agent) -->
                    <a href="<?php echo $appBase; ?>agent/index.php" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-slate-800 transition flex items-center gap-1.5">
                        <span>👨‍💻</span> Bàn Làm Việc KTV (Agent Workspace)
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                        <span>🎫</span> Nhiệm Vụ Được Giao
                    </a>

                <?php elseif ($userType === 'lecturer'): ?>
                    <!-- Menu Giảng viên (Lecturer) -->
                    <a href="<?php echo $appBase; ?>index.php" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-slate-800 transition flex items-center gap-1.5">
                        <span>🏠</span> Cổng Giảng Viên
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/create.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-amber-300 hover:text-amber-200 hover:bg-amber-950/40 transition flex items-center gap-1.5">
                        <span>🚨</span> Báo Sự Cố Khẩn Cấp (HIGH / URGENT)
                    </a>
                    <a href="<?php echo $appBase; ?>LeHaMy_GiangVien/index.php?action=index" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                        <span>📚</span> Lớp Học Phần Phụ Trách
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                        <span>📋</span> Phiếu Của Tôi
                    </a>

                <?php else: ?>
                    <!-- Menu Sinh viên (Student) -->
                    <a href="<?php echo $appBase; ?>index.php" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-slate-800 transition flex items-center gap-1.5">
                        <span>🏠</span> Cổng Sinh Viên
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/create.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/40 transition flex items-center gap-1.5">
                        <span>➕</span> Gửi Yêu Cầu Hỗ Trợ
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                        <span>📋</span> Phiếu Của Tôi
                    </a>
                    <a href="<?php echo $appBase; ?>NguyenAnhNhi_SinhVien/index.php?action=ratings" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-yellow-300 hover:text-yellow-200 hover:bg-yellow-950/40 transition flex items-center gap-1.5">
                        <span>⭐</span> Đánh Giá Dịch Vụ (CSAT)
                    </a>
                <?php endif; ?>
            </nav>

            <!-- Góc phải: Thông tin người dùng & Đăng xuất -->
            <div class="flex items-center gap-2.5">
                <?php if ($isLoggedIn): ?>
                    <div class="flex items-center gap-2 bg-slate-800/80 border border-slate-700/80 px-3 py-1 rounded-xl text-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="font-semibold text-white"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                        <span class="text-slate-400 font-mono text-[11px]">(<?php echo htmlspecialchars($currentUser['user_code']); ?>)</span>
                        <span class="px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 text-[10px] font-bold uppercase">
                            <?php 
                            if ($userRole === 'admin') echo 'Admin';
                            elseif ($userRole === 'agent') echo 'KTV';
                            elseif ($userType === 'lecturer') echo 'Giảng Viên';
                            else echo 'Sinh Viên';
                            ?>
                        </span>
                    </div>

                    <a href="<?php echo $appBase; ?>index.php?action=logout" 
                       onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');" 
                       class="px-2.5 py-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 rounded-xl text-xs font-bold transition flex items-center gap-1">
                        <span>🚪</span> <span class="hidden sm:inline">Đăng Xuất</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $appBase; ?>index.php" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm">
                        🔑 Đăng Nhập
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</header>
