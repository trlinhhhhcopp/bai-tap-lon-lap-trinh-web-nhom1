<?php
/**
 * View: Giao bài tập theo từng buổi học & Quản lý thời hạn nộp bài
 * Phân hệ Giảng viên
 */
require_once __DIR__ . '/../../../config/database.php';
$appBase = get_app_base_url();
$csrfToken = $_SESSION['csrf'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giao Bài Tập & Quản Lý Buổi Học - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- NAVBAR CHÍNH -->
    <?php require_once __DIR__ . '/../../../includes/navbar.php'; ?>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- HEADER BANNER -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        👨‍🏫 CỔNG GIẢNG VIÊN ĐÀO TẠO
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        GIAO BÀI TẬP & THỜI HẠN DEADLINE TỪNG BUỔI
                    </span>
                </div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>Quản Lý Tiến Độ & Giao Đề Bài Theo Buổi</span>
                </h1>
                <p class="text-xs text-indigo-200/80 mt-1">
                    Thiết lập yêu cầu nộp bài, điều chỉnh hạn chót (Deadline) và nghiệm thu các bài tập đính kèm của sinh viên theo từng buổi học.
                </p>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-800/80 border border-slate-700/80 p-3 rounded-2xl text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Giảng viên phụ trách:</span>
                    <strong class="text-white"><?php echo htmlspecialchars($currentUser['name']); ?></strong>
                    <span class="text-indigo-300 font-mono text-[11px]">(<?php echo htmlspecialchars($currentUser['user_code']); ?>)</span>
                </div>
            </div>
        </div>

        <!-- THÔNG BÁO FLASH MESSAGE -->
        <?php if ($flash): ?>
            <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold flex items-center justify-between <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
                <div class="flex items-center gap-2">
                    <span><?php echo $flash['type'] === 'success' ? '✅' : '⚠️'; ?></span>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
                <span class="text-slate-400 text-[10px]">Đã lưu thay đổi</span>
            </div>
        <?php endif; ?>

        <!-- THANH CHỌN HỌC PHẦN (TABS) -->
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-2.5">
                <span class="font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span>📚</span> Chọn Lớp Học Phần Cần Giao Bài Tập:
                </span>
                <span class="text-slate-500 font-mono text-[11px]">Tổng số: <strong><?php echo count($courses); ?></strong> lớp học phần</span>
            </div>

            <div class="flex items-center gap-2.5 overflow-x-auto pb-1 text-xs">
                <?php foreach ($courses as $c): ?>
                    <?php $isActive = ($c['ca_id'] == $selectedCaId); ?>
                    <a href="lessons.php?ca_id=<?php echo $c['ca_id']; ?>" 
                       title="Chọn lớp <?php echo htmlspecialchars($c['class_code']); ?> môn <?php echo htmlspecialchars($c['course_name']); ?>"
                       class="px-4 py-2.5 rounded-2xl font-bold transition whitespace-nowrap flex items-center gap-2 <?php echo $isActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 ring-2 ring-indigo-400' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'; ?>">
                        <span class="font-mono"><?php echo htmlspecialchars($c['course_code']); ?></span>
                        <span>–</span>
                        <span><?php echo htmlspecialchars($c['course_name']); ?></span>
                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold <?php echo $isActive ? 'bg-white text-indigo-700 shadow-2xs' : 'bg-indigo-100 text-indigo-700'; ?>">
                            Lớp: <?php echo htmlspecialchars($c['class_code']); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- KHỐI LỌC CẢNH BÁO SINH VIÊN BỊ KTV BÁO CÁO VI PHẠM (DÀNH CHO GIẢNG VIÊN ĐÀO TẠO) -->
        <?php if (!empty($violatingStudents)): ?>
            <div class="bg-gradient-to-r from-purple-50 via-rose-50 to-amber-50 border-2 border-purple-300 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2 border-b border-purple-200 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🚨</span>
                        <div>
                            <h3 class="text-sm font-black text-purple-950 flex items-center gap-2">
                                <span>CẢNH BÁO SINH VIÊN THUỘC LỚP BỊ KTV BÁO CÁO VI PHẠM / SPAM</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-black bg-rose-600 text-white"><?php echo count($violatingStudents); ?> trường hợp</span>
                            </h3>
                            <p class="text-[11px] text-purple-800/90 mt-0.5">
                                Kỹ thuật viên IT đã lập biên bản báo cáo các sinh viên sau có hành vi gửi yêu cầu spam, phá hoại hoặc vi phạm nội quy. Giảng viên lưu ý khi chấm điểm và đánh giá rèn luyện.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-purple-100/70 text-purple-900 font-bold border-b border-purple-200 text-[11px]">
                                <th class="py-2.5 px-3">Mã SV</th>
                                <th class="py-2.5 px-3">Họ và Tên</th>
                                <th class="py-2.5 px-3">Lớp Sinh Viên</th>
                                <th class="py-2.5 px-3">Mã Phiếu Sự Cố</th>
                                <th class="py-2.5 px-3">Lý Do KTV Báo Cáo Vi Phạm</th>
                                <th class="py-2.5 px-3 text-center">Thời Điểm Báo Cáo</th>
                                <th class="py-2.5 px-3 text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-purple-100 bg-white/70">
                            <?php foreach ($violatingStudents as $vs): ?>
                                <tr class="hover:bg-purple-50 transition">
                                    <td class="py-2.5 px-3 font-mono font-bold text-purple-900"><?php echo htmlspecialchars($vs['user_code']); ?></td>
                                    <td class="py-2.5 px-3 font-bold text-slate-900"><?php echo htmlspecialchars($vs['student_name']); ?></td>
                                    <td class="py-2.5 px-3 font-mono text-slate-700"><?php echo htmlspecialchars($vs['class_code']); ?></td>
                                    <td class="py-2.5 px-3 font-mono text-indigo-700 font-semibold"><?php echo htmlspecialchars($vs['ticket_code']); ?></td>
                                    <td class="py-2.5 px-3 text-rose-700 font-medium">
                                        <span class="inline-flex items-center gap-1">
                                            <span>⚠️</span> <?php echo htmlspecialchars($vs['spam_reason'] ?: 'Gửi tin nhắn spam, tin rác nhiều lần'); ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono text-slate-500 text-[11px]"><?php echo htmlspecialchars($vs['reported_at']); ?></td>
                                    <td class="py-2.5 px-3 text-center">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo (int)$vs['ticket_id']; ?>" target="_blank" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg text-[11px] transition inline-flex items-center gap-1 shadow-2xs">
                                            <span>🔍</span> Xem Phiếu
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($selectedCourse): ?>
            <!-- KHỐI DANH SÁCH CÁC BUỔI HỌC CỦA MÔN NÀY -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden space-y-4 p-6">
                <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <span>📖</span> Danh Sách 9 Buổi Học Môn: <?php echo htmlspecialchars($selectedCourse['course_name']); ?> (<?php echo htmlspecialchars($selectedCourse['course_code']); ?>)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Lớp: <strong><?php echo htmlspecialchars($selectedCourse['class_name']); ?></strong> (<?php echo htmlspecialchars($selectedCourse['class_code']); ?>) · Đề cương chuẩn Bài 1 đến Bài 9
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($lessons as $item): ?>
                        <div class="border border-slate-200 rounded-2xl p-5 hover:border-indigo-300 transition bg-slate-50/40 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 font-black text-sm flex items-center justify-center shrink-0">
                                        B<?php echo $item['lesson_number']; ?>
                                    </span>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-sm">
                                            Buổi <?php echo $item['lesson_number']; ?>: <?php echo htmlspecialchars($item['title']); ?>
                                        </h3>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                            <?php echo htmlspecialchars($item['description']); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0 self-start sm:self-center">
                                    <button type="button" 
                                            onclick='openEditLessonModal(<?php echo json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' 
                                            class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
                                        <span>✏️</span> Giao Đề & Đổi Hạn
                                    </button>
                                    <a href="lessons.php?ca_id=<?php echo $selectedCaId; ?>&view_lesson_id=<?php echo $item['id']; ?>" 
                                       class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-2xs">
                                        <span>👥</span> Xem Bài Nộp (<?php echo (int)$item['submission_count']; ?>)
                                    </a>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-200/70 flex flex-wrap items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3 text-slate-600">
                                    <span class="flex items-center gap-1 font-semibold">
                                        <span>📝</span> Yêu cầu bài tập: 
                                        <strong class="text-slate-900"><?php echo htmlspecialchars($item['assignment_requirement'] ?: 'Chưa cập nhật đề bài'); ?></strong>
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500">
                                        <span>⏰ Hạn nộp (Deadline):</span>
                                        <strong class="text-rose-600 font-mono"><?php echo htmlspecialchars($item['deadline_formatted']); ?></strong>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Sinh viên đã nộp: <strong><?php echo (int)$item['submission_count']; ?></strong> bài
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- NẾU ĐANG CHỌN XEM DANH SÁCH BÀI NỘP CỦA BUỔI -->
            <?php if ($activeLesson): ?>
                <div id="submissionsBox" class="bg-white border-2 border-indigo-300 rounded-3xl p-6 shadow-md space-y-4 scroll-mt-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-100 text-indigo-700">
                                BUỔI <?php echo $activeLesson['lesson_number']; ?>
                            </span>
                            <h3 class="text-base font-black text-slate-900 mt-1">
                                Danh Sách Sinh Viên Đã Nộp Bài: <?php echo htmlspecialchars($activeLesson['title']); ?>
                            </h3>
                            <p class="text-xs text-slate-500">
                                Hạn chót nộp bài: <strong class="text-rose-600"><?php echo htmlspecialchars($activeLesson['deadline_at']); ?></strong>
                            </p>
                        </div>
                        <a href="lessons.php?ca_id=<?php echo $selectedCaId; ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                            ✕ Đóng Danh Sách
                        </a>
                    </div>

                    <?php if (empty($lessonSubmissions)): ?>
                        <div class="py-10 text-center text-slate-400 text-xs space-y-2 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <span class="text-3xl block">📭</span>
                            <p class="font-bold text-slate-600">Chưa có sinh viên nào trong lớp nộp bài tập cho buổi học này.</p>
                            <p class="text-[11.5px] text-slate-400">Sinh viên sẽ nộp file bài làm kèm minh chứng qua Cổng Học Tập Sinh Viên.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 text-[11px] uppercase tracking-wider">
                                        <th class="py-3 px-4">Mã Sinh Viên</th>
                                        <th class="py-3 px-4">Họ và Tên</th>
                                        <th class="py-3 px-4">Lớp</th>
                                        <th class="py-3 px-4">File Đính Kèm</th>
                                        <th class="py-3 px-4">Ghi Chú Sinh Viên</th>
                                        <th class="py-3 px-4">Thời Gian Nộp</th>
                                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($lessonSubmissions as $sub): ?>
                                        <tr class="hover:bg-slate-50/80 transition">
                                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-700">
                                                <?php echo htmlspecialchars($sub['student_code']); ?>
                                            </td>
                                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span><?php echo htmlspecialchars($sub['student_name']); ?></span>
                                                    <?php if (!empty($sub['spam_ticket_count'])): ?>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-2xs" 
                                                              title="KTV báo cáo: <?php echo htmlspecialchars($sub['spam_reason'] ?: 'Gửi yêu cầu spam/vi phạm'); ?>">
                                                            🚨 SV Vi Phạm (KTV Báo Cáo)
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($sub['spam_ticket_count'])): ?>
                                                    <span class="block text-[10px] text-rose-600 font-normal mt-0.5 italic">
                                                        Lý do vi phạm: <?php echo htmlspecialchars($sub['spam_reason']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-slate-600 font-mono">
                                                <?php echo htmlspecialchars($sub['class_code'] ?? 'D24-CNTT1'); ?>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <?php if (!empty($sub['file_path'])): ?>
                                                    <a href="<?php echo $appBase; ?><?php echo htmlspecialchars($sub['file_path']); ?>" target="_blank" download class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg border border-indigo-200 transition text-[11px]">
                                                        <span>📥</span>
                                                        <span class="max-w-[150px] truncate"><?php echo htmlspecialchars($sub['file_name']); ?></span>
                                                        <span class="text-[10px] text-slate-400 font-normal">(<?php echo round($sub['file_size'] / 1024, 1); ?> KB)</span>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-slate-400 italic">Không có file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-slate-600 italic">
                                                <?php echo htmlspecialchars($sub['notes'] ?: '—'); ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px]">
                                                <?php echo htmlspecialchars($sub['submitted_formatted']); ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <?php if ($sub['is_late']): ?>
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                                        ⚠️ Nộp muộn
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                                                        ✅ Đúng hạn
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <!-- MODAL GIAO ĐỀ BÀI VÀ THIẾT LẬP THỜI HẠN (DEADLINE) -->
    <div id="editLessonModal" class="hidden fixed inset-0 z-[90] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200 animate-scale-up">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">📝</span>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm" id="modalLessonTitle">Giao Bài Tập Cho Buổi Học</h4>
                        <span class="text-[11px] text-slate-500">Thiết lập yêu cầu nộp bài và thời hạn kết thúc (Deadline)</span>
                    </div>
                </div>
                <button type="button" onclick="closeEditLessonModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center font-bold text-sm">✕</button>
            </div>

            <form method="POST" action="lessons.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                <input type="hidden" name="course_id" value="<?php echo (int)$selectedCourseId; ?>" />
                <input type="hidden" name="ca_id" value="<?php echo (int)$selectedCaId; ?>" />
                <input type="hidden" name="lesson_id" id="formLessonId" value="0" />

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tiêu Đề Buổi Học:</label>
                    <input type="text" name="title" id="formLessonTitle" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800" />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Yêu Cầu Bài Tập & Minh Chứng (Đề bài giao cho sinh viên): <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="assignment_requirement" id="formLessonRequirement" rows="3" required 
                              placeholder="Nhập yêu cầu sinh viên phải đính kèm file nộp bài (ví dụ: Nộp file PDF báo cáo hoặc file nén mã nguồn)..." 
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium text-slate-800 text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Thời Hạn Nộp Bài Tập (Deadline): <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" name="deadline_at" id="formLessonDeadline" required 
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-slate-800" />
                    <span class="text-[11px] text-slate-400 mt-1 block">Sinh viên nộp sau thời điểm này sẽ bị hệ thống gắn nhãn ⚠️ Nộp muộn.</span>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeEditLessonModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-200">
                        💾 Lưu & Ban Hành Đề Bài
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT ĐIỀU KHIỂN MODAL -->
    <script>
        function openEditLessonModal(lesson) {
            document.getElementById('modalLessonTitle').innerText = 'Giao Bài Tập - Buổi ' + lesson.lesson_number;
            document.getElementById('formLessonId').value = lesson.id;
            document.getElementById('formLessonTitle').value = lesson.title;
            document.getElementById('formLessonRequirement').value = lesson.assignment_requirement || '';
            document.getElementById('formLessonDeadline').value = lesson.deadline_iso || '';
            document.getElementById('editLessonModal').classList.remove('hidden');
        }

        function closeEditLessonModal() {
            document.getElementById('editLessonModal').classList.add('hidden');
        }
    </script>

    <!-- FOOTER CHUẨN TOÀN TRƯỜNG -->
    <?php require_once __DIR__ . '/../../../includes/footer.php'; ?>

</body>
</html>

