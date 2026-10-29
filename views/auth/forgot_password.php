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
                    Gửi thư xác thực khôi phục mật khẩu vào Hòm thư nội bộ (@daihocthudo.edu.vn)
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
                               placeholder="VD: 224001736@daihocthudo.edu.vn">
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">
                        * Nhập chính xác địa chỉ Email trường cấp có đuôi @daihocthudo.edu.vn
                    </span>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-sky-500 via-sky-600 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-sky-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                    <span>Gửi Thư Khôi Phục Mật Khẩu</span>
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
        <!-- THÔNG BÁO VÀ HƯỚNG DẪN MỞ HÒM THƯ NỘI BỘ (KHÔNG IN LỘ LINK ĐỔI MẬT KHẨU TẠI ĐÂY) -->
        <div class="w-full max-w-md mt-6 bg-indigo-50/90 border border-indigo-200 rounded-3xl p-6 shadow-xl shadow-indigo-500/10 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl shrink-0">
                    📬
                </div>
                <div>
                    <h3 class="text-sm font-black text-indigo-950">Đã gửi thư vào Hòm thư nội bộ!</h3>
                    <p class="text-xs text-indigo-800">Email: <strong class="font-mono"><?= htmlspecialchars($simulatedEmail['email']) ?></strong></p>
                </div>
            </div>

            <div class="bg-white/90 p-4 rounded-2xl text-xs text-slate-700 space-y-2.5 border border-indigo-100 leading-relaxed shadow-2xs">
                <p class="font-bold text-slate-900">🔒 Bảo mật tài khoản học thuật:</p>
                <p class="text-slate-600">
                    Hệ thống đã gửi thư xác thực kèm liên kết đổi mật khẩu an toàn đến đúng Hòm thư điện tử nội bộ <strong><?= htmlspecialchars($simulatedEmail['email']) ?></strong>.
                    Vì nguyên tắc bảo mật, liên kết khôi phục <strong>không hiển thị trực tiếp tại trang này</strong>.
                </p>
                <div class="p-2.5 bg-indigo-50 text-indigo-900 rounded-xl text-[11px] border border-indigo-200">
                    💡 Bạn vui lòng bấm nút bên dưới để mở Hòm thư nội bộ Webmail, mở thư mới nhận và bấm nút xác nhận đặt lại mật khẩu.
                </div>
            </div>

            <div class="text-center pt-1">
                <a href="https://mail.google.com" target="_blank" rel="noopener noreferrer" 
                   class="inline-flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition">
                    <span>📬 MỞ HỘP THƯ GMAIL (MAIL.GOOGLE.COM ↗)</span>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
