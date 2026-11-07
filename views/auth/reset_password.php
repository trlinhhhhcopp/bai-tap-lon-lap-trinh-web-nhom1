<?php
$appRoot = dirname(__DIR__, 2);
require $appRoot . '/includes/navbar.php';

$otpData = $_SESSION['reset_otp_data'] ?? null;
$token = $token ?? ($_GET['token'] ?? '');
?>

<div class="my-auto py-8 flex items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                🛡️ ĐỔI MẬT KHẨU MỚI
            </span>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 mt-2">
                Thiết Lập Mật Khẩu Mới
            </h2>
            <?php if (!empty($otpData['user_name'])): ?>
                <p class="text-xs text-slate-500 mt-1">
                    Tài khoản: <strong><?= htmlspecialchars($otpData['user_name']); ?></strong> (<?= htmlspecialchars($otpData['user_code']); ?>)
                </p>
            <?php else: ?>
                <p class="text-xs text-slate-500 mt-1">
                    Nhập mật khẩu mới an toàn để hoàn tất khôi phục tài khoản.
                </p>
            <?php endif; ?>
        </div>

        <form method="POST" action="<?= $appBase ?>reset-password" class="space-y-4 text-xs">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Mật khẩu mới (Tối thiểu 6 ký tự): <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <input id="reset-new-password" type="password" name="new_password" required minlength="6" autofocus autocomplete="new-password" placeholder="Nhập mật khẩu mới..."
                           class="w-full px-4 pr-12 py-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                    <button type="button" data-password-toggle aria-controls="reset-new-password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500 rounded-r-xl">
                        <svg data-password-show-icon aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        <svg data-password-hide-icon aria-hidden="true" class="hidden w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Xác nhận lại mật khẩu mới: <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <input id="reset-confirm-password" type="password" name="confirm_password" required minlength="6" autocomplete="new-password" placeholder="Nhập lại mật khẩu mới..."
                           class="w-full px-4 pr-12 py-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                    <button type="button" data-password-toggle aria-controls="reset-confirm-password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500 rounded-r-xl">
                        <svg data-password-show-icon aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        <svg data-password-hide-icon aria-hidden="true" class="hidden w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-md shadow-emerald-100 flex items-center justify-center gap-2 text-xs cursor-pointer">
                <span>✅</span> Cập Nhật Mật Khẩu &amp; Đăng Nhập
            </button>
        </form>

        <div class="pt-3 border-t border-slate-100 text-center">
            <a href="<?= $appBase ?>login" class="text-xs text-slate-500 hover:text-slate-700 font-semibold hover:underline transition">
                ← Hủy thao tác và quay lại Đăng nhập
            </a>
        </div>
    </div>
</div>

<?php require $appRoot . '/includes/footer.php'; ?>
