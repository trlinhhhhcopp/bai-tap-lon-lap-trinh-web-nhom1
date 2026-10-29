<?php
$pageTitle = 'Quản Lý Phân Công Giảng Dạy (course_assignments)';
require_once __DIR__ . '/../../../../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
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
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                📚 QUẢN LÝ PHÂN CÔNG ĐÀO TẠO
            </span>
            <h1 class="text-2xl font-black text-white mt-1">
                Phân Công Giảng Dạy Lớp Học Phần
            </h1>
            <p class="text-xs text-indigo-200/80">Quy tắc đào tạo ITIL v4: Mỗi lớp học phần có duy nhất 1 giảng viên phụ trách.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="index.php?action=create" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                <span>➕</span> Thêm Phân Công
            </a>
            <a href="../index.php" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition">
                ← Cổng Chính
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="p-3.5 rounded-2xl text-xs font-semibold <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <!-- THANH TÌM KIẾM -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between flex-wrap gap-3 text-xs">
        <form method="get" action="index.php" class="flex items-center gap-2 flex-1 max-w-lg">
            <input type="hidden" name="action" value="index">
            <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm kiếm theo mã lớp, môn học, giảng viên..." 
                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" />
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">
                Tìm
            </button>
            <?php if ($keyword !== ''): ?>
                <a href="index.php?action=index" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition">
                    Xóa lọc
                </a>
            <?php endif; ?>
        </form>
        <div class="text-slate-500 font-mono text-[11.5px]">
            Tổng số: <strong class="text-slate-800"><?= $total ?></strong> bản ghi · Trang <?= $page ?>/<?= $totalPages ?>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                        <th class="py-3.5 px-4">Lớp Học</th>
                        <th class="py-3.5 px-4">Môn Học</th>
                        <th class="py-3.5 px-4">Giảng Viên Phụ Trách</th>
                        <th class="py-3.5 px-4 text-center">Học Kỳ</th>
                        <th class="py-3.5 px-4 text-center">Năm Học</th>
                        <th class="py-3.5 px-4 text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Không có dữ liệu phân công giảng dạy nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($assignments as $row): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4">
                                    <strong class="font-mono text-indigo-600 font-bold"><?= htmlspecialchars($row['class_code']) ?></strong>
                                    <div class="text-[11px] text-slate-500"><?= htmlspecialchars($row['class_name']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    <?= htmlspecialchars($row['course_name']) ?>
                                    <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($row['course_code']) ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($row['lecturer_name']) ?></div>
                                    <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($row['lecturer_email']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono"><?= htmlspecialchars($row['semester']) ?></td>
                                <td class="py-3.5 px-4 text-center font-mono text-slate-600"><?= htmlspecialchars($row['academic_year']) ?></td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="index.php?action=edit&id=<?= $row['id'] ?>" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition text-[11px]">
                                            Sửa
                                        </a>
                                        <a href="index.php?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa phân công này?');" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg transition text-[11px]">
                                            Xóa
                                        </a>
                                    </div>
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
                    <a href="index.php?action=index&page=<?= $i ?>&q=<?= urlencode($keyword) ?>" 
                       class="px-3 py-1.5 rounded-lg border <?= $i == $page ? 'bg-indigo-600 text-white border-indigo-600 font-bold' : 'border-slate-300 text-slate-700 hover:bg-slate-100' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/../../../../includes/footer.php'; ?>
</body>
</html>