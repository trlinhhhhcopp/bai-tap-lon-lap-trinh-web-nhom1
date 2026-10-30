<?php
$pageTitle = 'Thêm Phân Công Giảng Dạy';
require_once __DIR__ . '/../../../../config/database.php';
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
<?php require_once __DIR__ . '/../../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        
        <div class="border-b border-slate-100 pb-4">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                📚 PHÂN HỆ GIẢNG VIÊN & ĐÀO TẠO
            </span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">
                <?= htmlspecialchars($pageTitle) ?>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Nguyên tắc nghiệp vụ ITIL v4: Mỗi lớp học phần được phân công <strong>1 giảng viên duy nhất</strong> phụ trách trong kỳ học.
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
                <p class="font-bold">⚠️ Có lỗi phát sinh khi lưu:</p>
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?action=create" novalidate class="space-y-4 text-xs">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="class_id" class="font-bold text-slate-700">
                        Lớp Học: <span class="text-rose-500">*</span>
                    </label>
                    <button type="button" onclick="document.getElementById('modalAddClass').classList.remove('hidden')" class="text-indigo-600 hover:text-indigo-800 font-bold text-[11px] hover:underline flex items-center gap-1">
                        <span>➕</span> Thêm Lớp Mới
                    </button>
                </div>
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
                <div class="flex items-center justify-between mb-1">
                    <label for="course_id" class="font-bold text-slate-700">
                        Môn Học / Học Phần: <span class="text-rose-500">*</span>
                    </label>
                    <button type="button" onclick="document.getElementById('modalAddCourse').classList.remove('hidden')" class="text-indigo-600 hover:text-indigo-800 font-bold text-[11px] hover:underline flex items-center gap-1">
                        <span>➕</span> Thêm Môn Mới
                    </button>
                </div>
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
                    <input type="text" id="semester" name="semester" value="<?= htmlspecialchars($old['semester']) ?>" placeholder="Ví dụ: 2026_1" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-slate-800">
                </div>
                <div>
                    <label for="academic_year" class="block font-bold text-slate-700 mb-1">
                        Năm Học: <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="academic_year" name="academic_year" value="<?= htmlspecialchars($old['academic_year']) ?>" placeholder="Ví dụ: 2026-2027" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-slate-800">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="index.php?action=index" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center gap-1.5">
                    💾 Lưu Phân Công
                </button>
            </div>
        </form>
    </div>
</main>

<!-- MODAL THÊM LỚP HỌC MỚI (PAGE 3 & PAGE 4) -->
<div id="modalAddClass" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span>🏫</span> Thêm Lớp Học Mới
            </h3>
            <button type="button" onclick="document.getElementById('modalAddClass').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <form method="POST" action="index.php?action=add_class" class="space-y-3">
            <input type="hidden" name="redirect_to" value="create" />
            <div>
                <label class="block font-bold text-slate-700 mb-1">Mã Lớp Học: <span class="text-rose-500">*</span></label>
                <input type="text" name="class_code" required placeholder="VD: D24-CNTT3, D23-CNTT1..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold" />
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tên Đầy Đủ Lớp Học: <span class="text-rose-500">*</span></label>
                <input type="text" name="class_name" required placeholder="VD: Lớp Công nghệ Thông tin D24-3..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-semibold" />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Niên Khóa:</label>
                    <input type="text" name="cohort" value="D24" placeholder="VD: D24, D25" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono" />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Khoa / Viện:</label>
                    <input type="text" name="department" value="Khoa Công Nghệ Thông Tin" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalAddClass').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-xl font-semibold text-slate-600 hover:bg-slate-100">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm">
                    💾 Lưu Lớp Học
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL THÊM MÔN HỌC MỚI (PAGE 3 & PAGE 4) -->
<div id="modalAddCourse" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span>📖</span> Thêm Môn Học Mới
            </h3>
            <button type="button" onclick="document.getElementById('modalAddCourse').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <form method="POST" action="index.php?action=add_course" class="space-y-3">
            <input type="hidden" name="redirect_to" value="create" />
            <div>
                <label class="block font-bold text-slate-700 mb-1">Mã Môn Học: <span class="text-rose-500">*</span></label>
                <input type="text" name="course_code" required placeholder="VD: INT1316, CS101, IT3020..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold" />
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tên Môn Học: <span class="text-rose-500">*</span></label>
                <input type="text" name="course_name" required placeholder="VD: Trí Tuệ Nhân Tạo, Lập Trình Di Động..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-semibold" />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Số Tín Chỉ:</label>
                    <input type="number" name="credits" value="3" min="1" max="10" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono" />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Khoa / Bộ Môn:</label>
                    <input type="text" name="department" value="Khoa Công Nghệ Thông Tin" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500" />
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalAddCourse').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-xl font-semibold text-slate-600 hover:bg-slate-100">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm">
                    💾 Lưu Môn Học
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../../../includes/footer.php'; ?>
</body>
</html>
