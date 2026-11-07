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
                <a href="#form-tao-phieu" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs cursor-pointer">
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
                    <a href="#form-tao-phieu" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
                        <span>➕</span> Gửi Phiếu Mới
                    </a>
                </div>
            </div>

            <div id="student_tickets_body" data-collapsible-table>
            <?php if (empty($myTickets)): ?>
                <div class="p-10 text-center text-slate-400 text-xs space-y-2">
                    <span class="text-3xl block">📭</span>
                    <p class="text-sm font-bold text-slate-600">Hiện bạn chưa gửi yêu cầu hỗ trợ nào.</p>
                    <p class="text-[11.5px] text-slate-400">Khi gặp sự cố kỹ thuật phòng máy, phần mềm hoặc tài khoản, hãy dùng biểu mẫu bên dưới để gửi yêu cầu.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                                <th class="py-3 px-4">Mã Vé</th>
                                <th class="py-3 px-4">Tiêu Đề Sự Cố</th>
                                <th class="py-3 px-4">Chuyên Mục</th>
                                <th class="py-3 px-4 text-center">TRẠNG THÁI</th>
                                <th class="py-3 px-4">KTV Phụ Trách</th>
                                <th class="py-3 px-4 text-center">Đánh Giá CSAT</th>
                                <th class="py-3 px-4 text-center">Thao Tác</th>
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

        <!-- 5. FORM TẠO PHIẾU HỖ TRỢ MỚI NGAY TẠI TRANG (TÍCH HỢP WEBCAM HTML5 + UPLOAD) -->
        <div id="form-tao-phieu" class="bg-white border border-slate-200/90 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <span>➕</span> GỬI YÊU CẦU HỖ TRỢ KỸ THUẬT MỚI
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Thuật toán điều phối Triage thông minh sẽ tự động phân loại và gán phiếu cho KTV chuyên trách đang trực ca có tải thấp nhất.
                </p>
            </div>

            <form method="POST" action="<?= $appBase ?>student/tickets/create" enctype="multipart/form-data" class="space-y-5 text-xs">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Chọn danh mục -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Chuyên Mục Sự Cố (*)</label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800 text-xs">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id']; ?>">
                                    <?= htmlspecialchars($cat['name']); ?> (SLA chuẩn: <?= $cat['sla_hours']; ?>h)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Mức ưu tiên -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">Mức Độ Ưu Tiên (*)</label>
                        <select name="priority_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800 text-xs">
                            <?php foreach ($priorities as $pri): ?>
                                <option value="<?= $pri['id']; ?>" <?= ($pri['code'] ?? '') === 'MED' ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($pri['name']); ?> (Hệ số SLA: <?= $pri['sla_factor']; ?>x)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Tiêu đề sự cố -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Tiêu Đề Sự Cố Ngắn Gọn (*)</label>
                    <input type="text" name="title" required placeholder="Ví dụ: Lỗi không tải được tài liệu buổi 3 môn Lập trình Web..."
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800 text-xs" />
                </div>

                <!-- Mô tả chi tiết -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Mô Tả Chi Tiết Sự Cố & Thông Báo Lỗi (*)</label>
                    <textarea name="description" rows="4" required placeholder="Mô tả cụ thể triệu chứng, phòng máy, thiết bị, môn học liên quan để KTV xử lý nhanh chóng..."
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 text-xs leading-relaxed"></textarea>
                </div>

                <!-- ĐÍNH KÈM TỆP & WEBCAM HTML5 TRỰC TIẾP -->
                <div class="p-4 bg-slate-50 border border-slate-200/90 rounded-2xl space-y-3">
                    <label class="block font-bold text-slate-700">Minh Chứng Lỗi (Tệp tải lên hoặc Chụp ảnh Webcam trực tiếp)</label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                        <div>
                            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.docx,.zip"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
                            <p class="text-[11px] text-slate-400 mt-1">Định dạng hỗ trợ: JPG, PNG, PDF, DOCX, ZIP (Tối đa 5MB).</p>
                        </div>

                        <!-- Nút Bật Webcam Chụp Ảnh Màn Hình / Thẻ Sinh Viên -->
                        <div class="flex items-center gap-2">
                            <button type="button" id="btnToggleWebcam" onclick="toggleWebcam()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl transition flex items-center gap-1.5 text-xs cursor-pointer shadow-sm">
                                <span>📸</span> Bật Webcam Chụp Lỗi
                            </button>
                            <span id="webcamStatus" class="text-[11px] text-slate-500">Chưa kích hoạt camera</span>
                        </div>
                    </div>

                    <!-- Khung Video Stream Live từ Webcam -->
                    <div id="webcamContainer" class="hidden pt-3 border-t border-slate-200 space-y-3">
                        <div class="relative w-full max-w-md mx-auto bg-slate-900 rounded-2xl overflow-hidden aspect-video flex items-center justify-center">
                            <video id="liveVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                            <canvas id="snapshotCanvas" class="hidden"></canvas>
                        </div>
                        <div class="flex items-center justify-center gap-3">
                            <button type="button" onclick="takeSnapshot()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-md cursor-pointer">
                                <span>📷</span> Chụp Khung Hình Này
                            </button>
                            <button type="button" onclick="stopWebcam()" class="px-3.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                                Đóng Camera
                            </button>
                        </div>
                        <!-- Preview ảnh vừa chụp -->
                        <div id="snapshotPreviewBox" class="hidden text-center space-y-2">
                            <p class="text-xs font-bold text-emerald-700">✓ Đã chụp thành công ảnh minh chứng:</p>
                            <img id="snapshotPreview" src="" alt="Snapshot Preview" class="max-h-36 mx-auto rounded-xl border border-emerald-300 shadow-sm" />
                        </div>
                    </div>

                    <!-- Input ẩn lưu chuỗi Base64 khi chụp từ webcam -->
                    <input type="hidden" name="captured_image" id="captured_image_input" value="" />
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="reset" onclick="resetForm()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition cursor-pointer">
                        Làm lại
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-xs transition shadow-lg shadow-indigo-100 flex items-center gap-1.5 cursor-pointer">
                        <span>Gửi Phiếu Báo Sự Cố</span>
                        <span>→</span>
                    </button>
                </div>

            </form>
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

        // Webcam Module
        let videoStream = null;
        function toggleWebcam() {
            const container = document.getElementById('webcamContainer');
            const status = document.getElementById('webcamStatus');
            if (container.classList.contains('hidden')) {
                navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
                    .then(function(stream) {
                        videoStream = stream;
                        const video = document.getElementById('liveVideo');
                        video.srcObject = stream;
                        container.classList.remove('hidden');
                        status.textContent = '● Camera đang hoạt động';
                        status.classList.add('text-emerald-600', 'font-bold');
                    })
                    .catch(function(err) {
                        alert('Không thể mở Webcam: ' + err.message + '\nVui lòng cấp quyền truy cập camera trên trình duyệt.');
                    });
            } else {
                stopWebcam();
            }
        }

        function stopWebcam() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
            document.getElementById('webcamContainer').classList.add('hidden');
            const status = document.getElementById('webcamStatus');
            status.textContent = 'Chưa kích hoạt camera';
            status.classList.remove('text-emerald-600', 'font-bold');
        }

        function takeSnapshot() {
            const video = document.getElementById('liveVideo');
            const canvas = document.getElementById('snapshotCanvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const base64Data = canvas.toDataURL('image/jpeg', 0.85);

            document.getElementById('captured_image_input').value = base64Data;
            document.getElementById('snapshotPreview').src = base64Data;
            document.getElementById('snapshotPreviewBox').classList.remove('hidden');
            stopWebcam();
            const status = document.getElementById('webcamStatus');
            status.textContent = '✓ Đã đính kèm ảnh chụp từ Webcam';
            status.classList.add('text-indigo-600', 'font-bold');
        }

        function resetForm() {
            stopWebcam();
            document.getElementById('captured_image_input').value = '';
            document.getElementById('snapshotPreviewBox').classList.add('hidden');
        }
    </script>

</body>
</html>
