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
                    Nhận liên kết đổi mật khẩu an toàn qua Email trường cấp (@daihocthudo.edu.vn)
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
        <!-- THÔNG BÁO VÀ HƯỚNG DẪN ĐỔI MẬT KHẨU NHANH -->
        <div class="w-full max-w-md mt-6 bg-amber-50/90 border-2 border-amber-300 rounded-3xl p-6 shadow-xl shadow-amber-500/10 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shrink-0">
                    🔑
                </div>
                <div>
                    <h3 class="text-sm font-black text-amber-950">Mã Xác Thực Đặt Lại Mật Khẩu</h3>
                    <p class="text-xs text-amber-800">Email: <strong class="font-mono"><?= htmlspecialchars($simulatedEmail['email']) ?></strong></p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl text-xs text-slate-700 space-y-2.5 border border-amber-200 leading-relaxed shadow-2xs">
                <p class="font-bold text-slate-900">Kính gửi bạn <?= htmlspecialchars($simulatedEmail['name']) ?>:</p>
                <p class="text-slate-600">
                    Hệ thống đã tạo liên kết đặt lại mật khẩu cho tài khoản sinh viên của bạn. Vui lòng bấm vào nút bên dưới để tiến hành đổi mật khẩu mới:
                </p>
                <div class="text-center py-1">
                    <a href="<?= htmlspecialchars($simulatedEmail['link']) ?>" 
                       class="inline-flex items-center justify-center gap-2 w-full py-3 bg-gradient-to-r from-amber-500 to-indigo-600 hover:from-amber-600 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                        <span>🔑 ĐẶT LẠI MẬT KHẨU NGAY ➔</span>
                    </a>
                </div>
                <div class="p-2 bg-amber-50 text-amber-900 rounded-xl text-[11px] border border-amber-200 text-center">
                    ⏱️ Liên kết có hiệu lực trong vòng 15 phút (đến <?= htmlspecialchars($simulatedEmail['expire_time']) ?>).
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
