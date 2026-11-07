<?php
$appRoot = dirname(__DIR__, 2);
require $appRoot . '/includes/navbar.php';

$step = $_GET['step'] ?? 'input_identifier';
$activeOtp = $_SESSION['reset_otp_data'] ?? null;
$hasOtp = ($activeOtp && time() <= ($activeOtp['expires_at'] ?? 0) && empty($activeOtp['verified']));
?>

<div class="my-auto py-8 flex flex-col items-center justify-center">
    <div class="w-full max-w-xl bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                🔑 SSPR · KHÔI PHỤC MẬT KHẨU QUA EMAIL TRƯỜNG CẤP
            </span>
            <h2 class="text-xl md:text-2xl font-black text-slate-900 mt-2">
                Quên Mật Khẩu Tài Khoản
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Mã OTP gồm 6 chữ cái in hoa sẽ được gửi tới Email trường của bạn (Hiệu lực: 15 phút).
            </p>
        </div>

        <?php if ($hasOtp || $step === 'enter_otp'): ?>
            <!-- 1. KHUNG THÔNG BÁO GỬI MÃ OTP VỀ HÒM THƯ -->
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 sm:p-5 space-y-3.5 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <span class="text-xs font-bold text-sky-950 flex items-center gap-1.5">
                        <span class="text-base">📬</span> HỘP THƯ TRƯỜNG NHẬN MÃ XÁC THỰC OTP
                    </span>
                    <span class="text-[11px] font-bold text-sky-800 bg-white px-2.5 py-1 rounded-full border border-sky-200 shadow-2xs">
                        Hiệu lực mã: 15 phút
                    </span>
                </div>

                <div class="bg-white rounded-xl p-4 border border-sky-100 text-xs space-y-2.5 shadow-2xs">
                    <div class="space-y-1 pb-2 border-b border-slate-100 text-[11.5px]">
                        <div class="text-slate-700">
                            Chủ tài khoản: <strong class="text-slate-900"><?= htmlspecialchars($activeOtp['user_name'] ?? 'Sinh viên'); ?></strong> 
                            (Mã: <code class="text-indigo-600 font-bold"><?= htmlspecialchars($activeOtp['user_code'] ?? ''); ?></code>)
                        </div>
                        <div class="text-slate-600">
                            Hòm thư trường nhận mã: <strong class="text-sky-800 font-mono text-sm"><?= htmlspecialchars($activeOtp['target_email'] ?? $activeOtp['email'] ?? ''); ?></strong>
                        </div>
                    </div>
                    
                    <p class="text-slate-600 leading-relaxed text-[11.5px]">
                        🔒 <strong>Bảo mật tài khoản:</strong> Hệ thống đã gửi thư xác thực kèm mã OTP gồm <strong>6 chữ cái in hoa</strong> đến hòm thư trường trên. Bạn hãy mở hòm thư của mình (hộp thư đến hoặc mục Thư rác/Spam), lấy đúng 6 chữ cái và nhập vào ô xác thực bên dưới.
                    </p>
                </div>
            </div>

            <!-- 2. FORM NHẬP MÃ OTP CHỮ CÁI -->
            <form method="POST" action="<?= $appBase ?>forgot-password" class="space-y-4 text-xs pt-1">
                <input type="hidden" name="step" value="verify_otp" />

                <div>
                    <label class="block font-bold text-slate-800 mb-1.5">
                        Nhập mã OTP gồm 6 chữ cái xác thực:
                    </label>
                    <input type="text" name="otp_input" required maxlength="6" autofocus placeholder="VD: TUDKHA (6 chữ cái)" 
                           style="text-transform: uppercase; letter-spacing: 0.35em;"
                           class="w-full px-4 py-3 bg-slate-50 border-2 border-indigo-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-center font-mono text-2xl font-black text-indigo-950 uppercase" />
                    <p class="text-[11px] text-slate-400 mt-1 text-center">Hệ thống tự động chuyển thành chữ in hoa khi bạn gõ.</p>
                </div>

                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2 text-xs cursor-pointer">
                    <span>✅</span> Xác Nhận Mã OTP Để Đổi Mật Khẩu
                </button>
            </form>

            <div class="flex items-center justify-between text-[11px] pt-1">
                <form method="POST" action="<?= $appBase ?>forgot-password" class="inline">
                    <input type="hidden" name="step" value="send_otp" />
                    <input type="hidden" name="identifier" value="<?= htmlspecialchars($activeOtp['user_code'] ?? $activeOtp['email'] ?? ''); ?>" />
                    <button type="submit" class="text-indigo-600 hover:underline font-semibold flex items-center gap-1 cursor-pointer">
                        <span>🔄</span> Gửi lại mã OTP khác
                    </button>
                </form>
                <a href="<?= $appBase ?>forgot-password?step=input_identifier" class="text-slate-500 hover:underline">
                    ← Đổi tài khoản khác
                </a>
            </div>

        <?php else: ?>
            <!-- FORM NHẬP MÃ SINH VIÊN HOẶC EMAIL TRƯỜNG -->
            <form method="POST" action="<?= $appBase ?>forgot-password" class="space-y-4 text-xs">
                <input type="hidden" name="step" value="send_otp" />

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Nhập Mã Sinh Viên hoặc Email trường của bạn:
                    </label>
                    <input type="text" name="identifier" required autofocus placeholder="Ví dụ: 224001736 hoặc 224001729@daihocthudo.edu.vn" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                    <p class="text-[11px] text-slate-400 mt-1">Hệ thống sẽ gửi mã OTP gồm các chữ cái về địa chỉ Email trường cấp của bạn.</p>
                </div>

                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2 text-xs cursor-pointer">
                    <span>🚀</span> Gửi Mã OTP Đến Email Trường (15 Phút)
                </button>
            </form>
        <?php endif; ?>

        <div class="pt-3 border-t border-slate-100 text-center">
            <a href="<?= $appBase ?>login" class="text-xs text-indigo-600 hover:underline font-semibold flex items-center justify-center gap-1">
                <span>←</span> Quay lại màn hình Đăng Nhập
            </a>
        </div>
    </div>
</div>

<?php require $appRoot . '/includes/footer.php'; ?>
