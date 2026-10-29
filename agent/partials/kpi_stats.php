<?php
/**
 * PARTIAL: KPI STATS - THỐNG KÊ PHIẾU CỦA RIÊNG KỸ THUẬT VIÊN
 * Phân hệ: Agent Workspace
 * Tác giả: Nhóm 1 - ĐH Thủ Đô Hà Nội
 */
?>
<!-- THẺ THỐNG KÊ NHIỆM VỤ CỦA RIÊNG KTV NÀY -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <!-- 1. Tổng số phiếu của riêng KTV -->
    <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Tổng Phiếu Của Tôi</span>
            <span class="text-xl">📋</span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-black text-slate-900 font-mono"><?php echo $countAll; ?></span>
            <span class="text-xs text-slate-500 font-medium">phiếu được giao</span>
        </div>
        <p class="text-[11px] text-slate-400">Toàn bộ lịch sử các phiếu KTV được chỉ định phụ trách</p>
    </div>

    <!-- 2. Số phiếu CHƯA hoàn thành (Đang xử lý / Mới / Chờ người dùng) -->
    <div class="bg-white border border-amber-200/80 rounded-3xl p-5 shadow-sm space-y-2 bg-gradient-to-br from-white to-amber-50/30">
        <div class="flex items-center justify-between text-amber-600">
            <span class="text-xs font-bold uppercase tracking-wider">Chưa Hoàn Thành</span>
            <span class="text-xl">⏳</span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-black text-amber-600 font-mono"><?php echo $countActive; ?></span>
            <span class="text-xs text-amber-700 font-semibold">phiếu đang mở</span>
        </div>
        <div class="text-[11px] text-slate-500 flex items-center gap-2">
            <span>Mới: <strong><?php echo $countNew; ?></strong></span>
            <span>•</span>
            <span>Đang xử lý: <strong><?php echo $countInProgress; ?></strong></span>
            <span>•</span>
            <span>Chờ SV: <strong><?php echo $countWaiting; ?></strong></span>
        </div>
    </div>

    <!-- 3. Số phiếu ĐÃ hoàn thành (Resolved / Closed) -->
    <div class="bg-white border border-emerald-200/80 rounded-3xl p-5 shadow-sm space-y-2 bg-gradient-to-br from-white to-emerald-50/30">
        <div class="flex items-center justify-between text-emerald-600">
            <span class="text-xs font-bold uppercase tracking-wider">Đã Hoàn Thành</span>
            <span class="text-xl">✅</span>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-black text-emerald-600 font-mono"><?php echo $countResolved; ?></span>
            <span class="text-xs text-emerald-700 font-semibold">phiếu hoàn tất</span>
        </div>
        <p class="text-[11px] text-slate-400">Đã cung cấp giải pháp kỹ thuật & đóng thành công</p>
    </div>

    <!-- 4. Tải trọng hiện tại & Ngưỡng kịch tải 15 phiếu -->
    <?php
    $cap = !empty($currentAgent['max_capacity']) ? (int)$currentAgent['max_capacity'] : 15;
    $loadPercent = min(100, round(($countActive / $cap) * 100));
    $isOverflow = ($countActive >= $cap);
    $loadColor = $isOverflow ? 'text-rose-600' : ($countActive >= 10 ? 'text-amber-600' : 'text-indigo-600');
    $barColor  = $isOverflow ? 'bg-rose-500' : ($countActive >= 10 ? 'bg-amber-500' : 'bg-indigo-600');
    ?>
    <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-2">
        <div class="flex items-center justify-between text-slate-400">
            <span class="text-xs font-bold uppercase tracking-wider">Tải Trọng Trực Ca</span>
            <span class="text-xl">⚙️</span>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="text-3xl font-black <?php echo $loadColor; ?> font-mono">
                <?php echo $countActive; ?><span class="text-sm font-bold text-slate-400">/<?php echo $cap; ?></span>
            </span>
            <span class="text-xs font-bold <?php echo $isOverflow ? 'text-rose-600' : 'text-slate-500'; ?>">
                <?php echo $loadPercent; ?>% Tải
            </span>
        </div>
        <!-- Progress bar -->
        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
            <div class="<?php echo $barColor; ?> h-full rounded-full transition-all duration-500" style="width: <?php echo $loadPercent; ?>%"></div>
        </div>
        <p class="text-[10.5px] text-slate-400">
            Ngưỡng điều phối tràn tải chuẩn: <strong>≥ 15 phiếu / KTV</strong>
        </p>
    </div>

</div>
