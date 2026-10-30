<?php
$pageTitle = 'Sửa Phân Công Giảng Dạy';
require_once __DIR__ . '/../../../config/database.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
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

<main class="flex-1 max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        
        <div class="border-b border-slate-100 pb-4">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                ✏️ HIỆU CHỈNH PHÂN CÔNG ĐÀO TẠO
            </span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">
                <?= htmlspecialchars($pageTitle) ?> #<?= (int)$assignment['id'] ?>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Điều chỉnh thông tin giảng viên phụ trách hoặc kỳ học theo kế hoạch đào tạo nhà trường.
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
                <p class="font-bold">⚠️ Có lỗi phát sinh khi cập nhật:</p>
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?action=edit&id=<?= (int)$assignment['id'] ?>" novalidate class="space-y-4 text-xs">
            <div>
                <label for="class_id" class="block font-bold text-slate-700 mb-1">
                    Lớp Học: <span class="text-rose-500">*</span>
                </label>
                <select id="class_id" name="class_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800">
                    <option value="">-- Chọn lớp học phần --</option>
                    <?php foreach ($classes as $cl): ?>
                        <option value="<?= (int)$cl['id'] ?>" <?= ($old['class_id'] == $cl['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cl['class_code'] . ' – ' . $cl['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="course_id" class="block font-bold text-slate-700 mb-1">
                    Môn Học / Học Phần: <span class="text-rose-500">*</span>
                </label>
                <select id="course_id" name="course_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800">
                    <option value="">-- Chọn môn học --</option>
                    <?php foreach ($courses as $co): ?>
                        <option value="<?= (int)$co['id'] ?>" <?= ($old['course_id'] == $co['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($co['course_code'] . ' – ' . $co['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="lecturer_id" class="block font-bold text-slate-700 mb-1">
                    Giảng Viên Phụ Trách: <span class="text-rose-500">*</span>
                </label>
                <select id="lecturer_id" name="lecturer_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800">
                    <option value="">-- Chọn giảng viên --</option>
                    <?php foreach ($lecturers as $gv): ?>
                        <option value="<?= (int)$gv['id'] ?>" <?= ($old['lecturer_id'] == $gv['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($gv['name'] . ' (' . $gv['user_code'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="semester" class="block font-bold text-slate-700 mb-1">
                        Học Kỳ: <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="semester" name="semester" value="<?= htmlspecialchars($old['semester']) ?>" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-slate-800">
                </div>
                <div>
                    <label for="academic_year" class="block font-bold text-slate-700 mb-1">
                        Năm Học: <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="academic_year" name="academic_year" value="<?= htmlspecialchars($old['academic_year']) ?>" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-slate-800">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="index.php?action=index" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow-md shadow-amber-100 flex items-center gap-1.5">
                    💾 Cập Nhật Phân Công
                </button>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>
