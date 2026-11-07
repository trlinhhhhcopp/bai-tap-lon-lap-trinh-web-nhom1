<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * CỔNG THÔNG TIN SINH VIÊN (CHUẨN LUỒNG 1 ĐẦY ĐỦ NỘI DUNG, ROUTER MVC LUỒNG 2)
 * ==============================================================================
 */
if (!isset($appBase)) {
    $appBase = get_app_base_url();
}
$flash = $flash ?? get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Sinh Viên - Helpdesk Đại Học Thủ Đô Hà Nội</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Điều Hướng Toàn Dự Án -->
    <?php require __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- 1. BANNER CHÀO MỪNG SINH VIÊN (TƯƠI SÁNG, HIỆN ĐẠI THEO CHUẨN CỔNG TRƯỜNG ĐH) -->
        <div class="bg-gradient-to-r from-sky-600 via-indigo-600 to-indigo-800 text-white rounded-3xl p-6 md:p-8 shadow-xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md text-white border border-white/30 flex items-center justify-center text-2xl font-black shadow-lg shrink-0">
                    🎓
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white border border-white/30">
                            🏛️ CỔNG THÔNG TIN SINH VIÊN
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-400/25 text-emerald-200 border border-emerald-400/40">
                            ● Tài khoản đang hoạt động
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                        Xin chào, <?= htmlspecialchars($currentUser['name'] ?? 'Sinh viên'); ?>!
                    </h1>
                    <p class="text-xs md:text-sm text-sky-100">
                        Mã SV: <strong class="text-white font-mono"><?= htmlspecialchars($currentUser['user_code'] ?? 'N/A'); ?></strong> ·
                        Lớp: <strong class="text-white"><?= htmlspecialchars($studentProfile['class_code'] ?? 'Chưa phân lớp'); ?></strong> ·
                        Khóa: <span class="text-sky-100">Đại học chính quy 2024</span>
                    </p>
                    <p class="text-[11.5px] text-sky-200/90">
                        Khoa Công Nghệ Thông Tin · Trường Đại Học Thủ Đô Hà Nội
                    </p>
                </div>
            </div>

            <!-- CÁC NÚT TRUY CẬP NHANH DÀNH CHO SINH VIÊN -->
            <div class="flex items-center gap-2.5 flex-wrap relative z-10">
                <a href="<?= $appBase ?>student/courses" class="px-4 py-2.5 bg-white text-indigo-700 hover:bg-sky-50 font-black rounded-2xl shadow-md transition flex items-center gap-2 text-xs border border-white/40">
                    <span>📚</span> Học Phần & Bài Tập (<?= (int)$myCoursesCount; ?> môn)
                </a>
                <a href="<?= $appBase ?>student/ratings" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs">
                    <span>⭐</span> Đánh Giá CSAT 5★
                </a>
                <a href="<?= $appBase ?>tickets/create" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs cursor-pointer">
                    <span>➕</span> Gửi Phiếu Mới
                </a>
            </div>

        </div>

        <?php if (!empty($flash)): ?>
            <div role="alert" data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                <?= $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- 2. CÁC CHỈ SỐ CHÍNH CỦA SINH VIÊN (4 CỘT CHUẨN LUỒNG 1) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="<?= $appBase ?>student/courses" class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between hover:shadow-md transition group">
                <div>
                    <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider block mb-1">Học Phần & Bài Tập</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-indigo-900"><?= (int)$myCoursesCount; ?></span>
                        <span class="text-xs text-indigo-600 font-bold">môn đang học</span>
                    </div>
                    <div class="text-[10.5px] text-indigo-500 font-semibold mt-1">Đã nộp: <?= (int)$completedLessonsCount; ?>/<?= (int)$totalLessonsCount; ?> buổi</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                    📚
                </div>
            </a>

            <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tổng Phiếu Đã Gửi</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900"><?= (int)$myTicketsCount; ?></span>
                        <span class="text-xs text-sky-600 font-bold">yêu cầu hỗ trợ</span>
                    </div>
                    <div class="text-[10.5px] text-slate-400 mt-1">Lịch sử sự cố học thuật</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">
                    🔔
                </div>
            </div>

            <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Đang Xử Lý</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-amber-600"><?= (int)$myActiveCount; ?></span>
                        <span class="text-xs text-amber-700 font-bold">đang khắc phục</span>
                    </div>
                    <div class="text-[10.5px] text-amber-600/80 mt-1">KTV đang tiếp nhận & xử lý</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    ⏳
                </div>
            </div>

            <a href="<?= $appBase ?>student/ratings" class="bg-gradient-to-br from-amber-50/50 to-white border border-amber-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between hover:shadow-md transition group">
                <div>
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block mb-1">Đã Hoàn Thành & CSAT</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-emerald-600"><?= (int)$myResolvedCount; ?></span>
                        <span class="text-xs text-emerald-700 font-bold">đã hoàn tất</span>
                    </div>
                    <div class="text-[10.5px] text-amber-700 font-bold mt-1">⭐ Bấm nghiệm thu & chấm sao</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                    ⭐
                </div>
            </a>
        </div>

        <!-- 3. BANNER THÔNG BÁO HỆ THỐNG MỚI NHẤT (SỰ CỐ DIỆN RỘNG / CẢNH BÁO KHẨN) -->
        <?php require __DIR__ . '/../../includes/notification_banner.php'; ?>

        <!-- 4. BẢNG DANH SÁCH PHIẾU CỦA RIÊNG SINH VIÊN NÀY -->
        <div class="bg-white border border-slate-200/90 rounded-3xl shadow-sm overflow-hidden space-y-3">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>🎫</span> MỤC PHIẾU HỖ TRỢ RIÊNG CỦA TÔI (CÁCH LY DỮ LIỆU)
                    </h2>
                    <p class="text-[11.5px] text-slate-500 mt-0.5">Sinh viên chỉ xem và trao đổi trên các phiếu do chính mình gửi, bảo mật tuyệt đối.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-target-table="student_tickets_body" onclick="toggleTableCollapse('student_tickets_body', this)" title="Ẩn hoặc hiện bảng này" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1 border border-slate-200 cursor-pointer">
                        <span class="toggle-table-icon">✕</span>
                        <span class="toggle-table-text">Ẩn bảng</span>
                    </button>
                    <a href="<?= $appBase ?>tickets/create" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
                        <span>➕</span> Gửi Phiếu Mới
                    </a>
                </div>
            </div>

            <div id="student_tickets_body" data-collapsible-table>
            <?php if (empty($myTickets)): ?>
                <div class="p-10 text-center text-slate-400 text-xs space-y-2">
                    <span class="text-3xl block">📭</span>
                    <p class="text-sm font-bold text-slate-600">Hiện bạn chưa gửi yêu cầu hỗ trợ nào.</p>
                    <p class="text-[11.5px] text-slate-400">Khi gặp sự cố kỹ thuật phòng máy, phần mềm hoặc tài khoản, hãy bấm nút Gửi Phiếu Mới ở trên để gửi yêu cầu.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-200 text-[10.5px] uppercase tracking-wider">
                                <th class="py-2.5 px-3.5 whitespace-nowrap">Mã<br>Vé</th>
                                <th class="py-2.5 px-3.5">Tiêu Đề<br>Sự Cố</th>
                                <th class="py-2.5 px-3.5 whitespace-nowrap">Chuyên<br>Mục</th>
                                <th class="py-2.5 px-3.5 text-center whitespace-nowrap">Trạng<br>Thái</th>
                                <th class="py-2.5 px-3.5 whitespace-nowrap">Kỹ Thuật<br>Viên</th>
                                <th class="py-2.5 px-3.5 text-center whitespace-nowrap">Đánh Giá<br>CSAT</th>
                                <th class="py-2.5 px-3.5 text-center whitespace-nowrap">Thao<br>Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($myTickets as $t): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                                        <a href="<?= $appBase ?>tickets/show?id=<?= $t['id']; ?>" class="hover:underline">
                                            <?= htmlspecialchars($t['ticket_code']); ?>
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-900">
                                        <a href="<?= $appBase ?>tickets/show?id=<?= $t['id']; ?>" class="hover:text-indigo-600 transition block max-w-sm truncate">
                                            <?= htmlspecialchars($t['title']); ?>
                                        </a>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10.5px] text-slate-400 font-normal">Gửi lúc: <?= htmlspecialchars($t['created_at']); ?></span>
                                            <?php if (!empty($t['attachment_path'])): ?>
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">
                                                    📎 Có minh chứng
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600">
                                        <?= htmlspecialchars($t['category_name'] ?? 'Khác'); ?>
                                    </td>
                                    <!-- CỘT TRẠNG THÁI: ÁP DỤNG BADGE PILL CÓ DOT ● CHUẨN MẪU -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <?= render_status_badge($t['status']); ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600">
                                        <?= htmlspecialchars($t['agent_name'] ?: 'Đang phân công...'); ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if (!empty($t['rating_score'])): ?>
                                            <span class="font-bold text-amber-500 font-mono text-sm"><?= $t['rating_score']; ?> ★</span>
                                        <?php elseif ($t['status'] === 'resolved' || $t['status'] === 'closed'): ?>
                                            <a href="<?= $appBase ?>tickets/show?id=<?= $t['id']; ?>#csat-box"
                                               class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg text-[10.5px] font-bold transition inline-block">
                                                Chấm sao ★
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-300 text-[11px]">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap space-x-1">
                                        <a href="<?= $appBase ?>tickets/show?id=<?= $t['id']; ?>" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl transition inline-block text-xs">
                                            Chi tiết & Chat →
                                        </a>
                                        <?php if (in_array($t['status'], ['new', 'waiting_requester'])): ?>
                                            <form method="POST" action="<?= $appBase ?>student/tickets/cancel" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy phiếu hỗ trợ #<?= htmlspecialchars($t['ticket_code']); ?>?');">
                                                <input type="hidden" name="ticket_id" value="<?= $t['id']; ?>">
                                                <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl transition inline-block text-xs border border-rose-200 cursor-pointer" title="Hủy phiếu này">
                                                    ✕ Hủy
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- Chân trang chuyên nghiệp -->
    <?php require __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        // Ẩn / hiện bảng dữ liệu
        window.toggleTableCollapse = function(wrapperId, buttonElement) {
            const wrapper = document.getElementById(wrapperId);
            if (!wrapper) return;
            const isHidden = wrapper.classList.toggle('hidden');
            const textSpan = buttonElement.querySelector('.toggle-table-text');
            const iconSpan = buttonElement.querySelector('.toggle-table-icon');
            if (textSpan) textSpan.textContent = isHidden ? 'Hiện bảng' : 'Ẩn bảng';
            if (iconSpan) iconSpan.textContent = isHidden ? '👁️' : '✕';
        };
    </script>

</body>
</html>
