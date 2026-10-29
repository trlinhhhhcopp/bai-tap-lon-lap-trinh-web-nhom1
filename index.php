<?php
/**
 * ==============================================================================
 * HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
 * TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD) — KHOA CÔNG NGHỆ THÔNG TIN
 * ------------------------------------------------------------------------------
 * FRONT-CONTROLLER & PHÂN LUỒNG TÀI KHOẢN (ROLE-BASED AUTHENTICATION GATE)
 * Kiến trúc chuẩn mực theo Tài liệu Đặc tả ITIL v4:
 * 1. Chưa đăng nhập: Bắt buộc vào màn hình Đăng nhập hoặc Khôi phục mật khẩu SSPR
 * 2. Đã đăng nhập: Tự động phân luồng theo 4 vai trò (Sinh viên, Giảng viên, KTV, Admin)
 * ==============================================================================
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Models/PasswordReset.php';
require_once __DIR__ . '/app/Services/AuthService.php';

use App\Services\AuthService;
use App\Models\PasswordReset;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = db();

// Helper Base URL
if (!function_exists('get_app_base_url')) {
    function get_app_base_url(): string {
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $appRoot = str_replace('\\', '/', realpath(__DIR__) ?: __DIR__);
        if ($docRoot && str_starts_with($appRoot, $docRoot)) {
            $rel = substr($appRoot, strlen($docRoot));
            $base = '/' . trim(str_replace('\\', '/', $rel), '/') . '/';
            return str_replace('//', '/', $base);
        }
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $folder = 'bai-tap-lon-lap-trinh-web-nhom1';
        $pos = strpos($script, $folder);
        if ($pos !== false) {
            return substr($script, 0, $pos + strlen($folder)) . '/';
        }
        return '/';
    }
}
$appBase = get_app_base_url();

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// -----------------------------------------------------------------------------
// 1. XỬ LÝ ĐĂNG XUẤT (LOGOUT)
// -----------------------------------------------------------------------------
if ($action === 'logout') {
    unset($_SESSION['user']);
    $_SESSION['flash'] = [
        'type'    => 'info',
        'message' => 'Bạn đã đăng xuất an toàn khỏi hệ thống!'
    ];
    header('Location: ' . $appBase . 'index.php');
    exit;
}

// -----------------------------------------------------------------------------
// 2. XỬ LÝ ĐĂNG NHẬP NHANH THỬ NGHIỆM (QUICK 1-CLICK DEMO LOGIN DÀNH CHO THẦY CÔ)
// -----------------------------------------------------------------------------
if ($action === 'quick_login') {
    $code = trim($_GET['code'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_code = :code LIMIT 1");
    $stmt->execute(['code' => $code]);
    $user = $stmt->fetch();
    if ($user) {
        if ($user['is_active'] == 0) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Tài khoản này đang bị khóa!'
            ];
        } else {
            $_SESSION['user'] = $user;
            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "Đăng nhập thành công với vai trò: <strong>{$user['name']}</strong> ({$user['user_code']})"
            ];
        }
    }
    header('Location: ' . $appBase . 'index.php');
    exit;
}

// -----------------------------------------------------------------------------
// 3. XỬ LÝ FORM ĐĂNG NHẬP (STANDARD LOGIN)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = trim($_POST['password'] ?? '');

    if (empty($identifier) || empty($password)) {
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Vui lòng nhập đầy đủ Mã tài khoản và Mật khẩu!'
        ];
        header('Location: ' . $appBase . 'index.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_code = :id1 OR email = :id2 LIMIT 1");
    $stmt->execute(['id1' => $identifier, 'id2' => $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Tài khoản không tồn tại trên hệ thống Helpdesk!'
        ];
        header('Location: ' . $appBase . 'index.php');
        exit;
    }

    if ($user['is_active'] == 0) {
        $reason = ($user['lock_reason'] === 'expelled') ? 'kỷ luật Buộc thôi học' : 'hết hạn niên khóa';
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => "Tài khoản bị KHÓA CỨNG do {$reason}. Vui lòng liên hệ Phòng Đào tạo hoặc gửi phiếu cứu hộ!"
        ];
        header('Location: ' . $appBase . 'index.php');
        exit;
    }

    // Kiểm tra mật khẩu (Bcrypt hoặc pass mặc định 123456)
    $passValid = password_verify($password, $user['password_hash']) || ($password === '123456');

    if (!$passValid) {
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Mật khẩu không chính xác! (Mật khẩu mặc định là: 123456)'
        ];
        header('Location: ' . $appBase . 'index.php');
        exit;
    }

    // Đăng nhập thành công
    $_SESSION['user'] = $user;
    $_SESSION['flash'] = [
        'type'    => 'success',
        'message' => "Chào mừng <strong>{$user['name']}</strong> đã đăng nhập thành công!"
    ];
    header('Location: ' . $appBase . 'index.php');
    exit;
}

// -----------------------------------------------------------------------------
// 4. XỬ LÝ QUÊN MẬT KHẨU (SSPR - TRẦN THỤC LINH)
// -----------------------------------------------------------------------------
$ssprTokenSent = false;
$ssprTokenValue = '';
$ssprUserEmail = '';
$ssprUserName = '';

if ($action === 'forgot_password') {
    $flash = get_flash();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $identifier = trim($_POST['identifier'] ?? '');
        try {
            $token = AuthService::requestResetToken($identifier);
            $user = \App\Models\User::findByEmailOrCode($identifier);
            $ssprTokenSent = true;
            $ssprTokenValue = $token;
            $ssprUserEmail = $user['email'] ?? '';
            $ssprUserName = $user['name'] ?? '';
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => $e->getMessage()
            ];
            header('Location: ' . $appBase . 'index.php?action=forgot_password');
            exit;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Khôi Phục Mật Khẩu (SSPR) - Helpdesk ĐH Thủ Đô</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-900 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <div class="flex-1 max-w-xl mx-auto w-full px-4 py-10 flex flex-col justify-center">
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        🔑 SSPR · TỰ PHỤC HỒI MẬT KHẨU HỌC THUẬT
                    </span>
                    <h2 class="text-xl font-black text-slate-900 mt-2">Quên Mật Khẩu Tài Khoản</h2>
                    <p class="text-xs text-slate-500 mt-1">Cơ chế bảo mật xác thực cấp Token an toàn có hiệu lực trong vòng 15 phút (Bài 4 - Trần Thục Linh).</p>
                </div>

                <?php if ($flash): ?>
                    <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <?php if ($ssprTokenSent): ?>
                    <!-- Mô phỏng Hòm thư sinh viên nhận email chứa token -->
                    <div class="bg-indigo-50/80 border border-indigo-200 rounded-2xl p-5 space-y-4">
                        <div class="flex items-center gap-2 text-indigo-900 font-bold text-xs">
                            <span class="text-lg">📬</span> HÒM THƯ NỘI BỘ SINH VIÊN (MÔ PHỎNG EMAIL TRƯỜNG CẤP)
                        </div>
                        <div class="bg-white rounded-xl p-4 border border-indigo-100 text-xs space-y-2">
                            <p class="text-slate-500 text-[11px]">Người nhận: <strong class="text-slate-800"><?php echo htmlspecialchars($ssprUserName); ?></strong> &lt;<?php echo htmlspecialchars($ssprUserEmail); ?>&gt;</p>
                            <p class="text-slate-500 text-[11px]">Tiêu đề: <strong class="text-indigo-700">[TUD-Helpdesk] Yêu cầu khôi phục mật khẩu học thuật</strong></p>
                            <hr class="border-slate-100 my-2" />
                            <p class="text-slate-700 leading-relaxed">
                                Chào bạn <strong><?php echo htmlspecialchars($ssprUserName); ?></strong>, hệ thống đã tạo mã Token xác thực khôi phục mật khẩu. Token này có hiệu lực tối đa trong vòng <strong>15 phút</strong>:
                            </p>
                            <div class="p-2.5 bg-slate-50 rounded-lg font-mono text-[11px] text-indigo-700 font-bold break-all border border-slate-200 select-all">
                                <?php echo htmlspecialchars($ssprTokenValue); ?>
                            </div>
                            <div class="pt-2">
                                <a href="<?php echo $appBase; ?>index.php?action=reset_password&token=<?php echo urlencode($ssprTokenValue); ?>" 
                                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 text-xs">
                                    👉 Nhấp Vào Đây Để Đặt Lại Mật Khẩu Mới Ngay
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo $appBase; ?>index.php?action=forgot_password" class="space-y-4 text-xs">
                        <input type="hidden" name="action" value="forgot_password" />
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nhập Mã Sinh Viên hoặc Email trường của bạn:</label>
                            <input type="text" name="identifier" required placeholder="Ví dụ: 224001736 hoặc 224001729@daihocthudo.edu.vn" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                            <span>🚀</span> Gửi Mã Token Xác Thực (15 Phút)
                        </button>
                    </form>
                <?php endif; ?>

                <div class="pt-2 text-center text-xs">
                    <a href="<?php echo $appBase; ?>index.php" class="text-indigo-600 hover:underline font-semibold">
                        ← Quay lại màn hình Đăng Nhập
                    </a>
                </div>
            </div>
        </div>

        <?php require __DIR__ . '/includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// -----------------------------------------------------------------------------
// 5. XỬ LÝ ĐẶT LẠI MẬT KHẨU MỚI (RESET PASSWORD QUA TOKEN)
// -----------------------------------------------------------------------------
if ($action === 'reset_password') {
    $token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
    $flash = get_flash();
    $tokenRecord = PasswordReset::verifyToken($token);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        try {
            AuthService::resetPasswordWithToken($token, $newPass, $confirmPass);
            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => '🎉 Đặt lại mật khẩu mới thành công! Vui lòng đăng nhập với mật khẩu mới.'
            ];
            header('Location: ' . $appBase . 'index.php');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => $e->getMessage()
            ];
            header('Location: ' . $appBase . 'index.php?action=reset_password&token=' . urlencode($token));
            exit;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Đặt Lại Mật Khẩu Mới - Helpdesk ĐH Thủ Đô</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-900 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <div class="flex-1 max-w-md mx-auto w-full px-4 py-10 flex flex-col justify-center">
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        🛡️ ĐỔI MẬT KHẨU MỚI
                    </span>
                    <h2 class="text-xl font-black text-slate-900 mt-2">Thiết Lập Mật Khẩu</h2>
                    <p class="text-xs text-slate-500 mt-1">Mật khẩu mới sẽ được mã hóa an toàn bằng chuẩn Bcrypt.</p>
                </div>

                <?php if ($flash): ?>
                    <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$tokenRecord): ?>
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-2">
                        <p class="font-bold">⚠️ Mã Token không hợp lệ hoặc đã hết hạn!</p>
                        <p class="text-[11.5px] leading-relaxed">
                            Liên kết khôi phục chỉ có hiệu lực trong 15 phút theo quy định an toàn bảo mật. Vui lòng gửi lại yêu cầu mới.
                        </p>
                        <div class="pt-2">
                            <a href="<?php echo $appBase; ?>index.php?action=forgot_password" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl inline-block transition hover:bg-rose-700">
                                Yêu cầu cấp lại Token mới →
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo $appBase; ?>index.php?action=reset_password" class="space-y-4 text-xs">
                        <input type="hidden" name="action" value="reset_password" />
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Mật khẩu mới (Tối thiểu 6 ký tự):</label>
                            <input type="password" name="new_password" required minlength="6" placeholder="Nhập mật khẩu mới..." 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Xác nhận lại mật khẩu mới:</label>
                            <input type="password" name="confirm_password" required minlength="6" placeholder="Nhập lại mật khẩu mới..." 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-md shadow-emerald-100 flex items-center justify-center gap-2">
                            <span>✅</span> Cập Nhật Mật Khẩu Mới
                        </button>
                    </form>
                <?php endif; ?>

                <div class="pt-2 text-center text-xs">
                    <a href="<?php echo $appBase; ?>index.php" class="text-indigo-600 hover:underline font-semibold">
                        ← Quay lại màn hình Đăng Nhập
                    </a>
                </div>
            </div>
        </div>

        <?php require __DIR__ . '/includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// -----------------------------------------------------------------------------
// 6. KIỂM TRA ĐĂNG NHẬP: NẾU CHƯA ĐĂNG NHẬP -> HIỂN THỊ MÀN HÌNH ĐĂNG NHẬP
// -----------------------------------------------------------------------------
$currentUser = $_SESSION['user'] ?? null;
$flash = get_flash();

if (!$currentUser) {
    // GIAO DIỆN ĐĂNG NHẬP BẢO MẬT (GUEST / NOT LOGGED IN)
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Đăng Nhập - Hệ Thống Helpdesk CNTT ĐH Thủ Đô Hà Nội</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-900 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">

        <!-- Header Khách -->
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <!-- Khu vực Đăng nhập trung tâm -->
        <div class="flex-1 max-w-4xl mx-auto w-full px-4 py-8 flex flex-col justify-center">
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                
                <!-- Cột giới thiệu hệ thống -->
                <div class="md:col-span-5 text-white space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-bold">
                        🏛️ ĐẠI HỌC THỦ ĐÔ HÀ NỘI
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight leading-tight">
                        CỔNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT
                    </h1>
                    <p class="text-xs text-indigo-200/80 leading-relaxed">
                        Hệ thống dịch vụ số tiếp nhận sự cố kỹ thuật từ xa theo chuẩn ITIL v4. Tự động phân cấp mức ưu tiên Auto-Priority, hàng đợi SLA thời gian thực và quản lý đào tạo học thuật.
                    </p>
                    <div class="p-3.5 bg-slate-800/80 rounded-2xl border border-slate-700/60 text-xs text-slate-300 space-y-1.5">
                        <p class="font-bold text-white flex items-center gap-1.5">
                            <span>🔒</span> Yêu Cầu Xác Thực Tài Khoản:
                        </p>
                        <p class="text-[11.5px] text-slate-400">
                            Để bảo mật thông tin nội bộ và cách ly dữ liệu sự cố, bạn bắt buộc phải đăng nhập bằng tài khoản trường cấp để truy cập vào hệ thống.
                        </p>
                    </div>
                </div>

                <!-- Cột Form Đăng Nhập -->
                <div class="md:col-span-7 bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-5">
                    
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Đăng Nhập Tài Khoản</h2>
                        <p class="text-xs text-slate-500 mt-1">Dành cho Sinh viên, Giảng viên, Kỹ thuật viên & Quản trị viên</p>
                    </div>

                    <?php if ($flash): ?>
                        <div class="p-3 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                            <?php echo $flash['message']; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form đăng nhập chuẩn -->
                    <form method="POST" action="<?php echo $appBase; ?>index.php" class="space-y-4 text-xs">
                        <input type="hidden" name="action" value="login" />

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Mã tài khoản (Mã SV / Mã GV / Mã KTV / Email):</label>
                            <input type="text" name="identifier" required placeholder="Ví dụ: 224001736, GV001, KTV004..." 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-slate-700">Mật khẩu:</label>
                                <span class="text-[11px] text-indigo-600 font-medium">Mặc định: 123456</span>
                            </div>
                            <input type="password" name="password" required placeholder="Nhập mật khẩu của bạn..." 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                            
                            <!-- Link Quên mật khẩu SSPR Trần Linh -->
                            <div class="flex items-center justify-between text-[11px] pt-1.5">
                                <a href="<?php echo $appBase; ?>index.php?action=forgot_password" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline flex items-center gap-1">
                                    <span>🔑</span> Quên mật khẩu? (Khôi phục SSPR)
                                </a>
                                <span class="text-slate-400">Token bảo mật 15 phút</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5 text-xs">
                            <span>🔐</span> Đăng Nhập Hệ Thống
                        </button>
                    </form>

                    <!-- Danh sách tài khoản thử nghiệm 1-Click (Phục vụ chấm bài) -->
                    <div class="pt-4 border-t border-slate-100 space-y-2.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-slate-600 uppercase tracking-wider">⚡ Đăng Nhập Nhanh Để Kiểm Thử (1-Click Login):</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <!-- SV Thanh Ngân -->
                            <a href="<?php echo $appBase; ?>index.php?action=quick_login&code=224001736" 
                               class="p-2 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 transition flex items-center gap-1.5 font-medium">
                                <span>🎓</span> <span>SV Thanh Ngân</span>
                            </a>
                            <!-- SV Thục Linh -->
                            <a href="<?php echo $appBase; ?>index.php?action=quick_login&code=224001729" 
                               class="p-2 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 text-slate-700 hover:text-indigo-700 transition flex items-center gap-1.5 font-medium">
                                <span>🎓</span> <span>SV Thục Linh</span>
                            </a>
                            <!-- Giảng viên -->
                            <a href="<?php echo $appBase; ?>index.php?action=quick_login&code=GV001" 
                               class="p-2 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200 hover:border-amber-300 text-slate-700 hover:text-amber-800 transition flex items-center gap-1.5 font-medium">
                                <span>👨‍🏫</span> <span>GV Nguyễn Văn Giảng</span>
                            </a>
                            <!-- KTV Canvas -->
                            <a href="<?php echo $appBase; ?>index.php?action=quick_login&code=KTV004" 
                               class="p-2 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition flex items-center gap-1.5 font-medium">
                                <span>👨‍💻</span> <span>KTV Hoàng Dũng</span>
                            </a>
                            <!-- KTV SSO -->
                            <a href="<?php echo $appBase; ?>index.php?action=quick_login&code=KTV001" 
                               class="p-2 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition flex items-center gap-1.5 font-medium">
                                <span>👨‍💻</span> <span>KTV Nguyễn Văn An</span>
                            </a>
                            <!-- Admin -->
                            <a href="<?php echo $appBase; ?>index.php?action=quick_login&code=ADM001" 
                               class="p-2 rounded-xl bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-300 text-slate-700 hover:text-purple-800 transition flex items-center gap-1.5 font-medium">
                                <span>🛡️</span> <span>Quản Trị Viên (Admin)</span>
                            </a>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between text-[11px] text-slate-400">
                        <a href="<?php echo $appBase; ?>views/guest/create_ticket.php" class="text-rose-600 hover:underline font-semibold">
                            🆘 Cứu hộ tài khoản khi bị khóa?
                        </a>
                        <a href="<?php echo $appBase; ?>views/guest/lookup_ticket.php" class="text-indigo-600 hover:underline">
                            🔍 Tra cứu kết quả cứu hộ
                        </a>
                    </div>

                </div>

            </div>

        </div>

        <!-- Chân trang chuyên nghiệp -->
        <?php require __DIR__ . '/includes/footer.php'; ?>

    </body>
    </html>
    <?php
    exit;
}

// -----------------------------------------------------------------------------
// 7. KHI ĐÃ ĐĂNG NHẬP: HỆ THỐNG PHÂN LUỒNG TỰ ĐỘNG THEO VAI TRÒ (4 VAI TRÒ CHUẨN PDF)
// -----------------------------------------------------------------------------
$userId   = (int)$currentUser['id'];
$userRole = $currentUser['role'];
$userType = $currentUser['user_type'] ?? 'student';

// =============================================================================
// PHÂN HỆ 1: CỔNG SINH VIÊN (STUDENT PORTAL)
// =============================================================================
if ($userType === 'student') {
    // Truy vấn chi tiết hồ sơ sinh viên kèm thông tin lớp & học thuật (Trần Thục Linh)
    $studentStmt = $pdo->prepare("
        SELECT u.*, COALESCE(c.name, 'Chưa phân lớp') AS class_name, COALESCE(c.class_code, 'N/A') AS class_code 
        FROM users u 
        LEFT JOIN classes c ON u.class_id = c.id 
        WHERE u.id = :id
    ");
    $studentStmt->execute([':id' => $userId]);
    $studentProfile = $studentStmt->fetch() ?: $currentUser;

    // Thống kê riêng của sinh viên này
    $myTicketsCount = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId}")->fetchColumn();
    $myActiveCount  = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId} AND status IN ('new', 'in_progress', 'waiting_requester')")->fetchColumn();
    $myResolvedCount = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId} AND status IN ('resolved', 'closed')")->fetchColumn();

    // Lấy danh sách phiếu của riêng sinh viên này
    $myTickets = $pdo->query("
        SELECT t.*, c.name AS category_name, p.name AS priority_name, p.code AS priority_code,
               ag.name AS agent_name, r.score AS rating_score
        FROM tickets t
        JOIN categories c ON t.category_id = c.id
        JOIN priorities p ON t.priority_id = p.id
        LEFT JOIN users ag ON t.assigned_to = ag.id
        LEFT JOIN ticket_ratings r ON r.ticket_id = t.id
        WHERE t.requester_id = {$userId}
        ORDER BY t.id DESC
    ")->fetchAll();

    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cổng Sinh Viên - Helpdesk ĐH Thủ Đô</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            <!-- Banner Chào Mừng Sinh Viên -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        🎓 CỔNG SINH VIÊN (STUDENT PORTAL)
                    </span>
                    <h1 class="text-2xl font-black text-white">
                        Xin chào, <?php echo htmlspecialchars($currentUser['name']); ?>!
                    </h1>
                    <p class="text-xs text-indigo-200/80">Mã sinh viên: <strong class="text-white"><?php echo htmlspecialchars($currentUser['user_code']); ?></strong> • Email: <?php echo htmlspecialchars($currentUser['email']); ?></p>
                </div>
                <div>
                    <a href="<?php echo $appBase; ?>tickets/create.php" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-md transition flex items-center gap-2 text-xs">
                        <span>➕</span> Gửi Phiếu Sự Cố Mới (Auto-Priority)
                    </a>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <!-- THÔNG TIN HỒ SƠ SINH VIÊN & ĐỊNH DANH HỌC THUẬT (PHÂN HỆ TRẦN THỤC LINH) -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-sm">
                            👤
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                                HỒ SƠ SINH VIÊN & ĐỊNH DANH HỌC THUẬT
                            </h2>
                            <p class="text-[11px] text-slate-500">Phân hệ Quản trị User & Vòng đời tài khoản học thuật (Trần Thục Linh - Bài 4)</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?php echo ($studentProfile['is_active'] == 1) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'; ?>">
                        ● <?php echo ($studentProfile['is_active'] == 1) ? 'Tài khoản hoạt động' : 'Tài khoản bị khóa'; ?>
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-1">
                        <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Mã Sinh Viên (MSSV)</span>
                        <span class="font-mono font-bold text-indigo-700 text-sm block"><?php echo htmlspecialchars($studentProfile['user_code']); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-1">
                        <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Họ và Tên</span>
                        <span class="font-bold text-slate-900 text-sm block"><?php echo htmlspecialchars($studentProfile['name']); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-1">
                        <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Lớp Sinh Hoạt</span>
                        <span class="font-bold text-indigo-900 text-sm block"><?php echo htmlspecialchars($studentProfile['class_code']); ?></span>
                        <span class="text-[10px] text-slate-400 block truncate"><?php echo htmlspecialchars($studentProfile['class_name']); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-1">
                        <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Trạng Thái Học Tập</span>
                        <?php 
                        $academicStatus = $studentProfile['academic_status'] ?? 'studying';
                        $statusText = match($academicStatus) {
                            'studying'  => '🟢 Đang học bình thường',
                            'suspended' => '🟡 Tạm đình chỉ',
                            'expelled'  => '🔴 Buộc thôi học',
                            default     => '🟢 Đang học bình thường',
                        };
                        ?>
                        <span class="font-bold text-slate-800 text-xs block"><?php echo $statusText; ?></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs pt-1">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-0.5">
                        <span class="text-[11px] text-slate-400 font-bold block">Email Nhà Trường Cấp:</span>
                        <span class="font-medium text-slate-800 break-all"><?php echo htmlspecialchars($studentProfile['email']); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-0.5">
                        <span class="text-[11px] text-slate-400 font-bold block">Khoa Chuyên Môn / Khóa:</span>
                        <span class="font-medium text-slate-800">Khoa CNTT · Khóa D24 (2024 - 2028)</span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-0.5">
                        <span class="text-[11px] text-slate-400 font-bold block">Cơ Chế Bảo Mật & SSPR:</span>
                        <span class="font-medium text-emerald-700">Bcrypt Hash + Token khôi phục 15 phút</span>
                    </div>
                </div>
            </div>

            <!-- 3 Thẻ chỉ số cá nhân của Sinh viên -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tổng Số Phiếu Đã Gửi</span>
                    <span class="text-2xl font-black text-slate-900"><?php echo $myTicketsCount; ?></span>
                    <span class="text-[11px] text-slate-400 block mt-1">Sự cố kỹ thuật cá nhân</span>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                    <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Phiếu Đang Xử Lý</span>
                    <span class="text-2xl font-black text-amber-600"><?php echo $myActiveCount; ?></span>
                    <span class="text-[11px] text-slate-400 block mt-1">KTV đang tiếp nhận & khắc phục</span>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block mb-1">Phiếu Đã Giải Quyết</span>
                    <span class="text-2xl font-black text-emerald-600"><?php echo $myResolvedCount; ?></span>
                    <span class="text-[11px] text-slate-400 block mt-1">Đã có giải pháp & hoàn tất</span>
                </div>
            </div>

            <!-- BẢNG DANH SÁCH PHIẾU CỦA RIÊNG SINH VIÊN NÀY -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden space-y-3">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>🎫</span> MỤC PHIẾU HỖ TRỢ RIÊNG CỦA TÔI (CÁCH LY DỮ LIỆU)
                        </h2>
                        <p class="text-[11.5px] text-slate-500 mt-0.5">Sinh viên chỉ xem và trao đổi trên các phiếu do chính mình gửi, bảo mật tuyệt đối.</p>
                    </div>
                </div>

                <?php if (empty($myTickets)): ?>
                    <div class="p-8 text-center text-slate-400 text-xs space-y-2">
                        <span class="text-3xl block">📭</span>
                        <p>Hiện bạn chưa gửi yêu cầu hỗ trợ nào. Khi gặp sự cố kỹ thuật, hãy nhấn nút <strong>"Gửi Phiếu Sự Cố Mới"</strong> bên trên nhé!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                                    <th class="py-3 px-4">Mã Vé</th>
                                    <th class="py-3 px-4">Tiêu Đề Sự Cố</th>
                                    <th class="py-3 px-4">Chuyên Mục</th>
                                    <th class="py-3 px-4 text-center">Trạng Thái</th>
                                    <th class="py-3 px-4">KTV Phụ Trách</th>
                                    <th class="py-3 px-4 text-center">Đánh Giá CSAT</th>
                                    <th class="py-3 px-4 text-center">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($myTickets as $t): ?>
                                    <?php
                                    $statusBadges = [
                                        'new'               => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'in_progress'       => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'waiting_requester' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'resolved'          => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'closed'            => 'bg-slate-100 text-slate-700 border-slate-300',
                                    ];
                                    $statusLabels = [
                                        'new'               => 'Mới tiếp nhận',
                                        'in_progress'       => 'Đang xử lý',
                                        'waiting_requester' => 'Chờ bạn phản hồi',
                                        'resolved'          => 'Đã có giải pháp',
                                        'closed'            => 'Đã đóng hoàn tất',
                                    ];
                                    ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:underline">
                                                <?php echo htmlspecialchars($t['ticket_code']); ?>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-800">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:text-indigo-600 transition block max-w-sm truncate">
                                                <?php echo htmlspecialchars($t['title']); ?>
                                            </a>
                                            <span class="text-[10.5px] text-slate-400 font-normal">Gửi lúc: <?php echo htmlspecialchars($t['created_at']); ?></span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">
                                            <?php echo htmlspecialchars($t['category_name']); ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold border <?php echo $statusBadges[$t['status']] ?? 'bg-slate-100 text-slate-600 border-slate-200'; ?>">
                                                ● <?php echo $statusLabels[$t['status']] ?? $t['status']; ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">
                                            <?php echo htmlspecialchars($t['agent_name'] ?: 'Đang phân công...'); ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($t['rating_score']): ?>
                                                <span class="font-bold text-amber-500 font-mono"><?php echo $t['rating_score']; ?> ★</span>
                                            <?php elseif ($t['status'] === 'resolved' || $t['status'] === 'closed'): ?>
                                                <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>#csat-box" 
                                                   class="px-2 py-0.5 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 border border-yellow-200 rounded-lg text-[10.5px] font-bold transition">
                                                    Chấm sao ★
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300 text-[11px]">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition inline-block">
                                                Chi tiết & Chat →
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>

        <?php require __DIR__ . '/includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// =============================================================================
// PHÂN HỆ 2: CỔNG GIẢNG VIÊN (LECTURER PORTAL)
// =============================================================================
if ($userType === 'lecturer') {
    // Thống kê của giảng viên
    $myTicketsCount = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId}")->fetchColumn();
    $myCoursesCount = (int)$pdo->query("SELECT COUNT(*) FROM course_assignments WHERE lecturer_id = {$userId}")->fetchColumn();

    // Lấy danh sách lớp học phần phụ trách
    $myCourses = $pdo->query("
        SELECT ca.*, u.name AS lecturer_name
        FROM course_assignments ca
        JOIN users u ON ca.lecturer_id = u.id
        WHERE ca.lecturer_id = {$userId}
        ORDER BY ca.semester DESC, ca.id DESC
    ")->fetchAll();

    // Lấy danh sách phiếu sự cố đào tạo của giảng viên
    $myTickets = $pdo->query("
        SELECT t.*, c.name AS category_name, p.name AS priority_name, p.code AS priority_code, ag.name AS agent_name
        FROM tickets t
        JOIN categories c ON t.category_id = c.id
        JOIN priorities p ON t.priority_id = p.id
        LEFT JOIN users ag ON t.assigned_to = ag.id
        WHERE t.requester_id = {$userId}
        ORDER BY t.id DESC
    ")->fetchAll();

    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cổng Giảng Viên - Helpdesk ĐH Thủ Đô</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            <!-- Banner Chào Mừng Giảng Viên -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        👨‍🏫 CỔNG GIẢNG VIÊN (LECTURER PORTAL)
                    </span>
                    <h1 class="text-2xl font-black text-white">
                        Kính chào Thầy/Cô: <?php echo htmlspecialchars($currentUser['name']); ?>
                    </h1>
                    <p class="text-xs text-indigo-200/80">Mã cán bộ: <strong class="text-white"><?php echo htmlspecialchars($currentUser['user_code']); ?></strong> • Đơn vị: <?php echo htmlspecialchars($currentUser['department'] ?? 'Khoa Công Nghệ Thông Tin'); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?php echo $appBase; ?>tickets/create.php" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs">
                        <span>🚨</span> Báo Sự Cố Khẩn Cấp (HIGH / URGENT)
                    </a>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <!-- BẢNG LỚP HỌC PHẦN PHỤ TRÁCH (1 LỚP HP = 1 GIẢNG VIÊN) -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden space-y-3">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>📚</span> LỚP HỌC PHẦN PHỤ TRÁCH GIẢNG DẠY (COURSE ASSIGNMENTS)
                        </h2>
                        <p class="text-[11.5px] text-slate-500 mt-0.5">Mỗi lớp học phần chỉ có duy nhất 1 giảng viên phụ trách theo nguyên tắc đào tạo.</p>
                    </div>
                    <a href="<?php echo $appBase; ?>LeHaMy_GiangVien/index.php?action=index" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs transition">
                        Xem Tất Cả Lớp Phụ Trách →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                                <th class="py-3 px-4">Mã Lớp HP</th>
                                <th class="py-3 px-4">Tên Lớp Học Phần</th>
                                <th class="py-3 px-4">Môn Học</th>
                                <th class="py-3 px-4">Học Kỳ</th>
                                <th class="py-3 px-4 text-center">Năm Học</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($myCourses)): ?>
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">Chưa có phân công lớp học phần nào trong học kỳ này.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($myCourses as $c): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-indigo-600"><?php echo htmlspecialchars($c['class_code']); ?></td>
                                        <td class="py-3 px-4 font-semibold text-slate-800"><?php echo htmlspecialchars($c['class_name']); ?></td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($c['course_name']); ?></td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($c['semester']); ?></td>
                                        <td class="py-3 px-4 text-center font-mono text-slate-500"><?php echo htmlspecialchars($c['academic_year']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BẢNG PHIẾU ĐÃ BÁO CỦA GIẢNG VIÊN -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden space-y-3">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>🎫</span> PHIẾU YÊU CẦU HỖ TRỢ ĐÃ GỬI
                        </h2>
                        <p class="text-[11.5px] text-slate-500 mt-0.5">Theo dõi phản hồi kỹ thuật từ Đội ngũ KTV trực ca.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                                <th class="py-3 px-4">Mã Vé</th>
                                <th class="py-3 px-4">Nội Dung Sự Cố</th>
                                <th class="py-3 px-4">Chuyên Mục</th>
                                <th class="py-3 px-4 text-center">Mức Ưu Tiên</th>
                                <th class="py-3 px-4 text-center">Trạng Thái</th>
                                <th class="py-3 px-4">KTV Phụ Trách</th>
                                <th class="py-3 px-4 text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($myTickets)): ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400">Thầy/Cô chưa gửi yêu cầu hỗ trợ nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($myTickets as $t): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:underline">
                                                <?php echo htmlspecialchars($t['ticket_code']); ?>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-800">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:text-indigo-600 transition block max-w-sm truncate">
                                                <?php echo htmlspecialchars($t['title']); ?>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($t['category_name']); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                <?php echo htmlspecialchars($t['priority_name']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                ● <?php echo htmlspecialchars($t['status']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($t['agent_name'] ?: 'Đang phân công...'); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition inline-block">
                                                Chi tiết →
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

        <?php require __DIR__ . '/includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}

// =============================================================================
// PHÂN HỆ 3: BÀN LÀM VIỆC KỸ THUẬT VIÊN (AGENT WORKSPACE)
// =============================================================================
if ($userRole === 'agent') {
    // Tự động chuyển hướng KTV vào bàn làm việc chuyên trách của chính mình
    header('Location: ' . $appBase . 'agent/index.php');
    exit;
}

// =============================================================================
// PHÂN HỆ 4: TRUNG TÂM ĐIỀU HÀNH QUẢN TRỊ VIÊN (ADMIN CONTROL CENTER)
// =============================================================================
// Thống kê toàn hệ thống dành cho Admin
$totalTickets    = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$activeTickets   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('new', 'in_progress', 'waiting_requester')")->fetchColumn();
$urgentTickets   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE priority_id IN (1, 2)")->fetchColumn();
$resolvedTickets = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status IN ('resolved', 'closed')")->fetchColumn();
$totalUsers      = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses    = (int)$pdo->query("SELECT COUNT(*) FROM course_assignments")->fetchColumn();

// Danh sách 5 KTV và tải trọng
$agents = $pdo->query("
    SELECT u.id, u.user_code, u.name, c.name AS category_name,
           COUNT(t.id) AS active_load
    FROM users u
    LEFT JOIN categories c ON u.specialty_category_id = c.id
    LEFT JOIN tickets t ON t.assigned_to = u.id AND t.status IN ('new', 'in_progress', 'waiting_requester')
    WHERE u.role = 'agent'
    GROUP BY u.id, u.user_code, u.name, c.name
    ORDER BY u.id ASC
")->fetchAll();

// Danh sách phiếu cần Triage & xử lý
$recentTickets = $pdo->query("
    SELECT t.*, c.name AS category_name, p.name AS priority_name, p.code AS priority_code,
           req.name AS requester_name, req.user_code AS requester_code, ag.name AS agent_name
    FROM tickets t
    JOIN categories c ON t.category_id = c.id
    JOIN priorities p ON t.priority_id = p.id
    JOIN users req ON t.requester_id = req.id
    LEFT JOIN users ag ON t.assigned_to = ag.id
    ORDER BY t.id DESC
    LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung Tâm Quản Trị Hệ Thống - Admin Control Center</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
    
    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Banner Admin Control Center -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                    🛡️ TRUNG TÂM QUẢN TRỊ VIÊN (ADMIN CONTROL CENTER)
                </span>
                <h1 class="text-2xl font-black text-white">
                    Bảng Điều Hành Hệ Thống Helpdesk
                </h1>
                <p class="text-xs text-indigo-200/80">Quản trị người dùng, phân công đào tạo, nhập dữ liệu Excel hàng loạt và điều phối tràn tải.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="<?php echo $appBase; ?>admin/import_excel.php" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-md transition text-xs flex items-center gap-1.5">
                    <span>📊</span> Nhập Excel / CSV
                </a>
                <a href="<?php echo $appBase; ?>LeHaMy_GiangVien/index.php?action=index" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-2xl shadow-md transition text-xs flex items-center gap-1.5">
                    <span>📚</span> Phân Công Giảng Dạy
                </a>
                <a href="<?php echo $appBase; ?>TranLinh/users.php" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl shadow-md transition text-xs flex items-center gap-1.5">
                    <span>👥</span> Quản Lý Tài Khoản
                </a>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- 4 THẺ KPI TOÀN TRƯỜNG -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tổng Phiếu Sự Cố</span>
                <span class="text-2xl font-black text-slate-900"><?php echo $totalTickets; ?></span>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Đang Chờ Xử Lý</span>
                <span class="text-2xl font-black text-amber-600"><?php echo $activeTickets; ?></span>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block mb-1">Khẩn Cấp / Cao</span>
                <span class="text-2xl font-black text-rose-600"><?php echo $urgentTickets; ?></span>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <span class="text-[11px] font-bold text-purple-600 uppercase tracking-wider block mb-1">Tổng Số Tài Khoản</span>
                <span class="text-2xl font-black text-purple-600"><?php echo $totalUsers; ?></span>
            </div>
        </div>

        <!-- 2 CỘT: DANH SÁCH PHIẾU CẦN TRIAGE & GIÁM SÁT TẢI 5 KTV -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Danh sách phiếu gần đây (8 cột) -->
            <div class="lg:col-span-8 bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden space-y-3">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>🎫</span> PHIẾU HỖ TRỢ CẦN TRIAGE & ĐIỀU PHỐI
                        </h2>
                        <p class="text-[11.5px] text-slate-500 mt-0.5">Quản trị viên có thẩm quyền nâng mức URGENT diện rộng và điều phối KTV.</p>
                    </div>
                    <a href="<?php echo $appBase; ?>tickets/index.php" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                        Xem Tất Cả →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                                <th class="py-3 px-4">Mã Vé</th>
                                <th class="py-3 px-4">Tiêu Đề</th>
                                <th class="py-3 px-4">Người Gửi</th>
                                <th class="py-3 px-4 text-center">Ưu Tiên</th>
                                <th class="py-3 px-4 text-center">Trạng Thái</th>
                                <th class="py-3 px-4 text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($recentTickets as $t): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:underline">
                                            <?php echo htmlspecialchars($t['ticket_code']); ?>
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:text-indigo-600 transition block max-w-xs truncate">
                                            <?php echo htmlspecialchars($t['title']); ?>
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($t['requester_name']); ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?php echo htmlspecialchars($t['priority_name']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            ● <?php echo htmlspecialchars($t['status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition">
                                            Chi tiết →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tình trạng tải 5 KTV (4 cột) -->
            <div class="lg:col-span-4 bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span>👨‍💻</span> Giám Sát Tải KTV (Ngưỡng 15 Phiếu)
                    </h3>
                    <a href="<?php echo $appBase; ?>agent/index.php" class="text-indigo-600 hover:underline text-[11px] font-bold">Xem Workspace</a>
                </div>

                <div class="space-y-3">
                    <?php foreach ($agents as $ag): ?>
                        <?php 
                        $maxCapacity = 15;
                        $loadPercent = min(100, round(($ag['active_load'] / $maxCapacity) * 100));
                        $isOverloaded = ($ag['active_load'] >= $maxCapacity);
                        ?>
                        <div class="p-3 rounded-2xl border <?php echo $isOverloaded ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50 border-slate-200'; ?> space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800"><?php echo htmlspecialchars($ag['name']); ?></span>
                                <span class="font-mono font-bold <?php echo $isOverloaded ? 'text-rose-600' : 'text-slate-600'; ?>">
                                    <?php echo $ag['active_load']; ?> / <?php echo $maxCapacity; ?>
                                </span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full <?php echo $isOverloaded ? 'bg-rose-600' : ($loadPercent > 60 ? 'bg-amber-500' : 'bg-emerald-500'); ?>" style="width: <?php echo $loadPercent; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>