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

        <?php if ($selectedCourse): ?>
            <!-- KHỐI DANH SÁCH CÁC BUỔI HỌC CỦA MÔN NÀY -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden space-y-4 p-6">
                <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <span>📖</span> Danh Sách Buổi Học & Bài Tập Môn: <?php echo htmlspecialchars($selectedCourse['course_name']); ?> (<?php echo htmlspecialchars($selectedCourse['course_code']); ?>)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Lớp: <strong><?php echo htmlspecialchars($selectedCourse['class_name']); ?></strong> (<?php echo htmlspecialchars($selectedCourse['class_code']); ?>) · Giảng viên quản lý đề bài, chấm điểm trực tiếp
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="openCreateLessonModal()" 
                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <span>➕</span> Thêm Bài Tập / Buổi Học Mới
                        </button>
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
                                        <th class="py-3 px-4 text-center">⭐ Điểm & Đánh Giá</th>
                                        <th class="py-3 px-4 text-center">Thao Tác</th>
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
                                            <td class="py-3.5 px-4 text-center">
                                                <?php if ($sub['score'] !== null): ?>
                                                    <div class="space-y-1">
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-amber-400 text-slate-900 shadow-2xs">
                                                            ⭐ <?php echo number_format((float)$sub['score'], 1); ?> / 10.0
                                                        </span>
                                                        <?php if (!empty($sub['feedback'])): ?>
                                                            <div class="text-[10.5px] text-slate-600 italic max-w-[180px] mx-auto truncate" title="<?php echo htmlspecialchars($sub['feedback']); ?>">
                                                                💬 "<?php echo htmlspecialchars($sub['feedback']); ?>"
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($sub['graded_at'])): ?>
                                                            <div class="text-[10px] text-slate-400 font-mono">
                                                                <?php echo date('d/m H:i', strtotime($sub['graded_at'])); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                                        ⏳ Chưa chấm
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                    <button type="button" 
                                                            onclick="previewSubmissionOnApp(<?php echo (int)$sub['id']; ?>)" 
                                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer" 
                                                            title="Xem trực tiếp nội dung bài nộp / code / file ngay trên ứng dụng không cần tải về máy">
                                                        <span>👁️</span> Xem Trên App
                                                    </button>
                                                    <button type="button" 
                                                            onclick='openGradeModal(<?php echo json_encode($sub, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' 
                                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                                                        <span>⭐</span> Chấm Điểm
                                                    </button>
                                                </div>
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

    <!-- MODAL CHẤM ĐIỂM & ĐÁNH GIÁ BÀI NỘP CỦA SINH VIÊN -->
    <div id="gradeSubmissionModal" class="hidden fixed inset-0 z-[95] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-200 animate-scale-up">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">⭐</span>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm">Chấm Điểm & Đánh Giá Bài Nộp</h4>
                        <span class="text-[11px] text-slate-500">Giảng viên ghi nhận điểm số và lời nhận xét</span>
                    </div>
                </div>
                <button type="button" onclick="closeGradeModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center font-bold text-sm">✕</button>
            </div>

            <!-- THÔNG TIN SINH VIÊN & BÀI NỘP -->
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/70 space-y-1 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Sinh viên:</span>
                    <strong class="text-slate-900 font-bold" id="gradeStudentName">—</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Mã sinh viên:</span>
                    <span class="font-mono font-bold text-indigo-700" id="gradeStudentCode">—</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Tệp đã nộp:</span>
                    <span class="font-mono text-[11px] text-slate-700 truncate max-w-[200px]" id="gradeFileName">—</span>
                </div>
            </div>

            <form method="POST" action="lessons.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="grade" />
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                <input type="hidden" name="course_id" value="<?php echo (int)$selectedCourseId; ?>" />
                <input type="hidden" name="ca_id" value="<?php echo (int)$selectedCaId; ?>" />
                <input type="hidden" name="lesson_id" value="<?php echo (int)$viewLessonId; ?>" />
                <input type="hidden" name="submission_id" id="formSubmissionId" value="0" />

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Điểm Chấm (Thang điểm 10.0): <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="score" id="formGradeScore" required min="0" max="10" step="0.1" placeholder="Ví dụ: 8.5 hoặc 10" 
                               class="w-full px-3.5 py-2.5 bg-amber-50/40 border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-mono font-black text-amber-900 text-sm" />
                        <span class="absolute right-3 top-2.5 text-xs font-bold text-amber-600">/ 10.0</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Cho phép nhập số thập phân (ví dụ: 7.5, 8.2, 9.0, 10).</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Lời Nhận Xét & Đánh Giá Bài Làm:
                    </label>
                    <textarea name="feedback" id="formGradeFeedback" rows="3" 
                              placeholder="Nhận xét ưu điểm, mặt cần cải thiện hoặc góp ý sửa mã nguồn cho sinh viên..." 
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-medium text-slate-800 text-xs"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeGradeModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition shadow-md shadow-amber-200 flex items-center gap-1.5">
                        <span>💾</span> Lưu Điểm & Nhận Xét
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

        
        function selectGradeStar(star) {
            document.getElementById("formGradeStarRating").value = star;
            const scoreMap = { 5: 10.0, 4: 8.5, 3: 7.0, 2: 5.0, 1: 3.0 };
            document.getElementById("formGradeScore").value = scoreMap[star] || 10.0;
            
            document.querySelectorAll(".grade-star-btn").forEach(btn => {
                const s = parseInt(btn.getAttribute("data-star"));
                if (s === star) {
                    btn.className = "grade-star-btn px-1.5 py-2 rounded-xl border-2 border-amber-500 bg-amber-400 font-bold text-slate-950 text-center transition flex flex-col items-center gap-0.5 shadow-xs";
                } else {
                    btn.className = "grade-star-btn px-1.5 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-amber-50 font-bold text-slate-700 text-center transition flex flex-col items-center gap-0.5";
                }
            });
        }

        function openGradeModal(sub) {
            document.getElementById('formSubmissionId').value = sub.id;
            document.getElementById('gradeStudentName').innerText = sub.student_name || '—';
            document.getElementById('gradeStudentCode').innerText = sub.student_code || '—';
            document.getElementById('gradeFileName').innerText = sub.file_name || 'Không có tên file';
            const curScore = sub.score !== null ? parseFloat(sub.score) : 10.0;
            document.getElementById('formGradeScore').value = sub.score !== null ? curScore : '';
            const curStar = sub.star_rating ? parseInt(sub.star_rating) : (curScore >= 9 ? 5 : (curScore >= 8 ? 4 : (curScore >= 6.5 ? 3 : (curScore >= 5 ? 2 : 1))));
            selectGradeStar(curStar);
            document.getElementById('formGradeFeedback').value = sub.feedback || '';
            document.getElementById('gradeSubmissionModal').classList.remove('hidden');
            setTimeout(function() {
                document.getElementById('formGradeScore').focus();
            }, 100);
        }

        function closeGradeModal() {
            document.getElementById('gradeSubmissionModal').classList.add('hidden');
        }
    </script>

    <!-- FOOTER CHUẨN TOÀN TRƯỜNG -->
    
    <!-- MODAL THÊM BÀI TẬP / BUỔI HỌC MỚI (DÀNH CHO GIẢNG VIÊN) -->
    <div id="createLessonModal" class="hidden fixed inset-0 z-[95] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200 animate-scale-up">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">➕</span>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm">Giao Thêm Bài Tập / Buổi Học Mới</h4>
                        <span class="text-[11px] text-slate-500">Thêm bài tập lớn, tiểu luận hoặc buổi học bổ sung</span>
                    </div>
                </div>
                <button type="button" onclick="closeCreateLessonModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center font-bold text-sm">✕</button>
            </div>

            <form method="POST" action="lessons.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="create" />
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                <input type="hidden" name="course_id" value="<?php echo (int)$selectedCourseId; ?>" />
                <input type="hidden" name="ca_id" value="<?php echo (int)$selectedCaId; ?>" />

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Số Buổi (Tùy chọn):</label>
                        <input type="number" name="lesson_number" min="1" max="100" placeholder="Tự động" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold text-slate-800" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Tiêu Đề Bài Tập / Buổi Học: <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" required placeholder="Ví dụ: Bài Tập Lớn Cuối Kỳ / Buổi 10" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-800" />
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mô Tả Nội Dung:</label>
                    <textarea name="description" rows="2" placeholder="Tóm tắt yêu cầu thực hiện, mục tiêu bài tập..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-medium text-slate-800 text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Yêu Cầu Bài Tập & Đính Kèm Tệp: <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="assignment_requirement" rows="3" required placeholder="Nêu rõ yêu cầu sinh viên phải đính kèm tệp gì (mã nguồn .zip, báo cáo .pdf, minh chứng link github...)..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-medium text-slate-800 text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Hạn Chót Nộp Bài (Deadline): <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" name="deadline_at" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono font-bold text-slate-800" />
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCreateLessonModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-md shadow-emerald-200 flex items-center gap-1.5">
                        <span>➕</span> Ban Hành Bài Tập Mới
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL XEM TRỰC TIẾP BÀI NỘP TRÊN APP (IN-APP PREVIEWER) -->
    <div id="previewSubmissionModal" class="hidden fixed inset-0 z-[96] flex items-center justify-center p-3 md:p-6 bg-slate-900/70 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 animate-scale-up overflow-hidden">
            <!-- HEADER MODAL -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/70">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">👁️</span>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm flex items-center gap-2" id="previewStudentHeader">
                            Xem Trực Tiếp Bài Nộp Của Sinh Viên
                        </h4>
                        <span class="text-[11px] text-slate-500" id="previewMetaHeader">Xem và chấm điểm trực tiếp trên web app</span>
                    </div>
                </div>
                <button type="button" onclick="closePreviewModal()" class="w-8 h-8 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-700 flex items-center justify-center font-bold text-sm">✕</button>
            </div>

            <!-- NỘI DUNG CUỘN XEM BÀI & CHẤM ĐIỂM -->
            <div class="p-5 overflow-y-auto space-y-5 flex-1 text-xs">
                <!-- THÔNG TIN TỆP NỘP & GHI CHÚ -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                    <div class="space-y-1">
                        <div class="text-slate-500">Tệp nộp: <strong class="text-indigo-900 font-mono" id="previewFileName">—</strong> (<span id="previewFileSize">0 KB</span>)</div>
                        <div class="text-slate-500">Thời gian nộp: <span class="font-mono text-slate-700 font-semibold" id="previewSubmitTime">—</span></div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-slate-500 font-semibold">Ghi chú của sinh viên:</div>
                        <div class="p-2 bg-white rounded-xl border border-indigo-200/60 text-slate-700 italic" id="previewNotes">—</div>
                    </div>
                </div>

                <!-- KHUNG HIỂN THỊ NỘI DUNG BÀI LÀM (CODE / MEDIA / ZIP STRUCTURE) -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <div class="bg-slate-100 px-4 py-2 border-b border-slate-200 font-bold text-slate-700 flex items-center justify-between text-[11.5px]">
                        <span id="previewContentTitle">📄 Nội dung bài làm</span>
                        <a id="previewDownloadLink" href="#" target="_blank" download class="text-indigo-600 hover:underline flex items-center gap-1 font-semibold text-[11px]">
                            <span>⬇️</span> Tải bản sao dự phòng
                        </a>
                    </div>
                    <div id="previewContentBody" class="p-4 bg-slate-900 text-slate-100 font-mono text-[11.5px] max-h-80 overflow-y-auto whitespace-pre-wrap leading-relaxed">
                        Đang nạp dữ liệu bài nộp...
                    </div>
                </div>

                <!-- KHUNG CHẤM ĐIỂM & ĐÁNH GIÁ NHANH TRỰC TIẾP -->
                <div class="bg-amber-50/50 border border-amber-200 rounded-2xl p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">⭐</span>
                        <strong class="text-amber-950 font-black text-xs uppercase tracking-wide">Chấm Điểm & Góp Ý Nhanh Cho Sinh Viên</strong>
                    </div>

                    <form method="POST" action="lessons.php" class="space-y-3">
                        <input type="hidden" name="action" value="grade" />
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>" />
                        <input type="hidden" name="course_id" value="<?php echo (int)$selectedCourseId; ?>" />
                        <input type="hidden" name="ca_id" value="<?php echo (int)$selectedCaId; ?>" />
                        <input type="hidden" name="lesson_id" value="<?php echo (int)$viewLessonId; ?>" />
                        <input type="hidden" name="submission_id" id="previewFormSubmissionId" value="0" />

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Điểm (Thang 10): <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input type="number" name="score" id="previewFormScore" required min="0" max="10" step="0.1" placeholder="8.5" class="w-full px-3 py-2 bg-white border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-bold text-amber-900 font-mono" />
                                    <span class="absolute right-3 top-2 text-xs font-bold text-amber-500">/ 10</span>
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 mb-1">Lời Nhận Xét & Đánh Giá:</label>
                                <input type="text" name="feedback" id="previewFormFeedback" placeholder="Nhận xét ưu điểm, mặt cần cải thiện..." class="w-full px-3 py-2 bg-white border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-400 text-slate-800" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition shadow-md shadow-amber-200 flex items-center gap-1.5 cursor-pointer">
                                <span>💾</span> Lưu Điểm & Nhận Xét
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>

        function openCreateLessonModal() {
            document.getElementById("createLessonModal").classList.remove("hidden");
        }

        function closeCreateLessonModal() {
            document.getElementById("createLessonModal").classList.add("hidden");
        }

        function previewSubmissionOnApp(submissionId) {
            const modal = document.getElementById("previewSubmissionModal");
            const body = document.getElementById("previewContentBody");
            const title = document.getElementById("previewContentTitle");
            const dlLink = document.getElementById("previewDownloadLink");

            body.className = "p-4 bg-slate-900 text-slate-100 font-mono text-[11.5px] max-h-80 overflow-y-auto whitespace-pre-wrap leading-relaxed";
            body.innerHTML = "⏳ Đang tải nội dung bài làm từ máy chủ...";
            modal.classList.remove("hidden");

            fetch("lessons.php?action=preview&submission_id=" + submissionId)
                .then(r => r.json())
                .then(res => {
                    if (res.status !== "success" || !res.data) {
                        body.innerHTML = "⚠️ Không thể nạp bài nộp: " + (res.message || "Lỗi không xác định");
                        return;
                    }
                    const d = res.data;
                    document.getElementById("previewStudentHeader").innerText = d.student_name + " (" + d.student_code + ")";
                    document.getElementById("previewMetaHeader").innerText = "Lớp: " + (d.class_code || "D24-CNTT1") + " · Buổi " + d.lesson_number + ": " + d.lesson_title;
                    document.getElementById("previewFileName").innerText = d.file_name || "—";
                    document.getElementById("previewFileSize").innerText = (d.file_info ? d.file_info.size_kb : 0) + " KB";
                    document.getElementById("previewSubmitTime").innerText = d.submitted_at || "—";
                    document.getElementById("previewNotes").innerText = d.notes ? `"${d.notes}"` : "Không có ghi chú thêm.";
                    dlLink.href = "<?php echo $appBase; ?>" + (d.file_path || "");

                    document.getElementById("previewFormSubmissionId").value = d.id;
                    document.getElementById("previewFormScore").value = d.score !== null ? parseFloat(d.score) : "";
                    document.getElementById("previewFormFeedback").value = d.feedback || "";

                    const fi = d.file_info || {};
                    if (fi.preview_type === "text" && fi.content) {
                        title.innerText = "💻 Mã nguồn / Nội dung tệp: " + d.file_name;
                        body.innerText = fi.content;
                    } else if (fi.preview_type === "media") {
                        title.innerText = "🖼️ Minh chứng / Tài liệu: " + d.file_name;
                        const fileUrl = "<?php echo $appBase; ?>" + d.file_path;
                        if (fi.extension === "pdf") {
                            body.className = "p-0 h-96 overflow-hidden bg-slate-100";
                            body.innerHTML = `<iframe src="${fileUrl}" class="w-full h-full border-0"></iframe>`;
                        } else {
                            body.className = "p-4 bg-slate-900 flex justify-center";
                            body.innerHTML = `<img src="${fileUrl}" alt="Bài làm" class="max-h-80 rounded-lg shadow-md" />`;
                        }
                    } else if (fi.preview_type === "zip" && fi.zip_files && fi.zip_files.length > 0) {
                        title.innerText = "📦 Cấu trúc tệp mã nguồn bên trong file ZIP (" + fi.zip_files.length + " tệp/thư mục)";
                        body.className = "p-4 bg-slate-900 text-slate-200 font-mono text-[11px] max-h-80 overflow-y-auto leading-relaxed";
                        let html = "<div class='text-amber-400 font-bold mb-2'>📁 Danh sách cây thư mục dự án sinh viên đã nén nộp:</div><ul class='space-y-1'>";
                        fi.zip_files.forEach(f => {
                            html += `<li class="flex items-center justify-between border-b border-slate-800 py-0.5">
                                <span class="${f.is_dir ? "text-indigo-400 font-bold" : "text-emerald-300"}">${f.is_dir ? "📂 " : "📄 "}${f.name}</span>
                                <span class="text-slate-500 text-[10px]">${f.size}</span>
                            </li>`;
                        });
                        html += "</ul>";
                        body.innerHTML = html;
                    } else {
                        title.innerText = "📦 Tệp đính kèm: " + d.file_name;
                        body.className = "p-6 bg-slate-800 text-center text-slate-300 space-y-2";
                        body.innerHTML = `
                            <span class="text-3xl block">📦</span>
                            <div class="font-bold">Đã lưu trữ tệp bài làm trên hệ thống Helpdesk.</div>
                            <div class="text-xs text-slate-400">Định dạng .${fi.extension} có thể xem ghi chú sinh viên ở trên hoặc tải về máy nếu muốn chạy trực tiếp.</div>
                        `;
                    }
                })
                .catch(err => {
                    body.innerHTML = "⚠️ Lỗi kết nối máy chủ: " + err;
                });
        }

        function closePreviewModal() {
            document.getElementById("previewSubmissionModal").classList.add("hidden");
        }

</script>
<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>

</body>
</html>

