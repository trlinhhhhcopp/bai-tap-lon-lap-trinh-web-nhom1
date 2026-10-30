<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="my-auto py-8 flex items-center justify-center">
    <div class="w-full max-w-md bg-white border border-slate-200/80 rounded-3xl shadow-2xl shadow-indigo-950/10 overflow-hidden transition-all">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 md:p-8 text-center border-b-2 border-sky-500 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-32 h-32 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">
                    🏛️ ĐẠI HỌC THỦ ĐÔ HÀ NỘI
                </span>
                <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">
                    CỔNG ĐĂNG NHẬP XÁC THỰC
                </h2>
                <p class="text-xs text-slate-300">
                    Hệ Thống Tiếp Nhận & Xử Lý Hỗ Trợ CNTT Trực Tuyến
                </p>
            </div>
        </div>

        <!-- Body Form -->
        <div class="p-6 md:p-8 space-y-5">
            <form method="POST" action="<?= $appBase ?>login" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Mã sinh viên / Email trường cấp (*):
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            👤
                        </span>
                        <input type="text" name="identifier" id="identifier" required autofocus
                               class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder:text-slate-400"
                               placeholder="VD: 224001729 hoặc 224001729@daihocthudo.edu.vn">
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">Sinh viên nhập MSV (VD: 224001729) hoặc Email trường</span>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                            Mật khẩu (*):
                        </label>
                        <a href="<?= $appBase ?>forgot-password" class="text-xs text-sky-600 hover:text-sky-700 font-bold hover:underline transition">
                            Quên mật khẩu?
                        </a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            🔒
                        </span>
                        <input type="password" name="password" id="password" required autocomplete="current-password"
                               class="w-full pl-10 pr-12 py-3 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition-all placeholder:text-slate-400"
                               placeholder="Nhập mật khẩu (Mặc định: 123456)">
                        <button type="button" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden absolute inset-y-0 right-0 px-3.5 items-center text-slate-500 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sky-500 rounded-r-xl">
                            <svg data-password-show-icon aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            <svg data-password-hide-icon aria-hidden="true" class="hidden w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full mt-2 py-3.5 px-6 bg-gradient-to-r from-sky-500 via-sky-600 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-sky-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                    <span>Đăng Nhập Cổng CNTT</span>
                    <span>➔</span>
                </button>
            </form>

            <!-- Đường dẫn bổ trợ cứu hộ -->
            <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs">
                <a href="<?= $appBase ?>guest/ticket" class="text-rose-600 hover:text-rose-700 font-bold flex items-center gap-1 hover:underline">
                    <span>🆘</span> Báo cứu hộ khi bị khóa tài khoản
                </a>
                <a href="<?= $appBase ?>guest/lookup" class="text-slate-500 hover:text-slate-700 font-semibold flex items-center gap-1 hover:underline">
                    <span>🔍</span> Tra cứu tiến độ cứu hộ
                </a>
            </div>

            <!-- Hộp chọn nhanh tài khoản kiểm thử đồ án -->
            <div class="mt-4 pt-3 border-t border-dashed border-slate-200">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2 text-center">
                    ⚡ Điền nhanh tài khoản kiểm thử:
                </span>
                <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                    <button type="button" onclick="fillLogin('224001729', '123456')" 
                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 hover:border-sky-300 border border-slate-200 rounded-lg text-slate-700 font-medium transition text-left truncate">
                        🎓 SV Thục Linh (224001729)
                    </button>
                    <button type="button" onclick="fillLogin('224001736', '123456')" 
                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 hover:border-sky-300 border border-slate-200 rounded-lg text-slate-700 font-medium transition text-left truncate">
                        👑 SV Thanh Ngân (224001736)
                    </button>
                    <button type="button" onclick="fillLogin('KTV001', '123456')" 
                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 hover:border-sky-300 border border-slate-200 rounded-lg text-slate-700 font-medium transition text-left truncate">
                        👨‍💻 KTV An (SSO)
                    </button>
                    <button type="button" onclick="fillLogin('ADM001', '123456')" 
                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 hover:border-sky-300 border border-slate-200 rounded-lg text-slate-700 font-medium transition text-left truncate">
                        🛡️ Quản Trị Viên (Admin)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillLogin(user, pass) {
    document.getElementById('identifier').value = user;
    document.getElementById('password').value = pass;
    document.getElementById('identifier').focus();
}

</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
