<?php
/**
 * PHÂN HỆ QUẢN TRỊ VIÊN: NHẬP DỮ LIỆU TÀI KHOẢN TỪ EXCEL / CSV
 * Chuẩn hóa CSDL 12 bảng helpdesk_thudo
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc phải là Admin
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['flash'] = [
        'type'    => 'danger',
        'message' => 'Bạn không có quyền truy cập vào Chức năng Nhập dữ liệu Quản trị viên!'
    ];
    header('Location: ../index.php');
    exit;
}

$currentUser = $_SESSION['user'];
$db = db();
$importedUsers = [];
$skippedUsers  = [];
$errorMsg      = null;
$successMsg    = null;

// Xử lý import khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_excel') {
    $rows = [];

    // 1. Nếu tải file lên (.csv, .xlsx, .txt)
    if (!empty($_FILES['excel_file']['name']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['excel_file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, ['csv', 'txt'])) {
            $handle = fopen($tmpName, 'r');
            // Bỏ qua BOM nếu có
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Nếu file dùng dấu chấm phẩy
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    $data = explode(';', $data[0]);
                }
                $rows[] = array_map('trim', $data);
            }
            fclose($handle);
        } else {
            // Đọc dạng text cơ bản nếu là xlsx chưa có zip extension
            $content = file_get_contents($tmpName);
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $rows[] = array_map('trim', explode(',', $line));
                }
            }
        }
    }

    // 2. Nếu dán trực tiếp dữ liệu vào ô textarea
    if (empty($rows) && !empty($_POST['csv_text'])) {
        $lines = explode("\n", trim($_POST['csv_text']));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $delimiter = str_contains($line, "\t") ? "\t" : (str_contains($line, ';') ? ';' : ',');
                $rows[] = array_map('trim', explode($delimiter, $line));
            }
        }
    }

    if (empty($rows)) {
        $errorMsg = "Vui lòng chọn 1 tệp Excel/CSV hoặc dán danh sách dữ liệu vào ô bên dưới!";
    } else {
        // Kiểm tra dòng tiêu đề
        $firstRow = $rows[0];
        $isHeader = false;
        foreach ($firstRow as $col) {
            $colLower = strtolower($col);
            if (str_contains($colLower, 'mã') || str_contains($colLower, 'code') || str_contains($colLower, 'tên') || str_contains($colLower, 'name') || str_contains($colLower, 'email')) {
                $isHeader = true;
                break;
            }
        }
        if ($isHeader) {
            array_shift($rows); // Bỏ qua dòng tiêu đề
        }

        $defaultHash = password_hash('123456', PASSWORD_BCRYPT);

        foreach ($rows as $index => $r) {
            if (empty($r) || count($r) < 2) {
                continue;
            }

            // Thứ tự cột chuẩn:
            // 0: Mã tài khoản (VD: 224001740)
            // 1: Họ và tên (VD: Nguyễn Thị Lan)
            // 2: Email (VD: 224001740@daihocthudo.edu.vn)
            // 3: Mật khẩu (tùy chọn, mặc định 123456)
            // 4: Vai trò (requester, agent, admin)
            // 5: Loại người dùng (student, lecturer, agent, staff)
            // 6: Khoa / Bộ môn
            // 7: Số điện thoại
            // 8: Khóa (D24, D23...)

            $userCode   = trim($r[0] ?? '');
            $name       = trim($r[1] ?? '');
            $email      = trim($r[2] ?? '');
            $passPlain  = !empty($r[3]) ? trim($r[3]) : '123456';
            $role       = !empty($r[4]) ? strtolower(trim($r[4])) : 'requester';
            $userType   = !empty($r[5]) ? strtolower(trim($r[5])) : 'student';
            $department = !empty($r[6]) ? trim($r[6]) : 'Khoa Công Nghệ Thông Tin';
            $phone      = !empty($r[7]) ? trim($r[7]) : ('09' . str_pad((string)mt_rand(10000000, 99999999), 8, '0'));
            $cohort     = !empty($r[8]) ? trim($r[8]) : 'D24';

            if (empty($userCode) || empty($name)) {
                $skippedUsers[] = [
                    'row'    => $index + 1,
                    'code'   => $userCode ?: '(Trống)',
                    'name'   => $name ?: '(Trống)',
                    'reason' => 'Thiếu Mã tài khoản hoặc Họ tên bắt buộc'
                ];
                continue;
            }

            if (empty($email)) {
                $email = strtolower($userCode) . '@daihocthudo.edu.vn';
            }

            // Chuẩn hóa role & user_type
            if (!in_array($role, ['requester', 'agent', 'admin'])) {
                $role = 'requester';
            }
            if (!in_array($userType, ['student', 'lecturer', 'agent', 'staff'])) {
                $userType = 'student';
            }

            $passHash = ($passPlain === '123456') ? $defaultHash : password_hash($passPlain, PASSWORD_BCRYPT);

            // Kiểm tra trùng mã hoặc email
            $chk = $db->prepare("SELECT id, user_code, email FROM users WHERE user_code = :uc OR email = :em LIMIT 1");
            $chk->execute(['uc' => $userCode, 'em' => $email]);
            $existing = $chk->fetch();

            if ($existing) {
                $skippedUsers[] = [
                    'row'    => $index + 1,
                    'code'   => $userCode,
                    'name'   => $name,
                    'reason' => "Đã tồn tại trong CSDL (ID: #{$existing['id']})"
                ];
                continue;
            }

            try {
                $ins = $db->prepare("
                    INSERT INTO users (
                        user_code, name, email, password_hash, role, user_type, 
                        department, phone, is_active, cohort, academic_status, created_at
                    ) VALUES (
                        :user_code, :name, :email, :password_hash, :role, :user_type,
                        :department, :phone, 1, :cohort, 'studying', NOW()
                    )
                ");
                $ins->execute([
                    'user_code'     => $userCode,
                    'name'          => $name,
                    'email'         => $email,
                    'password_hash' => $passHash,
                    'role'          => $role,
                    'user_type'     => $userType,
                    'department'    => $department,
                    'phone'         => $phone,
                    'cohort'        => $cohort,
                ]);

                $importedUsers[] = [
                    'id'        => $db->lastInsertId(),
                    'code'      => $userCode,
                    'name'      => $name,
                    'email'     => $email,
                    'role'      => $role,
                    'user_type' => $userType,
                ];
            } catch (Exception $e) {
                $skippedUsers[] = [
                    'row'    => $index + 1,
                    'code'   => $userCode,
                    'name'   => $name,
                    'reason' => 'Lỗi SQL: ' . $e->getMessage()
                ];
            }
        }

        if (count($importedUsers) > 0) {
            $successMsg = "Đã nhập thành công <strong>" . count($importedUsers) . "</strong> tài khoản người dùng vào cơ sở dữ liệu!";
        } else {
            $errorMsg = "Không có tài khoản nào được nhập mới (toàn bộ dòng dữ liệu bị trùng hoặc lỗi).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập Tài Khoản Bằng Excel/CSV - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- HEADER BANNER -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                📊 QUẢN TRỊ ADMIN - BATCH IMPORT
            </span>
            <h1 class="text-2xl font-black text-white mt-1">
                Nhập Dữ Liệu Tài Khoản Từ Excel / CSV
            </h1>
            <p class="text-xs text-indigo-200/80">Nhập hàng loạt danh sách sinh viên, giảng viên và kỹ thuật viên vào CSDL helpdesk_thudo với mã hóa Bcrypt tự động.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="../TranLinh/users.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                <span>👥</span> Danh Sách Người Dùng
            </a>
        </div>
    </div>

    <?php if ($successMsg): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between gap-2">
            <div>✅ <?php echo $successMsg; ?></div>
            <a href="../TranLinh/users.php" class="text-emerald-700 underline font-bold">Xem danh sách người dùng ➔</a>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800">
            ⚠️ <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <!-- KHUNG IMPORT CHÍNH -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <!-- CỘT TRÁI: FORM TẢI FILE & DÁN DỮ LIỆU (7 CỘT) -->
        <div class="md:col-span-7 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                <span>📁</span> Tùy Chọn Tải Lên Danh Sách:
            </h2>

            <form method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="import_excel" />

                <!-- Cách 1: Tải file lên -->
                <div class="p-4 bg-slate-50 border border-dashed border-slate-300 rounded-2xl space-y-2">
                    <label class="block font-bold text-slate-700">Cách 1: Chọn tệp Excel / CSV từ máy tính:</label>
                    <input type="file" name="excel_file" accept=".csv,.xlsx,.txt" 
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-300 rounded-xl bg-white p-1" />
                    <span class="text-[11px] text-slate-400 block">Hỗ trợ định dạng: .CSV, .TXT (phân cách bằng dấu phẩy hoặc chấm phẩy)</span>
                </div>

                <!-- Cách 2: Dán trực tiếp -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-slate-700">Cách 2: Hoặc dán trực tiếp nội dung CSV vào đây:</label>
                        <button type="button" onclick="fillSampleData()" class="text-[11px] text-indigo-600 font-bold hover:underline">
                            ⚡ Điền dữ liệu mẫu kiểm thử
                        </button>
                    </div>
                    <textarea name="csv_text" id="csvTextInput" rows="6" placeholder="Mã SV, Họ tên, Email, Mật khẩu, Vai trò, Loại người dùng, Khoa, SĐT, Khóa&#10;224001801, Hoàng Văn An, 224001801@daihocthudo.edu.vn, 123456, requester, student, Khoa CNTT, 0901234801, D24&#10;224001802, Lê Thị Mai, 224001802@daihocthudo.edu.vn, 123456, requester, student, Khoa CNTT, 0901234802, D24" 
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono text-[11.5px] focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" onclick="downloadSampleCsv()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition flex items-center gap-1.5">
                        <span>📥</span> Tải File Mẫu (.csv)
                    </button>

                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-md shadow-emerald-200 flex items-center gap-1.5">
                        <span>🚀</span> Bắt Đầu Nhập Dữ Liệu
                    </button>
                </div>
            </form>
        </div>

        <!-- CỘT PHẢI: HƯỚNG DẪN CẤU TRÚC FILE EXCEL (5 CỘT) -->
        <div class="md:col-span-5 space-y-4">
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm text-xs space-y-3">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5 text-[11px]">
                    <span>ℹ️</span> Cấu Trúc Các Cột Dữ Liệu Chuẩn:
                </h3>
                <div class="space-y-2 text-slate-600 text-[11.5px] leading-relaxed">
                    <p>Mỗi dòng trong tệp tương ứng với 1 tài khoản mới theo thứ tự 9 cột:</p>
                    <ol class="list-decimal list-inside space-y-1 font-medium bg-slate-50 p-3 rounded-2xl border border-slate-200">
                        <li><strong>Mã tài khoản:</strong> Bắt buộc (VD: <code>224001801</code>)</li>
                        <li><strong>Họ và tên:</strong> Bắt buộc (VD: <code>Nguyễn Thị Hoa</code>)</li>
                        <li><strong>Email:</strong> Bắt buộc (hoặc tự tạo đuôi @daihocthudo.edu.vn)</li>
                        <li><strong>Mật khẩu:</strong> Mặc định <code>123456</code> (Bcrypt tự động)</li>
                        <li><strong>Vai trò:</strong> <code>requester</code>, <code>agent</code>, <code>admin</code></li>
                        <li><strong>Loại tài khoản:</strong> <code>student</code>, <code>lecturer</code>, <code>agent</code></li>
                        <li><strong>Khoa / Bộ môn:</strong> VD: <code>Khoa Công Nghệ Thông Tin</code></li>
                        <li><strong>Số điện thoại:</strong> Tùy chọn (VD: <code>0901234567</code>)</li>
                        <li><strong>Niên khóa:</strong> Mặc định <code>D24</code></li>
                    </ol>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-[11px] text-amber-900 space-y-1">
                    <span class="font-bold">⚠️ Quy tắc an toàn dữ liệu:</span>
                    <p>Hệ thống tự động phát hiện và bỏ qua các dòng bị trùng lặp Mã tài khoản hoặc Email đã có sẵn trong bảng <code>users</code> để bảo đảm toàn vẹn dữ liệu.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- KẾT QUẢ IMPORT CHI TIẾT (NẾU CÓ) -->
    <?php if (!empty($importedUsers)): ?>
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4 text-xs">
            <h3 class="font-bold text-emerald-700 uppercase tracking-wider text-xs flex items-center gap-1.5">
                <span>✅</span> Danh Sách <?php echo count($importedUsers); ?> Tài Khoản Đã Nhập Thành Công:
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                            <th class="py-2.5 px-3">ID CSDL</th>
                            <th class="py-2.5 px-3">Mã Tài Khoản</th>
                            <th class="py-2.5 px-3">Họ và Tên</th>
                            <th class="py-2.5 px-3">Email Trường Cấp</th>
                            <th class="py-2.5 px-3">Vai Trò</th>
                            <th class="py-2.5 px-3">Loại Tài Khoản</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($importedUsers as $u): ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-2.5 px-3 font-mono text-slate-400">#<?php echo $u['id']; ?></td>
                                <td class="py-2.5 px-3 font-mono font-bold text-indigo-700"><?php echo htmlspecialchars($u['code']); ?></td>
                                <td class="py-2.5 px-3 font-bold text-slate-900"><?php echo htmlspecialchars($u['name']); ?></td>
                                <td class="py-2.5 px-3 font-mono text-slate-600"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 uppercase"><?php echo $u['role']; ?></span>
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700"><?php echo $u['user_type']; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($skippedUsers)): ?>
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-3 text-xs">
            <h3 class="font-bold text-slate-600 uppercase tracking-wider text-xs flex items-center gap-1.5">
                <span>⏭️</span> Bỏ Qua <?php echo count($skippedUsers); ?> Dòng Do Trùng Lặp Hoặc Dữ Liệu Sai:
            </h3>
            <div class="space-y-1.5 max-h-48 overflow-y-auto">
                <?php foreach ($skippedUsers as $sk): ?>
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-[11px]">
                        <div>
                            Dòng <strong>#<?php echo $sk['row']; ?></strong>: Mã <code><?php echo htmlspecialchars($sk['code']); ?></code> — <?php echo htmlspecialchars($sk['name']); ?>
                        </div>
                        <span class="text-amber-700 font-semibold"><?php echo htmlspecialchars($sk['reason']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</main>

<script>
function fillSampleData() {
    const sample = `224001811, Hoàng Anh Tuấn, 224001811@daihocthudo.edu.vn, 123456, requester, student, Khoa CNTT, 0901234811, D24\n224001812, Vũ Mai Phương, 224001812@daihocthudo.edu.vn, 123456, requester, student, Khoa CNTT, 0901234812, D24\n224001813, Đỗ Quốc Bảo, 224001813@daihocthudo.edu.vn, 123456, requester, student, Khoa CNTT, 0901234813, D24`;
    document.getElementById('csvTextInput').value = sample;
}

function downloadSampleCsv() {
    const header = "Mã Tài Khoản,Họ và Tên,Email,Mật Khẩu,Vai Trò,Loại Người Dùng,Khoa/Bộ Môn,Số Điện Thoại,Niên Khóa\n";
    const sampleRows = "224001801,Nguyễn Văn An,224001801@daihocthudo.edu.vn,123456,requester,student,Khoa Công Nghệ Thông Tin,0901234801,D24\n224001802,Trần Thị Mai,224001802@daihocthudo.edu.vn,123456,requester,student,Khoa Công Nghệ Thông Tin,0901234802,D24\n";
    const blob = new Blob(["\uFEFF" + header + sampleRows], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "mau_nhap_lieu_sinh_vien_helpdesk.csv";
    link.click();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
