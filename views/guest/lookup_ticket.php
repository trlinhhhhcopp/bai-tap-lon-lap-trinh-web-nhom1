<?php 
require_once __DIR__ . '/../../config/database.php';
require __DIR__ . '/../layouts/header.php'; 

$code = $code ?? trim($_GET['code'] ?? '');
$ticket = $ticket ?? null;
$isInternalTicket = $isInternalTicket ?? false;

if (empty($ticket) && !empty($code)) {
    $found = \App\Models\Ticket::findByTicketCode($code);
    if ($found) {
        if (empty($found['is_guest_ticket'])) {
            $isInternalTicket = true;
        } else {
            $ticket = $found;
        }
    }
}
?>

<div class="my-auto py-8 flex items-center justify-center">
    <div class="w-full max-w-2xl bg-white border border-slate-200/80 rounded-3xl shadow-2xl shadow-indigo-950/10 overflow-hidden transition-all">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 md:p-8 text-center border-b-2 border-sky-500 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-32 h-32 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">
                    🔍 TRA CỨU TIẾN ĐỘ
                </span>
                <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    TRA CỨU TIẾN ĐỘ PHIẾU HỖ TRỢ
                </h2>
                <p class="text-xs text-slate-300">
                    Nhập mã phiếu bảo mật để kiểm tra kết quả xử lý thời gian thực từ Kỹ thuật viên
                </p>
            </div>
        </div>

        <!-- Body Form -->
        <div class="p-6 md:p-8 space-y-6">
            <form method="GET" action="<?= $appBase ?>guest/lookup" class="flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        🎫
                    </span>
                    <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" required 
                           placeholder="Nhập mã phiếu (VD: HD-2026-2910-842B19D)" 
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono font-bold uppercase focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder:text-slate-400">
                </div>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-sky-500/20 transition whitespace-nowrap flex items-center gap-1.5">
                    <span>🔍</span> Tra Cứu
                </button>
            </form>

            <?php if (!empty($isInternalTicket)): ?>
                <!-- BẢO MẬT: PHIẾU NỘI BỘ YÊU CẦU ĐĂNG NHẬP TRÁNH BỊ NGƯỜI NGOÀI XEM LÉN -->
                <div class="bg-amber-50/90 border border-amber-200 text-amber-900 p-6 rounded-2xl text-center space-y-3.5 shadow-sm animate-fade-in">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 mx-auto flex items-center justify-center text-2xl shadow-sm">
                        🔒
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-black text-amber-950">Phiếu Hỗ Trợ Nội Bộ Sinh Viên / Giảng Viên</h3>
                        <p class="text-xs text-amber-800/90 leading-relaxed max-w-md mx-auto">
                            Mã phiếu <strong class="font-mono"><?= htmlspecialchars($code) ?></strong> được khởi tạo từ tài khoản nội bộ Nhà trường.<br>
                            Để bảo đảm tính riêng tư và an toàn dữ liệu học tập cá nhân, vui lòng đăng nhập vào Cổng sinh viên để xem chi tiết!
                        </p>
                    </div>
                    <div class="flex items-center justify-center gap-2 pt-1 flex-wrap">
                        <a href="<?= $appBase ?>login" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-md shadow-sky-600/20 transition flex items-center gap-1.5">
                            <span>🔑</span> Đăng Nhập Cổng Sinh Viên
                        </a>
                        <a href="<?= $appBase ?>guest/lookup" class="px-4 py-2.5 bg-white hover:bg-slate-100 border border-amber-200 text-amber-900 text-xs font-bold rounded-xl transition">
                            Tra Cứu Mã Khác
                        </a>
                    </div>
                </div>

            <?php elseif ($ticket): ?>
                <!-- PHIẾU CỨU HỘ KHẨN CẤP / GUEST TICKET -->
                <?php
                    $maskedEmail = '';
                    if (!empty($ticket['contact_email'])) {
                        $emParts = explode('@', $ticket['contact_email']);
                        if (count($emParts) === 2) {
                            $p = $emParts[0];
                            $maskedP = (strlen($p) > 4) ? substr($p, 0, 3) . '***' . substr($p, -1) : substr($p, 0, 1) . '***';
                            $maskedEmail = $maskedP . '@' . $emParts[1];
                        }
                    }
                ?>
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 md:p-6 space-y-4 shadow-sm animate-fade-in">
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-200 flex-wrap gap-2">
                        <div>
                            <span class="text-[10.5px] uppercase font-bold text-slate-400 tracking-wider block">Mã Phiếu Cứu Hộ</span>
                            <h3 class="text-xl font-black font-mono text-sky-700 tracking-tight">
                                <?= htmlspecialchars($ticket['ticket_code']) ?>
                            </h3>
                        </div>
                        <div>
                            <?php if ($ticket['status'] === 'resolved'): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                    ✓ ĐÃ MỞ KHÓA THÀNH CÔNG
                                </span>
                            <?php elseif ($ticket['status'] === 'in_progress'): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1">
                                    ⏳ KTV ĐANG XỬ LÝ
                                </span>
                            <?php elseif ($ticket['status'] === 'closed'): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-200 inline-flex items-center gap-1">
                                    🔒 ĐÃ ĐÓNG
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-sky-50 text-sky-700 border border-sky-200 inline-flex items-center gap-1">
                                    ● MỚI TIẾP NHẬN
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs text-slate-700 leading-relaxed">
                        <div class="p-3 bg-white border border-slate-200 rounded-xl space-y-1.5">
                            <div><strong>Tiêu đề sự cố:</strong> <?= htmlspecialchars($ticket['title']) ?></div>
                            <div><strong>Kỹ thuật viên phụ trách:</strong> <span class="font-bold text-sky-700"><?= htmlspecialchars($ticket['agent_name']) ?></span></div>
                            <div><strong>Thời gian gửi:</strong> <span class="font-mono text-slate-500"><?= htmlspecialchars($ticket['created_at']) ?></span></div>
                            <?php if (!empty($maskedEmail)): ?>
                                <div><strong>Email nhận thông báo:</strong> <span class="font-mono bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><?= htmlspecialchars($maskedEmail) ?></span></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <span class="font-bold text-slate-500 block mb-1 uppercase tracking-wider text-[11px]">Chi tiết yêu cầu:</span>
                            <div class="p-3 bg-white border border-slate-200 rounded-xl leading-relaxed whitespace-pre-wrap">
                                <?= nl2br(htmlspecialchars($ticket['description'])) ?>
                            </div>
                        </div>

                        <?php if (!empty($ticket['attachment_path'])): ?>
                            <div class="pt-2">
                                <a href="<?= $appBase ?>tickets/attachment?file=<?= urlencode($ticket['attachment_path']) ?>" target="_blank" 
                                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl font-bold transition text-xs">
                                    📎 Xem ảnh minh chứng đã gửi
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif (!empty($code)): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-900 p-5 rounded-2xl text-center space-y-1.5 animate-fade-in">
                    <span class="text-2xl">❌</span>
                    <h4 class="font-bold text-sm">Không tìm thấy phiếu hỗ trợ nào</h4>
                    <p class="text-xs text-rose-700">
                        Mã phiếu <strong class="font-mono"><?= htmlspecialchars($code) ?></strong> không tồn tại trong hệ thống. Vui lòng kiểm tra lại chính xác từng ký tự!
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
