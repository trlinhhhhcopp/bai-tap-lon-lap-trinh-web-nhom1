<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I - ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD)
 * KHOA CÔNG NGHỆ THÔNG TIN • LỚP D24-CNTT1 • NHÓM 1
 * CHỦ ĐỀ 9: HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * ------------------------------------------------------------------------------
 * CỔNG ĐIỀU HÀNH TRUNG TÂM HỢP NHẤT (UNIFIED CENTRAL HELP DESK HUB)
 * Tích hợp toàn diện 5 phân hệ thành một hệ sinh thái web đồng nhất
 * Trưởng nhóm phụ trách: PHẠM THỊ THANH NGÂN (MSSV: 224001736)
 * ==============================================================================
 */

require_once __DIR__ . '/config/database.php';

$pdo = db();

// Thống kê số liệu thời gian thực
$totalTickets    = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$activeTickets   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('new', 'in_progress', 'waiting_requester')")->fetchColumn();
$urgentTickets   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE priority_id IN (1, 2)")->fetchColumn();
$resolvedTickets = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('resolved', 'closed')")->fetchColumn();
$totalUsers      = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses    = (int)$pdo->query("SELECT COUNT(*) FROM course_assignments")->fetchColumn();
$totalRatings    = (int)$pdo->query("SELECT COUNT(*) FROM ticket_ratings")->fetchColumn();
$avgRating       = round((float)$pdo->query("SELECT AVG(score) FROM ticket_ratings")->fetchColumn() ?: 5.0, 1);

