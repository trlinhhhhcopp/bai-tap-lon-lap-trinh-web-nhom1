<?php
/**
 * View: Học Phần & Bài Tập Của Tôi (Giao diện tích hợp dạng Bảng - Unified Course Workspace)
 * Phân hệ Sinh viên
 * Kết hợp danh mục học phần và bảng chi tiết các buổi học + đính kèm bài tập thực hành.
 */
require_once __DIR__ . '/../../../config/database.php';
$appBase = get_app_base_url();

$totalCourses = count($courses);
$totalLessonsAll = 0;
$totalCompletedAll = 0;
foreach ($courses as $c) {
    $totalLessonsAll += (int)($c['total_lessons'] ?? 0);
    $totalCompletedAll += (int)($c['completed_lessons'] ?? 0);
}
$overallProgress = $totalLessonsAll > 0 ? (int)round(($totalCompletedAll / $totalLessonsAll) * 100) : 0;

$csrfToken = $_SESSION['csrf'] ?? '';
$isAllDone = ($totalLessons > 0 && $completedLessons === $totalLessons);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($selectedCourse) ? htmlspecialchars($selectedCourse['course_name'] ?? $selectedCourse['name']) . ' - ' : '' ?>Học Phần & Bài Tập - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        html { scroll-behavior: smooth; }
        .table-row-hover:hover { background-color: #f8fafc; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
<?php require_once __DIR__ . '/../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- 1. HEADER BANNER TỔNG QUAN HỌC KỲ -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    📚 CỔNG HỌC PHẦN & BÀI TẬP SINH VIÊN
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    HỌC KỲ 2026_1
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white">
                Học Phần & Bài Tập Của Tôi
            </h1>
            <p class="text-xs text-indigo-200/80 mt-1 max-w-2xl">
                Không gian học tập tích hợp: Bấm vào từng học phần để xem bảng chi tiết các buổi học, theo dõi hạn nộp và đính kèm bài tập thực hành trực tiếp trên cùng một màn hình.
            </p>
        </div>
        <div class="flex items-center gap-3 bg-slate-800/80 border border-slate-700/80 p-3.5 rounded-2xl shrink-0 shadow-inner">
            <div class="text-center px-2">
                <div class="text-[11px] text-slate-400 font-medium">Học phần</div>
                <div class="text-xl font-black text-white"><?= $totalCourses ?></div>
            </div>
            <div class="w-px h-8 bg-slate-700"></div>
            <div class="text-center px-2">
                <div class="text-[11px] text-slate-400 font-medium">Đã hoàn thành</div>
                <div class="text-xl font-black text-emerald-400"><?= $totalCompletedAll ?>/<?= $totalLessonsAll ?></div>
            </div>
            <div class="w-px h-8 bg-slate-700"></div>
            <div class="text-center px-2">
                <div class="text-[11px] text-slate-400 font-medium">Tiến độ chung</div>
                <div class="text-xl font-black text-indigo-400"><?= $overallProgress ?>%</div>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    <?php if (!empty($flash)): ?>
        <div class="p-4 rounded-2xl bg-<?= $flash['type'] === 'success' ? 'emerald' : ($flash['type'] === 'warning' ? 'amber' : 'rose') ?>-50 border border-<?= $flash['type'] === 'success' ? 'emerald' : ($flash['type'] === 'warning' ? 'amber' : 'rose') ?>-200 text-xs font-semibold text-<?= $flash['type'] === 'success' ? 'emerald' : ($flash['type'] === 'warning' ? 'amber' : 'rose') ?>-900 flex items-center justify-between gap-2 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-base"><?= $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'warning' ? '⚠️' : '❌') ?></span>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
            <button onclick="this.parentElement.remove();" class="text-slate-400 hover:text-slate-600 font-bold px-1.5 py-0.5 rounded cursor-pointer">✕</button>
        </div>
    <?php endif; ?>

    <!-- 2. SECTION CHỌN NHANH HỌC PHẦN (CARDS GRID KÈM NÚT ẨN/HIỆN NHƯ CŨ) -->
    <section class="space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                <span>📖</span> DANH SÁCH HỌC PHẦN (BẤM VÀO MÔN ĐỂ HIỆN BẢNG BÀI TẬP BÊN DƯỚI)
            </h2>
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-slate-400 font-medium">
                    <?= $totalCourses ?> học phần đang theo học
                </span>
                <button type="button" data-target-table="student_courses_cards_container" onclick="toggleTableCollapse('student_courses_cards_container', this)" title="Ẩn hoặc hiện danh sách học phần" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1 border border-slate-200 cursor-pointer">
                    <span class="toggle-table-icon">✕</span>
                    <span class="toggle-table-text">Ẩn danh sách</span>
                </button>
            </div>
        </div>

        <div id="student_courses_cards_container" data-collapsible-table>
            <?php if (empty($courses)): ?>
                <div class="bg-white rounded-3xl p-10 text-center border border-slate-200 shadow-sm">
                    <div class="text-4xl mb-3">📭</div>
                    <h3 class="text-base font-bold text-slate-800">Chưa có học phần nào được phân công</h3>
                    <p class="text-xs text-slate-500 mt-1">Lớp của bạn hiện chưa được phân công môn học trong học kỳ này.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($courses as $c): 
                        $cid = (int)$c['course_id'];
                        $pct = (int)($c['progress_pct'] ?? 0);
                        $isActive = ($cid === $selectedCourseId);
                        $isAllDoneCourse = ($pct === 100);
                    ?>
                        <a href="<?= $appBase ?>student/courses?course_id=<?= $cid ?>#course-workspace"
                           onclick="ensureLessonsTableVisible()"
                           class="relative rounded-3xl p-4.5 transition flex flex-col justify-between overflow-hidden border text-left group <?= $isActive 
                               ? 'bg-indigo-50/70 border-indigo-500 ring-2 ring-indigo-500/30 shadow-md' 
                               : 'bg-white border-slate-200/90 hover:border-indigo-300 hover:shadow-md' ?>">
                            
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-1 <?= $isActive ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700' ?> font-black text-xs rounded-xl border <?= $isActive ? 'border-indigo-600' : 'border-slate-200' ?>">
                                        <?= htmlspecialchars($c['course_code']) ?>
                                    </span>

                                    <?php if ($isActive): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-600 text-white shadow-2xs flex items-center gap-1 animate-pulse">
                                            ● ĐANG XEM
                                        </span>
                                    <?php else: ?>
                                        <span class="text-[11px] font-bold text-slate-500">
                                            <?= (int)$c['credits'] ?> Tín chỉ
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <h3 class="text-sm font-black <?= $isActive ? 'text-indigo-950' : 'text-slate-900 group-hover:text-indigo-600' ?> transition line-clamp-1">
                                        <?= htmlspecialchars($c['course_name']) ?>
                                    </h3>
                                    <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">
                                        GV: <strong class="text-slate-600"><?= htmlspecialchars($c['lecturer_name'] ?: 'Chưa chỉ định') ?></strong>
                                        · Lớp: <span class="font-mono text-slate-600"><?= htmlspecialchars($c['class_code']) ?></span>
                                    </p>
                                </div>

                                <!-- THANH TIẾN ĐỘ THẺ MÔN HỌC -->
                                <div class="space-y-1 pt-1">
                                    <div class="flex justify-between items-center text-[10.5px]">
                                        <span class="font-medium text-slate-500">Tiến độ bài tập:</span>
                                        <span class="font-black <?= $isAllDoneCourse ? 'text-emerald-600' : ($isActive ? 'text-indigo-700' : 'text-slate-700') ?>">
                                            <?= (int)$c['completed_lessons'] ?> / <?= (int)$c['total_lessons'] ?> Buổi (<?= $pct ?>%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full transition-all duration-500 <?= $isAllDoneCourse ? 'bg-emerald-500' : ($isActive ? 'bg-indigo-600' : 'bg-slate-500') ?>" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 mt-3 border-t <?= $isActive ? 'border-indigo-200/80 text-indigo-700 font-bold' : 'border-slate-100 text-slate-500 group-hover:text-indigo-600' ?> flex items-center justify-between text-[11px]">
                                <?php if ($isActive): ?>
                                    <span class="font-black text-indigo-700 flex items-center gap-1">
                                        <span>✓</span> Đang hiển thị bảng buổi học bên dưới
                                    </span>
                                    <span class="text-xs">↓</span>
                                <?php else: ?>
                                    <span>Bấm xem bảng bài tập</span>
                                    <span class="font-bold text-xs group-hover:translate-x-1 transition">→</span>
                                <?php endif; ?>
                            </div>

                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 3. SECTION BẢNG BUỔI HỌC & BÀI TẬP CỦA MÔN ĐANG CHỌN (ẤN VÀO LÀ HIỆN BẢNG) -->
    <?php if (!empty($selectedCourse)): ?>
        <section id="course-workspace" class="space-y-4 pt-2">

            <!-- HEADER BANNER HỌC PHẦN ĐANG CHỌN -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            <?= htmlspecialchars($selectedCourse['course_code']) ?>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            <?= (int)$selectedCourse['credits'] ?> Tín chỉ
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-700/60 text-slate-300">
                            <?= htmlspecialchars($selectedCourse['department'] ?? 'Khoa Công nghệ Thông tin') ?>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Lớp: <?= htmlspecialchars($selectedCourse['class_code']) ?>
                        </span>
                    </div>

                    <h2 class="text-2xl md:text-3xl font-black text-white">
                        <?= htmlspecialchars($selectedCourse['course_name'] ?? $selectedCourse['name']) ?>
                    </h2>

                    <div class="text-xs text-indigo-200/90 flex items-center flex-wrap gap-x-4 gap-y-1">
                        <div>
                            <span>Giảng viên phụ trách:</span>
                            <strong class="text-white"><?= htmlspecialchars($selectedCourse['lecturer_name'] ?: 'Chưa chỉ định') ?></strong>
                            <?php if (!empty($selectedCourse['lecturer_email'])): ?>
                                <span class="text-indigo-300">(<?= htmlspecialchars($selectedCourse['lecturer_email']) ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span>Lớp học phần:</span>
                            <strong class="text-white"><?= htmlspecialchars($selectedCourse['class_name'] ?? $selectedCourse['class_code']) ?></strong>
                        </div>
                    </div>
                    <p class="text-[11.5px] text-slate-400 italic">
                        Quy định đào tạo: Mỗi buổi học sinh viên cần đính kèm bài tập thực hành mới được tính là hoàn thành buổi học.
                    </p>
                </div>

                <!-- WIDGET TIẾN ĐỘ MÔN ĐANG XEM -->
                <div class="bg-slate-800/80 border border-slate-700/80 p-4.5 rounded-2xl md:min-w-[280px] space-y-2.5 shadow-inner shrink-0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-300 font-semibold">Tiến độ bài tập môn:</span>
                        <span class="font-black text-sm <?= $isAllDone ? 'text-emerald-400' : 'text-indigo-400' ?>" id="progress-text">
                            <?= $completedLessons ?> / <?= $totalLessons ?> Buổi (<?= $progressPct ?>%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-700/70 rounded-full h-3 overflow-hidden">
                        <div id="progress-bar" class="h-3 rounded-full transition-all duration-500 <?= $isAllDone ? 'bg-emerald-500' : 'bg-indigo-500' ?>" style="width: <?= $progressPct ?>%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-0.5">
                        <span>Trạng thái:</span>
                        <span class="font-bold <?= $isAllDone ? 'text-emerald-400' : 'text-amber-400' ?>" id="progress-status">
                            <?= $isAllDone ? '✅ Đã hoàn thành tất cả' : '⚠️ Còn ' . ($totalLessons - $completedLessons) . ' buổi chưa nộp' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- THANH ĐIỀU HƯỚNG BỘ LỌC CÁC BUỔI HỌC & CÁI NÚT ẨN/HIỆN BẢNG NHƯ CŨ -->
            <div class="bg-white rounded-2xl border border-slate-200 p-3 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-bold text-slate-700 mr-1">Bộ lọc buổi:</span>
                    <button type="button" onclick="filterLessonsTable('all')" class="filter-btn px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white transition shadow-sm" data-filter="all">
                        Tất cả (<?= $totalLessons ?>)
                    </button>
                    <button type="button" onclick="filterLessonsTable('completed')" class="filter-btn px-3 py-1.5 rounded-xl font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-filter="completed">
                        Đã hoàn thành (<?= $completedLessons ?>)
                    </button>
                    <button type="button" onclick="filterLessonsTable('pending')" class="filter-btn px-3 py-1.5 rounded-xl font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition" data-filter="pending">
                        Chưa hoàn thành (<?= $totalLessons - $completedLessons ?>)
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[11.5px] text-slate-400 italic hidden sm:inline">
                        Bảng quản lý nộp bài tập chuẩn
                    </span>
                    <!-- CÁI NÚT ẨN / HIỆN NHƯ CŨ -->
                    <button type="button" data-target-table="student_lessons_table_body" onclick="toggleTableCollapse('student_lessons_table_body', this)" title="Ẩn hoặc hiện bảng này" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1.5 border border-slate-200 cursor-pointer shadow-xs">
                        <span class="toggle-table-icon">✕</span>
                        <span class="toggle-table-text">Ẩn bảng</span>
                    </button>
                </div>
            </div>

            <!-- 4. BẢNG BUỔI HỌC & ĐÍNH KÈM BÀI TẬP (TABLE CONTAINER) -->
            <div id="student_lessons_table_body" data-collapsible-table class="bg-white border border-slate-200/90 rounded-3xl shadow-sm overflow-hidden">
                <?php if (empty($lessons)): ?>
                    <div class="p-10 text-center text-slate-400 text-xs space-y-2">
                        <span class="text-3xl block">📋</span>
                        <h3 class="text-sm font-bold text-slate-800">Chưa có đề cương buổi học cho môn này</h3>
                        <p class="text-xs text-slate-400">Giảng viên phụ trách chưa cập nhật danh sách các buổi học cho môn <?= htmlspecialchars($selectedCourse['course_name'] ?? $selectedCourse['name']) ?>.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/90 text-slate-600 font-extrabold uppercase tracking-wider text-[11px] border-b border-slate-200">
                                    <th class="py-3.5 px-4 text-center w-20">Buổi</th>
                                    <th class="py-3.5 px-4 min-w-[240px]">Chủ Đề & Yêu Cầu Buổi Học</th>
                                    <th class="py-3.5 px-4 w-44">Hạn Nộp Bài</th>
                                    <th class="py-3.5 px-4 text-center w-36">Trạng Thái</th>
                                    <th class="py-3.5 px-4 min-w-[200px]">Tệp Đã Đính Kèm</th>
                                    <th class="py-3.5 px-4 text-center w-32">Đánh Giá GV</th>
                                    <th class="py-3.5 px-4 text-center w-40">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="lessons-table-rows">
                                <?php foreach ($lessons as $index => $item): 
                                    $isSubmitted = !empty($item['is_submitted']);
                                    $isOverdue = !empty($item['is_overdue']);
                                    $isLate = !empty($item['is_late']);
                                    $lessonId = (int)$item['lesson_id'];
                                    $filterType = $isSubmitted ? 'completed' : 'pending';
                                ?>
                                    <!-- DÒNG THÔNG TIN BUỔI HỌC -->
                                    <tr class="table-row-hover transition lesson-row <?= $isSubmitted ? 'bg-emerald-50/20' : ($isOverdue ? 'bg-rose-50/20' : '') ?>" data-status="<?= $filterType ?>" id="lesson-row-<?= $lessonId ?>">
                                        
                                        <!-- CỘT 1: BUỔI -->
                                        <td class="py-3.5 px-4 text-center font-black align-middle">
                                            <span class="inline-block px-2.5 py-1 rounded-xl text-xs font-black <?= $isSubmitted ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' ?>">
                                                Buổi <?= (int)$item['lesson_number'] ?>
                                            </span>
                                        </td>

                                        <!-- CỘT 2: CHỦ ĐỀ & YÊU CẦU -->
                                        <td class="py-3.5 px-4 align-middle space-y-1">
                                            <div class="font-bold text-slate-900 text-[13px] leading-snug">
                                                <?= htmlspecialchars($item['title']) ?>
                                            </div>
                                            <?php if (!empty($item['requirement'])): ?>
                                                <div class="text-[11.5px] text-slate-500 line-clamp-2">
                                                    <?= htmlspecialchars($item['requirement']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['attachment_path'])): ?>
                                                <div class="pt-0.5">
                                                    <a href="<?= $appBase . htmlspecialchars($item['attachment_path']) ?>" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-200">
                                                        <span>📥 Đề bài GV:</span>
                                                        <span class="truncate max-w-[150px]"><?= htmlspecialchars($item['attachment_name'] ?: 'Tệp đính kèm') ?></span>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- CỘT 3: HẠN NỘP BÀI -->
                                        <td class="py-3.5 px-4 align-middle">
                                            <div class="font-mono text-slate-700 font-semibold text-xs whitespace-nowrap">
                                                <?= $item['deadline_formatted'] ?>
                                            </div>
                                            <div class="mt-0.5">
                                                <span class="inline-block px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-<?= $item['status_color'] ?>-100 text-<?= $item['status_color'] ?>-800 border border-<?= $item['status_color'] ?>-300">
                                                    <?= htmlspecialchars($item['status_badge']) ?>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- CỘT 4: TRẠNG THÁI HOÀN THÀNH -->
                                        <td class="py-3.5 px-4 text-center align-middle whitespace-nowrap">
                                            <?php if ($isSubmitted): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                    <span>✅</span> ĐÃ HOÀN THÀNH
                                                </span>
                                            <?php elseif ($isOverdue): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 border border-rose-300">
                                                    <span>⛔</span> QUÁ HẠN NỘP
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                                    <span>⏳</span> CHƯA NỘP
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- CỘT 5: TỆP ĐÃ ĐÍNH KÈM -->
                                        <td class="py-3.5 px-4 align-middle">
                                            <?php if ($isSubmitted): ?>
                                                <div class="space-y-1">
                                                    <a href="<?= $appBase . htmlspecialchars($item['file_path']) ?>" download class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1 text-xs truncate max-w-[200px]" title="<?= htmlspecialchars($item['file_name']) ?>">
                                                        <span>📎</span>
                                                        <span class="truncate"><?= htmlspecialchars($item['file_name']) ?></span>
                                                    </a>
                                                    <div class="text-[10.5px] text-slate-400">
                                                        <span><?= !empty($item['file_size']) ? round($item['file_size'] / 1024, 1) . ' KB' : ($item['file_size_formatted'] ?? '') ?></span> · 
                                                        <span>Nộp: <?= !empty($item['submitted_at']) ? date('d/m/Y H:i', strtotime($item['submitted_at'])) : '' ?></span>
                                                    </div>
                                                    <?php if (!empty($item['notes'])): ?>
                                                        <div class="text-[10.5px] text-slate-500 italic truncate max-w-[200px]" title="<?= htmlspecialchars($item['notes']) ?>">
                                                            💬 <?= htmlspecialchars($item['notes']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-400 italic text-[11.5px]">Chưa đính kèm tệp</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- CỘT 6: ĐÁNH GIÁ CỦA GIẢNG VIÊN -->
                                        <td class="py-3.5 px-4 text-center align-middle whitespace-nowrap">
                                            <?php if ($item['score'] !== null): ?>
                                                <div>
                                                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-400 text-slate-900 shadow-2xs">
                                                        🎯 <?= number_format((float)$item['score'], 1) ?>
                                                    </span>
                                                    <?php if (!empty($item['feedback'])): ?>
                                                        <div class="text-[10.5px] text-slate-500 truncate max-w-[120px] mt-1" title="<?= htmlspecialchars($item['feedback']) ?>">
                                                            <?= htmlspecialchars($item['feedback']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif ($isSubmitted): ?>
                                                <span class="text-[11px] text-slate-400 font-semibold">⏳ Chờ chấm</span>
                                            <?php else: ?>
                                                <span class="text-slate-300">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- CỘT 7: THAO TÁC -->
                                        <td class="py-3.5 px-4 text-center align-middle whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                <?php if (!$isOverdue): ?>
                                                    <!-- NÚT MỞ FORM NỘP BÀI / NỘP LẠI -->
                                                    <button type="button" onclick="toggleSubmitFormRow(<?= $lessonId ?>)" class="px-3 py-1.5 <?= $isSubmitted ? 'bg-white border border-slate-300 hover:bg-slate-100 text-slate-700' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-2xs' ?> font-bold rounded-xl text-xs transition cursor-pointer flex items-center gap-1">
                                                        <span><?= $isSubmitted ? '✏️ Nộp lại' : '📎 Nộp bài' ?></span>
                                                    </button>

                                                    <?php if ($isSubmitted): ?>
                                                        <!-- NÚT HỦY BÀI NỘP -->
                                                        <form method="POST" action="<?= $appBase ?>student/courses/delete-submission" onsubmit="return confirm('Bạn có chắc chắn muốn hủy bài nộp Buổi <?= (int)$item['lesson_number'] ?>?');" class="inline">
                                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                                                            <input type="hidden" name="course_id" value="<?= (int)$selectedCourseId ?>">
                                                            <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
                                                            <input type="hidden" name="submission_id" value="<?= (int)$item['submission_id'] ?>">
                                                            <button type="submit" class="px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs transition cursor-pointer" title="Hủy nộp bài buổi này">
                                                                ✕
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <!-- NÚT BÁO SỰ CỐ KHI QUÁ HẠN -->
                                                    <a href="<?= $appBase ?>tickets/create?course_id=<?= (int)$selectedCourseId ?>&lesson_id=<?= $lessonId ?>&category_id=4" 
                                                       class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl text-xs transition shadow-2xs flex items-center gap-1"
                                                       title="Báo lỗi kỹ thuật để Thầy/Cô xem xét mở lại hạn">
                                                        <span>🚨 Báo sự cố</span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                    </tr>

                                    <!-- DÒNG PHỤ: FORM NỘP BÀI TẬP (MỞ RA KHI BẤM NỘP BÀI) -->
                                    <?php if (!$isOverdue): ?>
                                    <tr id="submit-form-row-<?= $lessonId ?>" class="hidden bg-indigo-50/50 border-y border-indigo-100">
                                        <td colspan="7" class="p-4 md:p-5">
                                            <div class="bg-white border border-indigo-200 rounded-2xl p-4 shadow-sm space-y-3 max-w-4xl mx-auto">
                                                <div class="flex items-center justify-between text-xs font-bold text-slate-800 border-b border-slate-100 pb-2.5">
                                                    <span class="flex items-center gap-1.5 text-indigo-700 font-black">
                                                        <span>📤</span> ĐÍNH KÈM TỆP BÀI TẬP - BUỔI <?= (int)$item['lesson_number'] ?>: <?= htmlspecialchars($item['title']) ?>
                                                    </span>
                                                    <button type="button" onclick="toggleSubmitFormRow(<?= $lessonId ?>)" class="text-slate-400 hover:text-slate-600 font-bold px-2 py-0.5 rounded cursor-pointer">
                                                        Đóng ✕
                                                    </button>
                                                </div>

                                                <form method="POST" action="<?= $appBase ?>student/courses/submit" enctype="multipart/form-data" class="space-y-3 text-xs" onsubmit="return handleFormSubmit(event, this, <?= $lessonId ?>);">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="course_id" value="<?= (int)$selectedCourseId ?>">
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
                                                                Ghi chú bài nộp / Link GitHub (nếu có):
                                                            </label>
                                                            <input type="text" name="notes" placeholder="VD: https://github.com/... hoặc Đã hoàn thiện xong chức năng" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs">
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-100">
                                                        <button type="button" onclick="toggleSubmitFormRow(<?= $lessonId ?>)" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                                                            Hủy bỏ
                                                        </button>
                                                        <button type="submit" class="submit-btn px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                                                            <span class="btn-icon">📎</span>
                                                            <span class="btn-text"><?= $isSubmitted ? 'Xác Nhận Cập Nhật Tệp' : 'Xác Nhận Nộp Bài' ?></span>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </section>
    <?php endif; ?>

</main>

<script>
// Đảm bảo bảng luôn mở khi bấm vào thẻ học phần phía trên
function ensureLessonsTableVisible() {
    const tableBody = document.getElementById('student_lessons_table_body');
    if (tableBody && tableBody.classList.contains('hidden')) {
        toggleTableCollapse('student_lessons_table_body');
    }
}

// Mở/đóng dòng form nộp bài tập ngay dưới buổi học
function toggleSubmitFormRow(lessonId) {
    const row = document.getElementById('submit-form-row-' + lessonId);
    if (row) {
        row.classList.toggle('hidden');
    }
}

// Bộ lọc các buổi học trong bảng
function filterLessonsTable(type) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.getAttribute('data-filter') === type) {
            btn.className = 'filter-btn px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white transition shadow-sm';
        } else {
            btn.className = 'filter-btn px-3 py-1.5 rounded-xl font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition';
        }
    });

    document.querySelectorAll('.lesson-row').forEach(row => {
        const status = row.getAttribute('data-status');
        const lessonId = row.id.replace('lesson-row-', '');
        const formRow = document.getElementById('submit-form-row-' + lessonId);

        if (type === 'all' || status === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
            if (formRow) {
                formRow.classList.add('hidden');
            }
        }
    });
}

// Xử lý nộp form
function handleFormSubmit(event, form, lessonId) {
    const fileInput = form.querySelector('input[type="file"]');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert('Vui lòng chọn tệp đính kèm bài tập!');
        event.preventDefault();
        return false;
    }

    const submitBtn = form.querySelector('.submit-btn');
    const btnText = form.querySelector('.btn-text');
    const btnIcon = form.querySelector('.btn-icon');

    if (submitBtn) {
        submitBtn.disabled = true;
        if (btnIcon) btnIcon.textContent = '⏳';
        if (btnText) btnText.textContent = 'Đang tải lên bài tập...';
    }

    return true;
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>
