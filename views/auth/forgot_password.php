<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="my-auto py-8 flex flex-col items-center justify-center">
    <div class="w-full max-w-md bg-white border border-slate-200/80 rounded-3xl shadow-2xl shadow-indigo-950/10 overflow-hidden transition-all">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 md:p-8 text-center border-b-2 border-sky-500 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-32 h-32 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">
                    🔐 XÁC THỰC BẢO MẬT SSPR
                </span>
                <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    KHÔI PHỤC MẬT KHẨU
                </h2>
                <p class="text-xs text-slate-300">
                    Cấp liên kết đổi mật khẩu bảo mật (Hiệu lực chuẩn 15 phút)
                </p>
            </div>
        </div>

        <!-- Body Form -->
        <div class="p-6 md:p-8 space-y-5">
            <form method="POST" action="<?= $appBase ?>forgot-password" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Email trường cấp (@daihocthudo.edu.vn) (*):
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            ✉️
                        </span>
                        <input type="email" name="email" required autofocus
                               class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder:text-slate-400"
                               placeholder="VD: 224001729@daihocthudo.edu.vn">
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">
                        * Nhập chính xác địa chỉ Email trường cấp có đuôi @daihocthudo.edu.vn
                    </span>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-sky-500 via-sky-600 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-sky-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                    <span>Gửi Liên Kết Đổi Mật Khẩu</span>
                    <span>➔</span>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 text-center">
                <a href="<?= $appBase ?>login" class="text-xs text-sky-600 hover:text-sky-700 font-bold hover:underline transition inline-flex items-center gap-1">
                    <span>←</span> Quay lại trang Đăng nhập
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($simulatedEmail)): ?>
        <?php if (!empty($simulatedEmail['is_sent_real'])): ?>
            <!-- ĐÃ GỬI EMAIL THẬT TỚI GMAIL -->
            <div class="w-full max-w-md mt-6 bg-emerald-50/90 border border-emerald-200 rounded-3xl p-6 shadow-xl shadow-emerald-500/10 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                        📬
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-emerald-950">Đã gửi email khôi phục thành công!</h3>
                        <p class="text-xs text-emerald-800">Email: <strong class="font-mono"><?= htmlspecialchars($simulatedEmail['email']) ?></strong></p>
                    </div>
                </div>

                <div class="bg-white/80 p-4 rounded-2xl text-xs text-slate-700 space-y-2 border border-emerald-100 leading-relaxed">
                    <p>👉 <strong>Bước tiếp theo:</strong></p>
                    <ol class="list-decimal pl-4 space-y-1 text-slate-600">
                        <li>Truy cập vào ứng dụng <strong>Gmail</strong> trên điện thoại hoặc trình duyệt web.</li>
                        <li>Mở bức thư mới từ <strong>Trung tâm CNTT - ĐH Thủ Đô Hà Nội</strong>.</li>
                        <li>Nhấn nút <strong>"🔑 ĐẶT LẠI MẬT KHẨU NGAY"</strong> bên trong email để mở tab thiết lập mật khẩu mới.</li>
                    </ol>
                    <p class="text-[11px] text-amber-800 bg-amber-50 p-2.5 rounded-xl border border-amber-200 mt-2">
                        💡 <strong>Lưu ý:</strong> Nếu chưa thấy ở Hộp thư đến, bạn hãy mở mục <strong>Thư rác (Spam)</strong> và bấm chọn <em>"Không phải spam"</em> nhé!
                    </p>
                </div>

                <div class="text-center pt-1">
                    <a href="https://mail.google.com" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition">
                        <span>📬 Mở Hộp Thư Gmail (mail.google.com)</span>
                        <span>➔</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Fallback mô phỏng khi chưa bật SMTP -->
            <div class="w-full max-w-lg mt-6 bg-white border border-sky-200 rounded-3xl shadow-xl shadow-sky-500/10 overflow-hidden animate-bounce-in">
                <div class="bg-sky-50/80 border-b border-sky-100 px-5 py-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📬</span>
                        <span class="font-bold text-sky-900 text-xs md:text-sm">Hòm Thư Webmail Mô Phỏng (Local Test)</span>
                    </div>
                    <span class="text-[11px] bg-sky-200/60 text-sky-800 px-2.5 py-0.5 rounded-full font-bold">1 thư mới</span>
                </div>
                <div class="p-5 md:p-6 space-y-4 text-xs md:text-sm text-slate-700 leading-relaxed">
                    <div class="space-y-1 pb-3 border-b border-dashed border-slate-200 text-slate-500 text-xs">
                        <div><strong>Người gửi:</strong> Trung tâm CNTT - ĐH Thủ Đô Hà Nội &lt;helpdesk@daihocthudo.edu.vn&gt;</div>
                        <div><strong>Người nhận:</strong> <?= htmlspecialchars($simulatedEmail['name']) ?> &lt;<?= htmlspecialchars($simulatedEmail['email']) ?>&gt;</div>
                        <div><strong>Tiêu đề:</strong> [Helpdesk TUD] Hướng dẫn khôi phục mật khẩu tài khoản sinh viên</div>
                    </div>

                    <p>Xin chào <strong><?= htmlspecialchars($simulatedEmail['name']) ?></strong>,</p>
                    <p>Hệ thống vừa tiếp nhận yêu cầu đặt lại mật khẩu cho tài khoản <strong><?= htmlspecialchars($simulatedEmail['email']) ?></strong>. Bấm vào nút bên dưới để mở tab đổi mật khẩu:</p>

                    <div class="text-center py-2">
                        <a href="<?= htmlspecialchars($simulatedEmail['link']) ?>" target="_blank" rel="noopener noreferrer" 
                           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-bold rounded-xl shadow-md shadow-sky-500/25 transition">
                            <span>🔑 Đặt Lại Mật Khẩu Ngay (Mở Tab Mới)</span>
                            <span>➔</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
