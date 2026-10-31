<?php
$pageTitle = 'Đánh Giá Chất Lượng Dịch Vụ (CSAT)';
require_once __DIR__ . '/../../../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
$appBase = get_app_base_url();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
<?php require_once __DIR__ . '/../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- HEADER BANNER -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    ⭐ CHỈ SỐ HÀI LÒNG KHÁCH HÀNG (CSAT)
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    NGHIỆM THU DỊCH VỤ ITIL v4
                </span>
            </div>
            <h1 class="text-2xl font-black text-white">
                Đánh Giá Chất Lượng Hỗ Trợ Kỹ Thuật
            </h1>
            <p class="text-xs text-indigo-200/80 mt-0.5">Khảo sát mức độ hài lòng từ 1 đến 5 sao và nhận xét phản hồi dịch vụ số trường học.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="index.php?action=rating_create" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs">
                <span>⭐</span> Chấm Điểm Đánh Giá Mới
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <!-- BỘ NÚT LỌC NHANH SỐ SAO (5 SAO, 4 SAO, 3 SAO, 2 SAO, 1 SAO) -->
    <div class="flex items-center gap-2 flex-wrap">
        <a href="ratings.php?q=<?= urlencode($keyword) ?>" 
           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 <?= ($scoreFilter === null || $scoreFilter === '') ? 'bg-slate-900 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100' ?>">
            <span>Tất cả số sao</span>
        </a>
        <?php for ($s = 5; $s >= 1; $s--): ?>
            <a href="ratings.php?score=<?= $s ?>&q=<?= urlencode($keyword) ?>" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= ($scoreFilter == $s) ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100' ?>">
                <span class="text-amber-500 font-bold"><?= str_repeat('★', $s) ?></span>
                <span><?= $s ?> sao</span>
            </a>
        <?php endfor; ?>
    </div>

    <!-- THANH TÌM KIẾM -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between flex-wrap gap-3 text-xs">
        <form method="get" action="ratings.php" class="flex items-center gap-2 flex-1 max-w-lg">
            <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm kiếm theo mã vé, sinh viên, nhận xét..." 
                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" />
            <select name="score" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                <option value="">Tất cả số sao</option>
                <?php for ($s = 5; $s >= 1; $s--): ?>
                    <option value="<?= $s ?>" <?= ($scoreFilter == $s) ? 'selected' : '' ?>><?= $s ?> sao</option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">
                Lọc
            </button>
        </form>
        <div class="text-slate-500 font-mono text-[11.5px]">
            Tổng số: <strong class="text-slate-800"><?= $total ?></strong> đánh giá · Trang <?= $page ?>/<?= $totalPages ?>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                        <th class="py-3.5 px-4">Mã Phiếu</th>
                        <th class="py-3.5 px-4">Tiêu Đề Sự Cố</th>
                        <th class="py-3.5 px-4">Người Đánh Giá</th>
                        <th class="py-3.5 px-4 text-center">Điểm CSAT</th>
                        <th class="py-3.5 px-4">Nhận Xét / Góp Ý</th>
                        <th class="py-3.5 px-4 text-center">Thời Gian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($ratings)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Chưa có đánh giá CSAT nào phù hợp.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ratings as $row): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                                    <a href="../tickets/show.php?id=<?= $row['ticket_id'] ?>" class="hover:underline">
                                        <?= htmlspecialchars($row['ticket_code']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800 max-w-xs truncate">
                                    <a href="../tickets/show.php?id=<?= $row['ticket_id'] ?>" class="hover:text-indigo-600 transition">
                                        <?= htmlspecialchars($row['ticket_title']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($row['user_name']) ?></div>
                                    <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($row['user_code']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-mono font-bold text-amber-500 text-sm">
                                        <?= str_repeat('★', (int)$row['score']) . str_repeat('☆', 5 - (int)$row['score']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 italic">
                                    <?= !empty($row['comment']) ? '"' . htmlspecialchars($row['comment']) . '"' : '<span class="text-slate-300">Không có nhận xét</span>' ?>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-400 font-mono text-[11px]">
                                    <?= htmlspecialchars($row['created_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-100 flex items-center justify-center gap-1.5 text-xs">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="ratings.php?page=<?= $i ?>&q=<?= urlencode($keyword) ?>&score=<?= urlencode((string)$scoreFilter) ?>" 
                       class="px-3 py-1.5 rounded-lg border <?= $i == $page ? 'bg-indigo-600 text-white border-indigo-600 font-bold' : 'border-slate-300 text-slate-700 hover:bg-slate-100' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>
