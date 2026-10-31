<?php
/**
 * View: Chi tiết Học phần & Danh sách các buổi học kèm Đính kèm bài tập
 * Phân hệ Sinh viên
 */
require_once __DIR__ . '/../../../config/database.php';
$appBase = get_app_base_url();

$csrfToken = $_SESSION['csrf'] ?? '';
$isAllDone = ($totalLessons > 0 && $completedLessons === $totalLessons);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['name']) ?> (<?= htmlspecialchars($course['course_code']) ?>) - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    <style>
        .lesson-card {
            transition: all 0.2s ease-in-out;
        }
        .lesson-card:hover {
            border-color: #cbd5e1;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
<?php require_once __DIR__ . '/../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-slate-500">
        <a href="index.php?action=courses" class="hover:text-indigo-600 font-semibold transition flex items-center gap-1">
            <span>📚</span> Mục Lục Học Phần
        </a>
        <span>/</span>
        <span class="text-slate-800 font-bold truncate max-w-sm"><?= htmlspecialchars($course['name']) ?></span>
    </nav>

    <!-- HEADER BANNER HỌC PHẦN -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    <?= htmlspecialchars($course['course_code']) ?>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    <?= (int)$course['credits'] ?> Tín chỉ
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-700/60 text-slate-300">
                    <?= htmlspecialchars($course['department']) ?>
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white">
                <?= htmlspecialchars($course['name']) ?>
            </h1>
            <p class="text-xs text-indigo-200/80">
                Quy định đào tạo: Mỗi buổi học sinh viên cần đính kèm bài tập thực hành mới được tính là hoàn thành buổi học.
            </p>
        </div>

        <!-- WIDGET TIẾN ĐỘ -->
        <div class="bg-slate-800/80 border border-slate-700/80 p-4 rounded-2xl md:min-w-[280px] space-y-2 shadow-inner">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-300 font-semibold">Tiến độ bài tập:</span>
                <span class="font-black text-sm <?= $isAllDone ? 'text-emerald-400' : 'text-indigo-400' ?>" id="progress-text">
                    <?= $completedLessons ?> / <?= $totalLessons ?> Buổi (<?= $progressPct ?>%)
                </span>
            </div>
            <div class="w-full bg-slate-700/70 rounded-full h-3 overflow-hidden">
                <div id="progress-bar" class="h-3 rounded-full transition-all duration-500 <?= $isAllDone ? 'bg-emerald-500' : 'bg-indigo-500' ?>" style="width: <?= $progressPct ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                <span>Trạng thái:</span>
                <span class="font-bold <?= $isAllDone ? 'text-emerald-400' : 'text-amber-400' ?>" id="progress-status">
                    <?= $isAllDone ? '✅ Đã hoàn thành tất cả' : '⚠️ Còn ' . ($totalLessons - $completedLessons) . ' buổi chưa nộp' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    <?php if ($flash): ?>
        <div class="p-4 rounded-2xl bg-<?= $flash['type'] === 'success' ? 'emerald' : ($flash['type'] === 'warning' ? 'amber' : 'rose') ?>-50 border border-<?= $flash['type'] === 'success' ? 'emerald' : ($flash['type'] === 'warning' ? 'amber' : 'rose') ?>-200 text-xs font-semibold text-<?= $flash['type'] === 'success' ? 'emerald' : ($flash['type'] === 'warning' ? 'amber' : 'rose') ?>-900 flex items-center justify-between gap-2 shadow-sm">
            <div><?= htmlspecialchars($flash['message']) ?></div>
            <button onclick="this.parentElement.remove();" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>
    <?php endif; ?>

    <!-- THANH ĐIỀU HƯỚNG BỘ LỌC CÁC BUỔI HỌC -->
    <div class="bg-white rounded-2xl border border-slate-200 p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="font-bold text-slate-700 mr-1">Bộ lọc buổi:</span>
            <button type="button" onclick="filterLessons('all')" class="filter-btn px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white transition shadow-sm" data-filter="all">
                Tất cả (<?= $totalLessons ?>)
            </button>
            <button type="button" onclick="filterLessons('completed')" class="filter-btn px-3 py-1.5 rounded-xl font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-filter="completed">
                Đã hoàn thành (<?= $completedLessons ?>)
            </button>
            <button type="button" onclick="filterLessons('pending')" class="filter-btn px-3 py-1.5 rounded-xl font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-filter="pending">
                Chưa hoàn thành (<?= $totalLessons - $completedLessons ?>)
            </button>
        </div>
        <div class="text-[11.5px] text-slate-500 italic">
            * Thời hạn nộp bài tập được tính theo thời khóa biểu thực tế của nhóm
        </div>
    </div>

    <!-- DANH SÁCH LẦN LƯỢT CÁC BUỔI HỌC -->
    <div class="space-y-4" id="lessons-container">
        <?php foreach ($lessons as $index => $item): ?>
            <?php 
                $isSubmitted = !empty($item['is_submitted']);
                $isOverdue = !empty($item['is_overdue']);
                $isLate = !empty($item['is_late']);
                $lessonId = (int)$item['lesson_id'];
                $filterType = $isSubmitted ? 'completed' : 'pending';
            ?>
            <article class="lesson-card bg-white rounded-3xl border <?= $isSubmitted ? 'border-emerald-200 shadow-sm' : ($isOverdue ? 'border-rose-200 shadow-sm' : 'border-slate-200 shadow-sm') ?> overflow-hidden" data-status="<?= $filterType ?>" id="lesson-card-<?= $lessonId ?>">
                
                <!-- TIÊU ĐỀ & THÔNG TIN HẠN BUỔI HỌC -->
                <div class="p-5 md:p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-gradient-to-r <?= $isSubmitted ? 'from-emerald-50/40 via-white to-white' : ($isOverdue ? 'from-rose-50/40 via-white to-white' : 'from-slate-50/60 via-white to-white') ?>">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-black <?= $isSubmitted ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-800' ?>">
                                BUỔI <?= (int)$item['lesson_number'] ?>
                            </span>

                            <!-- BADGE HOÀN THÀNH -->
                            <?php if ($isSubmitted): ?>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                                    <span>✅</span> ĐÃ HOÀN THÀNH
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1">
                                    <span>❌</span> CHƯA HOÀN THÀNH
                                </span>
                            <?php endif; ?>

                            <!-- BADGE DEADLINE / THỜI HẠN BÀI TẬP (NA NÁ GIỐNG TEAM) -->
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-<?= $item['status_color'] ?>-100 text-<?= $item['status_color'] ?>-800 border border-<?= $item['status_color'] ?>-300">
                                ⏱ Hạn nộp: <?= $item['deadline_formatted'] ?> (<?= htmlspecialchars($item['status_badge']) ?>)
                            </span>
                        </div>

                        <h2 class="text-base md:text-lg font-bold text-slate-900 pt-1">
                            <?= htmlspecialchars($item['title']) ?>
                        </h2>
                    </div>

                    <div class="shrink-0 flex items-center gap-2 flex-wrap">
                        <?php if ($isSubmitted): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-xs">
                                <span>🎉</span> Đã đính kèm bài tập
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 font-semibold text-xs">
                                <span>⚠️</span> Cần đính kèm tệp
                            </span>
                        <?php endif; ?>

                        <!-- NÚT BÁO SỰ CỐ KỸ THUẬT BUỔI HỌC THEO KỊCH BẢN 1 (ITIL INCIDENT MANAGEMENT) -->
                        <a href="<?php echo $appBase; ?>tickets/create.php?course_id=<?= (int)$course['id'] ?>&lesson_id=<?= $lessonId ?>" 
                           title="Gặp sự cố lỗi mạng, lỗi nộp tệp hoặc sự cố hệ thống ở buổi học này? Bấm để gửi phiếu cứu hộ ngay tới KTV trực ca"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition shadow-2xs">
                            <span>🆘</span> Báo Lỗi Buổi Này
                        </a>
                    </div>
                </div>

                <!-- NỘI DUNG YÊU CẦU & KHU VỰC ĐÍNH KÈM -->
                <div class="p-5 md:p-6 space-y-4">
                    
                    <!-- MÔ TẢ NỘI DUNG BUỔI HỌC -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 space-y-1">
                            <div class="font-bold text-slate-700 flex items-center gap-1.5">
                                <span>📖</span> Nội dung lý thuyết & thực hành buổi học:
                            </div>
                            <p class="text-slate-600 leading-relaxed">
                                <?= htmlspecialchars($item['description'] ?: 'Chưa cập nhật nội dung chi tiết.') ?>
                            </p>
                        </div>
                        <div class="bg-indigo-50/50 p-3.5 rounded-2xl border border-indigo-100 space-y-1">
                            <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                                <span>🎯</span> Yêu cầu đính kèm bài tập:
                            </div>
                            <p class="text-indigo-950 leading-relaxed font-medium">
                                <?= htmlspecialchars($item['assignment_requirement'] ?: 'Sinh viên hoàn thành bài thực hành và đính kèm tệp nén mã nguồn.') ?>
                            </p>
                        </div>
                    </div>

                    <!-- KHU VỰC TRẠNG THÁI BÀI TẬP ĐÍNH KÈM -->
                    <div class="pt-2 border-t border-slate-100">
                        <?php if ($isSubmitted): ?>
                            <!-- NẾU ĐÃ NỘP BÀI TẬP ĐÍNH KÈM -->
                            <div class="bg-emerald-50/60 border border-emerald-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                                        📦
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="text-xs font-bold text-emerald-950 flex items-center gap-2">
                                            <span>Tệp đính kèm:</span>
                                            <span class="font-mono bg-white px-2 py-0.5 rounded border border-emerald-300 text-emerald-800">
                                                <?= htmlspecialchars($item['file_name']) ?>
                                            </span>
                                            <span class="text-[11px] text-emerald-700 font-normal">
                                                (<?= round((int)$item['file_size'] / 1024, 1) ?> KB)
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-emerald-800">
                                            Thời điểm nộp: <span class="font-semibold"><?= date('d/m/Y H:i:s', strtotime($item['submitted_at'])) ?></span>
                                            <?php if ($isLate): ?>
                                                <span class="text-rose-600 font-bold ml-1">(Nộp muộn)</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($item['notes'])): ?>
                                            <div class="text-[11px] text-slate-600 italic mt-1">
                                                Ghi chú: "<?= htmlspecialchars($item['notes']) ?>"
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="<?php echo $appBase; ?><?= htmlspecialchars($item['file_path']) ?>" download="<?= htmlspecialchars($item['file_name']) ?>" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1">
                                        <span>⬇️</span> Tải Bài Nộp
                                    </a>
                                    
                                    <!-- NÚT MỞ FORM NỘP LẠI -->
                                    <button type="button" onclick="toggleSubmitForm(<?= $lessonId ?>)" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold rounded-xl text-xs transition">
                                        ✏️ Nộp Lại Tệp
                                    </button>

                                    <!-- FORM XÓA BÀI NỘP -->
                                    <form method="POST" action="index.php?action=course_delete_submission" onsubmit="return confirm('Bạn có chắc chắn muốn hủy bài nộp buổi này? Buổi học sẽ chuyển về trạng thái Chưa hoàn thành.');" class="inline">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
                                        <input type="hidden" name="submission_id" value="<?= (int)$item['submission_id'] ?>">
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs transition">
                                            ✕ Hủy nộp
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- NẾU CHƯA ĐÍNH KÈM BÀI TẬP -->
                            <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                                <div class="flex items-center gap-3 text-xs">
                                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0">
                                        ⏳
                                    </div>
                                    <div>
                                        <div class="font-bold text-amber-950">
                                            Chưa đính kèm bài tập cho buổi học này!
                                        </div>
                                        <div class="text-[11.5px] text-amber-800">
                                            Mỗi buổi cần đính kèm tệp bài tập (zip, rar, pdf, docx, mã nguồn) mới được tính là hoàn thành.
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="toggleSubmitForm(<?= $lessonId ?>)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5 shrink-0">
                                    <span>📎</span> Đính Kèm Bài Tập Ngay
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- FORM UPLOAD ĐÍNH KÈM BÀI TẬP (MẶC ĐỊNH ẨN HOẶC HIỆN KHI BẤM NÚT) -->
                        <div id="submit-form-<?= $lessonId ?>" class="<?= $isSubmitted ? 'hidden' : 'block' ?> mt-4 bg-slate-50 border border-indigo-100 rounded-2xl p-4 space-y-3">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-800 border-b border-slate-200 pb-2">
                                <span class="flex items-center gap-1.5">
                                    <span>📤</span> Nộp đính kèm bài tập Buổi <?= (int)$item['lesson_number'] ?>
                                </span>
                                <?php if ($isSubmitted): ?>
                                    <button type="button" onclick="toggleSubmitForm(<?= $lessonId ?>)" class="text-slate-400 hover:text-slate-600">Đóng ✕</button>
                                <?php endif; ?>
                            </div>

                            <form method="POST" action="index.php?action=course_submit" enctype="multipart/form-data" class="space-y-3 text-xs" onsubmit="return handleFormSubmit(event, this, <?= $lessonId ?>);">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
                                <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <!-- CHỌN TỆP ĐÍNH KÈM -->
                                    <div class="space-y-1">
                                        <label class="block font-bold text-slate-700">
                                            Chọn tệp bài tập đính kèm <span class="text-rose-500">*</span>:
                                        </label>
                                        <input type="file" name="assignment_file" required accept=".zip,.rar,.7z,.pdf,.docx,.doc,.png,.jpg,.jpeg,.sql,.txt,.php" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 bg-white border border-slate-300 rounded-xl cursor-pointer">
                                        <p class="text-[10.5px] text-slate-400">Hỗ trợ: .zip, .rar, .pdf, .docx, .sql, .png (Tối đa 25MB)</p>
                                    </div>

                                    <!-- GHI CHÚ BÀI NỘP -->
                                    <div class="space-y-1">
                                        <label class="block font-bold text-slate-700">
                                            Ghi chú bài nộp / Đường dẫn GitHub (nếu có):
                                        </label>
                                        <input type="text" name="notes" placeholder="VD: https://github.com/... hoặc Đã hoàn thiện chức năng CRUD" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs">
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-1">
                                    <button type="submit" class="submit-btn px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                                        <span class="btn-icon">📎</span>
                                        <span class="btn-text"><?= $isSubmitted ? 'Cập Nhật Tệp Bài Tập' : 'Xác Nhận Nộp & Hoàn Thành Buổi Học' ?></span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>

                </div>

            </article>
        <?php endforeach; ?>
    </div>

