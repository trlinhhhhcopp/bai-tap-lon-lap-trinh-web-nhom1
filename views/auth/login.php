<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * GIAO DIỆN ĐĂNG NHẬP XÁC THỰC BẢO MẬT (CHUẨN LUỒNG 1 ĐẦY ĐỦ NỘI DUNG)
 * ==============================================================================
 */
if (!isset($appBase)) {
    $appBase = get_app_base_url();
}
$flash = $flash ?? get_flash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Hệ Thống Helpdesk CNTT ĐH Thủ Đô Hà Nội</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Header Khách -->
    <?php require __DIR__ . '/../../includes/navbar.php'; ?>

    <!-- Khu vực Đăng nhập trung tâm -->
    <div class="flex-1 max-w-5xl mx-auto w-full px-4 py-8 flex flex-col justify-center">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">

            <!-- Cột giới thiệu hệ thống (5 cols) -->
            <div class="order-2 md:order-1 md:col-span-5 text-white space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-bold">
                    🏛️ ĐẠI HỌC THỦ ĐÔ HÀ NỘI
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight leading-tight">
                    CỔNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT
                </h1>
                <p class="text-xs text-indigo-200/80 leading-relaxed">
                    Hệ thống dịch vụ số tiếp nhận sự cố kỹ thuật từ xa theo chuẩn ITIL v4. Tự động phân cấp mức ưu tiên Auto-Priority, hàng đợi SLA thời gian thực và quản lý đào tạo học thuật.
                </p>
                <div class="p-4 bg-slate-800/80 rounded-2xl border border-slate-700/60 text-xs text-slate-300 space-y-2">
                    <p class="font-bold text-white flex items-center gap-1.5">
                        <span>🔒</span> Yêu Cầu Xác Thực Tài Khoản:
                    </p>
                    <p class="text-[11.5px] text-slate-400 leading-relaxed">
                        Để bảo mật thông tin nội bộ và cách ly dữ liệu sự cố, bạn bắt buộc phải đăng nhập bằng tài khoản trường cấp để truy cập vào hệ thống.
                    </p>
                </div>
            </div>

            <!-- Cột Form Đăng Nhập (7 cols) -->
            <div class="order-1 md:order-2 md:col-span-7 bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-5">

                <div>
                    <h2 class="text-xl font-black text-slate-900">Đăng Nhập Tài Khoản</h2>
                    <p class="text-xs text-slate-500 mt-1">Dành cho Sinh viên, Giảng viên, Kỹ thuật viên & Quản trị viên</p>
                </div>

                <?php if (!empty($currentUser)): 
                    $dashUrl = $appBase . 'student/dashboard';
                    if (($currentUser['role'] ?? '') === 'admin') $dashUrl = $appBase . 'admin/dashboard';
                    elseif (($currentUser['role'] ?? '') === 'agent') $dashUrl = $appBase . 'agent/workbench';
                    elseif (($currentUser['role'] ?? '') === 'lecturer' || ($currentUser['user_type'] ?? '') === 'lecturer') $dashUrl = $appBase . 'lecturer/dashboard';
                ?>
                    <div class="p-3.5 bg-indigo-50 border border-indigo-200 rounded-2xl text-xs text-indigo-900 flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <span>👤 Đang đăng nhập: <strong><?= htmlspecialchars($currentUser['name']) ?></strong> (<?= htmlspecialchars($currentUser['user_code']) ?>)</span>
                            <span class="block text-[11px] text-indigo-600 mt-0.5">Bạn có thể đăng nhập tài khoản khác bên dưới hoặc tiếp tục làm việc.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="<?= $dashUrl ?>" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-2xs">
                                Vào Bàn Làm Việc →
                            </a>
                            <a href="<?= $appBase ?>logout" class="px-2.5 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold rounded-xl text-xs transition">
                                Đăng Xuất
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flash)): ?>
                    <div role="alert" data-auto-dismiss="4500" class="p-3.5 rounded-xl text-xs font-semibold <?= $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?= $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <!-- Form đăng nhập chuẩn -->
                <form method="POST" action="<?= $appBase ?>login" class="space-y-4 text-sm">
                    <div>
                        <label for="login-identifier" class="block font-bold text-slate-700 mb-1.5">Mã tài khoản hoặc email trường</label>
                        <input id="login-identifier" type="text" name="identifier" required autocomplete="username" autocapitalize="off" spellcheck="false" placeholder="Ví dụ: 224001736 hoặc email trường"
                               class="w-full px-4 py-3 text-slate-900 placeholder:text-slate-500 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="login-password" class="font-bold text-slate-700">Mật khẩu</label>
                            <a href="<?= $appBase ?>forgot-password" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                Quên mật khẩu?
                            </a>
                        </div>
                        <div class="relative">
                            <input id="login-password" type="password" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu của bạn"
                                   class="w-full px-4 pr-12 py-3 text-slate-900 placeholder:text-slate-500 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                            <button type="button" onclick="toggleLoginPassword(this)" aria-controls="login-password" aria-label="Hiện mật khẩu" title="Hiện mật khẩu"
                                    class="absolute inset-y-0 right-0 px-3.5 flex items-center justify-center text-slate-500 hover:text-slate-800 focus:outline-none cursor-pointer z-10 text-lg transition">
                                <span id="pwd-icon">👁️</span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl transition shadow-lg shadow-indigo-100 flex items-center justify-center gap-2 cursor-pointer">
                        <span>Đăng Nhập Ngay</span>
                        <span>→</span>
                    </button>
                </form>

                <!-- 1-Click Quick Login cho các tài khoản mẫu -->
                <details open class="group bg-slate-50 rounded-2xl border border-slate-200/90 text-xs">
                    <summary class="p-3.5 font-bold text-slate-700 cursor-pointer list-none flex items-center justify-between select-none hover:bg-slate-100/70 transition rounded-2xl">
                        <span class="flex items-center gap-2">
                            <span>⚡</span> Đăng Nhập Nhanh 1-Click (Tài khoản mẫu đồ án)
                        </span>
                        <span class="text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <div class="p-3.5 pt-2 space-y-3 text-xs border-t border-slate-200/60 mt-1">
                        <!-- 5 Sinh viên Nhóm 1 -->
                        <div>
                            <span class="text-[10.5px] font-bold uppercase tracking-wider text-indigo-700 block mb-1.5 flex items-center gap-1">
                                <span>🎓</span> 5 Sinh Viên Nhóm 1 (Học Phần &amp; CSAT):
                            </span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                                <a href="<?= $appBase ?>quick-login?code=224001736" class="p-2 rounded-xl bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>👑</span> <span>SV Thanh Ngân</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=224001729" class="p-2 rounded-xl bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>🎓</span> <span>SV Thục Linh</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=224001735" class="p-2 rounded-xl bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>🎓</span> <span>SV Hà My</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=224001742" class="p-2 rounded-xl bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>🎓</span> <span>SV Ánh Nhi</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=224001746" class="p-2 rounded-xl bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>🎓</span> <span>SV Công Thành</span>
                                </a>
                            </div>
                        </div>

                        <!-- 5 Kỹ thuật viên (KTV) Chuyên trách ITIL -->
                        <div>
                            <span class="text-[10.5px] font-bold uppercase tracking-wider text-emerald-700 block mb-1.5 flex items-center gap-1">
                                <span>👨‍💻</span> 5 Kỹ Thuật Viên Chuyên Trách (Hàng Đợi &amp; Triage):
                            </span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                                <a href="<?= $appBase ?>quick-login?code=KTV001" class="p-2 rounded-xl bg-white hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>⚡</span> <span>KTV An (SSO)</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=KTV002" class="p-2 rounded-xl bg-white hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>⚡</span> <span>KTV Bích (Mail)</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=KTV003" class="p-2 rounded-xl bg-white hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>⚡</span> <span>KTV Cường (QLĐT)</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=KTV004" class="p-2 rounded-xl bg-white hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>⚡</span> <span>KTV Dũng (Canvas)</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=KTV005" class="p-2 rounded-xl bg-white hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>⚡</span> <span>KTV Én (Mạng/Wi-Fi)</span>
                                </a>
                            </div>
                        </div>

                        <!-- Giảng viên & Quản trị viên -->
                        <div>
                            <span class="text-[10.5px] font-bold uppercase tracking-wider text-purple-700 block mb-1.5 flex items-center gap-1">
                                <span>🛡️</span> Giảng Viên &amp; Ban Quản Trị Hệ Thống:
                            </span>
                            <div class="grid grid-cols-2 gap-1.5">
                                <a href="<?= $appBase ?>quick-login?code=GV001" class="p-2 rounded-xl bg-white hover:bg-amber-50 border border-slate-200 hover:border-amber-300 text-slate-700 hover:text-amber-800 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>👨‍🏫</span> <span>GV TS. Nguyễn Văn Giảng</span>
                                </a>
                                <a href="<?= $appBase ?>quick-login?code=ADM001" class="p-2 rounded-xl bg-white hover:bg-purple-50 border border-slate-200 hover:border-purple-300 text-slate-700 hover:text-purple-800 transition flex items-center gap-1.5 font-semibold shadow-2xs">
                                    <span>🛡️</span> <span>Quản Trị Viên (Admin)</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </details>

                <div class="pt-2 flex flex-wrap items-center justify-between gap-2 text-xs border-t border-slate-100">
                    <a href="<?= $appBase ?>guest/ticket" class="text-rose-600 hover:text-rose-700 hover:underline font-bold flex items-center gap-1">
                        <span>🆘</span> Cứu hộ tài khoản khi bị khóa?
                    </a>
                    <a href="<?= $appBase ?>guest/lookup" class="text-indigo-600 hover:text-indigo-800 hover:underline font-semibold flex items-center gap-1">
                        <span>🔍</span> Tra cứu kết quả cứu hộ
                    </a>
                </div>

            </div>

        </div>

    </div>

    <!-- Chân trang chuyên nghiệp -->
    <?php require __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        function toggleLoginPassword(btn) {
            const input = document.getElementById('login-password');
            if (!input) return;
            const isPwd = input.type === 'password';
            input.type = isPwd ? 'text' : 'password';
            const icon = document.getElementById('pwd-icon') || btn;
            icon.textContent = isPwd ? '🙈' : '👁️';
            btn.setAttribute('aria-label', isPwd ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            btn.setAttribute('title', isPwd ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            input.focus();
        }

        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.classList.remove('hidden');
            button.classList.add('flex', 'items-center', 'justify-center');
        });

        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-password-toggle]');
            if (!button) return;

            const input = document.getElementById(button.getAttribute('aria-controls'));
            if (!input) return;

            const isVisible = input.type === 'text';
            input.type = isVisible ? 'password' : 'text';
            button.setAttribute('aria-pressed', String(!isVisible));
            button.setAttribute('aria-label', isVisible ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
            const showIcon = button.querySelector('[data-password-show-icon]');
            const hideIcon = button.querySelector('[data-password-hide-icon]');
            if (showIcon) showIcon.classList.toggle('hidden', !isVisible);
            if (hideIcon) hideIcon.classList.toggle('hidden', isVisible);
        });

        document.querySelectorAll('[data-auto-dismiss]').forEach(function (message) {
            const delay = Number(message.dataset.autoDismiss) || 4500;
            window.setTimeout(function () {
                if (!message.isConnected) return;
                message.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-8px)';
                window.setTimeout(function () { message.remove(); }, 300);
            }, delay);
        });
    </script>
</body>
</html>
