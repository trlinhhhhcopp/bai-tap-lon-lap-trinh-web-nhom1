<?php
/**
 * PHÂN HỆ QUẢN TRỊ VIÊN: NHẬP DỮ LIỆU TÀI KHOẢN TỪ EXCEL / CSV
 * Chuẩn hóa CSDL 12 bảng helpdesk_thudo
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/SpreadsheetImportService.php';

use App\Services\SpreadsheetImportService;

function normalizeUserImportHeader(string $value): string
{
    $value = mb_strtolower($value, 'UTF-8');
    return preg_replace('/[^\p{L}\p{N}]/u', '', $value);
}

function findUserImportHeader(array $rows): ?array
{
    $aliases = [
        'code' => ['mataikhoan', 'mãtàikhoản', 'masinhvien', 'mãsinhviên', 'masv', 'mãsv', 'magiaovien', 'mãgiáoviên', 'magv', 'mãgv', 'usercode', 'code'],
        'name' => ['hovaten', 'họvàtên', 'hoten', 'họtên', 'tensinhvien', 'tênsinhviên', 'tengiangvien', 'têngiảngviên', 'fullname', 'name', 'ten', 'tên'],
        'email' => ['email', 'emailtruong', 'emailtrường'],
        'password' => ['matkhau', 'mậtkhẩu', 'password', 'pass'],
        'role' => ['vaitro', 'vaitrò', 'role'],
        'user_type' => ['loaitaikhoan', 'loạitàikhoản', 'loainguoidung', 'loạingườidùng', 'loaiuser', 'usertype', 'type'],
        'department' => ['khoabomon', 'khoabộmôn', 'khoaobomon', 'department', 'faculty', 'khoa'],
        'phone' => ['sodienthoai', 'sốđiệnthoại', 'sdt', 'phone', 'telephone'],
        'class' => ['lop', 'lớp', 'lopsinhhoat', 'lớpsinhhoạt', 'lophoc', 'lớphọc', 'malop', 'mãlớp', 'class', 'classcode', 'classname'],
    ];

    foreach (array_slice($rows, 0, 10, true) as $rowIndex => $row) {
        $headerMap = [];
        foreach ($row as $columnIndex => $cell) {
            $normalized = normalizeUserImportHeader((string) $cell);
            foreach ($aliases as $field => $fieldAliases) {
                $fieldAliases = array_map('normalizeUserImportHeader', $fieldAliases);
                $matchesHeader = in_array($normalized, $fieldAliases, true);
                if (!$matchesHeader) {
                    foreach ($fieldAliases as $alias) {
                        if (strlen($alias) >= 5 && str_starts_with($normalized, $alias)) {
                            $matchesHeader = true;
                            break;
                        }
                    }
                }
                if ($matchesHeader) {
                    $headerMap[$field] = $columnIndex;
                    break;
                }
            }
        }
        if (isset($headerMap['code'], $headerMap['name'])) {
            return ['row' => $rowIndex, 'columns' => $headerMap];
        }
    }

    return null;
}

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
$classLookup = [];
foreach ($db->query('SELECT id, class_code, name FROM classes')->fetchAll(PDO::FETCH_ASSOC) as $class) {
    $classLookup[normalizeUserImportHeader($class['name'])] = $class;
    $classLookup[normalizeUserImportHeader($class['class_code'])] = $class;
}
$importedUsers = [];
$skippedUsers  = [];
$errorMsg      = null;
$successMsg    = null;

// Xử lý import khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_excel') {
    $rows = [];
    try {
        if (!empty($_FILES['excel_file']['name'])) {
            if ($_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Không tải được tệp lên (mã lỗi ' . (int) $_FILES['excel_file']['error'] . ').');
            }
            $extension = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
            $rows = SpreadsheetImportService::readFile($_FILES['excel_file']['tmp_name'], $extension);
        } else {
            throw new RuntimeException('Vui lòng chọn một tệp .xlsx, .csv hoặc .txt để import.');
        }
    } catch (RuntimeException $exception) {
        $errorMsg = $exception->getMessage();
    }

    if ($errorMsg === null && empty($rows)) {
        $errorMsg = 'Tệp không có dòng dữ liệu.';
    } elseif ($errorMsg === null) {
        $header = findUserImportHeader($rows);
        if ($header !== null) {
            $headerColumns = $header['columns'];
            $dataRows = [];
            foreach (array_slice($rows, $header['row'] + 1) as $row) {
                $normalizedRow = array_fill(0, 9, '');
                $fieldOrder = ['code', 'name', 'email', 'password', 'role', 'user_type', 'department', 'phone', 'class'];
                foreach ($fieldOrder as $columnIndex => $field) {
                    if (isset($headerColumns[$field])) {
                        $normalizedRow[$columnIndex] = $row[$headerColumns[$field]] ?? '';
                    }
                }
                $dataRows[] = $normalizedRow;
            }
            $rows = $dataRows;
        } elseif (count($rows[0]) < 2) {
            $errorMsg = 'Không tìm thấy các cột Mã tài khoản và Họ tên. Hãy dùng mẫu import hoặc sắp xếp cột theo hướng dẫn.';
        }
    }

    if ($errorMsg === null) {
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
            // 8: Lớp (tùy chọn, mã lớp hoặc tên lớp)
            $userCode   = trim($r[0] ?? '');
            $name       = trim($r[1] ?? '');
            $email      = trim($r[2] ?? '');
            $passPlain  = !empty($r[3]) ? trim($r[3]) : '123456';
            $role       = !empty($r[4]) ? strtolower(trim($r[4])) : 'requester';
            $userType   = !empty($r[5]) ? strtolower(trim($r[5])) : 'student';
            $department = !empty($r[6]) ? trim($r[6]) : 'Khoa Công Nghệ Thông Tin';
            $phone      = !empty($r[7]) ? trim($r[7]) : ('09' . str_pad((string)mt_rand(10000000, 99999999), 8, '0'));
            $classValue = trim($r[8] ?? '');
            $class = null;

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

            if ($classValue !== '') {
                $class = $classLookup[normalizeUserImportHeader($classValue)] ?? null;
                if ($class === null) {
                    $skippedUsers[] = [
                        'row' => $index + 1,
                        'code' => $userCode,
                        'name' => $name,
                        'reason' => "Không tìm thấy lớp '{$classValue}' (nhập mã lớp hoặc tên lớp đã có trong hệ thống)",
                    ];
                    continue;
                }
            }

            // Chuẩn hóa role & user_type
            if (!in_array($role, ['requester', 'agent', 'admin'])) {
                $role = 'requester';
            }
            $userType = match (normalizeUserImportHeader($userType)) {
                'sinhvien', 'sinhviên' => 'student',
                'giangvien', 'giảngviên' => 'lecturer',
                'ktv', 'kythuatvien', 'kỹthuậtviên' => 'agent',
                'nhanvien', 'nhânviên' => 'staff',
                default => $userType,
            };
            if (!in_array($userType, ['student', 'lecturer', 'agent', 'staff'])) {
                $userType = 'student';
            }

            $cohort = null;
            if ($userType === 'student') {
                if (!preg_match('/^2(2\d)/', $userCode, $cohortMatch)) {
                    $skippedUsers[] = [
                        'row' => $index + 1,
                        'code' => $userCode,
                        'name' => $name,
                        'reason' => 'Không xác định được niên khóa từ mã sinh viên',
                    ];
                    continue;
                }
                $cohort = 'D' . $cohortMatch[1];
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
                        department, phone, class_id, is_active, cohort, academic_status, created_at
                    ) VALUES (
                        :user_code, :name, :email, :password_hash, :role, :user_type,
                        :department, :phone, :class_id, 1, :cohort, 'studying', NOW()
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
                    'class_id'      => $class['id'] ?? null,
                    'cohort'        => $cohort,
                ]);

                $importedUsers[] = [
                    'id'        => $db->lastInsertId(),
                    'code'      => $userCode,
                    'name'      => $name,
                    'email'     => $email,
                    'role'      => $role,
                    'user_type' => $userType,
                    'cohort'    => $cohort,
                    'class'     => $class['name'] ?? '—',
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
            <a href="users.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-1.5">
                <span>👥</span> Danh Sách Người Dùng
            </a>
        </div>
    </div>

    <?php if ($successMsg): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between gap-2">
            <div>✅ <?php echo $successMsg; ?></div>
            <a href="#imported-users" class="text-emerald-700 underline font-bold">Xem danh sách vừa nhập ↓</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($importedUsers)): ?>
        <section id="imported-users" class="bg-white border border-emerald-200 rounded-3xl p-5 shadow-sm space-y-3 text-xs">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-bold text-emerald-800 uppercase tracking-wider text-xs">
                    ✅ Danh sách <?= count($importedUsers) ?> tài khoản vừa nhập thành công
                </h2>
                <a href="users.php" class="text-indigo-700 underline font-bold">Mở danh sách người dùng</a>
            </div>
            <div class="overflow-x-auto max-h-96 border border-slate-200 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-emerald-50 text-emerald-900">
                        <tr class="font-bold border-b border-emerald-200 text-[11px]">
                            <th class="py-2.5 px-3">ID CSDL</th>
                            <th class="py-2.5 px-3">Mã sinh viên / tài khoản</th>
                            <th class="py-2.5 px-3">Họ và tên</th>
                            <th class="py-2.5 px-3">Email</th>
                            <th class="py-2.5 px-3">Niên khóa tự nhận diện</th>
                            <th class="py-2.5 px-3">Lớp</th>
                            <th class="py-2.5 px-3">Vai trò</th>
                            <th class="py-2.5 px-3">Loại tài khoản</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($importedUsers as $u): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-mono text-slate-400">#<?= htmlspecialchars((string) $u['id']) ?></td>
                                <td class="py-2.5 px-3 font-mono font-bold text-indigo-700"><?= htmlspecialchars($u['code']) ?></td>
                                <td class="py-2.5 px-3 font-bold text-slate-900"><?= htmlspecialchars($u['name']) ?></td>
                                <td class="py-2.5 px-3 font-mono text-slate-600"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="py-2.5 px-3 font-mono"><?= htmlspecialchars($u['cohort'] ?? '—') ?></td>
                                <td class="py-2.5 px-3"><?= htmlspecialchars($u['class']) ?></td>
                                <td class="py-2.5 px-3"><?= htmlspecialchars($u['role']) ?></td>
                                <td class="py-2.5 px-3"><?= htmlspecialchars($u['user_type']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800">
            ⚠️ <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <!-- KHUNG IMPORT CHÍNH -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <!-- CỘT TRÁI: FORM TẢI TỆP -->
        <div class="md:col-span-7 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                <span>📁</span> Import danh sách từ một tệp:
            </h2>

            <form method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="import_excel" />

                <div class="p-4 bg-slate-50 border border-dashed border-slate-300 rounded-2xl space-y-2">
                    <label for="excelFileInput" class="block font-bold text-slate-700">Chọn tệp Excel / CSV từ máy tính:</label>
                    <input id="excelFileInput" type="file" name="excel_file" accept=".csv,.xlsx,.txt" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-300 rounded-xl bg-white p-1" />
                    <span class="text-[11px] text-slate-400 block">Nhận tệp Excel .XLSX có kẻ ô/bảng, .CSV hoặc .TXT (dấu phẩy, chấm phẩy hoặc tab). Không hỗ trợ .XLS cũ.</span>
                    <span id="xlsxPreviewNotice" class="hidden text-[11px] text-indigo-700 block">Tệp .XLSX sẽ được đọc khi gửi biểu mẫu; trình duyệt không xem trước nội dung workbook.</span>
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

        <!-- CỘT PHẢI: HƯỚNG DẪN CẤU TRÚC FILE EXCEL (9 CỘT) -->
        <div class="md:col-span-5 space-y-4">
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm text-xs space-y-3">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5 text-[11px]">
                    <span>ℹ️</span> Cấu Trúc Các Cột Dữ Liệu Chuẩn:
                </h3>
                <div class="space-y-2 text-slate-600 text-[11.5px] leading-relaxed">
                    <p>Mỗi dòng trong tệp tương ứng với 1 tài khoản mới. Có thể đặt cột theo tiêu đề trong mẫu hoặc theo thứ tự 9 cột:</p>
                    <ol class="list-decimal list-inside space-y-1 font-medium bg-slate-50 p-3 rounded-2xl border border-slate-200">
                        <li><strong>Mã tài khoản:</strong> Bắt buộc (VD: <code>224001801</code>)</li>
                        <li><strong>Họ và tên:</strong> Bắt buộc (VD: <code>Nguyễn Thị Hoa</code>)</li>
                        <li><strong>Email:</strong> Bắt buộc (hoặc tự tạo đuôi @daihocthudo.edu.vn)</li>
                        <li><strong>Mật khẩu:</strong> Mặc định <code>123456</code> (Bcrypt tự động)</li>
                        <li><strong>Vai trò:</strong> <code>requester</code>, <code>agent</code>, <code>admin</code></li>
                        <li><strong>Loại tài khoản:</strong> <code>student</code>, <code>lecturer</code>, <code>agent</code></li>
                        <li><strong>Khoa / Bộ môn:</strong> VD: <code>Khoa Công Nghệ Thông Tin</code></li>
                        <li><strong>Số điện thoại:</strong> Tùy chọn (VD: <code>0901234567</code>)</li>
                        <li><strong>Lớp:</strong> Tùy chọn (mã lớp hoặc tên lớp đã có, VD: <code>D24-CNTT2</code>)</li>
                    </ol>
                    <p class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-900"><strong>Niên khóa sinh viên được tự nhận diện từ mã SV</strong> (ví dụ: <code>224...</code> → <code>D24</code>). Không cần thêm cột niên khóa vào tệp.</p>
                    <p class="text-slate-500">Tệp Excel định dạng .xlsx được đọc theo giá trị ô; bảng có kẻ viền, màu nền hoặc độ rộng cột vẫn import bình thường. Mã tài khoản và số điện thoại nên để dạng văn bản để giữ số 0 ở đầu.</p>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-[11px] text-amber-900 space-y-1">
                    <span class="font-bold">⚠️ Quy tắc an toàn dữ liệu:</span>
                    <p>Hệ thống tự động phát hiện và bỏ qua các dòng bị trùng lặp Mã tài khoản hoặc Email đã có sẵn trong bảng <code>users</code> để bảo đảm toàn vẹn dữ liệu.</p>
                </div>
            </div>
        </div>

    </div>

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
function downloadSampleCsv() {
    const header = '"Mã Tài Khoản","Họ và Tên","Email","Mật Khẩu","Vai Trò","Loại Người Dùng","Khoa/Bộ Môn","Số Điện Thoại","Lớp"\r\n';
    const sampleRows = '"224001801","Nguyễn Văn An","224001801@daihocthudo.edu.vn","123456","requester","student","Khoa Công Nghệ Thông Tin","0901234801","D24-CNTT2"\r\n"224001802","Trần Thị Mai","224001802@daihocthudo.edu.vn","123456","requester","student","Khoa Công Nghệ Thông Tin","0901234802","D24-CNTT2"\r\n';
    const blob = new Blob(["\uFEFF" + header + sampleRows], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "mau_nhap_lieu_sinh_vien_helpdesk.csv";
    link.click();
}

document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('excelFileInput');
    const xlsxNotice = document.getElementById('xlsxPreviewNotice');
    fileInput.addEventListener('change', function() {
        xlsxNotice.classList.toggle('hidden', !this.files[0] || !this.files[0].name.toLowerCase().endsWith('.xlsx'));
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