</main>

<script>
// Toggle hiển thị form nộp bài
function toggleSubmitForm(lessonId) {
    const el = document.getElementById('submit-form-' + lessonId);
    if (el) {
        el.classList.toggle('hidden');
    }
}

// Bộ lọc các buổi học
function filterLessons(type) {
    // Cập nhật giao diện nút bấm
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.getAttribute('data-filter') === type) {
            btn.className = 'filter-btn px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white transition shadow-sm';
        } else {
            btn.className = 'filter-btn px-3 py-1.5 rounded-xl font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition';
        }
    });

    // Ẩn/hiện card
    document.querySelectorAll('.lesson-card').forEach(card => {
        const status = card.getAttribute('data-status');
        if (type === 'all' || status === type) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Xử lý nộp form an toàn qua Fetch API (Slide 40-42 & 47 Bài 8)
async function handleFormSubmit(event, form, lessonId) {
    const fileInput = form.querySelector('input[type="file"]');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert('Vui lòng chọn tệp đính kèm bài tập!');
        event.preventDefault();
        return false;
    }

    const submitBtn = form.querySelector('.submit-btn');
    const btnText = form.querySelector('.btn-text');
    const btnIcon = form.querySelector('.btn-icon');

    // Chuyển nút sang trạng thái đang tải (Loading state theo Slide 43)
    if (submitBtn) {
        submitBtn.disabled = true;
        btnIcon.textContent = '⏳';
        btnText.textContent = 'Đang tải lên bài tập...';
    }

    // Cho phép form submit POST thông thường để máy chủ xử lý redirect PRG chuẩn MVC
    return true;
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>

