<?php
$appRoot = dirname(__DIR__, 2);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bàn Làm Việc Kỹ Thuật Viên - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- NAVBAR CHÍNH -->
    <?php require_once $appRoot . '/includes/navbar.php'; ?>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- HEADER BANNER WORKSPACE -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        👨‍💻 BÀN LÀM VIỆC KỸ THUẬT VIÊN (AGENT WORKSPACE)
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300">
                        🎯 Chuyên môn: <?php echo htmlspecialchars($currentAgent['specialty_name'] ?? 'Hỗ trợ CNTT'); ?>
                    </span>
                </div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2.5 flex-wrap">
                    <span><?php echo htmlspecialchars($currentAgent['name']); ?></span>
                    <span class="text-xs font-mono text-indigo-300 font-semibold">(<?php echo htmlspecialchars($currentAgent['user_code']); ?>)</span>
                    
                    <!-- ĐIỂM TRUNG BÌNH SAO CSAT KIỂU BE/GRAB ĐẶT NGAY SAU TÊN KTV -->
                    <a href="ratings.php<?php echo $isAdmin ? '?agent_id=' . $agentId : ''; ?>" 
                       title="Bấm để xem chi tiết nhận xét & lọc số sao đánh giá CSAT"
                       class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-400/20 hover:bg-amber-400/30 border border-amber-400/50 text-amber-300 rounded-full text-xs font-black transition shadow-xs cursor-pointer group">
                        <span class="text-amber-400 text-sm">⭐</span>
                        <span class="text-sm font-black text-white"><?php echo number_format($avgScore, 1); ?></span>
                        <span class="text-[11px] text-amber-200/90 font-medium">/ 5.0 (<?php echo $totalRatings; ?> đánh giá)</span>
                        <span class="text-xs text-amber-300 opacity-70 group-hover:translate-x-0.5 transition-transform">→</span>
                    </a>
                </h1>
                <p class="text-xs text-indigo-200/80 mt-0.5">Tiếp nhận, xử lý sự cố từ xa, ghi chú nội bộ và lập biên bản giải pháp kỹ thuật.</p>
            </div>

            <!-- KHỐI QUẢN LÝ CA TRỰC (ON-DUTY / OFF-DUTY - ĐỘC QUYỀN ADMIN ĐIỀU CHUYỂN) -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5">
                <?php if ((int)($currentAgent['is_on_duty'] ?? 1) === 1): ?>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-bold shadow-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                            <span>🟢 ĐANG TRỰC CA (Nhận vé tự động)</span>
                        </span>
                        <?php if ($isAdmin): ?>
                            <button type="button" onclick="document.getElementById('dutyToggleModal').classList.remove('hidden')" 
                                    class="px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/40 text-amber-300 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer" title="Chỉ Admin có thẩm quyền chuyển ca trực">
                                <span>⏸️</span> Admin: Chuyển Nghỉ Ca
                            </button>
                        <?php else: ?>
                            <span class="text-[11px] text-indigo-200/70 font-medium">🛡️ Do Admin phân công</span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-rose-500/20 text-rose-300 border border-rose-500/40 text-xs font-bold shadow-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400 inline-block"></span>
                            <span>⏸️ ĐANG NGHỈ CA (Không nhận vé)</span>
                        </span>
                        <?php if ($isAdmin): ?>
                            <form method="POST" action="index.php?agent_id=<?php echo $agentId; ?>" class="inline">
                                <input type="hidden" name="action" value="toggle_duty" />
                                <input type="hidden" name="agent_id" value="<?php echo $agentId; ?>" />
                                <input type="hidden" name="is_on_duty" value="1" />
                                <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1 cursor-pointer" title="Chỉ Admin có thẩm quyền bật ca trực">
                                    <span>▶️</span> Admin: Bật Trực Ca
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-[11px] text-rose-200/70 font-medium">🛡️ Do Admin phê duyệt nghỉ</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- THÔNG BÁO FLASH MESSAGE -->
        <?php if ($flash): ?>
            <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold flex items-center gap-2 <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
                <span><?php echo $flash['type'] === 'success' ? '✅' : '⚠️'; ?></span>
                <span><?php echo $flash['message']; ?></span>
            </div>
        <?php endif; ?>

        <!-- DÀNH CHO ADMIN: THANH CHUYỂN ĐỔI 5 KTV (NẾU LÀ ADMIN ĐĂNG NHẬP) -->
        <?php if ($isAdmin): ?>
            <?php require __DIR__ . '/partials/agent_switcher.php'; ?>
        <?php endif; ?>

        <!-- KHỐI 1: THỐNG KÊ PHIẾU CỦA RIÊNG KTV (TỔNG, HOÀN THÀNH, CHƯA HOÀN THÀNH, TẢI TRỌNG) -->
        <?php require __DIR__ . '/partials/kpi_stats.php'; ?>

        <!-- BANNER THÔNG BÁO HỆ THỐNG MỚI NHẤT (ĐẶT SAU PHẦN TỔNG QUAN) -->
        <?php require_once $appRoot . '/includes/notification_banner.php'; ?>

        <!-- KHỐI 2: HÀNG ĐỢI XỬ LÝ THEO SLA (DUE_AT ASC) -->
        <?php require __DIR__ . '/partials/ticket_queue.php'; ?>

    </main>

    <!-- KHỐI 3: MODAL LẬP BIÊN BẢN NGHIỆM THU GIẢI PHÁP -->
    <?php require __DIR__ . '/partials/resolve_modal.php'; ?>

    <?php if ($isAdmin): ?>
    <!-- KHỐI 4: MODAL ADMIN BÁO NGHỈ CA / BÁO ỐM CHO KTV (CHỈ ADMIN MỚI THẤY) -->
    <div id="dutyToggleModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="text-lg">⏸️</span> Admin: Chuyển Nghỉ Ca Cho KTV
                </h3>
                <button type="button" onclick="document.getElementById('dutyToggleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl leading-none cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="index.php?agent_id=<?php echo $agentId; ?>" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="toggle_duty" />
                <input type="hidden" name="agent_id" value="<?php echo $agentId; ?>" />
                <input type="hidden" name="is_on_duty" value="0" />

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 leading-relaxed text-[11.5px]">
                    Khi Admin tắt ca trực của KTV này, <strong>thuật toán điều phối Triage tự động</strong> sẽ chuyển hướng toàn bộ các phiếu sự cố mới sang KTV chuyên trách dự phòng đang trực ca khác.
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lý do xin nghỉ ca / vắng mặt: <span class="text-rose-500">*</span></label>
                    <select name="duty_reason" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 font-medium">
                        <option value="Báo ốm đột xuất">🤒 Báo ốm đột xuất (Sốt, sức khỏe không đảm bảo)</option>
                        <option value="Nghỉ phép có lý do">🏖️ Nghỉ phép cá nhân theo quy định</option>
                        <option value="Bận họp khoa / đào tạo nghiệp vụ">🏢 Bận họp bộ môn / tập huấn hệ thống</option>
                        <option value="Tạm rời vị trí hỗ trợ trực tiếp">🚶 Tạm rời vị trí hỗ trợ tại phòng máy</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('dutyToggleModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Hủy bỏ
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <span>⏸️</span> Xác Nhận Tắt Ca Trực
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- FOOTER TOÀN TRƯỜNG -->
    <?php require_once $appRoot . '/includes/footer.php'; ?>

</body>
</html>
