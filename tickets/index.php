<?php
/**
 * ==============================================================================
 * BÀI 5: QUẢN LÝ DANH SÁCH PHIẾU HỖ TRỢ & BỘ LỌC TÌM KIẾM THEO CHUẨN ITIL v4
 * Phân hệ: Core Architecture & Ticket Management
 * Trưởng nhóm: PHẠM THỊ THANH NGÂN (MSSV: 224001736 - D24-CNTT1)
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$currentUser   = $_SESSION['user'];
$userRole      = $currentUser['role'];
$userType      = $currentUser['user_type'] ?? 'student';
$filterReqId   = ($userRole === 'requester') ? (int)$currentUser['id'] : 0;
$filterAgentId = ($userRole === 'agent') ? (int)$currentUser['id'] : 0;

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

// 1. Tiếp nhận tham số tìm kiếm & lọc an toàn
$keyword     = trim($_GET['kw'] ?? '');
$status      = trim($_GET['status'] ?? '');
$categoryId  = (int)($_GET['category_id'] ?? 0);
$warningType = trim($_GET['warning'] ?? '');
if ($status === 'warning') {
    $warningType = 'sla';
    $status = '';
}
$hasWarning = !empty($warningType) ? $warningType : false;

// 2. Phân trang an toàn (5 bản ghi / trang)
$perPage    = 5;
$page       = max(1, (int)($_GET['page'] ?? 1));

$totalTickets   = TicketRepository::countAll($keyword, $status, $categoryId, $filterReqId, $filterAgentId, $hasWarning);
$totalPages     = max(1, (int)ceil($totalTickets / $perPage));

if ($page > $totalPages && $totalTickets > 0) {
    $page = $totalPages;
}

$tickets        = TicketRepository::findAll($keyword, $page, $perPage, $status, $categoryId, $filterReqId, $filterAgentId, $hasWarning);
$categories     = TicketRepository::getCategories();
$slaCount       = TicketRepository::countSlaOverdue($filterReqId, $filterAgentId);
$violationCount = TicketRepository::countViolations($filterReqId, $filterAgentId);
$flash        = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phiếu Hỗ Trợ - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- NAVBAR CHÍNH -->
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-3 sm:pt-4 pb-8 space-y-6">

        <!-- BANNER ĐẦU TRANG THEO THIẾT KẾ TRẦN THỤC LINH & CORE NHÓM 1 -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        🎫 QUẢN TRỊ & TRIAGE SỰ CỐ
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        MÃ ĐỊNH DANH THEO NGÀY: HD-YYYY-DDMM-xxxxxx
                    </span>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">
                    <?php 
                    if ($userRole === 'requester') {
                        echo 'DANH SÁCH PHIẾU HỖ TRỢ CỦA BẠN';
                    } elseif ($userRole === 'agent') {
                        echo 'NHIỆM VỤ ĐIỀU PHỐI KỸ THUẬT CỦA TÔI';
                    } else {
                        echo 'TRUNG TÂM TRIAGE & PHÂN PHỐI SỰ CỐ TOÀN TRƯỜNG';
                    }
                    ?>
                </h1>
                <p class="text-xs text-indigo-200/80 max-w-2xl leading-relaxed">
                    <?php 
                    if ($userRole === 'requester') {
                        echo 'Theo dõi trạng thái, tiến độ giải quyết và trao đổi giải pháp trực tiếp với Kỹ thuật viên phụ trách.';
                    } elseif ($userRole === 'agent') {
                        echo 'Hàng đợi nhiệm vụ được phân công theo chuyên môn & ngưỡng điều phối tải trọng tối đa 15 phiếu.';
                    } else {
                        echo 'Theo dõi tiếp nhận đa kênh, quy chuẩn ITIL v4, điều phối KTV chuyên trách và nâng mức ưu tiên khẩn cấp.';
                    }
                    ?>
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <?php if (!in_array($userRole, ['agent', 'admin'])): ?>
                    <a href="create.php" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs">
                        <span>➕</span> Gửi Phiếu Mới
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- THÔNG BÁO FLASH MESSAGE -->
        <?php if ($flash): ?>
            <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
                <div class="flex items-center gap-2">
                    <span><?php echo $flash['type'] === 'success' ? '✅' : '⚠️'; ?></span>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
                <span class="text-slate-400 text-[10px]">Đã cập nhật dữ liệu</span>
            </div>
        <?php endif; ?>

        <!-- KHUNG BỘ LỌC TÌM KIẾM ĐỒNG NHẤT VỚI BÀI 4 TRẦN THỤC LINH -->
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-2.5">
                <span class="font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span>🔍</span> Bộ Lọc & Tìm Kiếm Phiếu Sự Cố (WHERE & LIKE PDO)
                </span>
                <span class="text-slate-400 text-[11px]">Tìm thấy: <strong class="text-indigo-600"><?php echo $totalTickets; ?></strong> phiếu</span>
            </div>

            <!-- CÁC TAB LỌC NHANH TRẠNG THÁI & 2 BỘ LỌC CẢNH BÁO CHO ADMIN (SLA QUÁ GIỜ & SV VI PHẠM) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <a href="index.php?kw=<?= urlencode($keyword) ?>&category_id=<?= $categoryId ?>" 
                   class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 <?= (empty($warningType) && empty($status)) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                    <span>📋 Tất Cả Phiếu</span>
                </a>

                <!-- 1. CẢNH BÁO SLA QUÁ GIỜ -->
                <a href="index.php?warning=sla&kw=<?= urlencode($keyword) ?>&category_id=<?= $categoryId ?>" 
                   title="Xem danh sách các phiếu sự cố đã quá hạn giải quyết theo cam kết SLA"
                   class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 <?= ($warningType === 'sla') ? 'bg-rose-600 text-white shadow-sm ring-2 ring-rose-400' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' ?>">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-600"></span>
                    </span>
                    <span>⏰ Quá Hạn SLA</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black <?= ($warningType === 'sla') ? 'bg-white text-rose-700' : 'bg-rose-600 text-white' ?>"><?= $slaCount ?></span>
                </a>

                <!-- 2. CẢNH BÁO SINH VIÊN VI PHẠM (KTV BÁO CÁO) -->
                <a href="index.php?warning=violation&kw=<?= urlencode($keyword) ?>&category_id=<?= $categoryId ?>" 
                   title="Xem danh sách các phiếu sinh viên vi phạm/spam do KTV báo cáo để Admin xử lý"
                   class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1.5 <?= ($warningType === 'violation') ? 'bg-purple-700 text-white shadow-sm ring-2 ring-purple-400' : 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200' ?>">
                    <span>🚨 SV Vi Phạm (KTV Báo Cáo)</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black <?= ($warningType === 'violation') ? 'bg-white text-purple-700' : 'bg-purple-700 text-white' ?>"><?= $violationCount ?></span>
                </a>

                <a href="index.php?status=new&kw=<?= urlencode($keyword) ?>&category_id=<?= $categoryId ?>" 
                   class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1 <?= ($status === 'new' && empty($warningType)) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                    <span>⏳ Mới Tiếp Nhận</span>
                </a>

                <a href="index.php?status=in_progress&kw=<?= urlencode($keyword) ?>&category_id=<?= $categoryId ?>" 
                   class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1 <?= ($status === 'in_progress' && empty($warningType)) ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                    <span>⚙️ Đang Xử Lý</span>
                </a>

                <a href="index.php?status=waiting_requester&kw=<?= urlencode($keyword) ?>&category_id=<?= $categoryId ?>" 
                   class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1 <?= ($status === 'waiting_requester' && empty($warningType)) ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                    <span>💬 Chờ Phản Hồi</span>
                </a>

                <a href="index.php?status=resolved&kw=<?= urlencode($keyword) ?>&category_id=<?= $categoryId ?>" 
                   class="px-3.5 py-1.5 rounded-xl font-bold transition whitespace-nowrap flex items-center gap-1 <?= ($status === 'resolved' && empty($warningType)) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                    <span>✅ Đã Xong / Đóng</span>
                </a>
            </div>

            <form method="GET" action="index.php" class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
                <?php if (!empty($warningType)): ?>
                    <input type="hidden" name="warning" value="<?php echo htmlspecialchars($warningType); ?>" />
                <?php endif; ?>
                <!-- Từ khóa -->
                <div class="md:col-span-5">
                    <label class="block font-bold text-slate-600 mb-1">Mã phiếu hoặc tiêu đề sự cố:</label>
                    <input type="text" name="kw" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Ví dụ: HD-2026-2910, L0H004, Canvas, Wi-Fi..." 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                </div>

                <!-- Lọc trạng thái -->
                <div class="md:col-span-3">
                    <label class="block font-bold text-slate-600 mb-1">Trạng thái phiếu:</label>
                    <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold text-slate-700">
                                                <option value="">-- Tất cả trạng thái --</option>
                        <option value="warning" <?php echo $hasWarning ? 'selected' : ''; ?>>🚨 Phiếu có cảnh báo (Quá hạn SLA / SOS / Khẩn cấp)</option>
                        <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>1. Mới tiếp nhận (new)</option>
                        <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>2. Đang xử lý (in_progress)</option>
                        <option value="waiting_requester" <?php echo $status === 'waiting_requester' ? 'selected' : ''; ?>>3. Chờ phản hồi (waiting)</option>
                        <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>4. Đã giải quyết (resolved)</option>
                        <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>5. Đã đóng hoàn tất (closed)</option>
                    </select>
                </div>

                <!-- Lọc chuyên mục -->
                <div class="md:col-span-3">
                    <label class="block font-bold text-slate-600 mb-1">Danh mục dịch vụ:</label>
                    <select name="category_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold text-slate-700">
                        <option value="0">-- Tất cả danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId === (int)$cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Nút bấm -->
                <div class="md:col-span-1 flex items-end">
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-1">
                        <span>⚡</span> Lọc
                    </button>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH PHIẾU CHÍNH XÁC THEO CHUẨN TRẦN THỤC LINH & CORE ARCHITECTURE -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden space-y-3">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center text-xs">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <span>📋</span> BẢNG QUẢN LÝ DỮ LIỆU SỰ CỐ
                    </h2>
                    <p class="text-[11.5px] text-slate-500 mt-0.5">Dữ liệu kết nối đồng nhất với CSDL <code>helpdesk_thudo</code>.</p>
                </div>
                <span class="text-slate-500 font-medium">Trang <strong><?php echo $page; ?></strong> / <strong><?php echo $totalPages; ?></strong></span>
            </div>

            <?php if (empty($tickets)): ?>
                <div class="p-12 text-center text-slate-400 text-xs space-y-2">
                    <span class="text-4xl block">📭</span>
                    <p class="font-bold text-slate-600">Không tìm thấy phiếu hỗ trợ nào phù hợp với bộ lọc hiện tại.</p>
                    <p class="text-slate-400">Vui lòng thử lại với từ khóa khác hoặc bấm nút "Tất cả trạng thái".</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[880px] text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 text-[11px] uppercase tracking-wider">
                                <th class="py-3.5 px-4 w-32 whitespace-nowrap">Mã Vé</th>
                                <th class="py-3.5 px-4 min-w-[240px]">Tiêu Đề Sự Cố</th>
                                <th class="py-3.5 px-4 w-36 whitespace-nowrap">Người Gửi</th>
                                <th class="py-3.5 px-4 w-36 whitespace-nowrap">Danh Mục</th>
                                <th class="py-3.5 px-4 w-28 text-center whitespace-nowrap">Mức Ưu Tiên</th>
                                <th class="py-3.5 px-4 w-32 whitespace-nowrap">KTV Phụ Trách</th>
                                <th class="py-3.5 px-4 w-32 text-center whitespace-nowrap">Trạng Thái</th>
                                <th class="py-3.5 px-4 w-24 text-center whitespace-nowrap">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($tickets as $t): ?>
                                <?php
                                $statusBadge = match($t['status']) {
                                    'new'               => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'in_progress'       => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'waiting_requester' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'resolved'          => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'closed'            => 'bg-slate-100 text-slate-700 border-slate-300',
                                    default             => 'bg-slate-100 text-slate-600 border-slate-200'
                                };
                                $statusLabel = match($t['status']) {
                                    'new'               => 'Mới tiếp nhận',
                                    'in_progress'       => 'Đang xử lý',
                                    'waiting_requester' => 'Chờ người dùng',
                                    'resolved'          => 'Đã có giải pháp',
                                    'closed'            => 'Đã đóng hoàn tất',
                                    default             => $t['status']
                                };
                                $prioBadge = match($t['priority_code']) {
                                    'URGENT' => 'bg-rose-50 text-rose-700 border-rose-200 font-bold',
                                    'HIGH'   => 'bg-amber-50 text-amber-800 border-amber-200 font-bold',
                                    'MED'    => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'LOW'    => 'bg-slate-50 text-slate-700 border-slate-200',
                                    default  => 'bg-slate-50 text-slate-700 border-slate-200'
                                };
                                ?>
                                <?php
                                $isOverdueSLA    = ($t['due_at'] && strtotime($t['due_at']) < time() && !in_array($t['status'], ['resolved', 'closed']));
                                $isOverdueTriage = ($t['triage_due_at'] && strtotime($t['triage_due_at']) < time() && empty($t['assigned_to']) && !in_array($t['status'], ['resolved', 'closed']));
                                $isSoonOverdue   = ($t['due_at'] && (strtotime($t['due_at']) - time() <= 3600) && (strtotime($t['due_at']) - time() > 0) && !in_array($t['status'], ['resolved', 'closed']));
                                $isEmergency     = (in_array(strtoupper($t['priority_code']), ['URGENT', 'HIGH']) && !in_array($t['status'], ['resolved', 'closed']));
                                $isSos           = (str_contains($t['title'], '[CỨU HỘ KHẨN CẤP]') && !in_array($t['status'], ['resolved', 'closed']));
                                $isReopened      = (!empty($t['reopen_count']) && (int)$t['reopen_count'] > 0 && !in_array($t['status'], ['resolved', 'closed']));
                                $isSpamViolation = (!empty($t['is_spam']) && (int)$t['is_spam'] === 1);
                                $hasRowWarning   = ($isOverdueSLA || $isOverdueTriage || $isSoonOverdue || $isEmergency || $isSos || $isReopened || $isSpamViolation);
                                ?>
                                <tr class="hover:bg-slate-50/80 transition <?= $hasRowWarning ? 'bg-rose-50/25 border-l-4 border-l-rose-500' : '' ?>">
                                    <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                                        <a href="show.php?id=<?php echo $t['id']; ?>" class="hover:underline flex items-center gap-1">
                                            <?php if ($hasRowWarning): ?>
                                                <span class="text-rose-500 font-bold" title="Phiếu có cảnh báo">⚠️</span>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($t['ticket_code']); ?></span>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 min-w-[240px]">
                                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                            <?php if ($isOverdueSLA): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white animate-pulse">
                                                    ⏰ QUÁ HẠN SLA
                                                </span>
                                            <?php elseif ($isOverdueTriage): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white animate-pulse">
                                                    ⏳ QUÁ HẠN TIẾP NHẬN
                                                </span>
                                            <?php elseif ($isSoonOverdue): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-900">
                                                    ⚠️ SẮP TRỄ HẠN (&lt; 1h)
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($isSos): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-red-600 text-white">
                                                    🆘 CỨU HỘ KHẨN CẤP
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($isReopened): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-700 border border-purple-300">
                                                    🔄 MỞ LẠI (<?= $t['reopen_count'] ?> LẦN)
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="show.php?id=<?php echo $t['id']; ?>" class="font-bold text-slate-900 hover:text-indigo-600 transition block line-clamp-2 leading-relaxed">
                                            <?php echo htmlspecialchars($t['title']); ?>
                                        </a>
                                        <?php if ($isSpamViolation): ?>
                                            <div class="mt-1.5 p-2 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 text-[11px] flex items-start gap-1.5">
                                                <span class="text-sm shrink-0">🚨</span>
                                                <div>
                                                    <span class="font-bold text-purple-950 uppercase tracking-wide">KTV Báo Cáo Vi Phạm:</span>
                                                    <span><?php echo htmlspecialchars($t['spam_reason'] ?: 'Phát hiện sinh viên vi phạm quy định / gửi yêu cầu spam'); ?></span>
                                                    <span class="block text-[10px] text-purple-700 font-semibold mt-0.5">⚠️ Admin xem xét: Nhắc nhở hoặc xử lý kỷ luật tài khoản SV này</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <span class="text-slate-400 text-[11px] block mt-1 leading-normal">
                                            Hệ thống: <strong class="text-slate-700 font-semibold"><?php echo htmlspecialchars($t['system_affected']); ?></strong> · Hạn: <span class="font-mono text-slate-600"><?php echo htmlspecialchars($t['due_at']); ?></span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-800 block"><?php echo htmlspecialchars($t['requester_name']); ?></span>
                                        <span class="text-slate-400 text-[10.5px] font-mono block"><?php echo htmlspecialchars($t['requester_code']); ?></span>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600">
                                        <?php echo htmlspecialchars($t['category_name']); ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded text-[10.5px] font-bold border <?php echo $prioBadge; ?>">
                                            ● <?php echo htmlspecialchars($t['priority_name']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($t['agent_name']): ?>
                                            <span class="font-semibold text-slate-800 block"><?php echo htmlspecialchars($t['agent_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-amber-600 italic block text-[11px]">Đang phân công...</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold border <?php echo $statusBadge; ?>">
                                            ● <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center space-x-1 whitespace-nowrap">
                                        <a href="show.php?id=<?php echo $t['id']; ?>" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-[11px] font-bold transition inline-block">
                                            Chi Tiết →
                                        </a>
                                        <?php if (in_array($userRole, ['agent', 'admin'])): ?>
                                            <a href="edit.php?id=<?php echo $t['id']; ?>" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-medium transition inline-block">
                                                Sửa
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PHÂN TRANG CHUẨN SLIDE 11 -->
                <?php if ($totalPages > 1): ?>
                    <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs bg-slate-50/50">
                        <span class="text-slate-500">
                            Hiển thị trang <strong><?php echo $page; ?></strong> trên tổng số <strong><?php echo $totalPages; ?></strong> trang
                        </span>
                        <div class="flex items-center gap-1.5">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&kw=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&category_id=<?php echo $categoryId; ?>&warning=<?php echo urlencode($warningType); ?>" 
                                   class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 transition text-slate-700 font-semibold shadow-2xs">
                                    ← Trước
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&kw=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&category_id=<?php echo $categoryId; ?>&warning=<?php echo urlencode($warningType); ?>" 
                                   class="px-3.5 py-1.5 rounded-xl border font-bold transition <?php echo $i === $page ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'border-slate-300 bg-white hover:bg-slate-100 text-slate-700'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&kw=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&category_id=<?php echo $categoryId; ?>&warning=<?php echo urlencode($warningType); ?>" 
                                   class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 transition text-slate-700 font-semibold shadow-2xs">
                                    Sau →
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>

    </main>

    <!-- FOOTER CHUẨN HÓA TOÀN TRƯỜNG -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
