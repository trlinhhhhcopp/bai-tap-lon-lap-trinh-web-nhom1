<?php
$pageTitle = 'Quản Lý Lịch Nghỉ Lễ & Sự Kiện (holidays)';
require_once __DIR__ . '/../../../../config/database.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
if (empty($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

$keyword = $keyword ?? '';
$holidays = $holidays ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;

function holidayEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
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
<?php require_once __DIR__ . '/../../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- HEADER BANNER -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                📅 QUẢN TRỊ ADMIN - LỊCH NGHỈ LỄ (HOLIDAYS)
            </span>
            <h1 class="text-2xl font-black text-white mt-1">
                Lịch Nghỉ Lễ & Sự Kiện Học Thuật
            </h1>
            <p class="text-xs text-indigo-200/80">Đồng bộ tự động với Canvas LMS & Hệ thống tính hạn cam kết dịch vụ (SLA Exclusion) trừ ngày nghỉ theo tiêu chuẩn ITIL v4.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="index.php?controller=holiday&action=create" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                <span>➕</span> Thêm Ngày Nghỉ Lễ
            </a>
        </div>
    </div>

    <!-- THANH TÌM KIẾM & BỘ LỌC -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between flex-wrap gap-3 text-xs">
        <form method="GET" action="index.php" class="flex items-center gap-2 flex-1 max-w-lg">
            <input type="hidden" name="controller" value="holiday">
            <input type="hidden" name="action" value="index">
            <input type="text" name="keyword" value="<?= holidayEscape($keyword) ?>" placeholder="Tìm kiếm theo tên ngày nghỉ lễ (Tết, Giỗ Tổ, 30/4...)" 
                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" />
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">
                Tìm
            </button>
            <?php if ($keyword !== ''): ?>
                <a href="index.php?controller=holiday&action=index" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition">
                    Xóa lọc
                </a>
            <?php endif; ?>
        </form>
        <div class="text-slate-500 font-semibold">
            Tìm thấy: <strong class="text-indigo-600"><?= (int)$total ?></strong> ngày nghỉ lễ trong CSDL
        </div>
    </div>

    <!-- BẢNG DỮ LIỆU HOLIDAYS -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden text-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/75 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 w-12">ID</th>
                        <th class="py-3 px-4">Tên Ngày Nghỉ Lễ</th>
                        <th class="py-3 px-4">Ngày Bắt Đầu</th>
                        <th class="py-3 px-4">Ngày Kết Thúc</th>
                        <th class="py-3 px-4 text-center">Lặp Lại Hằng Năm</th>
                        <th class="py-3 px-4 text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($holidays)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Không tìm thấy ngày nghỉ lễ nào phù hợp với từ khóa "<?= holidayEscape($keyword) ?>".
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($holidays as $h): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono text-slate-500">#<?= (int)$h['id'] ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span><?= holidayEscape($h['name']) ?></span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-medium font-mono">
                                    📅 <?= holidayEscape($h['start_date']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-medium font-mono">
                                    📅 <?= holidayEscape($h['end_date']) ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ((int)$h['is_recurring'] === 1): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            🔄 Lặp lại hằng năm
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            Một lần (Đơn lẻ)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="index.php?controller=holiday&action=edit&id=<?= (int)$h['id'] ?>" 
                                       class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold rounded-lg border border-amber-200 transition">
                                        ✏️ Sửa
                                    </a>
                                    <form method="POST" action="index.php?controller=holiday&action=delete" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ngày nghỉ lễ: <?= holidayEscape($h['name']) ?>?');">
                                        <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg border border-rose-200 transition">
                                            🗑️ Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="p-3.5 border-t border-slate-200 bg-slate-50 flex items-center justify-between text-xs">
                <div>
                    Trang <strong class="text-indigo-600"><?= (int)$page ?></strong> / <?= (int)$totalPages ?>
                </div>
                <div class="flex items-center gap-1.5">
                    <?php if ($page > 1): ?>
                        <a href="?controller=holiday&action=index&page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>" 
                           class="px-3 py-1 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 font-medium">
                            ← Trang trước
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?controller=holiday&action=index&page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>" 
                           class="px-3 py-1 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 font-medium">
                            Trang sau →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/../../../../includes/footer.php'; ?>
</body>
</html>
