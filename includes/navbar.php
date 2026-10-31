<?php
/**
 * ==============================================================================
 * THANH ĐIỀU HƯỚNG THEO PHÂN QUYỀN
 * Menu ngang dành cho khách; người dùng đã đăng nhập dùng sidebar cố định theo vai trò.
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Tính điểm sao CSAT trung bình kiểu Be/Grab cho KTV hiển thị trên đầu
$navbarKtvAvgScore = null;
$navbarKtvTotalRatings = 0;
$currentUser = $_SESSION['user'] ?? null;
if (!empty($currentUser) && ($currentUser['role'] ?? '') === 'agent') {
    try {
        $navAgentId = (int)$currentUser['id'];
        $csatNavStmt = db()->prepare("
            SELECT COUNT(*) as total_ratings, AVG(r.score) as avg_score 
            FROM ticket_ratings r 
            JOIN tickets t ON r.ticket_id = t.id 
            WHERE t.assigned_to = :agent_id
        ");
        $csatNavStmt->execute([':agent_id' => $navAgentId]);
        $csatNavData = $csatNavStmt->fetch();
        $navbarKtvTotalRatings = (int)($csatNavData['total_ratings'] ?? 0);
        $navbarKtvAvgScore = $navbarKtvTotalRatings > 0 ? round((float)$csatNavData['avg_score'], 1) : 5.0;
    } catch (\Throwable $e) {
        $navbarKtvAvgScore = 5.0;
    }
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
$hasFixedSidebar = $isLoggedIn;
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$appPath = rtrim((string)(parse_url($appBase, PHP_URL_PATH) ?: '/'), '/');
$currentPage = ltrim(substr($requestPath, strlen($appPath)), '/');
$currentAction = (string)($_GET['action'] ?? $_POST['action'] ?? '');
$navAttributes = static function (array $paths, ?array $actions = null) use ($currentPage, $currentAction): string {
    $isActive = in_array($currentPage, $paths, true)
        && ($actions === null || in_array($currentAction, $actions, true));
    $classes = $isActive
        ? 'px-3 py-1.5 rounded-lg font-bold text-white bg-slate-800 border-l-2 border-indigo-400 transition flex items-center gap-1.5'
        : 'px-3 py-1.5 rounded-lg font-semibold text-slate-200 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5';

    return 'class="' . $classes . '"' . ($isActive ? ' aria-current="page"' : '');
};
?>

<!-- Tailwind CSS Engine -->
<script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }
    table {
        line-height: 1.45;
    }
</style>
<?php if ($hasFixedSidebar): ?>
<style>
    body.has-role-sidebar {
        padding-left: 16rem;
    }

    .app-role-sidebar {
        width: 16rem;
    }

    @media (max-width: 767px) {
        body.has-role-sidebar {
            padding-left: 4.5rem;
        }

        .app-role-sidebar {
            width: 4.5rem;
        }

        .app-role-sidebar .sidebar-brand-copy,
        .app-role-sidebar .sidebar-user-copy {
            display: none;
        }

        .app-role-sidebar .sidebar-label {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .app-role-sidebar > a {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }
</style>
<script>
    document.body.classList.add('has-role-sidebar');
</script>
<?php endif; ?>

<!-- Điều hướng ngang cho khách, sidebar cố định cho người dùng đã đăng nhập -->
<header class="bg-slate-900 border-b border-slate-800 text-slate-100 sticky top-0 z-[70] shadow-md relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between min-h-14">
            
            <!-- Logo & Tên hệ thống -->
            <?php if (!$hasFixedSidebar): ?>
            <div class="flex items-center gap-3 shrink-0">
                <a href="<?php echo $appBase; ?>index.php" aria-label="Helpdesk CNTT Online - Trang chủ" class="flex items-center gap-2.5 text-white hover:opacity-90 transition">
                    <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-600 text-white font-black rounded-lg text-sm shadow-sm">TUD</span>
                    <div class="hidden sm:block">
                        <span class="font-bold text-white tracking-wide text-sm block leading-tight">Helpdesk CNTT Online</span>
                        <span class="text-[10.5px] text-slate-400 block leading-tight">Đại Học Thủ Đô Hà Nội</span>
                    </div>
                </a>
            </div>
            <?php else: ?>
            <span class="text-xs font-semibold tracking-wide text-slate-200">Helpdesk CNTT</span>
            <?php endif; ?>

            <!-- Menu phân quyền dạng dọc -->
            <?php if ($hasFixedSidebar): ?>
                <nav aria-label="Điều hướng chính" class="app-role-sidebar fixed top-0 bottom-0 left-0 z-[80] flex flex-col overflow-y-auto bg-slate-900 p-4 shadow-2xl ring-1 ring-white/10 items-stretch gap-1 text-sm">
                    <a href="<?php echo $appBase; ?>index.php" aria-label="Helpdesk CNTT Online - Trang chủ" class="mb-5 flex min-h-12 items-center gap-2.5 border-b border-slate-700/80 pb-4 text-white">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-sm font-black text-white shadow-sm">TUD</span>
                        <span class="sidebar-brand-copy min-w-0">
                            <span class="block text-sm font-bold leading-tight tracking-wide">Helpdesk CNTT</span>
                            <span class="block text-[10px] leading-tight text-slate-400">Đại học Thủ Đô Hà Nội</span>
                        </span>
                    </a>
            <?php else: ?>
                <nav id="primary-navigation" aria-label="Điều hướng chính" class="flex min-w-0 flex-1 items-center gap-1 overflow-x-auto px-2 text-sm">
            <?php endif; ?>
                <?php if (!$isLoggedIn): ?>
                    <!-- Menu dành cho Khách chưa đăng nhập -->
                    <a href="<?php echo $appBase; ?>index.php?action=guest_ticket" class="shrink-0 whitespace-nowrap px-2 sm:px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold text-slate-200 hover:text-white hover:bg-slate-800 transition">
                        🆘 Cứu Hộ Tài Khoản (Bị Khóa)
                    </a>
                    <a href="<?php echo $appBase; ?>index.php?action=guest_lookup" class="shrink-0 whitespace-nowrap px-2 sm:px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold text-slate-200 hover:text-white hover:bg-slate-800 transition">
                        🔍 Tra Cứu Phiếu Cứu Hộ
                    </a>

                <?php elseif ($userRole === 'admin'): ?>
                    <!-- Menu Quản trị viên (Admin) -->
                    <a href="<?php echo $appBase; ?>index.php" <?php echo $navAttributes(['index.php'], ['']); ?>>
                        <span aria-hidden="true">🛡️</span><span class="sidebar-label">Tổng Quan Quản Trị</span>
                    </a>
                    <a href="<?php echo $appBase; ?>admin/users.php" <?php echo $navAttributes(['admin/users.php', 'admin/user_detail.php']); ?>>
                        <span aria-hidden="true">👥</span><span class="sidebar-label">Quản Lý Tài Khoản & Ca Trực</span>
                    </a>
                    <a href="<?php echo $appBase; ?>admin/import_excel.php" <?php echo $navAttributes(['admin/import_excel.php']); ?>>
                        <span aria-hidden="true">📊</span><span class="sidebar-label">Nhập Excel / CSV</span>
                    </a>
                    <a href="<?php echo $appBase; ?>assignments/index.php?action=manage_catalog" <?php echo $navAttributes(['assignments/index.php'], ['manage_catalog']); ?>>
                        <span aria-hidden="true">🏫</span><span class="sidebar-label">Quản Lý Lớp & Môn</span>
                    </a>
                    <a href="<?php echo $appBase; ?>assignments/index.php" <?php echo $navAttributes(['assignments/index.php'], ['', 'index', 'create', 'edit', 'delete', 'export_csv', 'toggle_lock', 'import_assignments']); ?>>
                        <span aria-hidden="true">📚</span><span class="sidebar-label">Phân Công Giảng Dạy</span>
                    </a>
                    <a href="<?php echo $appBase; ?>admin/holidays.php" <?php echo $navAttributes(['admin/holidays.php']); ?>>
                        <span aria-hidden="true">📅</span><span class="sidebar-label">Lịch Nghỉ Lễ & SLA</span>
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" <?php echo $navAttributes(['tickets/index.php', 'tickets/show.php', 'tickets/edit.php']); ?>>
                        <span aria-hidden="true">🎫</span><span class="sidebar-label">Triage & Phân Công Phiếu</span>
                    </a>
                    <a href="<?php echo $appBase; ?>agent/index.php" <?php echo $navAttributes(['agent/index.php']); ?>>
                        <span aria-hidden="true">👨‍💻</span><span class="sidebar-label">Bàn Làm Việc KTV</span>
                    </a>
                    <a href="<?php echo $appBase; ?>agent/ratings.php" <?php echo $navAttributes(['agent/ratings.php']); ?>>
                        <span aria-hidden="true">⭐</span><span class="sidebar-label">Đánh Giá & Nhận Xét CSAT</span>
                    </a>

                <?php elseif ($userRole === 'agent'): ?>
                    <!-- Menu Kỹ thuật viên (Agent) -->
                    <a href="<?php echo $appBase; ?>agent/index.php" <?php echo $navAttributes(['agent/index.php']); ?>>
                        <span aria-hidden="true">👨‍💻</span><span class="sidebar-label">Bàn Làm Việc KTV</span>
                    </a>
                    <a href="<?php echo $appBase; ?>agent/ratings.php" <?php echo $navAttributes(['agent/ratings.php']); ?>>
                        <span aria-hidden="true">⭐</span><span class="sidebar-label">Đánh Giá & Nhận Xét CSAT</span>
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" <?php echo $navAttributes(['tickets/index.php', 'tickets/show.php', 'tickets/edit.php']); ?>>
                        <span aria-hidden="true">🎫</span><span class="sidebar-label">Nhiệm Vụ Được Giao</span>
                    </a>

                <?php elseif ($userType === 'lecturer'): ?>
                    <!-- Menu Giảng viên (Lecturer) -->
                    <a href="<?php echo $appBase; ?>index.php" <?php echo $navAttributes(['index.php'], ['']); ?>>
                        <span aria-hidden="true">🏠</span><span class="sidebar-label">Cổng Giảng Viên</span>
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/create.php" <?php echo $navAttributes(['tickets/create.php']); ?>>
                        <span aria-hidden="true">🚨</span><span class="sidebar-label">Báo Sự Cố Khẩn Cấp</span>
                    </a>
                    <a href="<?php echo $appBase; ?>assignments/index.php" <?php echo $navAttributes(['assignments/index.php'], ['', 'index', 'create', 'edit', 'delete', 'export_csv', 'toggle_lock', 'import_assignments']); ?>>
                        <span aria-hidden="true">📚</span><span class="sidebar-label">Lớp Học Phần Phụ Trách</span>
                    </a>
                    <a href="<?php echo $appBase; ?>lecturer/lessons.php" <?php echo $navAttributes(['lecturer/lessons.php']); ?>>
                        <span aria-hidden="true">📝</span><span class="sidebar-label">Giao Bài Tập Từng Buổi</span>
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" <?php echo $navAttributes(['tickets/index.php', 'tickets/show.php', 'tickets/edit.php']); ?>>
                        <span aria-hidden="true">📋</span><span class="sidebar-label">Phiếu Của Tôi</span>
                    </a>

                <?php else: ?>
                    <!-- Menu Sinh viên (Student) -->
                    <a href="<?php echo $appBase; ?>index.php" <?php echo $navAttributes(['index.php'], ['']); ?>>
                        <span aria-hidden="true">🏠</span><span class="sidebar-label">Cổng Sinh Viên</span>
                    </a>
                    <a href="<?php echo $appBase; ?>student/index.php?action=courses" <?php echo $navAttributes(['student/index.php', 'student/courses.php'], ['courses', 'course_detail', '']); ?>>
                        <span aria-hidden="true">📚</span><span class="sidebar-label">Học Phần & Bài Tập</span>
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/create.php" <?php echo $navAttributes(['tickets/create.php']); ?>>
                        <span aria-hidden="true">➕</span><span class="sidebar-label">Gửi Yêu Cầu Hỗ Trợ</span>
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" <?php echo $navAttributes(['tickets/index.php', 'tickets/show.php', 'tickets/edit.php']); ?>>
                        <span aria-hidden="true">📋</span><span class="sidebar-label">Phiếu Của Tôi</span>
                    </a>
                    <a href="<?php echo $appBase; ?>student/ratings.php" <?php echo $navAttributes(['student/ratings.php', 'student/index.php'], ['ratings', 'rating_create', 'rating_edit']); ?>>
                        <span aria-hidden="true">⭐</span><span class="sidebar-label">Đánh Giá Dịch Vụ (CSAT)</span>
                    </a>
                <?php endif; ?>
                <?php if ($hasFixedSidebar): ?>
                    <div class="sidebar-user-copy mt-auto border-t border-slate-700/80 pt-4 text-xs text-slate-200">
                        <div class="truncate font-semibold"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                        <div class="mt-1 text-slate-400"><?php echo htmlspecialchars($currentUser['user_code']); ?></div>
                    </div>
                <?php endif; ?>
            </nav>

            <!-- Góc phải: Thông tin người dùng & Đăng xuất -->
            <div class="flex items-center gap-2 shrink-0">
                <?php if ($isLoggedIn): ?>
                    <div class="hidden sm:flex items-center gap-2 bg-slate-800/80 border border-slate-700/80 px-3 py-1 rounded-xl text-xs">
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

                        <?php if ($userRole === 'agent' && $navbarKtvAvgScore !== null): ?>
                            <!-- Điểm sao trung bình như Be hiển thị trên đầu sau tên KTV -->
                            <a href="<?php echo $appBase; ?>agent/ratings.php" 
                               title="Xem chi tiết nhận xét & lọc số sao đánh giá CSAT của bạn"
                               class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-400/20 hover:bg-amber-400/30 border border-amber-400/50 text-amber-300 font-black text-xs transition shadow-xs">
                                <span class="text-amber-400">⭐</span>
                                <span class="text-white font-bold"><?php echo number_format($navbarKtvAvgScore, 1); ?></span>
                                <span class="text-[10px] text-amber-200/90 font-medium">(<?php echo $navbarKtvTotalRatings; ?>)</span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <a href="<?php echo $appBase; ?>index.php?action=logout" 
                       aria-label="Đăng xuất" title="Đăng xuất"
                       onclick="return confirm('Bạn có chắc chắn muốn đăng xuất?');" 
                       class="px-2 sm:px-2.5 py-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 rounded-xl text-xs font-bold transition flex items-center gap-1">
                        <span>🚪</span> <span class="hidden sm:inline">Đăng Xuất</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $appBase; ?>index.php" aria-label="Đăng nhập" class="px-3 sm:px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm">
                        🔑 <span class="sr-only sm:not-sr-only">Đăng Nhập</span>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <style>
        #primary-navigation > a,
        .app-role-sidebar > a {
            min-height: 44px;
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
        }

        #primary-navigation > a:focus-visible,
        .app-role-sidebar > a:focus-visible {
            outline: 2px solid #818cf8;
            outline-offset: 2px;
        }
    </style>
</header>
