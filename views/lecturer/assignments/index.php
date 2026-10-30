<?php
$userRole   = $_SESSION['user']['role'] ?? '';
$userType   = $_SESSION['user']['user_type'] ?? '';
$isAdmin    = $userRole === 'admin';
$isLecturer = ($userType === 'lecturer' && $userRole !== 'admin');
$isLocked   = !empty($_SESSION['assignments_locked']);
$pageTitle  = $isLecturer ? 'Lớp Học Phần Tôi Phụ Trách' : 'Quản Lý Phân Công Giảng Dạy (Admin)';
$exportQuery = http_build_query([
    'action' => 'export_csv',
    'q' => $keyword,
    'class_id' => $classId,
    'lecturer_id' => $lecturerFilterId,
]);
require_once __DIR__ . '/../../../config/database.php';
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
<?php require_once __DIR__ . '/../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- HEADER BANNER -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    📚 QUẢN LÝ PHÂN CÔNG ĐÀO TẠO
                </span>
                <?php if ($isLocked): ?>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-500/30 text-rose-300 border border-rose-500/40 flex items-center gap-1">
                        🔒 CSDL ĐANG BỊ KHÓA
                    </span>
                <?php else: ?>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        🔓 CSDL ĐANG MỞ
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-2xl font-black text-white">
                <?= htmlspecialchars($pageTitle) ?>
            </h1>
            <p class="text-xs text-indigo-200/80">Quy tắc đào tạo ITIL v4: Mỗi lớp học phần có duy nhất 1 giảng viên phụ trách.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Nút xuất Excel / CSV (Page 3 & Page 8) -->
            <a href="index.php?<?= htmlspecialchars($exportQuery) ?>" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                <span>📥</span> Xuất File Excel
            </a>

            <?php if ($isAdmin): ?>
                <!-- Nút khóa / mở khóa CSDL cho Admin (Page 8) -->
                <a href="index.php?action=toggle_lock" 
                   onclick="return confirm('<?= $isLocked ? 'Bạn muốn MỞ KHÓA cơ sở dữ liệu để tiếp tục chỉnh sửa?' : 'Bạn muốn KHÓA cơ sở dữ liệu phân công giảng dạy (Chế độ chỉ đọc)?' ?>');"
                   class="px-3.5 py-2 <?= $isLocked ? 'bg-amber-600 hover:bg-amber-500' : 'bg-slate-700 hover:bg-slate-600' ?> text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                    <span><?= $isLocked ? '🔓' : '🔒' ?></span> <?= $isLocked ? 'Mở Khóa CSDL' : 'Khóa CSDL' ?>
                </a>

                <a href="index.php?action=create" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 <?= $isLocked ? 'opacity-50 pointer-events-none' : '' ?>">
                    <span>➕</span> Thêm Phân Công
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- CẢNH BÁO KHÓA CSDL (PAGE 8) -->
    <?php if ($isLocked): ?>
        <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-2xl flex items-center justify-between gap-3 text-xs text-amber-900 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-xl">🔒</span>
                <div>
                    <strong>CƠ SỞ DỮ LIỆU ĐANG BỊ KHÓA (FREEZE MODE):</strong>
                    <span>Dữ liệu phân công giảng dạy đang ở chế độ xem và xuất báo cáo. Quản trị viên đã khóa chức năng thêm, sửa, xóa để đảm bảo toàn vẹn dữ liệu kỳ thi.</span>
                </div>
            </div>
            <?php if ($isAdmin): ?>
                <a href="index.php?action=toggle_lock" class="shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition text-[11px]">
                    Mở Khóa Ngay 🔓
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash)): ?>
        <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <section class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm space-y-3 text-xs">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="font-bold text-slate-900">Import phân công từ một tệp</h2>
                    <p class="text-slate-500 mt-1">Cột: Mã lớp, Mã môn, Mã giảng viên hoặc email, Học kỳ, Năm học. Hỗ trợ .xlsx và .csv.</p>
                </div>
                <a href="index.php?action=download_template&amp;type=assignments" class="font-bold text-indigo-700 hover:underline">
                    Tải mẫu import (.csv)
                </a>
            </div>
            <form method="post" enctype="multipart/form-data" action="index.php?action=import_assignments" class="flex flex-wrap items-center gap-2">
                <input type="file" name="import_file" required accept=".xlsx,.csv,.txt" <?= $isLocked ? 'disabled' : '' ?>
                       class="min-w-0 flex-1 text-[11px] text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold border border-slate-200 rounded-xl bg-slate-50 p-1">
                <button type="submit" <?= $isLocked ? 'disabled' : '' ?>
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl">
                    Import phân công
                </button>
            </form>
        </section>
    <?php endif; ?>

    <?php if (($importedList['type'] ?? '') === 'assignments'): ?>
        <section class="bg-white border border-emerald-200 rounded-2xl p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-bold text-emerald-800 text-xs">Danh sách phân công vừa import thành công (<?= (int) $importedList['total'] ?>)</h2>
            </div>
            <div class="overflow-x-auto border border-emerald-100 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-emerald-50 text-emerald-900">
                        <tr>
                            <th class="px-3 py-2.5">Mã lớp</th>
                            <th class="px-3 py-2.5">Mã môn</th>
                            <th class="px-3 py-2.5">Mã / Email giảng viên</th>
                            <th class="px-3 py-2.5 text-center">Học kỳ</th>
                            <th class="px-3 py-2.5 text-center">Năm học</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($importedList['rows'] as $row): ?>
                            <tr>
                                <td class="px-3 py-2.5 font-mono font-bold text-indigo-700"><?= htmlspecialchars($row['class_code']) ?></td>
                                <td class="px-3 py-2.5 font-mono"><?= htmlspecialchars($row['course_code']) ?></td>
                                <td class="px-3 py-2.5"><?= htmlspecialchars($row['lecturer_code']) ?></td>
                                <td class="px-3 py-2.5 text-center"><?= htmlspecialchars($row['semester']) ?></td>
                                <td class="px-3 py-2.5 text-center"><?= htmlspecialchars($row['academic_year']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($importedList['total'] > count($importedList['rows'])): ?>
                <p class="text-[11px] text-slate-500">Đang hiển thị <?= count($importedList['rows']) ?> / <?= (int) $importedList['total'] ?> dòng vừa import.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- THANH TÌM KIẾM -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between flex-wrap gap-3 text-xs">
        <form method="get" action="index.php" class="flex items-center gap-2 flex-1 flex-wrap">
            <input type="hidden" name="action" value="index">
            <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm kiếm theo mã lớp, môn học, giảng viên..." 
                   class="min-w-52 flex-1 px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" />
            <select name="class_id" aria-label="Lọc theo lớp" class="px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                <option value="0">Tất cả lớp</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= (int) $class['id'] ?>" <?= $classId === (int) $class['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($class['class_code'] . ' - ' . $class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!$isLecturer && $userRole === 'admin'): ?>
                <select name="lecturer_id" aria-label="Lọc theo giảng viên" class="px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                    <option value="0">Tất cả giảng viên</option>
                    <?php foreach ($lecturers as $lecturer): ?>
                        <option value="<?= (int) $lecturer['id'] ?>" <?= $lecturerFilterId === (int) $lecturer['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lecturer['name'] . ' (' . $lecturer['user_code'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">
                Lọc
            </button>
            <?php if ($keyword !== '' || $classId > 0 || $lecturerFilterId > 0): ?>
                <a href="index.php?action=index" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition">
                    Xóa lọc
                </a>
            <?php endif; ?>
        </form>
        <div class="text-slate-500 font-mono text-[11.5px]">
            Tổng số: <strong class="text-slate-800"><?= $total ?></strong> bản ghi · Trang <?= $page ?>/<?= $totalPages ?>
        </div>
    </div>

    <details class="group" <?= ($importedList['type'] ?? '') === 'assignments' ? '' : 'open' ?>>
        <summary class="cursor-pointer rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs font-bold text-slate-700 shadow-sm">
            Danh sách phân công hiện có (<?= (int) $total ?>) · Trang <?= $page ?>/<?= $totalPages ?>
        </summary>
    <!-- BẢNG DANH SÁCH -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                        <th class="py-3.5 px-4">Lớp Học Phần</th>
                        <th class="py-3.5 px-4">Môn Học</th>
                        <th class="py-3.5 px-4">Giảng Viên Phụ Trách</th>
                        <th class="py-3.5 px-4 text-center">Học Kỳ</th>
                        <th class="py-3.5 px-4 text-center">Năm Học</th>
                        <?php if ($isAdmin): ?>
                            <!-- Page 8: Ẩn cột thao tác ở role giảng viên (chỉ Admin mới có cột Thao Tác) -->
                            <th class="py-3.5 px-4 text-center">Thao Tác</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="<?= $isLecturer ? 5 : 6 ?>" class="py-8 text-center text-slate-400">Không có dữ liệu phân công giảng dạy nào.</td>
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
                                <td class="py-3.5 px-4 text-center font-mono font-semibold text-indigo-700"><?= htmlspecialchars($row['semester']) ?></td>
                                <td class="py-3.5 px-4 text-center font-mono text-slate-600"><?= htmlspecialchars($row['academic_year']) ?></td>
                                <?php if ($isAdmin): ?>
                                    <!-- Thao tác chỉ dành cho Admin -->
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ($isLocked): ?>
                                            <span class="text-[11px] text-slate-400 italic">Đã khóa</span>
                                        <?php else: ?>
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="index.php?action=edit&id=<?= $row['id'] ?>" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition text-[11px]">
                                                    Sửa
                                                </a>
                                                <a href="index.php?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa phân công này?');" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-lg transition text-[11px]">
                                                    Xóa
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-100 flex items-center justify-center gap-1.5 text-xs">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?action=index&page=<?= $i ?>&q=<?= urlencode($keyword) ?>&class_id=<?= $classId ?>&lecturer_id=<?= $lecturerFilterId ?>"
                       class="px-3 py-1.5 rounded-lg border <?= $i == $page ? 'bg-indigo-600 text-white border-indigo-600 font-bold' : 'border-slate-300 text-slate-700 hover:bg-slate-100' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    </details>

</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>