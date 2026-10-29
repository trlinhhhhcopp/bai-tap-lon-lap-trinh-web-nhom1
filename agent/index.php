<?php
// ==============================================================================
// PHÂN HỆ: CORE ARCHITECTURE & AGENT WORKSPACE (BÀN LÀM VIỆC KỸ THUẬT VIÊN)
// Trưởng nhóm phụ trách độc quyền: PHẠM THỊ THANH NGÂN (MSSV: 224001736 - D24-CNTT1)
// CƠ CHẾ: HÀNG ĐỢI SLA (DUE_AT ASC), GIẢI PHÁP KỸ THUẬT & TRÀN TẢI THÔNG MINH
// ==============================================================================

require_once __DIR__ . '/../config/database.php';

use App\Services\TicketService;
use App\Models\MasterData;

// Chọn KTV làm việc (Mô phỏng 5 KTV chuyên môn)
$agentId = (int)($_GET['agent_id'] ?? 2);
$statusFilter = trim($_GET['status'] ?? '');

$db = db();
$agentStmt = $db->prepare("
    SELECT u.*, c.name AS specialty_name, c.code AS specialty_code 
    FROM users u 
    LEFT JOIN categories c ON u.specialty_category_id = c.id 
    WHERE u.id = :id AND u.role = 'agent'
");
$agentStmt->execute([':id' => $agentId]);
$currentAgent = $agentStmt->fetch();

if (!$currentAgent) {
    // Mặc định KTV 1 (id=2) nếu chọn sai
    $agentId = 2;
    $agentStmt->execute([':id' => 2]);
    $currentAgent = $agentStmt->fetch();
}

// Xử lý Giải quyết phiếu kèm biên bản giải pháp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_ticket') {
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $solution = trim($_POST['solution_note'] ?? '');

    if (!empty($solution) && $ticketId > 0) {
        try {
            TicketService::resolveWithSolution($ticketId, $agentId, $solution);
            set_flash('success', "Đã lập biên bản giải pháp và nghiệm thu phiếu #{$ticketId} thành công!");
            header("Location: index.php?agent_id={$agentId}");
            exit;
        } catch (Exception $e) {
            set_flash('error', $e->getMessage());
        }
    } else {
        set_flash('error', "Vui lòng nhập nội dung giải pháp kỹ thuật trước khi nghiệm thu!");
    }
}

// Xử lý tạo phiếu test để thử nghiệm cơ chế phân công & tràn tải
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test_dispatch') {
    $catId = (int)($_POST['category_id'] ?? 1);
    try {
        $res = TicketService::createTicketWithAutoDispatch([
            'requester_id'    => 13, // SV Thanh Ngân
            'category_id'     => $catId,
            'priority_id'     => 2,  // HIGH
            'title'           => "[TEST DISPATCH] Yêu cầu hỗ trợ hệ thống lúc " . date('H:i:s'),
            'description'     => "Phiếu thử nghiệm cơ chế tự động phân công KTV chuyên trách & kiểm tra tràn tải (Auto-Overflow).",
            'system_affected' => 'Hệ thống ĐH Thủ Đô',
        ]);

        $msg = "Đã tạo phiếu {$res['ticket_code']}! Kết quả: <strong>{$res['assigned_agent']}</strong> tiếp nhận.";
        if ($res['is_overflow']) {
            $msg .= " <span class='bg-rose-500 text-white px-2 py-0.5 rounded text-[11px] font-bold'>⚠️ KÍCH HOẠT TRÀN TẢI THÀNH CÔNG!</span>";
        }
        set_flash('success', $msg);
        header("Location: index.php?agent_id=" . $res['dispatch_info']['agent_id']);
        exit;
    } catch (Exception $e) {
        set_flash('error', $e->getMessage());
    }
}

// Danh sách 5 KTV chuyên trách
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

// Lấy hàng đợi phiếu của KTV hiện tại (Sắp xếp theo due_at ASC)
$queue = TicketService::getAgentQueue($agentId, $statusFilter);
$categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Workspace - Bàn Làm Việc Kỹ Thuật Viên</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 md:p-6">

