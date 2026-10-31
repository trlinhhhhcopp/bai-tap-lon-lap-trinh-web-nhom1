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

// Bắt buộc đổi mật khẩu lần đầu: nếu user có is_first_login == 1 thì không được đi đâu khác ngoài first_login hoặc logout
if (!empty($_SESSION['user']) && !empty($_SESSION['user']['is_first_login']) && $_SESSION['user']['is_first_login'] == 1) {
    if (!in_array($action, ['first_login', 'logout'], true)) {
        header('Location: ' . $appBase . 'index.php?action=first_login');
        exit;
    }
}

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

    // Kiểm tra khóa mềm do nhập sai quá 5 lần (Soft Lock 30 phút theo Page 1)
    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        $remainingMin = max(1, (int)ceil((strtotime($user['locked_until']) - time()) / 60));
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => "🔒 Tài khoản của bạn đang bị TẠM KHÓA do nhập sai mật khẩu quá 5 lần liên tiếp! Vui lòng thử lại sau <strong>{$remainingMin} phút</strong> hoặc sử dụng tính năng <strong>Quên mật khẩu</strong> / <strong>Cứu hộ tài khoản</strong>."
        ];
        header('Location: ' . $appBase . 'index.php');
        exit;
    }

    if ($user['is_active'] == 0) {
        $reason = ($user['lock_reason'] === 'banned_rescue_abuse') 
            ? 'vi phạm/nghịch trạm cứu hộ (Bị khóa vĩnh viễn)' 
            : (($user['lock_reason'] === 'expelled') ? 'kỷ luật Buộc thôi học' : 'hết hạn niên khóa hoặc vi phạm quy chế');
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => "⛔ Tài khoản bị KHÓA CỨNG do {$reason}. Vui lòng liên hệ Phòng Đào tạo hoặc Trung tâm CNTT!"
        ];
        header('Location: ' . $appBase . 'index.php');
        exit;
    }

    // Xác thực mật khẩu theo chuẩn bảo mật BCrypt (Không dùng backdoor 123456)
    $passValid = password_verify($password, $user['password_hash']);

    if (!$passValid) {
        $newFailed = (int)($user['failed_attempts'] ?? 0) + 1;
        if ($newFailed >= 5) {
            $lockUntil = date('Y-m-d H:i:s', time() + 30 * 60);
            $pdo->prepare("UPDATE users SET failed_attempts = :f, locked_until = :lu, lock_reason = 'wrong_password_5_times' WHERE id = :id")
                ->execute(['f' => $newFailed, 'lu' => $lockUntil, 'id' => $user['id']]);
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => '🔒 <strong>BÁO ĐỘNG BẢO MẬT:</strong> Bạn đã nhập sai mật khẩu 5 lần liên tiếp! Tài khoản đã bị <strong>TẠM KHÓA 30 PHÚT</strong>. Vui lòng bấm "Quên mật khẩu" bên dưới để khôi phục hoặc quay lại sau 30 phút.'
            ];
        } else {
            $remaining = 5 - $newFailed;
            $pdo->prepare("UPDATE users SET failed_attempts = :f WHERE id = :id")
                ->execute(['f' => $newFailed, 'id' => $user['id']]);
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => "Mật khẩu không chính xác! Bạn còn <strong>{$remaining} lần thử</strong> trước khi tài khoản bị tạm khóa 30 phút."
            ];
        }
        header('Location: ' . $appBase . 'index.php');
        exit;
    }

    // Đăng nhập thành công -> Reset số lần sai và mở khóa tạm
    $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id")->execute(['id' => $user['id']]);
    $user['failed_attempts'] = 0;
    $user['locked_until'] = null;

    $_SESSION['user'] = $user;

    // Kiểm tra BẮT BUỘC ĐỔI MẬT KHẨU LẦN ĐẦU (Page 1)
    if (!empty($user['is_first_login']) && $user['is_first_login'] == 1) {
        $_SESSION['flash'] = [
            'type'    => 'warning',
            'message' => '⚠️ Tài khoản đang sử dụng mật khẩu khởi tạo mặc định. Theo quy định an toàn CNTT, <strong>bắt buộc phải đổi mật khẩu mới</strong> để kích hoạt tài khoản!'
        ];
        header('Location: ' . $appBase . 'index.php?action=first_login');
        exit;
    }

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
                    <div data-auto-dismiss="4500" class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
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
                'message' => '🎉 Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại bằng mật khẩu mới của bạn để vào hệ thống.'
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
                    <div data-auto-dismiss="4500" class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $appBase; ?>index.php?action=reset_password" class="space-y-4 text-xs">
                    <input type="hidden" name="action" value="reset_password" />

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mật khẩu mới (Tối thiểu 6 ký tự):</label>
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
                        <label class="block font-bold text-slate-700 mb-1">Xác nhận lại mật khẩu mới:</label>
                        <div class="relative">
                            <input id="reset-confirm-password" type="password" name="confirm_password" required minlength="6" autocomplete="new-password" placeholder="Nhập lại mật khẩu mới..."
                                   class="w-full px-4 pr-12 py-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                            <button type="button" data-password-toggle aria-controls="reset-confirm-password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500 rounded-r-xl">
                                <svg data-password-show-icon aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                <svg data-password-hide-icon aria-hidden="true" class="hidden w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                            </button>
                        </div>
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
// 5.5. XỬ LÝ BẮT BUỘC ĐỔI MẬT KHẨU KHỞI TẠO LẦN ĐẦU (PAGE 1)
// -----------------------------------------------------------------------------
if ($action === 'first_login') {
    if (empty($_SESSION['user'])) {
        header('Location: ' . $appBase . 'index.php');
        exit;
    }

    $firstUser = $_SESSION['user'];
    $flash = get_flash();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (strlen($newPass) < 6) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Mật khẩu mới phải có tối thiểu 6 ký tự!'
            ];
            header('Location: ' . $appBase . 'index.php?action=first_login');
            exit;
        }

        if ($newPass === '123456') {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Mật khẩu mới không được trùng với mật khẩu mặc định 123456! Vui lòng chọn mật khẩu mới an toàn hơn.'
            ];
            header('Location: ' . $appBase . 'index.php?action=first_login');
            exit;
        }

        if ($newPass !== $confirmPass) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Mật khẩu xác nhận không trùng khớp với mật khẩu mới!'
            ];
            header('Location: ' . $appBase . 'index.php?action=first_login');
            exit;
        }

        try {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password_hash = :hash, is_first_login = 0, failed_attempts = 0, locked_until = NULL, lock_reason = NULL WHERE id = :id")
                ->execute(['hash' => $hash, 'id' => $firstUser['id']]);

            $_SESSION['user']['is_first_login'] = 0;
            $_SESSION['user']['password_hash']  = $hash;

            $_SESSION['flash'] = [
                'type'    => 'success',
                'message' => '🎉 Kích hoạt tài khoản và đổi mật khẩu thành công! Chào mừng bạn đến với Hệ thống Helpdesk ĐH Thủ Đô.'
            ];
            header('Location: ' . $appBase . 'index.php');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Lỗi khi cập nhật mật khẩu: ' . $e->getMessage()
            ];
            header('Location: ' . $appBase . 'index.php?action=first_login');
            exit;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Đổi Mật Khẩu Lần Đầu - Helpdesk ĐH Thủ Đô</title>
        <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    </head>
    <body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <div class="flex-1 max-w-md mx-auto w-full px-4 py-10 flex flex-col justify-center">
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-6">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        🔐 BẮT BUỘC ĐỔI MẬT KHẨU LẦN ĐẦU
                    </span>
                    <h2 class="text-xl font-black text-slate-900 mt-2">Kích Hoạt Tài Khoản Học Thuật</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Xin chào <strong><?php echo htmlspecialchars($firstUser['name']); ?></strong> (<code><?php echo htmlspecialchars($firstUser['user_code']); ?></code>).<br>
                        Tài khoản của bạn đang dùng mật khẩu khởi tạo mặc định (123456). Theo chính sách an toàn thông tin của Trường, bạn cần đổi mật khẩu mới để tiếp tục sử dụng.
                    </p>
                </div>

                <?php if ($flash): ?>
                    <div data-auto-dismiss="4500" class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : ($flash['type'] === 'warning' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'); ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $appBase; ?>index.php?action=first_login" class="space-y-4 text-xs">
                    <input type="hidden" name="action" value="first_login" />

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mật khẩu mới (Tối thiểu 6 ký tự, khác 123456):</label>
                        <input type="password" name="new_password" required minlength="6" autofocus placeholder="Nhập mật khẩu mới của bạn..."
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Xác nhận lại mật khẩu mới:</label>
                        <input type="password" name="confirm_password" required minlength="6" placeholder="Nhập lại mật khẩu mới..."
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold" />
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
                        <span>🔐</span> Lưu Mật Khẩu Mới & Vào Hệ Thống
                    </button>
                </form>

                <div class="pt-2 text-center text-xs border-t border-slate-100">
                    <a href="<?php echo $appBase; ?>index.php?action=logout" class="text-rose-600 hover:underline font-semibold">
                        🚪 Đăng xuất tài khoản này
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

        if (empty($userCode) || empty($contactEmail) || empty($description)) {
            $_SESSION['flash'] = [
                'type'    => 'danger',
                'message' => 'Vui lòng điền đầy đủ Mã SV/CB, Email liên hệ cá nhân và Mô tả sự cố!'
            ];
            header('Location: ' . $appBase . 'index.php?action=guest_ticket');
            exit;
        }

        try {
            $user = \App\Models\User::findByEmailOrCode($userCode);
            if ($user) {
                // Kiểm tra tài khoản có bị khóa vĩnh viễn do nghịch trạm cứu hộ/spam không (Page 8)
                if (($user['lock_reason'] ?? '') === 'banned_rescue_abuse' || ($user['lock_reason'] ?? '') === 'spam') {
                    $_SESSION['flash'] = [
                        'type'    => 'danger',
                        'message' => '⛔ Tài khoản này đã bị KHÓA VĨNH VIỄN do cố tình nghịch trạm cứu hộ hoặc spam vi phạm nội quy. Hệ thống từ chối tiếp nhận phiếu cứu hộ từ tài khoản này!'
                    ];
                    header('Location: ' . $appBase . 'index.php?action=guest_ticket');
                    exit;
                }
                if (empty($name)) {
                    $name = $user['name'];
                }
            } else {
                if (empty($name)) {
                    $name = 'Sinh viên / Cán bộ ' . $userCode;
                }
            }
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
                    <div data-auto-dismiss="4500" class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $appBase; ?>index.php?action=guest_ticket" enctype="multipart/form-data" class="space-y-4 text-sm">
                    <input type="hidden" name="action" value="guest_ticket" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Mã Sinh Viên / Cán Bộ: <span class="text-rose-500">*</span></label>
                            <input type="text" name="user_code" required placeholder="VD: 224001736" 
                                   autocomplete="username" class="w-full px-3.5 py-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Email cá nhân nhận kết quả (Gmail...): <span class="text-rose-500">*</span></label>
                            <input type="email" name="contact_email" required placeholder="VD: thanhngan.personal@gmail.com" 
                                   autocomplete="email" class="w-full px-3.5 py-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Chọn sự cố cần cứu hộ: <span class="text-rose-500">*</span></label>
                        <select name="issue_title" required class="w-full px-3.5 py-3 bg-rose-50/50 border border-rose-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 text-base md:text-sm font-bold text-rose-900">
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
                                  class="w-full px-3.5 py-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition text-base md:text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tệp ảnh minh chứng (Ảnh thẻ SV, CCCD hoặc ảnh chụp lỗi):</label>
                        <input type="file" name="attachment" accept="image/*,.pdf" 
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none text-sm file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100" />
                        <p class="text-[11px] text-slate-400 mt-0.5">Chấp nhận JPG, PNG, PDF (tối đa 5MB). Giúp KTV đối chiếu mở khóa nhanh hơn.</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 text-white font-bold rounded-xl transition shadow-md shadow-rose-200 flex items-center justify-center gap-2 text-sm">
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
                    <div data-auto-dismiss="4500" class="p-3.5 rounded-xl text-xs font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
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
$flash = ($currentUser['role'] ?? '') === 'agent'
    ? ($_SESSION['flash'] ?? null)
    : get_flash();

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
                <div class="order-1 md:order-2 md:col-span-7 bg-white rounded-3xl p-6 md:p-8 shadow-2xl border border-slate-200 space-y-5">
                    
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Đăng Nhập Tài Khoản</h2>
                        <p class="text-xs text-slate-500 mt-1">Dành cho Sinh viên, Giảng viên, Kỹ thuật viên & Quản trị viên</p>
                    </div>

                    <?php if ($flash): ?>
                        <div role="alert" data-auto-dismiss="4500" class="p-3 rounded-xl text-sm font-semibold <?php echo $flash['type'] === 'danger' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200'; ?>">
                            <?php echo $flash['message']; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form đăng nhập chuẩn -->
                    <form method="POST" action="<?php echo $appBase; ?>index.php" class="space-y-5 text-sm">
                        <input type="hidden" name="action" value="login" />

                        <div>
                            <label for="login-identifier" class="block font-bold text-slate-700 mb-1.5">Mã tài khoản hoặc email trường</label>
                            <input id="login-identifier" type="text" name="identifier" required autocomplete="username" autocapitalize="off" spellcheck="false" placeholder="Ví dụ: 224001736 hoặc email trường"
                                   class="w-full px-4 py-3 text-slate-900 placeholder:text-slate-500 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="login-password" class="font-bold text-slate-700">Mật khẩu</label>
                            </div>
                            <div class="relative">
                                <input id="login-password" type="password" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu của bạn"
                                       class="w-full px-4 pr-12 py-3 text-slate-900 placeholder:text-slate-500 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-base md:text-sm font-semibold" />
                                <button type="button" data-password-toggle aria-controls="login-password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 rounded-r-xl">
                                    <svg data-password-show-icon aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    <svg data-password-hide-icon aria-hidden="true" class="hidden w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                                </button>
                            </div>
                            
                            <!-- Link Quên mật khẩu SSPR Trần Linh -->
                            <div class="flex flex-wrap items-center justify-between gap-x-2 gap-y-1 text-xs pt-2">
                                <a href="<?php echo $appBase; ?>index.php?action=forgot_password" class="text-indigo-600 hover:text-indigo-800 font-bold hover:underline flex items-center gap-1">
                                    <span aria-hidden="true">🔑</span> Quên mật khẩu?
                                </a>
                                <span class="text-slate-500">Khôi phục bằng email trường</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5 text-sm">
                            <span aria-hidden="true">🔐</span> Đăng nhập
                        </button>
                    </form>

                    <!-- Danh sách tài khoản thử nghiệm 1-Click (Phục vụ chấm bài) -->
                    <details class="pt-4 border-t border-slate-100">
                        <summary class="cursor-pointer rounded-lg py-2 text-sm font-semibold text-slate-600 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Tài khoản dùng thử
                        </summary>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 text-sm">
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
                    </details>

                    <div class="pt-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <a href="<?php echo $appBase; ?>index.php?action=guest_ticket" class="text-rose-700 hover:underline font-semibold">
                            🆘 Cứu hộ tài khoản khi bị khóa?
                        </a>
                        <a href="<?php echo $appBase; ?>index.php?action=guest_lookup" class="text-indigo-700 hover:underline">
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

    // Thống kê học phần & bài tập
    require_once __DIR__ . '/app/Models/CourseLesson.php';
    $courseModel = new CourseLesson();
    $myStudentCourses = $courseModel->getStudentCourses($userId);
    $myCoursesCount = count($myStudentCourses);
    $totalLessonsCount = 0;
    $completedLessonsCount = 0;
    foreach ($myStudentCourses as $mc) {
        $totalLessonsCount += (int)$mc['total_lessons'];
        $completedLessonsCount += (int)$mc['completed_lessons'];
    }

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

                <!-- CÁC NÚT TRUY CẬP NHANH DÀNH CHO SINH VIÊN -->
                <div class="flex items-center gap-2.5 flex-wrap relative z-10">
                    <a href="<?php echo $appBase; ?>student/index.php?action=courses" class="px-4 py-2.5 bg-white text-indigo-700 hover:bg-sky-50 font-black rounded-2xl shadow-md transition flex items-center gap-2 text-xs border border-white/40">
                        <span>📚</span> Học Phần & Bài Tập (<?php echo $myCoursesCount; ?> môn)
                    </a>
                    <a href="<?php echo $appBase; ?>student/ratings.php" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs">
                        <span>⭐</span> Đánh Giá CSAT 5★
                    </a>
                    <a href="<?php echo $appBase; ?>tickets/create.php" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-md transition flex items-center gap-1.5 text-xs">
                        <span>➕</span> Gửi Phiếu Mới
                    </a>
                </div>

            </div>

            <?php if ($flash): ?>
                <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <!-- Các chỉ số chính của sinh viên (4 CỘT) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="<?php echo $appBase; ?>student/index.php?action=courses" class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between hover:shadow-md transition group">
                    <div>
                        <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider block mb-1">Học Phần & Bài Tập</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-indigo-900"><?php echo $myCoursesCount; ?></span>
                            <span class="text-xs text-indigo-600 font-bold">môn đang học</span>
                        </div>
                        <div class="text-[10.5px] text-indigo-500 font-semibold mt-1">Đã nộp: <?= $completedLessonsCount ?>/<?= $totalLessonsCount ?> buổi</div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                        📚
                    </div>
                </a>

                <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tổng Phiếu Đã Gửi</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-slate-900"><?php echo $myTicketsCount; ?></span>
                            <span class="text-xs text-sky-600 font-bold">yêu cầu hỗ trợ</span>
                        </div>
                        <div class="text-[10.5px] text-slate-400 mt-1">Lịch sử sự cố học thuật</div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">
                        🔔
                    </div>
                </div>

                <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Đang Xử Lý</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-amber-600"><?php echo $myActiveCount; ?></span>
                            <span class="text-xs text-amber-700 font-bold">đang khắc phục</span>
                        </div>
                        <div class="text-[10.5px] text-amber-600/80 mt-1">KTV đang tiếp nhận & xử lý</div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                        ⏳
                    </div>
                </div>

                <a href="<?php echo $appBase; ?>student/ratings.php" class="bg-gradient-to-br from-amber-50/50 to-white border border-amber-200/90 rounded-2xl p-5 shadow-sm flex items-center justify-between hover:shadow-md transition group">
                    <div>
                        <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block mb-1">Đã Hoàn Thành & CSAT</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black text-emerald-600"><?php echo $myResolvedCount; ?></span>
                            <span class="text-xs text-emerald-700 font-bold">đã hoàn tất</span>
                        </div>
                        <div class="text-[10.5px] text-amber-700 font-bold mt-1">⭐ Bấm nghiệm thu & chấm sao</div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                        ⭐
                    </div>
                </a>
            </div>

            <!-- BẢNG DANH SÁCH PHIẾU CỦA RIÊNG SINH VIÊN NÀY -->
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
                        <p class="text-[11.5px] text-slate-400">Khi gặp sự cố kỹ thuật phòng máy, phần mềm hoặc tài khoản, hãy chọn <strong>Gửi Yêu Cầu Hỗ Trợ</strong> trên menu dọc.</p>
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

    // Lấy danh sách sinh viên trong các lớp GV phụ trách bị KTV báo cáo vi phạm
    $violatingStudents = $pdo->query("
        SELECT DISTINCT t.id AS ticket_id, t.ticket_code, t.title AS ticket_title, 
                        t.spam_reason, t.created_at AS reported_at,
                        u.id AS student_id, u.user_code, u.name AS student_name, u.email AS student_email,
                        cl.class_code, cl.name AS class_name
        FROM tickets t
        JOIN users u ON t.requester_id = u.id
        LEFT JOIN classes cl ON u.class_id = cl.id
        WHERE t.is_spam = 1 AND u.user_type = 'student'
          AND u.class_id IN (SELECT class_id FROM course_assignments WHERE lecturer_id = {$userId})
        ORDER BY t.created_at DESC
    ")->fetchAll();
    $violatingCount = count($violatingStudents);

    // Lấy danh sách lớp học phần phụ trách (kèm thông tin Lớp và Môn học)
    $myCourses = $pdo->query("
        SELECT ca.*, u.name AS lecturer_name,
               cl.class_code, cl.name AS class_name,
               co.course_code, co.name AS course_name
        FROM course_assignments ca
        JOIN users u ON ca.lecturer_id = u.id
        JOIN classes cl ON ca.class_id = cl.id
        JOIN courses co ON ca.course_id = co.id
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
            </div>

            <!-- Thống Kê Nhanh Phân Hệ Giảng Viên (Đã bỏ card cảnh báo theo yêu cầu) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Lớp Học Phần Phụ Trách</div>
                        <div class="text-2xl font-black text-indigo-600 mt-1"><?php echo $myCoursesCount; ?> lớp</div>
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl">
                        📚
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Phiếu Sự Cố Đã Gửi</div>
                        <div class="text-2xl font-black text-amber-600 mt-1"><?php echo $myTicketsCount; ?> phiếu</div>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl">
                        🎫
                    </div>
                </div>
            </div>

            <?php if ($flash): ?>
                <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
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
                    <div class="flex items-center gap-2">
                        <a href="<?php echo $appBase; ?>lecturer/lessons.php" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1 shadow-xs">
                            📝 Giao Bài Tập Từng Buổi →
                        </a>
                        <a href="<?php echo $appBase; ?>assignments/index.php" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs transition">
                            Xem Danh Sách Phân Công →
                        </a>
                    </div>
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
                                <th class="py-3 px-4 text-center">Nhiệm Vụ Học Phần</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($myCourses)): ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400">Chưa có phân công lớp học phần nào trong học kỳ này.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($myCourses as $c): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4 font-mono font-bold text-indigo-600"><?php echo htmlspecialchars($c['class_code']); ?></td>
                                        <td class="py-3 px-4 font-semibold text-slate-800"><?php echo htmlspecialchars($c['class_name']); ?></td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($c['course_name']); ?></td>
                                        <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($c['semester']); ?></td>
                                        <td class="py-3 px-4 text-center font-mono text-slate-500"><?php echo htmlspecialchars($c['academic_year']); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="<?php echo $appBase; ?>lecturer/lessons.php?ca_id=<?php echo (int)$c['id']; ?>" 
                                               class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 font-bold rounded-lg text-xs transition inline-flex items-center gap-1 border border-indigo-200">
                                                📝 Giao Đề & Buổi →
                                            </a>
                                        </td>
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
    <style>
        @media (max-width: 767px) {
            .admin-ticket-table,
            .admin-ticket-table tbody {
                display: block;
                width: 100%;
            }

            .admin-ticket-table thead {
                display: none;
            }

            .admin-ticket-table tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                padding: 1rem;
                border-bottom: 1px solid #e2e8f0;
            }

            .admin-ticket-table tbody td {
                display: flex;
                min-width: 0;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
                padding: 0;
                overflow-wrap: anywhere;
                text-align: left;
            }

            .admin-ticket-table tbody td::before {
                content: attr(data-label);
                color: #64748b;
                font-size: 0.75rem;
                font-weight: 700;
            }

            .admin-ticket-table .admin-ticket-code,
            .admin-ticket-table .admin-ticket-title,
            .admin-ticket-table .admin-ticket-requester {
                grid-column: 1 / -1;
            }

            .admin-ticket-table .admin-ticket-title a {
                max-width: none;
                overflow: visible;
                text-overflow: clip;
                white-space: normal;
            }
        }
    </style>
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
        </div>

        <?php if ($flash): ?>
            <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
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
                <div class="p-3 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>🎫</span>
                            <span class="sm:hidden">Phiếu cần xử lý</span>
                            <span class="hidden sm:inline">PHIẾU HỖ TRỢ CẦN TRIAGE & ĐIỀU PHỐI</span>
                        </h2>
                        <p class="hidden sm:block text-[11.5px] text-slate-500 mt-0.5">Quản trị viên có thẩm quyền nâng mức URGENT diện rộng và điều phối KTV.</p>
                    </div>
                    <a href="<?php echo $appBase; ?>tickets/index.php" class="shrink-0 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                        <span class="sm:hidden">Tất cả →</span>
                        <span class="hidden sm:inline">Xem Tất Cả →</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="admin-ticket-table w-full text-left border-collapse text-xs">
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
                                    <td data-label="Mã vé" class="admin-ticket-code py-3 px-4 font-mono font-bold text-indigo-600">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:underline">
                                            <?php echo htmlspecialchars($t['ticket_code']); ?>
                                        </a>
                                    </td>
                                    <td data-label="Tiêu đề" class="admin-ticket-title py-3 px-4 font-semibold text-slate-800">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:text-indigo-600 transition block max-w-xs truncate">
                                            <?php echo htmlspecialchars($t['title']); ?>
                                        </a>
                                    </td>
                                    <td data-label="Người gửi" class="admin-ticket-requester py-3 px-4 text-slate-600"><?php echo htmlspecialchars($t['requester_name']); ?></td>
                                    <td data-label="Ưu tiên" class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?php echo htmlspecialchars($t['priority_name']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Trạng thái" class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            ● <?php echo htmlspecialchars($t['status']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Chi tiết" class="py-3 px-4 text-center">
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