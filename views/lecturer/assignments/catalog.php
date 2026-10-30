<?php
$pageTitle = 'Quản lý Lớp học & Môn học';
require_once __DIR__ . '/../../../config/database.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: index.php?action=index');
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
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-200 border border-indigo-500/30">
                DANH MỤC ĐÀO TẠO
            </span>
            <h1 class="text-2xl font-black mt-2"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="text-xs text-indigo-200/80 mt-1">Thêm lớp học và môn học tại một trang riêng; danh sách được cập nhật ngay sau khi lưu.</p>
        </div>
        <a href="index.php?action=index" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl text-xs transition border border-white/20">
            ← Danh sách phân công
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div role="status" class="p-3.5 rounded-2xl text-xs font-semibold <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <?php if ($isLocked): ?>
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900">
            Cơ sở dữ liệu đang bị khóa. Hãy mở khóa tại trang phân công trước khi thêm lớp hoặc môn học.
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-lg font-black text-slate-900">🏫 Quản lý lớp học</h2>
                    <p class="text-xs text-slate-500 mt-1"><?= count($classes) ?> lớp trong danh mục</p>
                </div>
            </div>
            <form method="post" action="index.php?action=add_class" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <label class="font-bold text-slate-700">Mã lớp <span class="text-rose-500">*</span>
                    <input type="text" name="class_code" required placeholder="VD: D24-CNTT3" <?= $isLocked ? 'disabled' : '' ?>
                           class="mt-1 w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </label>
                <label class="font-bold text-slate-700">Tên lớp <span class="text-rose-500">*</span>
                    <input type="text" name="class_name" required placeholder="VD: Công nghệ thông tin D24-3" <?= $isLocked ? 'disabled' : '' ?>
                           class="mt-1 w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </label>
                <label class="font-bold text-slate-700">Niên khóa
                    <input type="text" name="cohort" value="D24" placeholder="VD: D24" <?= $isLocked ? 'disabled' : '' ?>
                           class="mt-1 w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </label>
                <label class="font-bold text-slate-700">Khoa / Viện
                    <input type="text" name="department" value="Khoa Công Nghệ Thông Tin" <?= $isLocked ? 'disabled' : '' ?>
                           class="mt-1 w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </label>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" <?= $isLocked ? 'disabled' : '' ?>
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl transition">
                        + Thêm lớp
                    </button>
                </div>
            </form>

            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 space-y-3">
                <div>
                    <h3 class="font-bold text-indigo-950 text-xs">Import nhiều lớp từ tệp</h3>
                    <p class="text-[11px] text-slate-600 mt-1">Cột: Mã lớp, Tên lớp, Niên khóa, Khoa. Có thể dùng .xlsx hoặc .csv.</p>
                </div>
                <form method="post" enctype="multipart/form-data" action="index.php?action=import_classes" class="flex flex-wrap items-center gap-2">
                    <input type="file" name="import_file" required accept=".xlsx,.csv,.txt" <?= $isLocked ? 'disabled' : '' ?>
                           class="min-w-0 flex-1 text-[11px] text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:font-semibold border border-slate-200 rounded-xl bg-white p-1">
                    <button type="submit" <?= $isLocked ? 'disabled' : '' ?>
                            class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold rounded-xl text-xs">Import lớp</button>
                </form>
                <a href="index.php?action=download_template&amp;type=classes" class="text-[11px] font-bold text-indigo-700 hover:underline">Tải mẫu import lớp (.csv)</a>
            </div>

            <?php if (($importedList['type'] ?? '') === 'classes'): ?>
                <div class="space-y-2">
                    <h3 class="font-bold text-emerald-800 text-xs">Danh sách lớp vừa import thành công (<?= (int) $importedList['total'] ?>)</h3>
                    <div class="overflow-x-auto border border-emerald-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-emerald-50 text-emerald-900">
                                <tr>
                                    <th class="px-3 py-2.5">Mã lớp</th>
                                    <th class="px-3 py-2.5">Tên lớp</th>
                                    <th class="px-3 py-2.5">Niên khóa</th>
                                    <th class="px-3 py-2.5">Khoa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($importedList['rows'] as $class): ?>
                                    <tr>
                                        <td class="px-3 py-2.5 font-mono font-bold text-indigo-700"><?= htmlspecialchars($class['class_code']) ?></td>
                                        <td class="px-3 py-2.5"><?= htmlspecialchars($class['name']) ?></td>
                                        <td class="px-3 py-2.5"><?= htmlspecialchars($class['cohort']) ?></td>
                                        <td class="px-3 py-2.5"><?= htmlspecialchars($class['department']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($importedList['total'] > count($importedList['rows'])): ?>
                        <p class="text-[11px] text-slate-500">Đang hiển thị <?= count($importedList['rows']) ?> / <?= (int) $importedList['total'] ?> dòng vừa import.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <details class="group" <?= ($importedList['type'] ?? '') === 'classes' ? '' : 'open' ?>>
                <summary class="cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-bold text-slate-700">
                    Danh sách tất cả lớp học (<?= count($classes) ?>)
                </summary>
                <div class="overflow-x-auto border border-slate-100 rounded-xl mt-2">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2.5">Mã lớp</th>
                            <th class="px-3 py-2.5">Tên lớp</th>
                            <th class="px-3 py-2.5">Niên khóa</th>
                            <th class="px-3 py-2.5">Khoa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($classes)): ?>
                            <tr><td colspan="4" class="px-3 py-5 text-center text-slate-400">Chưa có lớp học.</td></tr>
                        <?php else: ?>
                            <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td class="px-3 py-2.5 font-mono font-bold text-indigo-700"><?= htmlspecialchars($class['class_code']) ?></td>
                                    <td class="px-3 py-2.5"><?= htmlspecialchars($class['name']) ?></td>
                                    <td class="px-3 py-2.5"><?= htmlspecialchars($class['cohort'] ?? '') ?></td>
                                    <td class="px-3 py-2.5"><?= htmlspecialchars($class['department'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </details>
        </section>

        <section class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-lg font-black text-slate-900">📖 Quản lý môn học</h2>
                    <p class="text-xs text-slate-500 mt-1"><?= count($courses) ?> môn trong danh mục</p>
                </div>
            </div>
            <form method="post" action="index.php?action=add_course" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <label class="font-bold text-slate-700">Mã môn <span class="text-rose-500">*</span>
                    <input type="text" name="course_code" required placeholder="VD: INT1316" <?= $isLocked ? 'disabled' : '' ?>
                           class="mt-1 w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </label>
                <label class="font-bold text-slate-700">Tên môn <span class="text-rose-500">*</span>
                    <input type="text" name="course_name" required placeholder="VD: Lập trình web" <?= $isLocked ? 'disabled' : '' ?>
                           class="mt-1 w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </label>
                <label class="font-bold text-slate-700">Số tín chỉ
                    <input type="number" name="credits" value="3" min="1" max="10" <?= $isLocked ? 'disabled' : '' ?>
                           class="mt-1 w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono">
                </label>
                <label class="font-bold text-slate-700">Khoa / Bộ môn
                    <input type="text" name="department" value="Khoa Công Nghệ Thông Tin" <?= $isLocked ? 'disabled' : '' ?>
                           class="mt-1 w-full px-3 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                </label>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" <?= $isLocked ? 'disabled' : '' ?>
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl transition">
                        + Thêm môn
                    </button>
                </div>
            </form>

            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 space-y-3">
                <div>
                    <h3 class="font-bold text-indigo-950 text-xs">Import nhiều môn học từ tệp</h3>
                    <p class="text-[11px] text-slate-600 mt-1">Cột: Mã môn, Tên môn, Số tín chỉ, Khoa/Bộ môn. Có thể dùng .xlsx hoặc .csv.</p>
                </div>
                <form method="post" enctype="multipart/form-data" action="index.php?action=import_courses" class="flex flex-wrap items-center gap-2">
                    <input type="file" name="import_file" required accept=".xlsx,.csv,.txt" <?= $isLocked ? 'disabled' : '' ?>
                           class="min-w-0 flex-1 text-[11px] text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:font-semibold border border-slate-200 rounded-xl bg-white p-1">
                    <button type="submit" <?= $isLocked ? 'disabled' : '' ?>
                            class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold rounded-xl text-xs">Import môn</button>
                </form>
                <a href="index.php?action=download_template&amp;type=courses" class="text-[11px] font-bold text-indigo-700 hover:underline">Tải mẫu import môn (.csv)</a>
            </div>

            <?php if (($importedList['type'] ?? '') === 'courses'): ?>
                <div class="space-y-2">
                    <h3 class="font-bold text-emerald-800 text-xs">Danh sách môn vừa import thành công (<?= (int) $importedList['total'] ?>)</h3>
                    <div class="overflow-x-auto border border-emerald-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-emerald-50 text-emerald-900">
                                <tr>
                                    <th class="px-3 py-2.5">Mã môn</th>
                                    <th class="px-3 py-2.5">Tên môn</th>
                                    <th class="px-3 py-2.5 text-center">Tín chỉ</th>
                                    <th class="px-3 py-2.5">Khoa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($importedList['rows'] as $course): ?>
                                    <tr>
                                        <td class="px-3 py-2.5 font-mono font-bold text-indigo-700"><?= htmlspecialchars($course['course_code']) ?></td>
                                        <td class="px-3 py-2.5"><?= htmlspecialchars($course['name']) ?></td>
                                        <td class="px-3 py-2.5 text-center"><?= (int) $course['credits'] ?></td>
                                        <td class="px-3 py-2.5"><?= htmlspecialchars($course['department']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($importedList['total'] > count($importedList['rows'])): ?>
                        <p class="text-[11px] text-slate-500">Đang hiển thị <?= count($importedList['rows']) ?> / <?= (int) $importedList['total'] ?> dòng vừa import.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <details class="group" <?= ($importedList['type'] ?? '') === 'courses' ? '' : 'open' ?>>
                <summary class="cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-bold text-slate-700">
                    Danh sách tất cả môn học (<?= count($courses) ?>)
                </summary>
                <div class="overflow-x-auto border border-slate-100 rounded-xl mt-2">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2.5">Mã môn</th>
                            <th class="px-3 py-2.5">Tên môn</th>
                            <th class="px-3 py-2.5 text-center">Tín chỉ</th>
                            <th class="px-3 py-2.5">Khoa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($courses)): ?>
                            <tr><td colspan="4" class="px-3 py-5 text-center text-slate-400">Chưa có môn học.</td></tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td class="px-3 py-2.5 font-mono font-bold text-indigo-700"><?= htmlspecialchars($course['course_code']) ?></td>
                                    <td class="px-3 py-2.5"><?= htmlspecialchars($course['name']) ?></td>
                                    <td class="px-3 py-2.5 text-center"><?= (int) $course['credits'] ?></td>
                                    <td class="px-3 py-2.5"><?= htmlspecialchars($course['department'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </details>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>
