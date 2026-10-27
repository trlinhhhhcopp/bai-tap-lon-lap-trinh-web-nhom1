<?php
// ==============================================================================
// BÀI 5: MÀN HÌNH DANH SÁCH PHIẾU - TÌM KIẾM & PHÂN TRANG (SLIDE 6, 7 & 11)
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

// 1. Tiếp nhận tham số tìm kiếm & lọc (Làm sạch đầu vào)
$keyword    = trim($_GET['kw'] ?? '');
$status     = trim($_GET['status'] ?? '');
$categoryId = (int)($_GET['category_id'] ?? 0);

// 2. Phân trang an toàn (Slide 11)
$perPage    = 5; // Hiển thị 5 phiếu / trang
$page       = max(1, (int)($_GET['page'] ?? 1)); // Ép kiểu số nguyên, tối thiểu là 1

$totalTickets = TicketRepository::countAll($keyword, $status, $categoryId);
$totalPages   = max(1, (int)ceil($totalTickets / $perPage));

if ($page > $totalPages && $totalTickets > 0) {
    $page = $totalPages;
}

$tickets = TicketRepository::findAll($keyword, $page, $perPage, $status, $categoryId);
$categories = TicketRepository::getCategories();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phiếu Hỗ Trợ - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 md:p-6">

