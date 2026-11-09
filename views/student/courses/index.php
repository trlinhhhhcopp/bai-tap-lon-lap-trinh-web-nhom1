<?php
/**
 * View: Học Phần & Bài Tập Của Tôi (Giao diện 1 trang duy nhất - Unified Integrated Workspace)
 * Phân hệ Sinh viên
 * Kết hợp danh mục học phần (Course Switcher) và chi tiết buổi học + nộp bài tập trên cùng một màn hình.
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
        .lesson-card {
            transition: all 0.2s ease-in-out;
        }
        .lesson-card:hover {
            border-color: #cbd5e1;
        }
        html {
            scroll-behavior: smooth;
        }
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
                Không gian học tập tích hợp: Theo dõi tiến độ tất cả học phần, xem đề cương chi tiết và đính kèm bài tập thực hành từng buổi học trực tiếp trên cùng một màn hình.
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

    <!-- 2. SECTION CHỌN NHANH HỌC PHẦN (COURSE SWITCHER / CARDS GRID) -->
    <section class="space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                <span>📖</span> DANH SÁCH HỌC PHẦN (BẤM VÀO MÔN ĐỂ XEM VÀ NỘP BÀI TẬP NGAY BÊN DƯỚI)
            </h2>
            <span class="text-[11px] text-slate-400 font-medium">
                <?= $totalCourses ?> học phần đang theo học
            </span>
        </div>

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
                                    <span>✓</span> Đang hiển thị buổi học bên dưới
                                </span>
                                <span class="text-xs">↓</span>
                            <?php else: ?>
                                <span>Chuyển sang môn này</span>
                                <span class="font-bold text-xs group-hover:translate-x-1 transition">→</span>
                            <?php endif; ?>
                        </div>

                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- 3. SECTION CHI TIẾT HỌC PHẦN & KHÔNG GIAN BÀI TẬP CỦA MÔN ĐANG CHỌN -->
    <?php if (!empty($selectedCourse)): ?>
        <section id="course-workspace" class="space-y-5 pt-2">

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
            <?php if (empty($lessons)): ?>
                <div class="bg-white rounded-3xl p-10 text-center border border-slate-200 shadow-sm space-y-2">
                    <span class="text-3xl block">📋</span>
                    <h3 class="text-sm font-bold text-slate-800">Chưa có đề cương buổi học cho môn này</h3>
                    <p class="text-xs text-slate-400">Giảng viên phụ trách chưa cập nhật danh sách các buổi học cho môn <?= htmlspecialchars($selectedCourse['course_name'] ?? $selectedCourse['name']) ?>.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4" id="lessons-container">
                    <?php foreach ($lessons as $index => $item): 
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

                                        <!-- BADGE DEADLINE / THỜI HẠN BÀI TẬP -->
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-<?= $item['status_color'] ?>-100 text-<?= $item['status_color'] ?>-800 border border-<?= $item['status_color'] ?>-300">
                                            ⏱ Hạn nộp: <?= $item['deadline_formatted'] ?> (<?= htmlspecialchars($item['status_badge']) ?>)
                                        </span>
                                    </div>

                                    <h3 class="text-base md:text-lg font-bold text-slate-900 pt-1">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </h3>
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
                                        <div class="font-bold text-indigo-900 flex items-center justify-between gap-1.5 flex-wrap">
                                            <span class="flex items-center gap-1.5"><span>🎯</span> Yêu cầu đính kèm bài tập:</span>
                                            <?php if (!empty($item['attachment_path'])): ?>
                                                <a href="<?= $appBase . htmlspecialchars($item['attachment_path']); ?>" target="_blank" download class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[11px] font-bold transition shadow-2xs">
                                                    <span>📎 Tải Đề Bài / Tài Liệu</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-indigo-950 leading-relaxed font-medium">
                                            <?= htmlspecialchars($item['assignment_requirement'] ?: 'Sinh viên hoàn thành bài thực hành và đính kèm tệp nén mã nguồn.') ?>
                                        </p>
                                        <?php if (!empty($item['attachment_path'])): ?>
                                            <div class="pt-1 text-[11px] text-indigo-700 font-mono flex items-center gap-1">
                                                <span>📄 Tệp đính kèm từ Thầy/Cô:</span>
                                                <strong><?= htmlspecialchars($item['attachment_name'] ?: 'De_bai.pdf') ?></strong>
                                            </div>
                                        <?php endif; ?>
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
                                                    <div class="text-xs font-bold text-emerald-950 flex items-center gap-2 flex-wrap">
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

                                            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                                <a href="<?= $appBase . htmlspecialchars($item['file_path']) ?>" download="<?= htmlspecialchars($item['file_name']) ?>" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1">
                                                    <span>⬇️</span> Tải Bài Nộp
                                                </a>
                                                
                                                <?php if (!$isOverdue): ?>
                                                <!-- NÚT MỞ FORM NỘP LẠI (CHỈ KHI CÒN HẠN) -->
                                                <button type="button" onclick="toggleSubmitForm(<?= $lessonId ?>)" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                                                    ✏️ Nộp Lại Tệp
                                                </button>

                                                <!-- FORM XÓA BÀI NỘP (CHỈ KHI CÒN HẠN) -->
                                                <form method="POST" action="<?= $appBase ?>student/courses/delete-submission" onsubmit="return confirm('Bạn có chắc chắn muốn hủy bài nộp buổi này? Buổi học sẽ chuyển về trạng thái Chưa hoàn thành.');" class="inline">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="course_id" value="<?= (int)$selectedCourseId ?>">
                                                    <input type="hidden" name="lesson_id" value="<?= $lessonId ?>">
                                                    <input type="hidden" name="submission_id" value="<?= (int)$item['submission_id'] ?>">
                                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs transition cursor-pointer">
                                                        ✕ Hủy nộp
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- KHỐI ĐIỂM SỐ & NHẬN XÉT CỦA GIẢNG VIÊN (THANG ĐIỂM 10) -->
                                        <?php if ($item['score'] !== null): ?>
                                            <div class="mt-3 bg-gradient-to-r from-amber-50/80 via-white to-amber-50/50 border border-amber-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-2xs">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                                                        🎯
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="text-xs font-black text-slate-800">ĐÁNH GIÁ CỦA GIẢNG VIÊN:</span>
                                                            <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-400 text-slate-900 shadow-2xs">
                                                                🎯 Điểm số: <?= number_format((float)$item['score'], 1) ?> / 10.0
                                                            </span>
                                                            <?php if (!empty($item['graded_at'])): ?>
                                                                <span class="text-[11px] text-slate-400 font-medium">
                                                                    (Chấm lúc: <?= date('d/m/Y H:i', strtotime($item['graded_at'])) ?>)
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="mt-1 text-xs text-slate-700 bg-white/80 p-2.5 rounded-xl border border-amber-200/60 leading-relaxed font-medium">
                                                            💬 <strong>Nhận xét:</strong> <?= htmlspecialchars($item['feedback'] ?: 'Đã hoàn thành bài tập.') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="shrink-0 text-right">
                                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-full text-[11px] font-bold">
                                                        ✅ Đã Được Chấm Điểm
                                                    </span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-3 bg-slate-50 border border-slate-200/80 rounded-2xl p-3 flex items-center justify-between gap-2 text-xs text-slate-500">
                                                <div class="flex items-center gap-2">
                                                    <span>⏳</span>
                                                    <span>Trạng thái đánh giá: <strong class="text-slate-700">Đang chờ Giảng viên chấm điểm & nhận xét</strong></span>
                                                </div>
                                                <span class="text-[11px] text-slate-400 font-mono">Thang điểm 10.0</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- NẾU CHƯA ĐÍNH KÈM BÀI TẬP -->
                                        <?php if ($isOverdue): ?>
                                            <!-- NẾU ĐÃ QUÁ HẠN -->
                                            <div class="bg-rose-50 border-2 border-rose-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 text-xs">
                                                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg shrink-0">
                                                        ⛔
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-rose-950">
                                                            ĐÃ HẾT HẠN NỘP BÀI (HẠN CHÓT: <?= htmlspecialchars($item['deadline_formatted'] ?? '') ?>)
                                                        </div>
                                                        <div class="text-[11.5px] text-rose-800">
                                                            Hệ thống đã khóa tiếp nhận bài nộp tự động. Nếu bạn gặp sự cố kỹ thuật (lỗi mạng, hệ thống LMS, sự cố máy tính...), vui lòng gửi phiếu báo lỗi để KTV xác thực và Thầy/Cô xem xét mở lại Deadline!
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="<?= $appBase ?>tickets/create?course_id=<?= (int)$selectedCourseId ?>&lesson_id=<?= $lessonId ?>&category_id=4" 
                                                   class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5 shrink-0">
                                                    <span>🚨</span> Báo Lỗi Để Thầy/Cô Xem Xét Gia Hạn
                                                </a>
                                            </div>
                                        <?php else: ?>
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
                                                            Mỗi buổi cần đính kèm tệp bài tập (zip, rar, pdf, docx, mã nguồn) mới được tính là hoàn thành buổi học.
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" onclick="toggleSubmitForm(<?= $lessonId ?>)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                                                    <span>📎</span> Đính Kèm Bài Tập Ngay
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- FORM UPLOAD ĐÍNH KÈM BÀI TẬP (CHỈ HIỂN THỊ KHI CÒN HẠN) -->
                                    <?php if (!$isOverdue): ?>
                                    <div id="submit-form-<?= $lessonId ?>" class="<?= $isSubmitted ? 'hidden' : 'block' ?> mt-4 bg-slate-50 border border-indigo-100 rounded-2xl p-4 space-y-3">
                                        <div class="flex items-center justify-between text-xs font-bold text-slate-800 border-b border-slate-200 pb-2">
                                            <span class="flex items-center gap-1.5">
                                                <span>📤</span> Nộp đính kèm bài tập Buổi <?= (int)$item['lesson_number'] ?>
                                            </span>
                                            <?php if ($isSubmitted): ?>
                                                <button type="button" onclick="toggleSubmitForm(<?= $lessonId ?>)" class="text-slate-400 hover:text-slate-600 cursor-pointer">Đóng ✕</button>
                                            <?php endif; ?>
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
                                                        Ghi chú bài nộp / Đường dẫn GitHub (nếu có):
                                                    </label>
                                                    <input type="text" name="notes" placeholder="VD: https://github.com/... hoặc Đã hoàn thiện chức năng CRUD" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs">
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-end gap-2 pt-1">
                                                <button type="submit" class="submit-btn px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                                                    <span class="btn-icon">📎</span>
                                                    <span class="btn-text"><?= $isSubmitted ? 'Cập Nhật Tệp Bài Tập' : 'Xác Nhận Nộp & Hoàn Thành Buổi Học' ?></span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <?php endif; ?>

                                </div>

                            </div>

                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </section>
    <?php endif; ?>

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

    // Chuyển nút sang trạng thái đang tải (Loading state)
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
