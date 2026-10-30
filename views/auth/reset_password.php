<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="my-auto py-8 flex items-center justify-center">
    <div class="w-full max-w-md bg-white border border-slate-200/80 rounded-3xl shadow-2xl shadow-indigo-950/10 overflow-hidden transition-all">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 md:p-8 text-center border-b-2 border-sky-500 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-32 h-32 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">
                    🔑 THIẾT LẬP BẢO MẬT
                </span>
                <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    ĐẶT LẠI MẬT KHẨU MỚI
                </h2>
                <p class="text-xs text-slate-300">
                    Nhập mật khẩu mới an toàn để hoàn tất khôi phục tài khoản
                </p>
            </div>
        </div>

        <!-- Body Form -->
        <div class="p-6 md:p-8 space-y-5">
            <form method="POST" action="<?= $appBase ?>reset-password" class="space-y-4">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Mật khẩu mới (Tối thiểu 6 ký tự) (*):
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            🔒
                        </span>
                        <input type="password" name="new_password" id="reset-new-password" required minlength="6" autofocus autocomplete="new-password"
                               class="w-full pl-10 pr-12 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder:text-slate-400"
                               placeholder="Nhập mật khẩu mới an toàn">
                        <button type="button" data-password-toggle aria-controls="reset-new-password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden absolute inset-y-0 right-0 px-3.5 items-center text-slate-500 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-r-xl">
                            <svg data-password-show-icon aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            <svg data-password-hide-icon aria-hidden="true" class="hidden w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Xác nhận lại mật khẩu mới (*):
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            🔒
                        </span>
                        <input type="password" name="confirm_password" id="reset-confirm-password" required minlength="6" autocomplete="new-password"
                               class="w-full pl-10 pr-12 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder:text-slate-400"
                               placeholder="Gõ lại mật khẩu mới vừa nhập">
                        <button type="button" data-password-toggle aria-controls="reset-confirm-password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden absolute inset-y-0 right-0 px-3.5 items-center text-slate-500 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-r-xl">
                            <svg data-password-show-icon aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            <svg data-password-hide-icon aria-hidden="true" class="hidden w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                    <span>Xác Nhận & Cập Nhật Mật Khẩu</span>
                    <span>➔</span>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 text-center">
                <a href="<?= $appBase ?>login" class="text-xs text-slate-500 hover:text-slate-700 font-bold hover:underline transition">
                    ← Hủy thao tác và quay lại Đăng nhập
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