<div class="max-w-7xl mx-auto space-y-6">

    <!-- HEADER & ĐIỀU HƯỚNG -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">ĐH Thủ Đô Hà Nội • Khóa TUD D24</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Bài 5: CRUD bằng PDO</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Quản Lý Phiếu Hỗ Trợ (Tickets CRUD)</h1>
            <p class="text-xs text-slate-500 mt-1">Phân hệ Kỹ thuật viên & Sinh viên — Phụ trách bởi Trưởng nhóm: <strong>Phạm Thị Thanh Ngân</strong></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="../Thanhngan/cam_nang_toan_bo_csdl_helpdesk.html" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                📚 Xem Cẩm Nang 12 Bảng
            </a>
            <a href="create.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-indigo-200 transition flex items-center gap-1.5">
                <span>➕</span> Tạo Phiếu Hỗ Trợ Mới
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGE (SLIDE 2 & 8) -->
    <?php if ($flash): ?>
        <div class="p-4 rounded-xl text-xs font-semibold flex items-center justify-between <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
            <div class="flex items-center gap-2">
                <span><?php echo $flash['type'] === 'success' ? '✅' : '⚠️'; ?></span>
                <span><?php echo htmlspecialchars($flash['message']); ?></span>
            </div>
            <span class="text-slate-400 text-[10px]">Tự động xóa sau khi xem</span>
        </div>
    <?php endif; ?>

    <!-- THANH TÌM KIẾM & BỘ LỌC (SLIDE 6) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="index.php" class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
            <!-- Tìm kiếm theo từ khóa -->
            <div class="md:col-span-5">
                <label class="block font-semibold text-slate-600 mb-1">🔍 Tìm kiếm mã phiếu / tiêu đề:</label>
                <input type="text" name="kw" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Ví dụ: HD-2026, Canvas, Wi-Fi..." 
                       class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
            </div>

            <!-- Lọc trạng thái -->
            <div class="md:col-span-3">
                <label class="block font-semibold text-slate-600 mb-1">Trạng thái phiếu:</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>Mới tiếp nhận (new)</option>
                    <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>Đang xử lý (in_progress)</option>
                    <option value="waiting_requester" <?php echo $status === 'waiting_requester' ? 'selected' : ''; ?>>Chờ người dùng (waiting)</option>
                    <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Đã giải quyết (resolved)</option>
                    <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>Đã đóng (closed)</option>
                </select>
            </div>

            <!-- Lọc danh mục -->
            <div class="md:col-span-3">
                <label class="block font-semibold text-slate-600 mb-1">Nhóm dịch vụ CNTT:</label>
                <select name="category_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                    <option value="0">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId === (int)$cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nút Lọc -->
            <div class="md:col-span-1 flex items-end">
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm">
                    Lọc
                </button>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH PHIẾU (SLIDE 7 - LIST) -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center text-xs">
            <span class="font-bold text-slate-700 uppercase tracking-wider">
                Tìm thấy <strong><?php echo $totalTickets; ?></strong> phiếu hỗ trợ
            </span>
            <span class="text-slate-400">Trang <?php echo $page; ?> / <?php echo $totalPages; ?></span>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="p-8 text-center text-slate-500 text-xs">
                <span class="text-3xl block mb-2">📭</span>
                Không tìm thấy phiếu hỗ trợ nào phù hợp với bộ lọc hiện tại.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 font-bold border-b border-slate-200">
                            <th class="p-3 w-32">Mã Vé</th>
                            <th class="p-3">Tiêu Đề Sự Cố</th>
                            <th class="p-3 w-40">Người Gửi (SV/GV)</th>
                            <th class="p-3 w-36">Danh Mục</th>
                            <th class="p-3 w-28 text-center">Mức Ưu Tiên</th>
                            <th class="p-3 w-36">KTV Tiếp Nhận</th>
                            <th class="p-3 w-28 text-center">Trạng Thái</th>
                            <th class="p-3 w-32 text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($tickets as $t): ?>
                            <?php
                            // Màu trạng thái
                            $statusBadge = match($t['status']) {
                                'new'               => 'bg-blue-100 text-blue-800 border-blue-200',
                                'in_progress'       => 'bg-amber-100 text-amber-800 border-amber-200',
                                'waiting_requester' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'resolved'          => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'closed'            => 'bg-slate-100 text-slate-700 border-slate-200',
                                default             => 'bg-slate-100 text-slate-700'
                            };

                            // Màu ưu tiên
                            $prioBadge = match($t['priority_code']) {
                                'URGENT' => 'bg-rose-100 text-rose-800 font-bold',
                                'HIGH'   => 'bg-orange-100 text-orange-800 font-semibold',
                                'MED'    => 'bg-blue-50 text-blue-700',
                                default  => 'bg-slate-100 text-slate-600'
                            };
                            ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-mono font-bold text-indigo-600">
                                    <a href="show.php?id=<?php echo $t['id']; ?>" class="hover:underline">
                                        <?php echo htmlspecialchars($t['ticket_code']); ?>
                                    </a>
                                </td>
                                <td class="p-3">
                                    <a href="show.php?id=<?php echo $t['id']; ?>" class="font-semibold text-slate-900 hover:text-indigo-600 line-clamp-1">
                                        <?php echo htmlspecialchars($t['title']); ?>
                                    </a>
                                    <span class="text-slate-400 text-[11px] block mt-0.5">
                                        Hệ thống: <?php echo htmlspecialchars($t['system_affected']); ?> • Hạn SLA: <?php echo htmlspecialchars($t['due_at']); ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <span class="font-medium text-slate-900"><?php echo htmlspecialchars($t['requester_name']); ?></span>
                                    <span class="text-slate-400 text-[11px] block font-mono"><?php echo htmlspecialchars($t['requester_code']); ?></span>
                                </td>
                                <td class="p-3 text-slate-600">
                                    <?php echo htmlspecialchars($t['category_name']); ?>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] <?php echo $prioBadge; ?>">
                                        <?php echo htmlspecialchars($t['priority_name']); ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <?php if ($t['agent_name']): ?>
                                        <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($t['agent_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">Chưa tiếp nhận</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] border <?php echo $statusBadge; ?>">
                                        <?php echo htmlspecialchars($t['status']); ?>
                                    </span>
                                </td>
                                <td class="p-3 text-center space-x-1">
                                    <a href="show.php?id=<?php echo $t['id']; ?>" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-semibold transition" title="Xem chi tiết">
                                        Xem
                                    </a>
                                    <a href="edit.php?id=<?php echo $t['id']; ?>" class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded text-[11px] font-semibold transition" title="Sửa phiếu">
                                        Sửa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- PHÂN TRANG (SLIDE 11: offset = (page - 1) * perPage) -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
                <span class="text-slate-500">
                    Hiển thị trang <strong><?php echo $page; ?></strong> trên tổng số <strong><?php echo $totalPages; ?></strong> trang
                </span>
                <div class="flex items-center gap-1">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&kw=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&category_id=<?php echo $categoryId; ?>" 
                           class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100 transition">
                            ← Trước
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&kw=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&category_id=<?php echo $categoryId; ?>" 
                           class="px-3 py-1.5 rounded-lg border <?php echo $i === $page ? 'bg-indigo-600 text-white font-bold border-indigo-600' : 'border-slate-300 hover:bg-slate-100'; ?> transition">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&kw=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&category_id=<?php echo $categoryId; ?>" 
                           class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-100 transition">
                            Sau →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>

