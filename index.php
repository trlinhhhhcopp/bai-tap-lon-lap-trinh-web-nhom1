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
require_once __DIR__ . '/app/Services/InternalMailboxService.php';

use App\Services\AuthService;
use App\Services\InternalMailboxService;
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
// 4. XỬ LÝ QUÊN MẬT KHẨU (GỬI THƯ EMAIL TRƯỜNG & OTP DẠNG CHỮ CÁI)
// -----------------------------------------------------------------------------
if ($action === 'forgot_password') {
    $flash = get_flash();
    $step  = $_POST['step'] ?? ($_GET['step'] ?? 'input_identifier');

    // 4.1. Gửi mã OTP dạng chữ cái về Email trường
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'send_otp') {
        $identifier = trim($_POST['identifier'] ?? '');
        try {
            $user = \App\Models\User::findByEmailOrCode($identifier);
            if (!$user) {
                throw new \Exception("Không tìm thấy tài khoản với thông tin [{$identifier}]! Vui lòng kiểm tra lại Mã sinh viên hoặc Email trường cấp.");
            }
            if ($user['is_active'] == 0 || $user['academic_status'] === 'expelled') {
                throw new \Exception("Tài khoản này đã bị khóa kỷ luật, không thể sử dụng tính năng tự khôi phục mật khẩu! Vui lòng gửi đơn cứu hộ tài khoản.");
            }

            // Sinh mã OTP dạng chữ cái in hoa (6 ký tự, ví dụ: TUDKHA, HLMNPQ...)
            $otp = \App\Models\PasswordReset::createToken($user['id'], $user['email'], 15);

            // Gửi OTP về đúng Email trường của sinh viên/người dùng
            $targetEmail = $user['email'];
            $_SESSION['reset_otp_data'] = [
                'user_id'        => (int)$user['id'],
                'user_name'      => $user['name'],
                'user_code'      => $user['user_code'],
                'email'          => $user['email'],
                'target_email'   => $targetEmail,
                'otp'            => $otp,
                'sent_at'        => time(),
                'expires_at'     => time() + (15 * 60),
                'verified'       => false,
            ];

            // Gửi thư chứa mã OTP đến Email trường của sinh viên/giảng viên (@daihocthudo.edu.vn)
            $mailSubject = "[TUD-Helpdesk] Mã OTP xác nhận khôi phục mật khẩu tài khoản học thuật";
            $mailBody = "
                <p>Kính gửi bạn <strong>{$user['name']}</strong> (Mã định danh: <code>{$user['user_code']}</code>),</p>
                <p>Hệ thống Hỗ trợ Kỹ thuật & CNTT - Trường Đại Học Thủ Đô Hà Nội đã tiếp nhận yêu cầu cấp lại mật khẩu cho tài khoản học thuật của bạn.</p>
                <div style='margin: 18px 0; padding: 20px; background: #0f172a; border-radius: 14px; border: 2px solid #f59e0b; text-align: center;'>
                    <div style='color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold;'>MÃ XÁC THỰC OTP (GỒM 6 CHỮ CÁI IN HOA):</div>
                    <div style='color: #fbbf24; font-family: monospace; font-size: 36px; font-weight: 900; letter-spacing: 8px; margin: 10px 0;'>{$otp}</div>
                    <div style='color: #cbd5e1; font-size: 11.5px;'>Hiệu lực mã: 15 phút. Tuyệt đối không chia sẻ mã này cho bất kỳ ai để đảm bảo an toàn tài khoản.</div>
                </div>
                <p>Vui lòng quay lại Cổng Helpdesk và nhập đúng 6 chữ cái trên vào ô xác thực để hoàn tất đổi mật khẩu.</p>
                <p style='margin-top: 24px; color: #64748b; font-size: 11.5px; border-top: 1px solid #e2e8f0; padding-top: 12px;'>
                    Trân trọng,<br>
                    <strong>Trung tâm CNTT & Thư viện - Trường Đại Học Thủ Đô Hà Nội</strong><br>
                    Email hỗ trợ: helpdesk@daihocthudo.edu.vn | Hotline: (024) 3833 0708
                </p>
            ";
            
            // Gửi mail thực tế qua SMTP Gmail đến đúng email trường của người dùng
            if (class_exists('\App\Services\MailService')) {
                \App\Services\MailService::send($targetEmail, $user['name'], $mailSubject, $mailBody);
            }
            if (class_exists('\App\Services\InternalMailboxService')) {
                \App\Services\InternalMailboxService::send($targetEmail, $user['name'], $mailSubject, $mailBody, $otp);
            }

            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "Đã gửi mã xác thực OTP về hòm thư trường của bạn: <strong>{$targetEmail}</strong>! Vui lòng kiểm tra hộp thư đến (hoặc mục Thư rác/Spam) và nhập mã bên dưới."
            ];
            header('Location: ' . $appBase . 'index.php?action=forgot_password&step=enter_otp');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => $e->getMessage()
            ];
            header('Location: ' . $appBase . 'index.php?action=forgot_password');
            exit;
        }
    }

    // 4.2. Xác thực mã OTP dạng chữ cái
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'verify_otp') {
        $otpInput = strtoupper(trim($_POST['otp_input'] ?? ''));
        $otpData  = $_SESSION['reset_otp_data'] ?? null;

        if (!$otpData || empty($otpData['otp'])) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Phiên xác thực đã hết hạn hoặc chưa được tạo. Vui lòng gửi lại mã OTP!'
            ];
            header('Location: ' . $appBase . 'index.php?action=forgot_password');
            exit;
        }

        if (time() > $otpData['expires_at']) {
            unset($_SESSION['reset_otp_data']);
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Mã OTP đã hết hạn (quá 15 phút). Vui lòng yêu cầu cấp lại mã mới!'
            ];
            header('Location: ' . $appBase . 'index.php?action=forgot_password');
            exit;
        }

        if ($otpInput !== strtoupper($otpData['otp'])) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => "Mã OTP [{$otpInput}] không chính xác! Vui lòng kiểm tra lại hòm thư email trường và nhập đúng 6 ký tự chữ cái."
            ];
            header('Location: ' . $appBase . 'index.php?action=forgot_password&step=enter_otp');
            exit;
        }

        // Nhập đúng mã OTP dạng chữ cái -> Chuyển sang bước Đổi mật khẩu
        $_SESSION['reset_otp_data']['verified'] = true;
        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => '✅ Xác thực mã OTP thành công! Vui lòng nhập mật khẩu mới của bạn bên dưới.'
        ];
        header('Location: ' . $appBase . 'index.php?action=reset_password');
        exit;
    }

    $activeOtp = $_SESSION['reset_otp_data'] ?? null;
    $hasOtp = ($activeOtp && time() <= $activeOtp['expires_at'] && empty($activeOtp['verified']));
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Khôi Phục Mật Khẩu (SSPR OTP) - Helpdesk ĐH Thủ Đô</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <div class="flex-1 max-w-xl mx-auto w-full px-4 py-8 flex flex-col justify-center">
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        🔑 SSPR · KHÔI PHỤC MẬT KHẨU QUA EMAIL TRƯỜNG CẤP
                    </span>
                    <h2 class="text-xl font-black text-slate-900 mt-2">Quên Mật Khẩu Tài Khoản</h2>
                    <p class="text-xs text-slate-500 mt-1">Mã OTP gồm 6 chữ cái sẽ được gửi tới Email trường của bạn (Hiệu lực: 15 phút).</p>
                </div>

                <?php if ($flash): ?>
                    <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <?php if ($hasOtp || $step === 'enter_otp'): ?>
                    <!-- KHUNG THÔNG BÁO GỬI MÃ OTP VỀ HÒM THƯ (BẢO MẬT: KHÔNG IN SẴN MÃ TRÊN MÀN HÌNH) -->
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
                                    Chủ tài khoản: <strong class="text-slate-900"><?php echo htmlspecialchars($activeOtp['user_name'] ?? 'Sinh viên'); ?></strong> 
                                    (Mã: <code class="text-indigo-600 font-bold"><?php echo htmlspecialchars($activeOtp['user_code'] ?? ''); ?></code>)
                                </div>
                                <div class="text-slate-600">
                                    Hòm thư trường nhận mã xác thực: <strong class="text-sky-800 font-mono text-sm"><?php echo htmlspecialchars($activeOtp['target_email'] ?? $activeOtp['email'] ?? ''); ?></strong>
                                </div>
                            </div>
                            
                            <p class="text-slate-600 leading-relaxed text-[11.5px]">
                                🔒 <strong>Bảo mật tài khoản:</strong> Hệ thống đã gửi thư xác thực kèm mã OTP gồm <strong>6 chữ cái in hoa</strong> đến hòm thư trường trên. Bạn hãy mở hòm thư của mình (hộp thư đến hoặc mục Thư rác/Spam), lấy đúng 6 chữ cái và nhập vào ô xác thực bên dưới.
                            </p>
                        </div>
                    </div>

                    <!-- 2. FORM NHẬP MÃ OTP CHỮ CÁI -->
                    <form method="POST" action="<?php echo $appBase; ?>index.php?action=forgot_password" class="space-y-4 text-xs pt-1">
                        <input type="hidden" name="action" value="forgot_password" />
                        <input type="hidden" name="step" value="verify_otp" />

                        <div>
                            <label class="block font-bold text-slate-800 mb-1.5">
                                Nhập mã OTP gồm 6 chữ cái xác thực:
                            </label>
                            <input type="text" name="otp_input" required maxlength="6" autofocus placeholder="VD: ABCXYZ (6 chữ cái)" 
                                   style="text-transform: uppercase; letter-spacing: 0.35em;"
                                   class="w-full px-4 py-3 bg-slate-50 border-2 border-indigo-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-center font-mono text-2xl font-black text-indigo-950 uppercase" />
                            <p class="text-[11px] text-slate-400 mt-1 text-center">Hệ thống tự động chuyển thành chữ in hoa khi bạn gõ.</p>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2 text-xs">
                            <span>✅</span> Xác Nhận Mã OTP Để Đổi Mật Khẩu
                        </button>
                    </form>

                    <div class="flex items-center justify-between text-[11px] pt-1">
                        <form method="POST" action="<?php echo $appBase; ?>index.php?action=forgot_password" class="inline">
                            <input type="hidden" name="action" value="forgot_password" />
                            <input type="hidden" name="step" value="send_otp" />
                            <input type="hidden" name="identifier" value="<?php echo htmlspecialchars($activeOtp['user_code'] ?? $activeOtp['email'] ?? ''); ?>" />
                            <button type="submit" class="text-indigo-600 hover:underline font-semibold flex items-center gap-1">
                                <span>🔄</span> Gửi lại mã OTP khác
                            </button>
                        </form>
                        <a href="<?php echo $appBase; ?>index.php?action=forgot_password&step=input_identifier" class="text-slate-500 hover:underline">
                            ← Đổi tài khoản khác
                        </a>
                    </div>

                <?php else: ?>
                    <!-- FORM NHẬP MÃ SINH VIÊN HOẶC EMAIL TRƯỜNG -->
                    <form method="POST" action="<?php echo $appBase; ?>index.php?action=forgot_password" class="space-y-4 text-xs">
                        <input type="hidden" name="action" value="forgot_password" />
                        <input type="hidden" name="step" value="send_otp" />

                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">Nhập Mã Sinh Viên hoặc Email trường của bạn:</label>
                            <input type="text" name="identifier" required placeholder="Ví dụ: 224001736 hoặc 224001729@daihocthudo.edu.vn" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                            <p class="text-[11px] text-slate-400 mt-1">Hệ thống sẽ gửi mã OTP gồm các chữ cái về địa chỉ Email trường cấp của bạn.</p>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                            <span>🚀</span> Gửi Mã OTP Đến Email Trường (15 Phút)
                        </button>
                    </form>
                <?php endif; ?>

                <div class="pt-2 text-center text-xs border-t border-slate-100">
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
// 5. XỬ LÝ ĐẶT LẠI MẬT KHẨU MỚI (SAU KHI NHẬP ĐÚNG OTP CHỮ CÁI)
// -----------------------------------------------------------------------------
if ($action === 'reset_password') {
    $otpData = $_SESSION['reset_otp_data'] ?? null;
    $flash = get_flash();

    // Kiểm tra đã xác thực OTP thành công chưa
    if (!$otpData || empty($otpData['verified']) || empty($otpData['user_id'])) {
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Vui lòng xác thực mã OTP gửi về Email trường trước khi đổi mật khẩu!'
        ];
        header('Location: ' . $appBase . 'index.php?action=forgot_password');
        exit;
    }

    $userId = (int)$otpData['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (strlen($newPass) < 6) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Mật khẩu mới phải có tối thiểu 6 ký tự!'
            ];
            header('Location: ' . $appBase . 'index.php?action=reset_password');
            exit;
        }

        if ($newPass !== $confirmPass) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Mật khẩu xác nhận không trùng khớp với mật khẩu mới!'
            ];
            header('Location: ' . $appBase . 'index.php?action=reset_password');
            exit;
        }

        try {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            // Đặt is_first_login = 0 để người dùng sau khi đổi mật khẩu đăng nhập lại BÌNH THƯỜNG
            // hoàn toàn không bị chuyển sang luồng bắt buộc đổi mật khẩu lần đầu!
            $upStmt = $pdo->prepare("UPDATE users SET password_hash = :hash, is_first_login = 0, failed_attempts = 0, locked_until = NULL, lock_reason = NULL WHERE id = :id");
            $upStmt->execute(['hash' => $hash, 'id' => $userId]);

            // Xóa token trong bảng password_resets
            $delStmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid");
            $delStmt->execute(['uid' => $userId]);

            // Xóa session OTP
            unset($_SESSION['reset_otp_data']);

            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => '🎉 Đặt lại mật khẩu thành công! Bạn có thể đăng nhập bình thường ngay bây giờ.'
            ];
            header('Location: ' . $appBase . 'index.php');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Lỗi khi cập nhật mật khẩu: ' . $e->getMessage()
            ];
            header('Location: ' . $appBase . 'index.php?action=reset_password');
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
    <body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <div class="flex-1 max-w-md mx-auto w-full px-4 py-10 flex flex-col justify-center">
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        🛡️ ĐỔI MẬT KHẨU MỚI
                    </span>
                    <h2 class="text-xl font-black text-slate-900 mt-2">Thiết Lập Mật Khẩu Mới</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Tài khoản: <strong><?php echo htmlspecialchars($otpData['user_name']); ?></strong> (<?php echo htmlspecialchars($otpData['user_code']); ?>)
                    </p>
                </div>

                <?php if ($flash): ?>
                    <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $appBase; ?>index.php?action=reset_password" class="space-y-4 text-xs">
                    <input type="hidden" name="action" value="reset_password" />

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mật khẩu mới (Tối thiểu 6 ký tự):</label>
                        <input type="password" name="new_password" required minlength="6" autofocus placeholder="Nhập mật khẩu mới..." 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-xs font-semibold" />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Xác nhận lại mật khẩu mới:</label>
                        <input type="password" name="confirm_password" required minlength="6" placeholder="Nhập lại mật khẩu mới..." 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-xs font-semibold" />
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-md shadow-emerald-100 flex items-center justify-center gap-2">
                        <span>✅</span> Cập Nhật Mật Khẩu & Đăng Nhập
                    </button>
                </form>

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
// 6. XỬ LÝ BÁO KHÓA TÀI KHOẢN & CỨU HỘ KHẨN CẤP (GUEST TICKET)
// -----------------------------------------------------------------------------
if ($action === 'guest_ticket') {
    $flash = get_flash();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userCode     = trim($_POST['user_code'] ?? '');
        $name         = trim($_POST['name'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $issueTitle   = trim($_POST['issue_title'] ?? 'Yêu cầu mở khóa tài khoản');
        $description  = trim($_POST['description'] ?? '');

        if (empty($userCode) || empty($name) || empty($contactEmail) || empty($description)) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Vui lòng điền đầy đủ Mã SV, Họ tên, Email liên hệ cá nhân và Mô tả sự cố!'
            ];
            header('Location: ' . $appBase . 'index.php?action=guest_ticket');
            exit;
        }

        try {
            $user = \App\Models\User::findByEmailOrCode($userCode);
            $userId = $user ? (int)$user['id'] : 1;

            // Xử lý tệp đính kèm nếu có
            $attachmentPath = null;
            if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $fileTmp  = $_FILES['attachment']['tmp_name'];
                $fileName = $_FILES['attachment']['name'];
                $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

                if (in_array($ext, $allowed, true)) {
                    $upDir = __DIR__ . '/uploads/attachments/';
                    if (!is_dir($upDir)) {
                        mkdir($upDir, 0777, true);
                    }
                    $safeName = 'guest_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    if (move_uploaded_file($fileTmp, $upDir . $safeName)) {
                        $attachmentPath = 'uploads/attachments/' . $safeName;
                    }
                }
            }

            $ticketId = \App\Models\Ticket::create([
                'requester_id'     => $userId,
                'requester_name'   => $name,
                'assigned_to'      => 2, // Phân công ngay cho KTV Nguyễn Văn An (Chuyên trách SSO)
                'category_id'      => 1, // CAT_SSO: Tài khoản & Xác thực
                'priority_id'      => 2, // HIGH
                'is_guest_ticket'  => 1,
                'contact_email'    => $contactEmail,
                'title'            => "[CỨU HỘ KHẨN CẤP] {$issueTitle} - {$name} ({$userCode})",
                'description'      => "SĐT liên hệ: {$phone}\nChi tiết sự cố: {$description}",
                'system_affected'  => 'Portal SSO / Tài khoản',
                'attachment_path'  => $attachmentPath,
            ]);

            $createdTicket = \App\Models\Ticket::findById($ticketId);
            $ticketCode = $createdTicket['ticket_code'];

            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => "🎉 Đã gửi đơn cứu hộ thành công! Mã tra cứu duy nhất của bạn: <strong>{$ticketCode}</strong>. Yêu cầu đã được chuyển ngay đến <strong>KTV Nguyễn Văn An (Chuyên trách SSO)</strong> tiếp nhận xử lý trong vòng 2 giờ."
            ];
            header('Location: ' . $appBase . 'index.php?action=guest_lookup&code=' . urlencode($ticketCode));
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Lỗi khi gửi phiếu cứu hộ: ' . $e->getMessage()
            ];
            header('Location: ' . $appBase . 'index.php?action=guest_ticket');
            exit;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Báo Khóa Tài Khoản & Cứu Hộ Khẩn Cấp - Helpdesk ĐH Thủ Đô</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <div class="flex-1 max-w-2xl mx-auto w-full px-4 py-8">
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        🆘 QUY TRÌNH CỨU HỘ TÀI KHOẢN KHẨN CẤP (GUEST TICKET)
                    </span>
                    <h2 class="text-xl font-black text-slate-900 mt-2">Báo Cứu Hộ Mở Khóa Tài Khoản</h2>
                    <p class="text-xs text-slate-500 mt-1">Dành cho sinh viên, cán bộ bị khóa mềm 30 phút, quên mật khẩu hoặc không thể đăng nhập vào cổng trường.</p>
                </div>

                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-900 space-y-1">
                    <p class="font-bold">⚠️ Hướng dẫn cứu hộ:</p>
                    <p class="text-[11.5px] leading-relaxed">
                        Đơn cứu hộ sẽ gửi thẳng đến <strong>Kỹ thuật viên chuyên trách SSO (KTV Nguyễn Văn An)</strong> để xử lý mở khóa trong vòng <strong>2 giờ làm việc</strong>. Sau khi gửi, bạn sẽ nhận được <strong>Mã phiếu tra cứu</strong> để theo dõi tiến độ mà không cần đăng nhập.
                    </p>
                </div>

                <?php if ($flash): ?>
                    <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $appBase; ?>index.php?action=guest_ticket" enctype="multipart/form-data" class="space-y-4 text-xs">
                    <input type="hidden" name="action" value="guest_ticket" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Mã Sinh Viên / Cán Bộ: <span class="text-rose-500">*</span></label>
                            <input type="text" name="user_code" required placeholder="VD: 224001736" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition text-xs font-semibold" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Họ và tên người yêu cầu: <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="VD: Nguyễn Văn A" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition text-xs font-semibold" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email cá nhân nhận kết quả (Gmail...): <span class="text-rose-500">*</span></label>
                            <input type="email" name="contact_email" required placeholder="VD: thanhngan.personal@gmail.com" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition text-xs font-semibold" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Số điện thoại liên hệ: <span class="text-rose-500">*</span></label>
                            <input type="tel" name="phone" required placeholder="VD: 0912345678" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition text-xs font-semibold" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chọn sự cố cần cứu hộ: <span class="text-rose-500">*</span></label>
                        <select name="issue_title" class="w-full px-3.5 py-2.5 bg-rose-50/50 border border-rose-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 text-xs font-bold text-rose-900">
                            <option value="Tài khoản bị khóa mềm 30 phút do gõ sai mật khẩu quá 5 lần">Tài khoản bị khóa mềm 30 phút do gõ sai mật khẩu quá 5 lần</option>
                            <option value="Quên mật khẩu / Không nhớ mật khẩu tài khoản trường cấp">Quên mật khẩu / Không nhớ mật khẩu tài khoản trường cấp</option>
                            <option value="Tài khoản bị khóa cứng (kỷ luật / hết hạn khóa học)">Tài khoản bị khóa cứng (kỷ luật / hết hạn khóa học)</option>
                            <option value="Lỗi xác thực SSO / Không đăng nhập được vào Portal và Canvas">Lỗi xác thực SSO / Không đăng nhập được vào Portal và Canvas</option>
                            <option value="Sự cố tài khoản học thuật khác">Sự cố tài khoản học thuật khác...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mô tả hiện tượng lỗi / Lý do chi tiết: <span class="text-rose-500">*</span></label>
                        <textarea name="description" rows="3" required placeholder="Nhập rõ tình trạng tài khoản của bạn để KTV hỗ trợ mở khóa nhanh nhất..." 
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tệp ảnh minh chứng (Ảnh thẻ SV, CCCD hoặc ảnh chụp lỗi):</label>
                        <input type="file" name="attachment" accept="image/*,.pdf" 
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100" />
                        <p class="text-[11px] text-slate-400 mt-0.5">Chấp nhận JPG, PNG, PDF (tối đa 5MB). Giúp KTV đối chiếu mở khóa nhanh hơn.</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition shadow-md shadow-rose-200 flex items-center justify-center gap-2 text-xs">
                            <span>🚀</span> Gửi Yêu Cầu Cứu Hộ Ngay Đến KTV SSO
                        </button>
                    </div>
                </form>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                    <a href="<?php echo $appBase; ?>index.php?action=guest_lookup" class="text-indigo-600 hover:underline font-semibold">
                        🔍 Đã gửi đơn? Bấm vào đây để tra cứu tiến độ
                    </a>
                    <a href="<?php echo $appBase; ?>index.php" class="text-slate-500 hover:underline">
                        ← Quay lại đăng nhập
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
// 7. XỬ LÝ TRA CỨU TIẾN ĐỘ PHIẾU CỨU HỘ (GUEST LOOKUP)
// -----------------------------------------------------------------------------
if ($action === 'guest_lookup') {
    $code = trim($_GET['code'] ?? ($_POST['code'] ?? ''));
    $flash = get_flash();
    $ticket = null;
    $isInternalTicket = false;
    $replies = [];

    if (!empty($code)) {
        $found = \App\Models\Ticket::findByTicketCode($code);
        if ($found) {
            if (empty($found['is_guest_ticket'])) {
                $isInternalTicket = true;
            } else {
                $ticket = $found;
                // Lấy lịch sử trao đổi
                $rStmt = $pdo->prepare("SELECT tr.*, u.name as sender_name, u.role as sender_role 
                                        FROM ticket_replies tr 
                                        LEFT JOIN users u ON u.id = tr.user_id 
                                        WHERE tr.ticket_id = :tid AND tr.is_internal = 0 
                                        ORDER BY tr.created_at ASC");
                $rStmt->execute(['tid' => $ticket['id']]);
                $replies = $rStmt->fetchAll();
            }
        }
    }

    // Xử lý gửi phản hồi bổ sung từ khách
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guest_reply']) && $ticket) {
        $msg = trim($_POST['reply_message'] ?? '');
        if (!empty($msg)) {
            $repStmt = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message, is_internal, created_at) VALUES (:tid, :uid, :msg, 0, NOW())");
            $repStmt->execute([
                'tid' => $ticket['id'],
                'uid' => $ticket['requester_id'] ?: 1,
                'msg' => "[Người gửi bổ sung]: " . $msg
            ]);
            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => 'Đã gửi phản hồi thành công đến Kỹ thuật viên!'
            ];
            header('Location: ' . $appBase . 'index.php?action=guest_lookup&code=' . urlencode($code));
            exit;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tra Cứu Tiến Độ Phiếu Cứu Hộ - Helpdesk ĐH Thủ Đô</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <div class="flex-1 max-w-2xl mx-auto w-full px-4 py-8 space-y-6">
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-5">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        🔍 TRA CỨU TIẾN ĐỘ THỜI GIAN THỰC
                    </span>
                    <h2 class="text-xl font-black text-slate-900 mt-2">Tra Cứu Phiếu Cứu Hộ</h2>
                    <p class="text-xs text-slate-500 mt-1">Nhập mã phiếu cứu hộ của bạn để kiểm tra tình trạng mở khóa tài khoản.</p>
                </div>

                <?php if ($flash): ?>
                    <div class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <form method="GET" action="<?php echo $appBase; ?>index.php" class="flex gap-2">
                    <input type="hidden" name="action" value="guest_lookup" />
                    <input type="text" name="code" value="<?php echo htmlspecialchars($code); ?>" required 
                           placeholder="Nhập mã phiếu (VD: HD-2026-2910-0Z010E)..." 
                           class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-xs uppercase" />
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-indigo-100 whitespace-nowrap">
                        🔍 Tra Cứu
                    </button>
                </form>

                <?php if (!empty($code)): ?>
                    <?php if ($isInternalTicket): ?>
                        <div class="p-5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 space-y-2 text-center">
                            <span class="text-2xl">🔒</span>
                            <h4 class="font-bold text-sm">Phiếu Hỗ Trợ Nội Bộ Sinh Viên / Giảng Viên</h4>
                            <p class="text-slate-600 leading-relaxed max-w-md mx-auto">
                                Mã phiếu <strong><?php echo htmlspecialchars($code); ?></strong> được tạo từ tài khoản nội bộ Nhà trường. Để đảm bảo tính riêng tư và bảo mật, vui lòng đăng nhập vào tài khoản để xem chi tiết.
                            </p>
                            <div class="pt-2">
                                <a href="<?php echo $appBase; ?>index.php" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-xl text-xs inline-block">
                                    🔑 Đăng Nhập Cổng Sinh Viên
                                </a>
                            </div>
                        </div>

                    <?php elseif (!$ticket): ?>
                        <div class="p-5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1 text-center">
                            <p class="font-bold text-sm">⚠️ Không tìm thấy phiếu cứu hộ với mã [<?php echo htmlspecialchars($code); ?>]!</p>
                            <p class="text-[11.5px] text-slate-500">Vui lòng kiểm tra lại chính xác từng ký tự trong mã phiếu được cấp.</p>
                        </div>

                    <?php else: ?>
                        <!-- KẾT QUẢ PHIẾU CỨU HỘ GUEST TICKET -->
                        <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl space-y-4 text-xs">
                            <div class="flex items-center justify-between flex-wrap gap-2 pb-3 border-b border-slate-200">
                                <div>
                                    <span class="text-[11px] text-slate-400 font-mono">MÃ PHIẾU CỨU HỘ:</span>
                                    <h3 class="font-mono font-black text-indigo-700 text-base"><?php echo htmlspecialchars($ticket['ticket_code']); ?></h3>
                                </div>
                                <div>
                                    <?php 
                                    $statusMap = [
                                        'new'               => ['Mới tiếp nhận', 'bg-blue-50 text-blue-700 border-blue-200'],
                                        'in_progress'       => ['Đang xử lý mở khóa', 'bg-amber-50 text-amber-700 border-amber-200'],
                                        'waiting_requester' => ['Chờ phản hồi xác nhận', 'bg-purple-50 text-purple-700 border-purple-200'],
                                        'resolved'          => ['Đã giải quyết / Đã mở khóa', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        'closed'            => ['Đã đóng hoàn tất', 'bg-slate-100 text-slate-700 border-slate-200'],
                                    ];
                                    $st = $statusMap[$ticket['status']] ?? ['Chờ xử lý', 'bg-slate-100 text-slate-600 border-slate-200'];
                                    ?>
                                    <span class="px-3 py-1 rounded-full font-bold border text-xs <?php echo $st[1]; ?>">
                                        ● <?php echo $st[0]; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11.5px]">
                                <div>
                                    <span class="text-slate-500">Sự cố:</span>
                                    <strong class="text-slate-800 ml-1"><?php echo htmlspecialchars($ticket['title']); ?></strong>
                                </div>
                                <div>
                                    <span class="text-slate-500">KTV phụ trách:</span>
                                    <strong class="text-indigo-700 ml-1"><?php echo htmlspecialchars($ticket['agent_name'] ?: 'KTV Nguyễn Văn An (SSO)'); ?></strong>
                                </div>
                                <div>
                                    <span class="text-slate-500">Người yêu cầu:</span>
                                    <strong class="text-slate-800 ml-1"><?php echo htmlspecialchars($ticket['requester_name']); ?></strong>
                                </div>
                                <div>
                                    <span class="text-slate-500">Email nhận kết quả:</span>
                                    <?php 
                                    $cEmail = $ticket['contact_email'] ?? '';
                                    $masked = $cEmail;
                                    if (strpos($cEmail, '@') !== false) {
                                        [$userPart, $domainPart] = explode('@', $cEmail, 2);
                                        $masked = substr($userPart, 0, 3) . '***@' . $domainPart;
                                    }
                                    ?>
                                    <strong class="text-slate-800 ml-1"><?php echo htmlspecialchars($masked); ?></strong>
                                </div>
                                <div>
                                    <span class="text-slate-500">Thời gian gửi:</span>
                                    <span class="text-slate-700 ml-1"><?php echo htmlspecialchars($ticket['created_at']); ?></span>
                                </div>
                                <div>
                                    <span class="text-slate-500">Hạn xử lý SLA:</span>
                                    <span class="text-slate-700 ml-1"><?php echo htmlspecialchars($ticket['due_at']); ?></span>
                                </div>
                            </div>

                            <div class="bg-white p-3.5 rounded-xl border border-slate-200">
                                <span class="text-[11px] font-bold text-slate-500 block mb-1">Mô tả sự cố ban đầu:</span>
                                <p class="text-slate-700 whitespace-pre-line leading-relaxed"><?php echo htmlspecialchars($ticket['description']); ?></p>
                                <?php if (!empty($ticket['attachment_path'])): ?>
                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center gap-2">
                                        <span class="text-slate-400">📎</span>
                                        <a href="<?php echo $appBase . htmlspecialchars($ticket['attachment_path']); ?>" target="_blank" class="text-indigo-600 font-semibold hover:underline">
                                            Xem tệp minh chứng đã đính kèm ↗
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- LỊCH SỬ PHẢN HỒI CỦA KTV -->
                            <div class="space-y-3 pt-2">
                                <h4 class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>💬</span> Quá Trình Trao Đổi & Kết Quả Xử Lý (<?php echo count($replies); ?> lượt):
                                </h4>

                                <?php if (empty($replies)): ?>
                                    <p class="text-slate-400 italic text-center py-3 bg-white rounded-xl border border-dashed border-slate-200">
                                        Kỹ thuật viên đang tiếp nhận kiểm tra thông tin của bạn. Phản hồi sẽ hiển thị tại đây ngay khi có kết quả.
                                    </p>
                                <?php else: ?>
                                    <div class="space-y-2">
                                        <?php foreach ($replies as $rep): ?>
                                            <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-1">
                                                <div class="flex items-center justify-between text-[11px]">
                                                    <strong class="<?php echo ($rep['sender_role'] === 'agent' || $rep['sender_role'] === 'admin') ? 'text-indigo-700' : 'text-slate-800'; ?>">
                                                        <?php echo htmlspecialchars($rep['sender_name'] ?: 'Kỹ thuật viên'); ?>
                                                        <?php if ($rep['sender_role'] === 'agent'): ?><span class="font-normal text-slate-400">(KTV)</span><?php endif; ?>
                                                    </strong>
                                                    <span class="text-slate-400"><?php echo htmlspecialchars($rep['created_at']); ?></span>
                                                </div>
                                                <p class="text-slate-700 whitespace-pre-line leading-relaxed"><?php echo htmlspecialchars($rep['message']); ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- FORM PHẢN HỒI THÊM TỪ KHÁCH -->
                                <?php if ($ticket['status'] !== 'closed'): ?>
                                    <form method="POST" action="<?php echo $appBase; ?>index.php?action=guest_lookup&code=<?php echo urlencode($code); ?>" class="space-y-2 pt-2">
                                        <input type="hidden" name="action" value="guest_lookup" />
                                        <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>" />
                                        <input type="hidden" name="guest_reply" value="1" />
                                        <textarea name="reply_message" rows="2" required placeholder="Gửi thêm tin nhắn hoặc cung cấp thêm thông tin cho KTV..." 
                                                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs"></textarea>
                                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition text-xs shadow-sm">
                                            Gửi Thêm Phản Hồi
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="pt-2 text-center text-xs border-t border-slate-100">
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
    <body class="bg-slate-900 text-slate-100 font-sans antialiased min-h-screen flex flex-col justify-between">

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
                        <a href="<?php echo $appBase; ?>index.php?action=guest_ticket" class="text-rose-600 hover:underline font-semibold">
                            🆘 Cứu hộ tài khoản khi bị khóa?
                        </a>
                        <a href="<?php echo $appBase; ?>index.php?action=guest_lookup" class="text-indigo-600 hover:underline">
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
    // Truy vấn chi tiết hồ sơ sinh viên kèm thông tin lớp & học thuật
    $studentStmt = $pdo->prepare("
        SELECT u.*, COALESCE(c.name, 'Chưa phân lớp') AS class_name, COALESCE(c.class_code, 'N/A') AS class_code 
        FROM users u 
        LEFT JOIN classes c ON u.class_id = c.id 
        WHERE u.id = :id
    ");
    $studentStmt->execute([':id' => $userId]);
    $studentProfile = $studentStmt->fetch() ?: $currentUser;

    // Thống kê riêng của sinh viên này
    $myTicketsCount  = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId}")->fetchColumn();
    $myActiveCount   = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE requester_id = {$userId} AND status IN ('new', 'in_progress', 'waiting_requester')")->fetchColumn();
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
        <title>Cổng Sinh Viên - Helpdesk Đại Học Thủ Đô Hà Nội</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            <!-- 1. BANNER CHÀO MỪNG SINH VIÊN (TƯƠI SÁNG, HIỆN ĐẠI THEO CHUẨN CỔNG TRƯỜNG ĐH) -->
            <div class="bg-gradient-to-r from-sky-600 via-indigo-600 to-indigo-800 text-white rounded-3xl p-6 md:p-8 shadow-xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md text-white border border-white/30 flex items-center justify-center text-2xl font-black shadow-lg shrink-0">
                        🎓
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white border border-white/30">
                                🏛️ CỔNG THÔNG TIN SINH VIÊN
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-400/25 text-emerald-200 border border-emerald-400/40">
                                ● Tài khoản đang hoạt động
                            </span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                            Xin chào, <?php echo htmlspecialchars($currentUser['name']); ?>!
                        </h1>
                        <p class="text-xs md:text-sm text-sky-100">
                            Mã SV: <strong class="text-white font-mono"><?php echo htmlspecialchars($currentUser['user_code']); ?></strong> · 
                            Lớp: <strong class="text-white"><?php echo htmlspecialchars($studentProfile['class_code']); ?></strong> · 
                            Khóa: <span class="text-sky-100">Đại học chính quy 2024</span>
                        </p>
                        <p class="text-[11.5px] text-sky-200/90">
                            Khoa Công Nghệ Thông Tin · Trường Đại Học Thủ Đô Hà Nội
                        </p>
                    </div>
                </div>

                <!-- Menu điều hướng các mục trên đầu (như phần admin làm) -->
                <div class="relative z-10 flex items-center gap-2 flex-wrap">
                    <a href="<?php echo $appBase; ?>tickets/create.php" 
                       class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs">
                        <span>➕</span> Báo Sự Cố Mới
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/index.php" 
                       class="px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white font-bold rounded-2xl border border-white/30 transition flex items-center gap-1.5 text-xs">
                        <span>📋</span> Phiếu Của Tôi
                    </a>
                    <a href="<?php echo $appBase; ?>NguyenAnhNhi_SinhVien/index.php?action=ratings" 
                       class="px-4 py-2.5 bg-amber-500/80 hover:bg-amber-500 text-white font-bold rounded-2xl border border-amber-400/40 transition flex items-center gap-1.5 text-xs">
                        <span>⭐</span> Đánh Giá CSAT
                    </a>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <!-- 2. BA THẺ CHỈ SỐ NHANH TƯƠI SÁNG (NỀN TRẮNG SÁCH SẼ) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Thông Báo & Yêu Cầu</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-900"><?php echo $myTicketsCount; ?></span>
                            <span class="text-xs text-sky-600 font-bold">phiếu hỗ trợ</span>
                        </div>
                        <span class="text-[11px] text-slate-400 block mt-1">0 thông báo mới chưa đọc</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">
                        🔔
                    </div>
                </div>

                <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Phiếu Đang Xử Lý & Công Nợ</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-amber-600"><?php echo $myActiveCount; ?></span>
                            <span class="text-xs text-amber-700 font-bold">đang khắc phục</span>
                        </div>
                        <span class="text-[11px] text-slate-400 block mt-1">0 đ công nợ cần xử lý</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        ⏳
                    </div>
                </div>

                <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block mb-1">Đã Giải Quyết & Tiến Độ</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-emerald-600"><?php echo $myResolvedCount; ?></span>
                            <span class="text-xs text-emerald-700 font-bold">đã hoàn tất</span>
                        </div>
                        <span class="text-[11px] text-slate-400 block mt-1">78 TC tiến độ học tập (3.69 GPA)</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                        ✅
                    </div>
                </div>
            </div>



            <!-- 4. BẢNG DANH SÁCH PHIẾU CỦA RIÊNG SINH VIÊN NÀY -->
            <div class="bg-white border border-slate-200/90 rounded-3xl shadow-sm overflow-hidden space-y-3">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>🎫</span> MỤC PHIẾU HỖ TRỢ RIÊNG CỦA TÔI (CÁCH LY DỮ LIỆU)
                        </h2>
                        <p class="text-[11.5px] text-slate-500 mt-0.5">Sinh viên chỉ xem và trao đổi trên các phiếu do chính mình gửi, bảo mật tuyệt đối.</p>
                    </div>
                    <a href="<?php echo $appBase; ?>tickets/create.php" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
                        <span>➕</span> Gửi Phiếu Mới
                    </a>
                </div>

                <?php if (empty($myTickets)): ?>
                    <div class="p-10 text-center text-slate-400 text-xs space-y-2">
                        <span class="text-3xl block">📭</span>
                        <p class="text-sm font-bold text-slate-600">Hiện bạn chưa gửi yêu cầu hỗ trợ nào.</p>
                        <p class="text-[11.5px] text-slate-400">Khi gặp sự cố kỹ thuật phòng máy, phần mềm hoặc tài khoản, hãy nhấn nút <strong>"Gửi Phiếu Mới"</strong> bên trên nhé!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
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
                                        'closed'            => 'bg-slate-100 text-slate-600 border-slate-200',
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
                                        <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:underline">
                                                <?php echo htmlspecialchars($t['ticket_code']); ?>
                                            </a>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:text-indigo-600 transition block max-w-sm truncate">
                                                <?php echo htmlspecialchars($t['title']); ?>
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10.5px] text-slate-400 font-normal">Gửi lúc: <?php echo htmlspecialchars($t['created_at']); ?></span>
                                                <?php if (!empty($t['attachment_path'])): ?>
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">
                                                        📎 Có minh chứng
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            <?php echo htmlspecialchars($t['category_name']); ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border <?php echo $statusBadges[$t['status']] ?? 'bg-slate-100 text-slate-600 border-slate-200'; ?>">
                                                ● <?php echo $statusLabels[$t['status']] ?? $t['status']; ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            <?php echo htmlspecialchars($t['agent_name'] ?: 'Đang phân công...'); ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <?php if ($t['rating_score']): ?>
                                                <span class="font-bold text-amber-500 font-mono text-sm"><?php echo $t['rating_score']; ?> ★</span>
                                            <?php elseif ($t['status'] === 'resolved' || $t['status'] === 'closed'): ?>
                                                <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>#csat-box" 
                                                   class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg text-[10.5px] font-bold transition inline-block">
                                                    Chấm sao ★
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300 text-[11px]">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl transition inline-block text-xs">
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