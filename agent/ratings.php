<?php
// ==============================================================================
// PHÂN HỆ: AGENT WORKSPACE - BÁO CÁO ĐÁNH GIÁ & NHẬN XÉT CSAT
// THỰC THỂ: TICKET_RATINGS & TICKETS & USERS
// Tác giả: Nhóm 1 - ĐH Thủ Đô Hà Nội
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc đăng nhập
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$currentUser = $_SESSION['user'];
$userRole    = $currentUser['role'] ?? 'requester';

// Chỉ Kỹ thuật viên (agent) và Quản trị viên (admin) mới được xem
if (!in_array($userRole, ['agent', 'admin'])) {
    set_flash('error', 'Bạn không có quyền truy cập vào trang đánh giá Kỹ thuật viên!');
    header('Location: ../index.php');
    exit;
}

$isAgent = ($userRole === 'agent');
$isAdmin = ($userRole === 'admin');

// 1. Xác định ID KTV cần xem
if ($isAgent) {
    $agentId = (int)$currentUser['id'];
} else {
    // Admin có thể xem đánh giá của từng KTV
    $agentId = (int)($_GET['agent_id'] ?? 2);
}

$db = db();

// 2. Lấy thông tin KTV
$agentStmt = $db->prepare("
    SELECT u.*, c.name AS specialty_name, c.code AS specialty_code 
    FROM users u 
    LEFT JOIN categories c ON u.specialty_category_id = c.id 
    WHERE u.id = :id AND u.role = 'agent'
");
$agentStmt->execute([':id' => $agentId]);
$currentAgent = $agentStmt->fetch();

if (!$currentAgent) {
    $agentId = 2;
    $agentStmt->execute([':id' => 2]);
    $currentAgent = $agentStmt->fetch();
}

// 3. Thống kê tổng quan CSAT (Điểm TB kiểu Be/Grab và số lượng từng loại sao)
$csatStatsStmt = $db->prepare("
    SELECT 
        COUNT(*) as total_ratings,
        AVG(r.score) as avg_score,
        SUM(CASE WHEN r.score = 5 THEN 1 ELSE 0 END) as count_5,
        SUM(CASE WHEN r.score = 4 THEN 1 ELSE 0 END) as count_4,
        SUM(CASE WHEN r.score = 3 THEN 1 ELSE 0 END) as count_3,
        SUM(CASE WHEN r.score = 2 THEN 1 ELSE 0 END) as count_2,
        SUM(CASE WHEN r.score = 1 THEN 1 ELSE 0 END) as count_1
    FROM ticket_ratings r
    JOIN tickets t ON r.ticket_id = t.id
    WHERE t.assigned_to = :agent_id
");
$csatStatsStmt->execute([':agent_id' => $agentId]);
$stats = $csatStatsStmt->fetch() ?: [];

$totalRatings = (int)($stats['total_ratings'] ?? 0);
$avgScore     = $totalRatings > 0 ? round((float)$stats['avg_score'], 1) : 5.0;
$count5       = (int)($stats['count_5'] ?? 0);
$count4       = (int)($stats['count_4'] ?? 0);
$count3       = (int)($stats['count_3'] ?? 0);
$count2       = (int)($stats['count_2'] ?? 0);
$count1       = (int)($stats['count_1'] ?? 0);

// Tính phần trăm từng mức sao
$pct5 = $totalRatings > 0 ? round(($count5 / $totalRatings) * 100) : 0;
$pct4 = $totalRatings > 0 ? round(($count4 / $totalRatings) * 100) : 0;
$pct3 = $totalRatings > 0 ? round(($count3 / $totalRatings) * 100) : 0;
$pct2 = $totalRatings > 0 ? round(($count2 / $totalRatings) * 100) : 0;
$pct1 = $totalRatings > 0 ? round(($count1 / $totalRatings) * 100) : 0;

// 4. Lọc theo số sao nếu người dùng bấm nút lọc (5 sao, 4 sao, 3 sao, 2 sao, 1 sao)
$starFilter = isset($_GET['star']) && in_array((int)$_GET['star'], [1, 2, 3, 4, 5], true) ? (int)$_GET['star'] : 0;

$sql = "
    SELECT r.*, 
           t.id as ticket_id, t.ticket_code, t.title as ticket_title, t.system_affected, t.created_at as ticket_created_at,
           c.name as category_name, c.code as category_code,
           u.name as student_name, u.user_code as student_code, u.email as student_email
    FROM ticket_ratings r
    JOIN tickets t ON r.ticket_id = t.id
    JOIN categories c ON t.category_id = c.id
    JOIN users u ON r.user_id = u.id
    WHERE t.assigned_to = :agent_id
";
$params = [':agent_id' => $agentId];

if ($starFilter > 0) {
    $sql .= " AND r.score = :star";
    $params[':star'] = $starFilter;
}

$sql .= " ORDER BY r.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$ratingsList = $stmt->fetchAll();

// 5. Nếu là Admin: lấy danh sách 5 KTV để chuyển đổi
$allAgents = [];
if ($isAdmin) {
    $allAgents = $db->query("
        SELECT u.*, c.name AS specialty_name,
               (SELECT COUNT(*) FROM ticket_ratings r JOIN tickets t ON r.ticket_id = t.id WHERE t.assigned_to = u.id) as rating_count,
               (SELECT ROUND(AVG(r.score), 1) FROM ticket_ratings r JOIN tickets t ON r.ticket_id = t.id WHERE t.assigned_to = u.id) as agent_avg
        FROM users u 
        LEFT JOIN categories c ON u.specialty_category_id = c.id 
        WHERE u.role = 'agent' AND u.is_active = 1
        ORDER BY u.id ASC
    ")->fetchAll();
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh Giá & Nhận Xét CSAT - KTV <?php echo htmlspecialchars($currentAgent['name']); ?> - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- NAVBAR CHÍNH -->
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-8 space-y-6">

        <!-- HEADER BANNER VỚI ĐIỂM TRUNG BÌNH SAO KIỂU BE/GRAB -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        ⭐ ĐÁNH GIÁ CHẤT LƯỢNG DỊCH VỤ (ITIL v4 CSAT)
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300">
                        🎯 Chuyên môn: <?php echo htmlspecialchars($currentAgent['specialty_name'] ?? 'Hỗ trợ CNTT'); ?>
                    </span>
                </div>
                
                <h1 class="text-2xl font-black text-white flex items-center gap-3 flex-wrap">
                    <span><?php echo htmlspecialchars($currentAgent['name']); ?></span>
                    <span class="text-xs font-mono text-indigo-300 font-semibold">(<?php echo htmlspecialchars($currentAgent['user_code']); ?>)</span>
                    
                    <!-- ĐIỂM TRUNG BÌNH SAO CSAT KIỂU BE/GRAB ĐẶT NGAY SAU TÊN KTV -->
                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1 bg-amber-400/20 border border-amber-400/50 text-amber-300 rounded-full text-xs font-black shadow-sm backdrop-blur-xs">
                        <span class="text-amber-400 text-base">⭐</span>
                        <span class="text-base font-black text-white"><?php echo number_format($avgScore, 1); ?></span>
                        <span class="text-[11px] text-amber-200/90 font-medium">/ 5.0 (<?php echo $totalRatings; ?> đánh giá)</span>
                    </div>
                </h1>
                <p class="text-xs text-indigo-200/80 mt-1">Lắng nghe phản hồi và mức độ hài lòng của Sinh viên & Cán bộ sau khi nghiệm thu sự cố.</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="index.php<?php echo $isAdmin ? '?agent_id=' . $agentId : ''; ?>" 
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                    <span>👨‍💻</span> Bàn Làm Việc KTV
                </a>
                <a href="../tickets/index.php" 
                   class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                    <span>🎫</span> Danh Sách Vé
                </a>
            </div>
        </div>

        <!-- DÀNH CHO ADMIN: CHUYỂN ĐỔI XEM ĐÁNH GIÁ CỦA 5 KTV -->
        <?php if ($isAdmin): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-slate-700 font-bold">
                    <span class="text-base">👔</span> <span>Admin giám sát chất lượng KTV:</span>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <?php foreach ($allAgents as $ag): 
                        $isSelected = ((int)$ag['id'] === $agentId);
                        $agAvg = $ag['agent_avg'] ? number_format((float)$ag['agent_avg'], 1) : '5.0';
                    ?>
                        <a href="ratings.php?agent_id=<?php echo $ag['id']; ?>" 
                           class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 <?php echo $isSelected ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">
                            <span><?php echo htmlspecialchars($ag['name']); ?></span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full <?php echo $isSelected ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'; ?>">
                                ⭐ <?php echo $agAvg; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- KHỐI TỔNG QUAN ĐIỂM SỐ VÀ PHÂN BỐ SAO (CSAT BREAKDOWN - KIỂU SHOPEE / BE) -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                
                <!-- Cột trái: Điểm số trung bình to nổi bật -->
                <div class="md:col-span-4 text-center md:border-r md:border-slate-100 md:pr-6 space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">ĐIỂM TRUNG BÌNH CSAT</span>
                    <div class="flex items-baseline justify-center gap-1.5">
                        <span class="text-5xl font-black text-amber-500 font-mono"><?php echo number_format($avgScore, 1); ?></span>
                        <span class="text-xl font-bold text-slate-400">/ 5.0</span>
                    </div>
                    
                    <div class="text-2xl text-amber-400 tracking-wider">
                        <?php 
                        $fullStars = (int)round($avgScore);
                        echo str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
                        ?>
                    </div>
                    
                    <p class="text-xs text-slate-500 font-medium">
                        Dựa trên <strong><?php echo $totalRatings; ?></strong> lượt đánh giá từ người dùng
                    </p>
                </div>

                <!-- Cột phải: Thanh tiến độ phân bố từng loại sao -->
                <div class="md:col-span-8 space-y-2.5 text-xs">
                    <!-- 5 sao -->
                    <div class="flex items-center gap-3">
                        <span class="w-16 font-bold text-slate-700 text-right shrink-0">5 ⭐</span>
                        <div class="flex-1 bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: <?php echo $pct5; ?>%"></div>
                        </div>
                        <span class="w-20 text-slate-500 font-mono text-[11px] text-right shrink-0">
                            <strong><?php echo $count5; ?></strong> (<?php echo $pct5; ?>%)
                        </span>
                    </div>

                    <!-- 4 sao -->
                    <div class="flex items-center gap-3">
                        <span class="w-16 font-bold text-slate-700 text-right shrink-0">4 ⭐</span>
                        <div class="flex-1 bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-sky-500 h-full rounded-full transition-all duration-500" style="width: <?php echo $pct4; ?>%"></div>
                        </div>
                        <span class="w-20 text-slate-500 font-mono text-[11px] text-right shrink-0">
                            <strong><?php echo $count4; ?></strong> (<?php echo $pct4; ?>%)
                        </span>
                    </div>

                    <!-- 3 sao -->
                    <div class="flex items-center gap-3">
                        <span class="w-16 font-bold text-slate-700 text-right shrink-0">3 ⭐</span>
                        <div class="flex-1 bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: <?php echo $pct3; ?>%"></div>
                        </div>
                        <span class="w-20 text-slate-500 font-mono text-[11px] text-right shrink-0">
                            <strong><?php echo $count3; ?></strong> (<?php echo $pct3; ?>%)
                        </span>
                    </div>

                    <!-- 2 sao -->
                    <div class="flex items-center gap-3">
                        <span class="w-16 font-bold text-slate-700 text-right shrink-0">2 ⭐</span>
                        <div class="flex-1 bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-orange-500 h-full rounded-full transition-all duration-500" style="width: <?php echo $pct2; ?>%"></div>
                        </div>
                        <span class="w-20 text-slate-500 font-mono text-[11px] text-right shrink-0">
                            <strong><?php echo $count2; ?></strong> (<?php echo $pct2; ?>%)
                        </span>
                    </div>

                    <!-- 1 sao -->
                    <div class="flex items-center gap-3">
                        <span class="w-16 font-bold text-slate-700 text-right shrink-0">1 ⭐</span>
                        <div class="flex-1 bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-rose-500 h-full rounded-full transition-all duration-500" style="width: <?php echo $pct1; ?>%"></div>
                        </div>
                        <span class="w-20 text-slate-500 font-mono text-[11px] text-right shrink-0">
                            <strong><?php echo $count1; ?></strong> (<?php echo $pct1; ?>%)
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <!-- CÁC NÚT LỌC SỐ SAO (TẤT CẢ, 5 SAO, 4 SAO, 3 SAO, 2 SAO, 1 SAO) -->
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <span>🔍</span> BỘ LỌC ĐÁNH GIÁ THEO SỐ SAO
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Bấm vào từng nút số sao để xem chi tiết nhận xét của người dùng.</p>
                </div>

                <span class="text-xs font-semibold text-slate-500">
                    Hiển thị: <strong class="text-indigo-600"><?php echo count($ratingsList); ?></strong> đánh giá
                </span>
            </div>

            <!-- Các nút bấm lọc trực quan -->
            <div class="flex items-center gap-2 flex-wrap text-xs">
                <!-- Nút Tất cả -->
                <a href="ratings.php?agent_id=<?php echo $agentId; ?>&star=0" 
                   class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-1.5 <?php echo ($starFilter === 0) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">
                    <span>Tất cả</span>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-mono <?php echo ($starFilter === 0) ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'; ?>">
                        <?php echo $totalRatings; ?>
                    </span>
                </a>

                <!-- Nút 5 sao -->
                <a href="ratings.php?agent_id=<?php echo $agentId; ?>&star=5" 
                   class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-1.5 <?php echo ($starFilter === 5) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100'; ?>">
                    <span>⭐⭐⭐⭐⭐ 5 sao</span>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-mono <?php echo ($starFilter === 5) ? 'bg-white/20 text-white' : 'bg-emerald-200 text-emerald-900'; ?>">
                        <?php echo $count5; ?>
                    </span>
                </a>

                <!-- Nút 4 sao -->
                <a href="ratings.php?agent_id=<?php echo $agentId; ?>&star=4" 
                   class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-1.5 <?php echo ($starFilter === 4) ? 'bg-sky-600 text-white shadow-sm' : 'bg-sky-50 text-sky-800 border border-sky-200 hover:bg-sky-100'; ?>">
                    <span>⭐⭐⭐⭐ 4 sao</span>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-mono <?php echo ($starFilter === 4) ? 'bg-white/20 text-white' : 'bg-sky-200 text-sky-900'; ?>">
                        <?php echo $count4; ?>
                    </span>
                </a>

                <!-- Nút 3 sao -->
                <a href="ratings.php?agent_id=<?php echo $agentId; ?>&star=3" 
                   class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-1.5 <?php echo ($starFilter === 3) ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100'; ?>">
                    <span>⭐⭐⭐ 3 sao</span>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-mono <?php echo ($starFilter === 3) ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-950'; ?>">
                        <?php echo $count3; ?>
                    </span>
                </a>

                <!-- Nút 2 sao -->
                <a href="ratings.php?agent_id=<?php echo $agentId; ?>&star=2" 
                   class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-1.5 <?php echo ($starFilter === 2) ? 'bg-orange-600 text-white shadow-sm' : 'bg-orange-50 text-orange-900 border border-orange-200 hover:bg-orange-100'; ?>">
                    <span>⭐⭐ 2 sao</span>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-mono <?php echo ($starFilter === 2) ? 'bg-white/20 text-white' : 'bg-orange-200 text-orange-950'; ?>">
                        <?php echo $count2; ?>
                    </span>
                </a>

                <!-- Nút 1 sao -->
                <a href="ratings.php?agent_id=<?php echo $agentId; ?>&star=1" 
                   class="px-4 py-2 rounded-xl font-bold transition flex items-center gap-1.5 <?php echo ($starFilter === 1) ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-900 border border-rose-200 hover:bg-rose-100'; ?>">
                    <span>⭐ 1 sao</span>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-mono <?php echo ($starFilter === 1) ? 'bg-white/20 text-white' : 'bg-rose-200 text-rose-950'; ?>">
                        <?php echo $count1; ?>
                    </span>
                </a>
            </div>
        </div>

        <!-- DANH SÁCH NHẬN XÉT CỦA NGƯỜI DÙNG -->
        <?php if (empty($ratingsList)): ?>
            <div class="bg-white border border-slate-200 rounded-3xl p-12 text-center text-xs text-slate-400 space-y-3">
                <span class="text-4xl block">🌟</span>
                <div class="font-bold text-slate-700 text-sm">Chưa có đánh giá nào phù hợp với bộ lọc!</div>
                <p class="text-[11px] text-slate-400 max-w-sm mx-auto">
                    Hiện tại chưa có người dùng nào chấm điểm ở mức <strong><?php echo $starFilter > 0 ? $starFilter . ' sao' : 'này'; ?></strong> cho KTV này.
                </p>
                <a href="ratings.php?agent_id=<?php echo $agentId; ?>" class="inline-block px-4 py-2 bg-indigo-50 text-indigo-700 font-bold rounded-xl hover:bg-indigo-100 transition">
                    ← Xem tất cả các mức sao
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($ratingsList as $r): 
                    $score = (int)$r['score'];
                    $badgeStyle = match($score) {
                        5 => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        4 => 'bg-sky-50 text-sky-800 border-sky-200',
                        3 => 'bg-amber-50 text-amber-900 border-amber-200',
                        2 => 'bg-orange-50 text-orange-900 border-orange-200',
                        default => 'bg-rose-50 text-rose-900 border-rose-200'
                    };
                    $scoreText = match($score) {
                        5 => 'Rất hài lòng / Xử lý tuyệt vời',
                        4 => 'Hài lòng / Xử lý tốt',
                        3 => 'Bình thường / Chấp nhận được',
                        2 => 'Chưa hài lòng',
                        default => 'Rất thất vọng'
                    };
                ?>
                    <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3 hover:border-indigo-300 transition-all">
                        
                        <!-- Header thẻ đánh giá: Người gửi, thời gian, số sao -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-700 font-black flex items-center justify-center text-sm shadow-2xs">
                                    <?php 
                                    $parts = explode(' ', $r['student_name']);
                                    echo mb_substr(end($parts), 0, 1, 'UTF-8');
                                    ?>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <strong class="text-slate-900 text-xs"><?php echo htmlspecialchars($r['student_name']); ?></strong>
                                        <span class="text-slate-400 font-mono text-[11px]">(<?php echo htmlspecialchars($r['student_code']); ?>)</span>
                                        <span class="text-[10.5px] px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full font-medium">
                                            🎓 Sinh viên
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">
                                        Thời gian đánh giá: <span class="font-mono"><?php echo htmlspecialchars($r['created_at']); ?></span>
                                    </span>
                                </div>
                            </div>

                            <!-- Huy hiệu số sao -->
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-2xl border text-xs font-bold <?php echo $badgeStyle; ?> self-start sm:self-auto">
                                <span class="text-amber-500 font-mono tracking-widest text-sm">
                                    <?php echo str_repeat('★', $score) . str_repeat('☆', 5 - $score); ?>
                                </span>
                                <span><?php echo $score; ?>/5 sao</span>
                                <span class="text-[10px] opacity-80">(<?php echo $scoreText; ?>)</span>
                            </div>
                        </div>

                        <!-- Khối nhận xét bằng chữ của người dùng -->
                        <div class="bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/80">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">
                                💬 Lời nhận xét / Góp ý từ người dùng:
                            </span>
                            <?php if (!empty($r['comment'])): ?>
                                <p class="text-xs text-slate-800 leading-relaxed font-medium italic">
                                    "<?php echo htmlspecialchars($r['comment']); ?>"
                                </p>
                            <?php else: ?>
                                <p class="text-xs text-slate-400 italic">
                                    (Người dùng đã gửi đánh giá <?php echo $score; ?> sao, không để lại nhận xét bằng chữ).
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Thông tin vé sự cố liên quan -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs pt-1 text-slate-500">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-700">Mã vé:</span>
                                <a href="../tickets/show.php?id=<?php echo $r['ticket_id']; ?>" class="font-mono font-bold text-indigo-600 hover:underline">
                                    <?php echo htmlspecialchars($r['ticket_code']); ?>
                                </a>
                                <span>•</span>
                                <span class="text-slate-700 line-clamp-1 max-w-md">
                                    <?php echo htmlspecialchars($r['ticket_title']); ?>
                                </span>
                                <span>•</span>
                                <span class="text-[11px] text-slate-400">
                                    Hệ thống: <strong class="text-slate-600"><?php echo htmlspecialchars($r['system_affected']); ?></strong>
                                </span>
                            </div>

                            <a href="../tickets/show.php?id=<?php echo $r['ticket_id']; ?>" 
                               class="text-indigo-600 hover:text-indigo-800 font-bold text-xs flex items-center gap-1 shrink-0">
                                <span>Xem phiếu</span>
                                <span>→</span>
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- FOOTER TOÀN TRƯỜNG -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>