<div class="max-w-6xl mx-auto space-y-6">

    <!-- BANNER PHÂN HỆ CỦA TRƯỞNG NHÓM -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-5 shadow-sm border border-indigo-900/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">
                    Phân hệ: Core Architecture & Agent Workspace
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300">
                    Thuật Toán Tràn Tải Thông Minh
                </span>
            </div>
            <h1 class="text-2xl font-black text-white">👨‍💻 BÀN LÀM VIỆC KỸ THUẬT VIÊN (AGENT WORKSPACE)</h1>
            <p class="text-xs text-indigo-200/80 mt-1">Phụ trách độc quyền: <strong class="text-white">Phạm Thị Thanh Ngân</strong> (MSSV: 224001736 · Khóa TUD D24 · Lớp D24-CNTT1)</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="../tickets/index.php" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl transition border border-white/20">
                🎫 Quản lý CRUD Bài 5
            </a>
            <a href="../index.php" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                🏠 Trang Chủ
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    <?php if ($flash): ?>
        <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <!-- THANH CHUYỂN ĐỔI 5 KTV CHUYÊN TRÁCH & TẢI TRỌNG -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <span>👥</span> Đội Ngũ 5 Kỹ Thuật Viên Chuyên Trách & Tình Trạng Tải Hiện Tại:
            </h3>
            <span class="text-[11px] text-slate-400">Giới hạn tải tối đa: <strong>15 phiếu/KTV</strong></span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <?php foreach ($allAgents as $ag): ?>
                <?php 
                $isSelected = ($ag['id'] == $agentId);
                $cap = !empty($ag['max_capacity']) ? (int)$ag['max_capacity'] : 15;
                $loadPercent = min(100, round(($ag['active_count'] / $cap) * 100));
                $loadColor = $ag['active_count'] >= $cap ? 'bg-rose-500 text-white' : ($ag['active_count'] >= 10 ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white');
                $cardBorder = $isSelected ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-500/20' : 'border-slate-200 hover:border-slate-300 bg-slate-50';
                ?>
                <a href="index.php?agent_id=<?php echo $ag['id']; ?>" class="p-3 rounded-xl border <?php echo $cardBorder; ?> transition text-xs block space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900"><?php echo htmlspecialchars($ag['user_code']); ?></span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $loadColor; ?>">
                            <?php echo $ag['active_count']; ?>/<?php echo $cap; ?>
                        </span>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($ag['name']); ?></div>
                        <div class="text-[10px] text-slate-500 truncate"><?php echo htmlspecialchars($ag['specialty_name']); ?></div>
                    </div>
                    <!-- Thanh tiến trình tải -->
                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                        <div class="<?php echo $ag['active_count'] >= $cap ? 'bg-rose-500' : ($ag['active_count'] >= 10 ? 'bg-amber-500' : 'bg-emerald-500'); ?> h-full rounded-full transition-all" style="width: <?php echo $loadPercent; ?>%"></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- KHUNG ĐIỀU KHIỂN & TEST TRÀN TẢI -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <!-- THÔNG TIN KTV ĐANG TRỰC (4 CỘT) -->
        <div class="md:col-span-4 space-y-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-sm">
                        <?php echo mb_substr($currentAgent['name'], 0, 1); ?>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900"><?php echo htmlspecialchars($currentAgent['name']); ?></div>
                        <div class="text-slate-500 font-mono"><?php echo htmlspecialchars($currentAgent['email']); ?></div>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 mt-1">
                            ● Đang trong ca trực
                        </span>
                    </div>
                </div>

                <div class="space-y-2 border-t border-slate-100 pt-3 text-[11.5px]">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Chuyên trách:</span>
                        <span class="font-bold text-indigo-700"><?php echo htmlspecialchars($currentAgent['specialty_name']); ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Số phiếu đang xử lý:</span>
                        <span class="font-bold text-slate-800"><?php echo count($queue); ?> phiếu</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Quy tắc sắp xếp:</span>
                        <span class="font-mono font-bold text-rose-600">due_at ASC (SLA)</span>
                    </div>
                </div>
            </div>

            <!-- FORM MÔ PHỎNG TEST THUẬT TOÁN PHÂN CÔNG & TRÀN TẢI -->
            <div class="bg-indigo-50/70 border border-indigo-200 rounded-2xl p-4 shadow-sm text-xs space-y-3">
                <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                    <span>⚡</span> Thử Nghiệm Thuật Toán Phân Công & Tràn Tải:
                </div>
                <p class="text-[11px] text-indigo-800 leading-relaxed">
                    Bấm nút dưới đây để tạo 1 phiếu mẫu. Nếu KTV chuyên trách đang ôm &ge; 15 phiếu (ngưỡng kịch tải), hệ thống sẽ <strong>tự động kích hoạt cơ chế tràn tải</strong> sang KTV khác ít việc nhất!
                </p>
                <form method="POST" action="index.php?agent_id=<?php echo $agentId; ?>" class="space-y-2">
                    <input type="hidden" name="action" value="test_dispatch" />
                    <select name="category_id" class="w-full px-3 py-2 bg-white border border-indigo-300 rounded-xl font-semibold text-slate-800 text-xs focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                Nhóm: <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm text-xs">
                        🚀 Gửi Phiếu & Kích Hoạt Phân Công
                    </button>
                </form>
            </div>
        </div>

        <!-- HÀNG ĐỢI PHIẾU THEO HẠN SLA (8 CỘT) -->
        <div class="md:col-span-8 space-y-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h3 class="font-black text-base text-slate-900 flex items-center gap-2">
                            <span>⏱️</span> HÀNG ĐỢI XỬ LÝ THEO HẠN CHÓT SLA (due_at ASC)
                        </h3>
                        <p class="text-xs text-slate-500">Ưu tiên giải quyết các sự cố sắp hết hạn cam kết với Nhà trường</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        <?php echo count($queue); ?> phiếu cần giải quyết
                    </span>
                </div>

                <?php if (empty($queue)): ?>
                    <div class="text-center py-12 text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-200 space-y-1">
                        <div>🎉 Tuyệt vời! Kỹ thuật viên hiện không có phiếu nào tồn đọng.</div>
                        <div class="text-[11px] text-slate-400">Bạn có thể dùng khung thử nghiệm bên trái để tạo phiếu mới.</div>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($queue as $t): ?>
                            <?php 
                            $mins = (int)$t['minutes_remaining'];
                            $isExpired = ($mins < 0);
                            $timeNotice = $isExpired ? '⚠️ ĐÃ QUÁ HẠN ' . abs($mins) . ' PHÚT!' : ($mins < 60 ? "⏱️ GẤP: Còn {$mins} phút!" : '⏱️ Còn ' . round($mins / 60, 1) . ' giờ');
                            $timeBadge = $isExpired ? 'bg-rose-500 text-white font-black' : ($mins < 60 ? 'bg-amber-100 text-amber-900 font-bold border border-amber-300' : 'bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200');
                            ?>
                            <div class="p-4 rounded-xl border border-slate-200 bg-white hover:border-indigo-300 transition shadow-sm space-y-3 text-xs">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-xs text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-200">
                                            <?php echo htmlspecialchars($t['ticket_code']); ?>
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                            <?php echo htmlspecialchars($t['category_name']); ?>
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Ưu tiên: <?php echo htmlspecialchars($t['priority_name']); ?>
                                        </span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] <?php echo $timeBadge; ?>">
                                        <?php echo $timeNotice; ?>
                                    </span>
                                </div>

                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1"><?php echo htmlspecialchars($t['title']); ?></h4>
                                    <p class="text-slate-600 line-clamp-2 leading-relaxed"><?php echo htmlspecialchars($t['description']); ?></p>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-slate-100 flex-wrap gap-2 text-[11px]">
                                    <div class="text-slate-500">
                                        Người gửi: <strong class="text-slate-800"><?php echo htmlspecialchars($t['requester_name']); ?></strong> (<?php echo htmlspecialchars($t['requester_code']); ?>) • Hạn: <span class="font-mono font-semibold text-slate-700"><?php echo $t['due_at']; ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="../tickets/show.php?id=<?php echo $t['id']; ?>&view_as=agent" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition">
                                            💬 Trao đổi & Chi tiết ➔
                                        </a>
                                        <button type="button" onclick="openResolveModal(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['ticket_code'], ENT_QUOTES); ?>')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition shadow-sm">
                                            ✅ Nghiệm thu xong
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- MODAL NGHIỆM THU GIẢI PHÁP KỸ THUẬT -->
<div id="resolveModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-1.5">
                <span>✅</span> Lập Biên Bản Nghiệm Thu Giải Pháp (<span id="modalTicketCode"></span>)
            </h3>
            <button type="button" onclick="closeResolveModal()" class="text-slate-400 hover:text-slate-600 text-base">✕</button>
        </div>
        <form method="POST" action="index.php?agent_id=<?php echo $agentId; ?>" class="space-y-3">
            <input type="hidden" name="action" value="resolve_ticket" />
            <input type="hidden" name="ticket_id" id="modalTicketId" value="0" />
            
            <div>
                <label class="block font-bold text-slate-700 mb-1">Mô tả phương án xử lý / Hướng dẫn sinh viên kiểm tra:</label>
                <textarea name="solution_note" rows="4" required placeholder="Ví dụ: Đã xóa cache tài khoản, reset mật khẩu tạm về 123456 và giải phóng IP mạng..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeResolveModal()" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 font-semibold hover:bg-slate-50">
                    Hủy
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-sm">
                    Xác Nhận Đã Giải Quyết Xong
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(id, code) {
    document.getElementById('modalTicketId').value = id;
    document.getElementById('modalTicketCode').textContent = code;
    document.getElementById('resolveModal').classList.remove('hidden');
}
function closeResolveModal() {
    document.getElementById('resolveModal').classList.add('hidden');
}
</script>

</body>
</html>

