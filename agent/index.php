<?php
/**
 * ==============================================================================
 * BÀN LÀM VIỆC KỸ THUẬT VIÊN (AGENT WORKSPACE)
 * Phân hệ: Core Architecture & KTV Workspace
 * Trưởng nhóm phụ trách: PHẠM THỊ THANH NGÂN (MSSV: 224001736 - D24-CNTT1)
 * ==============================================================================
 * Quy tắc nghiệp vụ:
 * 1. KTV chỉ quản lý và giải quyết các phiếu được phân công cho riêng mình.
 * 2. KTV không có nút "thêm phiếu" (chỉ tiếp nhận và hỗ trợ sinh viên/giảng viên).
 * 3. Hàng đợi sắp xếp theo hạn SLA (due_at ASC) ưu tiên phiếu cấp bách.
 * 4. Giao diện được tách thành các file thành phần (partials) dễ tìm kiếm & bảo trì.
 */

require_once __DIR__ . '/../config/database.php';

use App\Services\TicketService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra quyền truy cập bắt buộc (Chỉ Agent và Admin)
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$currentUser = $_SESSION['user'];
if (!in_array($currentUser['role'], ['agent', 'admin'])) {
    set_flash('error', 'Bạn không có quyền truy cập vào Bàn làm việc Kỹ thuật viên!');
    header('Location: ../index.php');
    exit;
}

$isAgent = ($currentUser['role'] === 'agent');
$isAdmin = ($currentUser['role'] === 'admin');

// 2. KTV chỉ được truy cập bàn làm việc của chính mình
if ($isAgent) {
    $agentId = (int)$currentUser['id'];
} else {
    // Admin có thể xem bàn làm việc của từng KTV để giám sát
    $agentId = (int)($_GET['agent_id'] ?? 2);
}

$statusFilter = trim($_GET['status'] ?? '');
$db = db();

// 3. Thông tin KTV hiện tại
$agentStmt = $db->prepare("
    SELECT u.*, c.name AS specialty_name, c.code AS specialty_code 
    FROM users u 
    LEFT JOIN categories c ON u.specialty_category_id = c.id 
    WHERE u.id = :id AND u.role = 'agent'
");
$agentStmt->execute([':id' => $agentId]);
$currentAgent = $agentStmt->fetch();

if (!$currentAgent) {
    $agentId = 2; // Mặc định KTV 1 nếu ID không hợp lệ
    $agentStmt->execute([':id' => 2]);
    $currentAgent = $agentStmt->fetch();
}

// 4. Xử lý Nghiệm thu giải pháp kỹ thuật
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_ticket') {
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $solution = trim($_POST['solution_note'] ?? '');

    if (!empty($solution) && $ticketId > 0) {
        try {
            TicketService::resolveWithSolution($ticketId, $agentId, $solution);
            set_flash('success', "Đã lập biên bản giải pháp và nghiệm thu hoàn tất phiếu #{$ticketId} thành công!");
            header("Location: index.php?agent_id={$agentId}");
            exit;
        } catch (Exception $e) {
            set_flash('error', $e->getMessage());
        }
    } else {
        set_flash('error', "Vui lòng nhập nội dung giải pháp kỹ thuật trước khi nghiệm thu!");
    }
}

// 5. Thống kê số lượng phiếu của RIÊNG KTV này
$countAll        = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE assigned_to = {$agentId}")->fetchColumn();
$countNew        = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE assigned_to = {$agentId} AND status = 'new'")->fetchColumn();
$countInProgress = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE assigned_to = {$agentId} AND status = 'in_progress'")->fetchColumn();
$countWaiting    = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE assigned_to = {$agentId} AND status = 'waiting_requester'")->fetchColumn();
$countActive     = $countNew + $countInProgress + $countWaiting; // Số phiếu chưa hoàn thành
$countResolved   = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE assigned_to = {$agentId} AND status IN ('resolved', 'closed')")->fetchColumn(); // Đã hoàn thành

// 6. Lấy hàng đợi phiếu của KTV này (Sắp xếp theo due_at ASC)
$queue = TicketService::getAgentQueue($agentId, $statusFilter);

// 7. Danh sách 5 KTV phục vụ bộ lọc Admin
$allAgents = [];
if ($isAdmin) {
    $allAgents = $db->query("
        SELECT u.*, c.name AS specialty_name,
               (SELECT COUNT(*) FROM tickets t 
                WHERE t.assigned_to = u.id AND t.status IN ('new', 'in_progress', 'waiting_requester')
               ) AS active_count
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
    <title>Bàn Làm Việc Kỹ Thuật Viên - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- NAVBAR CHÍNH -->
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- HEADER BANNER WORKSPACE -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        👨‍💻 BÀN LÀM VIỆC KỸ THUẬT VIÊN (AGENT WORKSPACE)
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300">
                        🎯 Chuyên môn: <?php echo htmlspecialchars($currentAgent['specialty_name'] ?? 'Hỗ trợ CNTT'); ?>
                    </span>
                </div>
                <h1 class="text-2xl font-black text-white">
                    <?php echo htmlspecialchars($currentAgent['name']); ?> 
                    <span class="text-xs font-mono text-indigo-300 font-semibold">(<?php echo htmlspecialchars($currentAgent['user_code']); ?>)</span>
                </h1>
                <p class="text-xs text-indigo-200/80 mt-0.5">Tiếp nhận, xử lý sự cố từ xa, ghi chú nội bộ và lập biên bản giải pháp kỹ thuật.</p>
            </div>

        </div>

        <!-- THÔNG BÁO FLASH MESSAGE -->
        <?php if ($flash): ?>
            <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold flex items-center gap-2 <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
                <span><?php echo $flash['type'] === 'success' ? '✅' : '⚠️'; ?></span>
                <span><?php echo $flash['message']; ?></span>
            </div>
        <?php endif; ?>

        <!-- DÀNH CHO ADMIN: THANH CHUYỂN ĐỔI 5 KTV (NẾU LÀ ADMIN ĐĂNG NHẬP) -->
        <?php if ($isAdmin): ?>
            <?php require __DIR__ . '/partials/agent_switcher.php'; ?>
        <?php endif; ?>

        <!-- KHỐI 1: THỐNG KÊ PHIẾU CỦA RIÊNG KTV (TỔNG, HOÀN THÀNH, CHƯA HOÀN THÀNH, TẢI TRỌNG) -->
        <?php require __DIR__ . '/partials/kpi_stats.php'; ?>

        <!-- KHỐI 2: HÀNG ĐỢI XỬ LÝ THEO SLA (DUE_AT ASC) -->
        <?php require __DIR__ . '/partials/ticket_queue.php'; ?>

    </main>

    <!-- KHỐI 3: MODAL LẬP BIÊN BẢN NGHIỆM THU GIẢI PHÁP -->
    <?php require __DIR__ . '/partials/resolve_modal.php'; ?>

    <!-- FOOTER TOÀN TRƯỜNG -->
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
