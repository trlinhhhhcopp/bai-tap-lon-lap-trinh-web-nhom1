<?php
/**
 * PARTIAL: RESOLVE MODAL - BIÊN BẢN NGHIỆM THU GIẢI PHÁP KỸ THUẬT
 * Phân hệ: Agent Workspace
 * Tác giả: Nhóm 1 - ĐH Thủ Đô Hà Nội
 */
?>
<!-- MODAL NGHIỆM THU GIẢI PHÁP KỸ THUẬT -->
<div id="resolveModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-1.5">
                <span>✅</span> Lập Biên Bản Nghiệm Thu (<span id="modalTicketCode" class="text-indigo-600 font-mono"></span>)
            </h3>
            <button type="button" onclick="closeResolveModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
        </div>
        <form method="POST" action="index.php?agent_id=<?php echo $agentId; ?>" class="space-y-4">
            <input type="hidden" name="action" value="resolve_ticket" />
            <input type="hidden" name="ticket_id" id="modalTicketId" value="0" />
            
            <div>
                <label class="block font-bold text-slate-700 mb-1">Mô tả phương án xử lý / Hướng dẫn sinh viên kiểm tra: <span class="text-rose-500">*</span></label>
                <textarea name="solution_note" rows="4" required 
                          placeholder="Ví dụ: Đã xóa cache tài khoản, reset mật khẩu tạm thời và giải phóng địa chỉ IP mạng KTX..." 
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:outline-none focus:bg-white transition text-xs leading-relaxed"></textarea>
            </div>

            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-emerald-800 leading-relaxed">
                💡 Sau khi xác nhận, phiếu sẽ chuyển sang trạng thái <strong>Đã có giải pháp (resolved)</strong> và gửi thông báo để sinh viên nghiệm thu & đánh giá CSAT (1–5 sao).
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" onclick="closeResolveModal()" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-700 font-semibold hover:bg-slate-50">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <span>✅</span> Xác Nhận Đã Giải Quyết Xong
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(id, code) {
    document.getElementById('modalTicketId').value = id;
    document.getElementById('modalTicketCode').textContent = code;
    document.getElementById('resolveModal').classList.remove('hidden');
}
function closeResolveModal() {
    document.getElementById('resolveModal').classList.add('hidden');
}
</script>