// Lấy danh sách 5 KTV và số lượng phiếu đang xử lý
$agents = $pdo->query("
    SELECT u.id, u.user_code, u.name, c.name AS category_name,
           COUNT(t.id) AS active_load
    FROM users u
    LEFT JOIN categories c ON u.specialty_category_id = c.id
    LEFT JOIN tickets t ON t.assigned_to = u.id AND t.status IN ('new', 'in_progress', 'waiting_requester')
    WHERE u.role = 'agent'
    GROUP BY u.id, u.user_code, u.name, c.name
    ORDER BY u.id ASC
")->fetchAll();

// Lấy danh sách phiếu sự cố mới nhất
$recentTickets = $pdo->query("
    SELECT t.id, t.ticket_code, t.title, t.status, t.created_at, t.due_at,
           c.name AS category_name,
           p.name AS priority_name, p.code AS priority_code,
           req.name AS requester_name, req.user_code AS requester_code,
           ag.name AS agent_name
    FROM tickets t
    JOIN categories c ON t.category_id = c.id
    JOIN priorities p ON t.priority_id = p.id
    JOIN users req ON t.requester_id = req.id
    LEFT JOIN users ag ON t.assigned_to = ag.id
    ORDER BY t.id DESC
    LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Điều Hành Helpdesk CNTT - Đại Học Thủ Đô Hà Nội (Nhóm 1)</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">

<!-- THANH ĐIỀU HƯỚNG TỔNG HỢP HỢP NHẤT TOÀN HỆ THỐNG -->
<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- NỘI DUNG TRUNG TÂM ĐIỀU HÀNH -->
<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- HERO BANNER TỔNG HỢP ĐỒ ÁN -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 md:p-8 shadow-xl border border-indigo-900/50 relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-indigo-800/40 pb-3 text-xs">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        🏛️ ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD)
                    </span>
                    <span class="px-3 py-1 rounded-full font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        KHOA CNTT • LỚP D24-CNTT1
                    </span>
                    <span class="px-3 py-1 rounded-full font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        BÀI TẬP LỚN LẬP TRÌNH WEB — NHÓM 1
                    </span>
                </div>
                <div class="flex items-center gap-2 text-indigo-200">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Chuẩn hóa <strong>12 Bảng 3NF · ITIL v4</strong></span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2 max-w-3xl">
                    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                        HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN
                    </h1>
                    <p class="text-xs md:text-sm text-indigo-200/90 leading-relaxed">
                        Cổng điều hành hợp nhất toàn bộ 5 phân hệ nghiệp vụ: Tiếp nhận sự cố tinh gọn, Auto-Priority Mapping chống gian lận SLA, Bàn làm việc KTV điều phối khi chạm ngưỡng tràn tải <strong>15 phiếu</strong>, Quản lý phân công đào tạo và Quản trị vòng đời tài khoản học thuật.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-indigo-300">
                        <span>👑 Trưởng nhóm & Kiến trúc sư: <strong class="text-white">Phạm Thị Thanh Ngân</strong> (MSSV: 224001736)</span>
                        <span>👥 Sĩ số: <strong class="text-white">5 Thành viên</strong></span>
                        <span>🗄️ CSDL: <strong class="text-white">helpdesk_thudo (MySQL utf8mb4)</strong></span>
                    </div>
                </div>

                <!-- Phím tắt thao tác nhanh -->
                <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 shrink-0">
                    <a href="tickets/create.php" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-lg transition flex items-center justify-center gap-2 text-xs">
                        <span>➕</span> Gửi Phiếu Sự Cố Mới
                    </a>
                    <a href="agent/index.php" class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-bold rounded-2xl border border-white/20 transition flex items-center justify-center gap-2 text-xs">
                        <span>👨‍💻</span> Vào Hàng Đợi KTV Xử Lý
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 THẺ KPI ĐO LƯỜNG THỜI GIAN THỰC -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- 1. Tổng phiếu -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tổng Phiếu Sự Cố</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900"><?php echo $totalTickets; ?></span>
                <span class="text-[11px] text-slate-400">phiếu</span>
            </div>
            <span class="text-[10.5px] text-indigo-600 mt-1 block font-medium">Toàn hệ sinh thái</span>
        </div>

        <!-- 2. Phiếu đang mở -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Đang Chờ Xử Lý</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-amber-600"><?php echo $activeTickets; ?></span>
                <span class="text-[11px] text-amber-500">phiếu</span>
            </div>
            <span class="text-[10.5px] text-slate-400 mt-1 block">New / In-progress</span>
        </div>

        <!-- 3. Sự cố khẩn cấp -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block mb-1">Khẩn Cấp / Cao</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-rose-600"><?php echo $urgentTickets; ?></span>
                <span class="text-[11px] text-rose-500">phiếu</span>
            </div>
            <span class="text-[10.5px] text-slate-400 mt-1 block">SLA 2h - 4h</span>
        </div>

        <!-- 4. Đã giải quyết -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block mb-1">Đã Khắc Phục</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-emerald-600"><?php echo $resolvedTickets; ?></span>
                <span class="text-[11px] text-emerald-500">phiếu</span>
            </div>
            <span class="text-[10.5px] text-slate-400 mt-1 block">Resolved & Closed</span>
        </div>

        <!-- 5. Điểm hài lòng CSAT -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-bold text-yellow-600 uppercase tracking-wider block mb-1">Chỉ Số CSAT</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-yellow-500"><?php echo $avgRating; ?>★</span>
                <span class="text-[11px] text-slate-400">/ 5.0</span>
            </div>
            <span class="text-[10.5px] text-slate-400 mt-1 block"><?php echo $totalRatings; ?> lượt sinh viên chấm</span>
        </div>

        <!-- 6. Tài khoản học thuật -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <span class="text-[11px] font-bold text-purple-600 uppercase tracking-wider block mb-1">Tài Khoản & Lớp</span>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-purple-600"><?php echo $totalUsers; ?></span>
                <span class="text-[11px] text-purple-400">user</span>
            </div>
            <span class="text-[10.5px] text-slate-400 mt-1 block"><?php echo $totalCourses; ?> lớp học phần</span>
        </div>
    </div>

    <!-- KHU VỰC ĐIỀU PHỐI CHÍNH: HÀNG ĐỢI XỬ LÝ & TRẠNG THÁI 5 KTV (2 CỘT) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- CỘT TRÁI: DANH SÁCH PHIẾU GẦN ĐÂY CẦN XỬ LÝ (8 CỘT) -->
        <div class="lg:col-span-8 space-y-4">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>🎫</span> PHIẾU HỖ TRỢ GẦN ĐÂY & TRAO ĐỔI HAI CHIỀU (BÀI 5)
                        </h2>
                        <p class="text-[11.5px] text-slate-500 mt-0.5">Nhấp vào từng phiếu để xem chi tiết, trao đổi hai chiều, đính kèm ảnh hoặc đổi trạng thái.</p>
                    </div>
                    <a href="tickets/index.php" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition shrink-0">
                        Xem Tất Cả →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                                <th class="py-3 px-4">Mã Vé</th>
                                <th class="py-3 px-4">Tiêu Đề Sự Cố</th>
                                <th class="py-3 px-4">Chuyên Mục</th>
                                <th class="py-3 px-4 text-center">Mức Ưu Tiên</th>
                                <th class="py-3 px-4 text-center">Trạng Thái</th>
                                <th class="py-3 px-4 text-center">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($recentTickets as $t): ?>
                                <?php
                                $statusBadges = [
                                    'new'               => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'in_progress'       => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'waiting_requester' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'resolved'          => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'closed'            => 'bg-slate-100 text-slate-700 border-slate-300',
                                ];
                                $statusLabels = [
                                    'new'               => 'Mới tiếp nhận',
                                    'in_progress'       => 'Đang xử lý',
                                    'waiting_requester' => 'Chờ xác nhận',
                                    'resolved'          => 'Đã có giải pháp',
                                    'closed'            => 'Đã hoàn tất',
                                ];

                                $prioColors = [
                                    'URGENT' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'HIGH'   => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'MED'    => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'LOW'    => 'bg-slate-50 text-slate-600 border-slate-200',
                                ];
                                ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                        <a href="tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:underline">
                                            <?php echo htmlspecialchars($t['ticket_code']); ?>
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        <a href="tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:text-indigo-600 transition block max-w-xs truncate">
                                            <?php echo htmlspecialchars($t['title']); ?>
                                        </a>
                                        <span class="text-[10.5px] text-slate-400 font-normal">Bởi: <?php echo htmlspecialchars($t['requester_name']); ?> (<?php echo htmlspecialchars($t['requester_code']); ?>)</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <?php echo htmlspecialchars($t['category_name']); ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[10.5px] font-bold border <?php echo $prioColors[$t['priority_code']] ?? 'bg-slate-100 text-slate-600 border-slate-200'; ?>">
                                            <?php echo htmlspecialchars($t['priority_name']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold border <?php echo $statusBadges[$t['status']] ?? 'bg-slate-100 text-slate-600 border-slate-200'; ?>">
                                            ● <?php echo $statusLabels[$t['status']] ?? $t['status']; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="tickets/show.php?id=<?php echo $t['id']; ?>" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition inline-block">
                                            Xử lý & Phản hồi →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: TRẠNG THÁI TẢI 5 KTV & ĐIỀU PHỐI TRÀN TẢI 15 VÉ (4 CỘT) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span>👨‍💻</span> Tình Trạng Tải 5 KTV (Ngưỡng 15 Vé):
                    </h3>
                    <a href="agent/index.php" class="text-indigo-600 hover:underline text-[11px] font-bold">Chi tiết</a>
                </div>

                <div class="space-y-3">
                    <?php foreach ($agents as $ag): ?>
                        <?php 
                        $maxCapacity = 15;
                        $loadPercent = min(100, round(($ag['active_load'] / $maxCapacity) * 100));
                        $isOverloaded = ($ag['active_load'] >= $maxCapacity);
                        ?>
                        <div class="p-3 rounded-xl border <?php echo $isOverloaded ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50 border-slate-200'; ?> space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800"><?php echo htmlspecialchars($ag['name']); ?></span>
                                <span class="font-mono font-bold <?php echo $isOverloaded ? 'text-rose-600' : 'text-slate-600'; ?>">
                                    <?php echo $ag['active_load']; ?> / <?php echo $maxCapacity; ?> vé
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[10.5px] text-slate-500">
                                <span>Chuyên trách: <?php echo htmlspecialchars($ag['category_name']); ?></span>
                                <?php if ($isOverloaded): ?>
                                    <span class="text-rose-600 font-bold animate-pulse">⚠️ Kích hoạt Tràn Tải</span>
                                <?php else: ?>
                                    <span class="text-emerald-600 font-semibold">Tải an toàn</span>
                                <?php endif; ?>
                            </div>
                            <!-- Thanh tiến trình tải -->
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full <?php echo $isOverloaded ? 'bg-rose-600' : ($loadPercent > 60 ? 'bg-amber-500' : 'bg-emerald-500'); ?>" style="width: <?php echo $loadPercent; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="p-3 bg-indigo-50/70 rounded-xl border border-indigo-200 text-[11px] text-indigo-900 space-y-1">
                    <strong>⚡ Thuật Toán Điều Phối Thông Minh (ITIL v4):</strong>
                    <p class="text-indigo-800/80 leading-relaxed">
                        Phiếu mới tự động chuyển đến đúng KTV chuyên trách. Nếu KTV đó đạt $\ge 15$ phiếu (quá tải), hệ thống tự động tràn sang KTV có tải thấp nhất trong ca trực.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- KHU VỰC LIÊN KẾT TRỰC TIẾP 5 PHÂN HỆ THÀNH VIÊN -->
    <div class="space-y-3 pt-2">
        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
            <span>🏛️</span> TRUY CẬP ĐỒNG BỘ 5 PHÂN HỆ CHUYÊN TRÁCH CỦA NHÓM 1
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <!-- Phân hệ 1: KTV Workspace -->
            <a href="agent/index.php" class="bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-md rounded-2xl p-4 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl">👨‍💻</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">Trưởng nhóm</span>
                    </div>
                    <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition text-xs">Bàn Làm Việc KTV</h3>
                    <p class="text-[11px] text-slate-500 mt-1">Hàng đợi SLA due_at, phân công 5 KTV, tràn tải 15 vé.</p>
                </div>
                <div class="pt-3 border-t border-slate-100 mt-3 text-[10.5px] font-bold text-indigo-600 flex items-center justify-between">
                    <span>Phạm Thị Thanh Ngân</span>
                    <span>→</span>
                </div>
            </a>

            <!-- Phân hệ 2: Quản lý phiếu & Trao đổi 2 chiều -->
            <a href="tickets/index.php" class="bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-md rounded-2xl p-4 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl">🎫</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">Bài 5 CRUD</span>
                    </div>
                    <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition text-xs">Quản Lý Phiếu (Bài 5)</h3>
                    <p class="text-[11px] text-slate-500 mt-1">Chi tiết phiếu, chat 2 chiều SV $\leftrightarrow$ KTV, đính kèm ảnh.</p>
                </div>
                <div class="pt-3 border-t border-slate-100 mt-3 text-[10.5px] font-bold text-indigo-600 flex items-center justify-between">
                    <span>Phạm Thị Thanh Ngân</span>
                    <span>→</span>
                </div>
            </a>

            <!-- Phân hệ 3: Sinh viên & Đánh giá CSAT -->
            <a href="NguyenAnhNhi_SinhVien/views/student/ratings/index.php" class="bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-md rounded-2xl p-4 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl">⭐</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-yellow-50 text-yellow-700">CSAT 1-5★</span>
                    </div>
                    <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition text-xs">Đánh Giá Dịch Vụ CSAT</h3>
                    <p class="text-[11px] text-slate-500 mt-1">Sinh viên đánh giá chất lượng hỗ trợ 1-5 sao, đóng góp ý kiến.</p>
                </div>
                <div class="pt-3 border-t border-slate-100 mt-3 text-[10.5px] font-bold text-indigo-600 flex items-center justify-between">
                    <span>Nguyễn Ánh Nhi</span>
                    <span>→</span>
                </div>
            </a>

            <!-- Phân hệ 4: Giảng viên & Lớp học phần -->
            <a href="LeHaMy_GiangVien/views/lecturer/assignments/index.php" class="bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-md rounded-2xl p-4 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl">👨‍🏫</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-700">1 Lớp = 1 GV</span>
                    </div>
                    <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition text-xs">Giảng Viên & Lớp Học Phần</h3>
                    <p class="text-[11px] text-slate-500 mt-1">Phân công giảng dạy, đảm bảo ràng buộc 1 lớp học phần duy nhất.</p>
                </div>
                <div class="pt-3 border-t border-slate-100 mt-3 text-[10.5px] font-bold text-indigo-600 flex items-center justify-between">
                    <span>Lê Hà My</span>
                    <span>→</span>
                </div>
            </a>

            <!-- Phân hệ 5: Quản trị người dùng & Phân ca -->
            <a href="TranLinh/users.php" class="bg-white border border-slate-200 hover:border-indigo-500 hover:shadow-md rounded-2xl p-4 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl">👥</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-pink-50 text-pink-700">User CRUD</span>
                    </div>
                    <h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition text-xs">Quản Trị Người Dùng</h3>
                    <p class="text-[11px] text-slate-500 mt-1">Vòng đời tài khoản, phân ca trực KTV, khóa mềm / buộc thôi học.</p>
                </div>
                <div class="pt-3 border-t border-slate-100 mt-3 text-[10.5px] font-bold text-indigo-600 flex items-center justify-between">
                    <span>Trần Thục Linh</span>
                    <span>→</span>
                </div>
            </a>
        </div>
    </div>

</main>

<!-- CHÂN TRANG HỢP NHẤT -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
