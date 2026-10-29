<?php
/**
 * PARTIAL: TICKET QUEUE - HÀNG ĐỢI XỬ LÝ PHIẾU THEO HẠN SLA
 * Phân hệ: Agent Workspace
 * Tác giả: Nhóm 1 - ĐH Thủ Đô Hà Nội
 */
?>
<div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-5">
    
    <!-- Tiêu đề hàng đợi & Bộ lọc nhanh trạng thái -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
        <div>
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <span>⏱️</span> HÀNG ĐỢI NHIỆM VỤ ĐƯỢC PHÂN CÔNG (due_at ASC)
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Ưu tiên xử lý các sự cố sắp tới hạn SLA cam kết với Nhà trường.</p>
        </div>

        <!-- Bộ lọc tab trạng thái nhanh -->
        <div class="flex items-center gap-1.5 flex-wrap text-xs">
            <a href="index.php?agent_id=<?php echo $agentId; ?>&status=" 
               class="px-3 py-1.5 rounded-xl font-bold transition <?php echo empty($statusFilter) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                Tất cả (<?php echo $countAll; ?>)
            </a>
            <a href="index.php?agent_id=<?php echo $agentId; ?>&status=in_progress" 
               class="px-3 py-1.5 rounded-xl font-bold transition <?php echo $statusFilter === 'in_progress' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                Đang xử lý (<?php echo $countInProgress; ?>)
            </a>
            <a href="index.php?agent_id=<?php echo $agentId; ?>&status=waiting_requester" 
               class="px-3 py-1.5 rounded-xl font-bold transition <?php echo $statusFilter === 'waiting_requester' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                Chờ SV (<?php echo $countWaiting; ?>)
            </a>
            <a href="index.php?agent_id=<?php echo $agentId; ?>&status=resolved" 
               class="px-3 py-1.5 rounded-xl font-bold transition <?php echo in_array($statusFilter, ['resolved', 'closed']) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">
                Đã xong (<?php echo $countResolved; ?>)
            </a>
        </div>
    </div>

    <!-- Danh sách phiếu trong hàng đợi -->
    <?php if (empty($queue)): ?>
        <div class="text-center py-12 text-slate-400 text-xs italic bg-slate-50 rounded-2xl border border-dashed border-slate-200 space-y-2">
            <span class="text-3xl block">🎉</span>
            <div class="font-semibold text-slate-600">Hiện không có phiếu nào trong hàng đợi của Kỹ thuật viên!</div>
            <p class="text-[11px] text-slate-400">Tất cả sự cố được giao cho bạn đã được giải quyết hoặc chưa có yêu cầu mới.</p>
        </div>
    <?php else: ?>
        <div class="space-y-3.5">
            <?php foreach ($queue as $t): ?>
                <?php 
                $mins = (int)($t['minutes_remaining'] ?? 0);
                $isExpired = ($mins < 0 && !in_array($t['status'], ['resolved', 'closed']));
                $isDone = in_array($t['status'], ['resolved', 'closed']);
                
                if ($isDone) {
                    $timeNotice = '✓ ĐÃ HOÀN TẤT';
                    $timeBadge = 'bg-emerald-100 text-emerald-800 font-bold border border-emerald-200';
                } elseif ($isExpired) {
                    $timeNotice = '⚠️ QUÁ HẠN ' . abs($mins) . ' PHÚT!';
                    $timeBadge = 'bg-rose-500 text-white font-black animate-pulse';
                } elseif ($mins < 60) {
                    $timeNotice = "⏱️ GẤP: Còn {$mins} phút!";
                    $timeBadge = 'bg-amber-100 text-amber-900 font-bold border border-amber-300';
                } else {
                    $timeNotice = '⏱️ Còn ' . round($mins / 60, 1) . ' giờ';
                    $timeBadge = 'bg-slate-100 text-slate-700 font-semibold border border-slate-200';
                }

                $prioBadgeAgent = match($t['priority_code'] ?? 'MED') {
                    'URGENT' => 'bg-rose-50 text-rose-700 border-rose-200',
                    'HIGH'   => 'bg-amber-50 text-amber-800 border-amber-200',
                    'MED'    => 'bg-blue-50 text-blue-700 border-blue-200',
                    default  => 'bg-slate-50 text-slate-700 border-slate-200',
                };

                $statusBadgeAgent = match($t['status']) {
                    'new'               => 'bg-blue-50 text-blue-700 border-blue-200',
                    'in_progress'       => 'bg-amber-50 text-amber-700 border-amber-200',
                    'waiting_requester' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'resolved'          => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'closed'            => 'bg-slate-100 text-slate-700 border-slate-300',
                    default             => 'bg-slate-100 text-slate-700 border-slate-200',
                };

                $statusLabelAgent = match($t['status']) {
                    'new'               => 'Mới tiếp nhận',
                    'in_progress'       => 'Đang xử lý',
                    'waiting_requester' => 'Chờ người dùng phản hồi',
                    'resolved'          => 'Đã có giải pháp',
                    'closed'            => 'Đã đóng hoàn tất',
                    default             => $t['status'],
                };
                ?>
                <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white hover:border-indigo-300 transition shadow-xs space-y-3 text-xs">
                    
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Mã phiếu -->
                            <span class="font-mono font-bold text-xs text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl border border-indigo-200/80">
                                <?php echo htmlspecialchars($t['ticket_code']); ?>
                            </span>

                            <!-- Chuyên mục -->
                            <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700">
                                <?php echo htmlspecialchars($t['category_name']); ?>
                            </span>

                            <!-- Mức ưu tiên -->
                            <span class="px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold border <?php echo $prioBadgeAgent; ?>">
                                ● <?php echo htmlspecialchars($t['priority_name'] ?? ''); ?> (<?php echo htmlspecialchars($t['priority_code'] ?? ''); ?>)
                            </span>

                            <!-- Trạng thái -->
                            <span class="px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold border <?php echo $statusBadgeAgent; ?>">
                                <?php echo $statusLabelAgent; ?>
                            </span>

                            <!-- Badge có tệp minh chứng đính kèm -->
                            <?php if (!empty($t['attachment_path'])): ?>
                                <span class="px-2 py-0.5 rounded-lg text-[10.5px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1">
                                    <span>📎</span> Có ảnh minh chứng
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Hạn SLA -->
                        <span class="px-3 py-1 rounded-full text-[11px] <?php echo $timeBadge; ?>">
                            <?php echo $timeNotice; ?>
                        </span>
                    </div>

                    <!-- Tiêu đề & Nội dung mô tả -->
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm mb-1">
                            <a href="../tickets/show.php?id=<?php echo $t['id']; ?>&view_as=agent" class="hover:text-indigo-600 transition">
                                <?php echo htmlspecialchars($t['title']); ?>
                            </a>
                        </h3>
                        <p class="text-slate-600 line-clamp-2 leading-relaxed">
                            <?php echo htmlspecialchars($t['description']); ?>
                        </p>
                    </div>

                    <!-- Chân thẻ: Người gửi & Nút thao tác -->
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 flex-wrap gap-2 text-[11px]">
                        <div class="text-slate-500">
                            Người gửi: <strong class="text-slate-800"><?php echo htmlspecialchars($t['requester_name']); ?></strong> (<?php echo htmlspecialchars($t['requester_code'] ?? ''); ?>) 
                            • Hạn SLA: <span class="font-mono font-semibold text-slate-700"><?php echo htmlspecialchars($t['due_at']); ?></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="../tickets/show.php?id=<?php echo $t['id']; ?>&view_as=agent" 
                               class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl transition flex items-center gap-1">
                                <span>💬</span> Trao đổi & Chi tiết ➔
                            </a>
                            <?php if (!in_array($t['status'], ['resolved', 'closed'])): ?>
                                <button type="button" 
                                        onclick="openResolveModal(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['ticket_code'], ENT_QUOTES); ?>')" 
                                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-sm flex items-center gap-1">
                                    <span>✅</span> Nghiệm thu xong
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
