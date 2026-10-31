<?php
/**
 * View: Danh sách Học phần của Sinh viên (Mục lục học phần)
 * Phân hệ Sinh viên
 */
require_once __DIR__ . '/../../../config/database.php';
$appBase = get_app_base_url();

$totalCourses = count($courses);
$totalLessonsAll = 0;
$totalCompletedAll = 0;
foreach ($courses as $c) {
    $totalLessonsAll += (int)$c['total_lessons'];
    $totalCompletedAll += (int)$c['completed_lessons'];
}
$overallProgress = $totalLessonsAll > 0 ? (int)round(($totalCompletedAll / $totalLessonsAll) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học Phần & Bài Tập Của Tôi - Đại Học Thủ Đô Hà Nội</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
<?php require_once __DIR__ . '/../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- HEADER BANNER -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                    📚 CỔNG HỌC TẬP SINH VIÊN
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    HỌC KỲ 2026_1
                </span>
            </div>
            <h1 class="text-2xl font-black text-white">
                Mục Lục Học Phần & Tiến Độ Bài Tập
            </h1>
            <p class="text-xs text-indigo-200/80 mt-1">
                Theo dõi đề cương, thời hạn nộp bài từng buổi. Mỗi buổi học chỉ được tính hoàn thành khi sinh viên đã đính kèm bài tập.
            </p>
        </div>
        <div class="flex items-center gap-3 bg-slate-800/80 border border-slate-700/80 p-3.5 rounded-2xl">
            <div class="text-center px-2">
                <div class="text-xs text-slate-400 font-medium">Học phần</div>
                <div class="text-xl font-black text-white"><?= $totalCourses ?></div>
            </div>
            <div class="w-px h-8 bg-slate-700"></div>
            <div class="text-center px-2">
                <div class="text-xs text-slate-400 font-medium">Đã hoàn thành</div>
                <div class="text-xl font-black text-emerald-400"><?= $totalCompletedAll ?>/<?= $totalLessonsAll ?></div>
            </div>
            <div class="w-px h-8 bg-slate-700"></div>
            <div class="text-center px-2">
                <div class="text-xs text-slate-400 font-medium">Tiến độ chung</div>
                <div class="text-xl font-black text-indigo-400"><?= $overallProgress ?>%</div>
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

    <!-- DANH SÁCH HỌC PHẦN (CARDS GRID) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php if (empty($courses)): ?>
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-200">
                <div class="text-4xl mb-3">📭</div>
                <h3 class="text-base font-bold text-slate-800">Chưa có học phần nào được phân công</h3>
                <p class="text-xs text-slate-500 mt-1">Lớp của bạn hiện chưa được phân công môn học trong học kỳ này.</p>
            </div>
        <?php else: ?>
            <?php foreach ($courses as $c): ?>
                <?php 
                    $pct = (int)$c['progress_pct'];
                    $isAllDone = $pct === 100;
                ?>
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden group">
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 font-black text-xs rounded-xl border border-indigo-100">
                                <?= htmlspecialchars($c['course_code']) ?>
                            </span>
                            <span class="text-[11px] font-semibold text-slate-500">
                                <?= (int)$c['credits'] ?> Tín chỉ
                            </span>
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition line-clamp-1">
                                <a href="index.php?action=course_detail&course_id=<?= (int)$c['course_id'] ?>">
                                    <?= htmlspecialchars($c['course_name']) ?>
                                </a>
                            </h2>
                            <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">
                                <?= htmlspecialchars($c['department']) ?>
                            </p>
                        </div>

                        <div class="pt-2 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 text-[11px]">Giảng viên:</span>
                                <span class="font-semibold text-slate-800 truncate max-w-[170px]"><?= htmlspecialchars($c['lecturer_name']) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 text-[11px]">Lớp sinh hoạt:</span>
                                <span class="font-mono text-slate-700"><?= htmlspecialchars($c['class_code']) ?></span>
                            </div>
                        </div>

                        <!-- THANH TIẾN ĐỘ HOÀN THÀNH BÀI TẬP -->
                        <div class="space-y-1 pt-1">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-[11px] font-bold text-slate-600">Tiến độ bài tập:</span>
                                <span class="font-extrabold <?= $isAllDone ? 'text-emerald-600' : 'text-indigo-600' ?>">
                                    <?= (int)$c['completed_lessons'] ?> / <?= (int)$c['total_lessons'] ?> Buổi (<?= $pct ?>%)
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 <?= $isAllDone ? 'bg-emerald-500' : 'bg-indigo-600' ?>" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11.5px] font-medium <?= $isAllDone ? 'text-emerald-600' : 'text-slate-500' ?>">
                            <?= $isAllDone ? '🎉 Đã hoàn tất cả buổi' : '📎 Cần đính kèm bài tập' ?>
                        </span>
                        <a href="index.php?action=course_detail&course_id=<?= (int)$c['course_id'] ?>" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 group-hover:translate-x-0.5 transition">
                            Vào Học Phần & Nộp Bài →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>

