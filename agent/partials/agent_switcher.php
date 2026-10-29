<?php
/**
 * PARTIAL: AGENT SWITCHER - THANH CHUYỂN ĐỔI 5 KTV DÀNH CHO ADMIN
 * Phân hệ: Agent Workspace
 * Tác giả: Nhóm 1 - ĐH Thủ Đô Hà Nội
 */
?>
<!-- DÀNH CHO ADMIN: THANH CHUYỂN ĐỔI 5 KTV CHUYÊN TRÁCH & TẢI TRỌNG -->
<div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3">
    <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <span>👥</span> Đội Ngũ 5 Kỹ Thuật Viên Chuyên Trách & Tình Trạng Tải Hiện Tại:
        </h3>
        <span class="text-[11px] text-slate-400">Giới hạn tải tối đa: <strong>15 phiếu / KTV</strong></span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
        <?php foreach ($allAgents as $ag): ?>
            <?php 
            $isSelected = ($ag['id'] == $agentId);
            $cap = !empty($ag['max_capacity']) ? (int)$ag['max_capacity'] : 15;
            $loadPercent = min(100, round(($ag['active_count'] / $cap) * 100));
            $loadColor = $ag['active_count'] >= $cap ? 'bg-rose-500 text-white' : ($ag['active_count'] >= 10 ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white');
            $cardBorder = $isSelected ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-500/20' : 'border-slate-200 hover:border-slate-300 bg-slate-50';
            ?>
            <a href="index.php?agent_id=<?php echo $ag['id']; ?>" class="p-3.5 rounded-2xl border <?php echo $cardBorder; ?> transition text-xs block space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900"><?php echo htmlspecialchars($ag['user_code']); ?></span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $loadColor; ?>">
                        <?php echo $ag['active_count']; ?>/<?php echo $cap; ?>
                    </span>
                </div>
                <div>
                    <div class="font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($ag['name']); ?></div>
                    <div class="text-[10.5px] text-slate-500 truncate"><?php echo htmlspecialchars($ag['specialty_name']); ?></div>
                </div>
                <!-- Thanh tiến trình tải -->
                <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                    <div class="<?php echo $ag['active_count'] >= $cap ? 'bg-rose-500' : ($ag['active_count'] >= 10 ? 'bg-amber-500' : 'bg-emerald-500'); ?> h-full rounded-full transition-all" style="width: <?php echo $loadPercent; ?>%"></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
