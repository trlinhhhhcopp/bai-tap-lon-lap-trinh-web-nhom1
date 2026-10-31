<?php
// ==============================================================================
// FORM TẠO PHIẾU HỖ TRỢ - CHUẨN ĐẶC TẢ ITIL V4 & AUTO-PRIORITY MAPPING
// Phân hệ: Core Architecture & Ticket Dispatch
// Trưởng nhóm phụ trách: PHẠM THỊ THANH NGÂN (MSSV: 224001736 - D24-CNTT1)
// ==============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

// Kiểm tra đăng nhập bắt buộc
if (empty($_SESSION['user'])) {
    set_flash('danger', 'Vui lòng đăng nhập để gửi yêu cầu hỗ trợ!');
    header('Location: ../index.php');
    exit;
}

$currentUser = $_SESSION['user'];
$userRole    = $currentUser['role'] ?? 'requester';
$userType    = $currentUser['user_type'] ?? 'student';

// Kỹ thuật viên & Quản trị viên không có chức năng tạo phiếu hỗ trợ (chỉ tiếp nhận, giám sát và xử lý)
if ($userRole === 'admin') {
    set_flash('error', 'Quản trị viên là người quản trị và điều phối hệ thống, không cần gửi yêu cầu hỗ trợ.');
    header('Location: index.php');
    exit;
}
if ($userRole === 'agent') {
    set_flash('error', 'Kỹ thuật viên IT không có chức năng tự gửi phiếu hỗ trợ. Nhiệm vụ của bạn là tiếp nhận và giải quyết phiếu sự cố!');
    header('Location: ../agent/index.php');
    exit;
}

$categories  = TicketRepository::getCategories();
$priorities  = TicketRepository::getPriorities();

$errors = [];

// XỬ LÝ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId     = (int)($_POST['category_id'] ?? 0);
    $title          = trim($_POST['title'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $systemAffected = trim($_POST['system_affected'] ?? '');

    // Tự động điền nếu để trống
    if (empty($systemAffected) && $categoryId > 0) {
        $catStmt = db()->prepare("SELECT code FROM categories WHERE id = :id LIMIT 1");
        $catStmt->execute(['id' => $categoryId]);
        $cCode = $catStmt->fetchColumn() ?: '';
        $sysMap = [
            'CAT_CANVAS' => 'Học tập trực tuyến Canvas LMS & MS Teams',
            'CAT_QLDT'   => 'Cổng đào tạo QLĐT (qldt.daihocthudo.edu.vn)',
            'CAT_MAIL'   => 'Microsoft 365 & Email trường (Outlook)',
            'CAT_WIFI'   => 'Hệ thống Wi-Fi & VPN Trường (HNMU-STUDENT / HNMU-STAFF)',
            'CAT_SSO'    => 'Cổng xác thực tập trung SSO Portal',
            'CAT_OTHER'  => 'Dịch vụ CNTT & Thiết bị khác',
        ];
        $systemAffected = $sysMap[$cCode] ?? 'Dịch vụ CNTT & Thiết bị khác';
    }

    // Người gửi luôn là tài khoản đang đăng nhập
    $requesterId = (int)$currentUser['id'];

    // Auto-Priority mapping chuẩn đặc tả PDF theo vai trò
    if ($userType === 'student') {
        // Sinh viên: Tự động gán theo Category default_priority_code (Không có trường chọn Priority)
        $catStmt = db()->prepare("SELECT code, default_priority_code FROM categories WHERE id = :id LIMIT 1");
        $catStmt->execute(['id' => $categoryId]);
        $catInfo = $catStmt->fetch();
        $defCode = $catInfo['default_priority_code'] ?? 'MED';
        if (str_contains(strtoupper($catInfo['code'] ?? ''), 'QLDT') || str_contains(strtoupper($catInfo['code'] ?? ''), 'CANVAS')) {
            $defCode = 'HIGH';
        }
        $prioStmt = db()->prepare("SELECT id FROM priorities WHERE code = :code LIMIT 1");
        $prioStmt->execute(['code' => $defCode]);
        $priorityId = (int)($prioStmt->fetchColumn() ?: 2);
    } else {
        // Giảng viên hoặc Quản trị viên: Được phép chọn mức ưu tiên (HIGH hoặc URGENT)
        $priorityId = (int)($_POST['priority_id'] ?? 2);
    }

    // Validate dữ liệu
    if ($categoryId <= 0) {
        $errors[] = "Vui lòng chọn nhóm dịch vụ gặp sự cố.";
    }
    if (empty($title)) {
        $errors[] = "Tiêu đề sự cố không được để trống.";
    }
    if (empty($description)) {
        $errors[] = "Vui lòng nhập nội dung mô tả chi tiết lỗi bạn đang gặp phải.";
    }

    // Xử lý tệp ảnh / minh chứng sự cố đính kèm
    $attachmentPath = null;
    if (!empty($_FILES['attachment']['name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileSize = $_FILES['attachment']['size'];
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];

        if (!in_array($ext, $allowed, true)) {
            $errors[] = "Chỉ hỗ trợ tệp đính kèm dạng hình ảnh (JPG, PNG, WEBP, GIF) hoặc tài liệu PDF!";
        } elseif ($fileSize > 10 * 1024 * 1024) {
            $errors[] = "Dung lượng tệp đính kèm không được vượt quá 10MB!";
        } else {
            $uploadDir = __DIR__ . '/../uploads/attachments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $safeName = 'proof_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($fileTmp, $uploadDir . $safeName)) {
                $attachmentPath = 'uploads/attachments/' . $safeName;
            }
        }
    }

    if (empty($errors)) {
        try {
            $ticketId = TicketRepository::create([
                'requester_id'    => $requesterId,
                'category_id'     => $categoryId,
                'priority_id'     => $priorityId,
                'title'           => $title,
                'description'     => $description,
                'system_affected' => $systemAffected,
                'attachment_path' => $attachmentPath,
            ]);

            $newCode = db()->query("SELECT ticket_code FROM tickets WHERE id = " . (int)$ticketId)->fetchColumn() ?: ("HD-" . $ticketId);
            set_flash('success', "Gửi phiếu hỗ trợ thành công! Mã phiếu bảo mật của bạn là: <strong>" . htmlspecialchars($newCode) . "</strong>. KTV sẽ tiếp nhận và xử lý sớm nhất.");
            header('Location: ../index.php');
            exit;
        } catch (Exception $e) {
            $errors[] = "Lỗi khi lưu phiếu vào CSDL: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Yêu Cầu Hỗ Trợ - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">

<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="flex-1 max-w-3xl mx-auto w-full p-4 md:p-6 space-y-6">

    <!-- HEADER FORM -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                🎫 TIẾP NHẬN SỰ CỐ ĐA KÊNH
            </span>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">
                Gửi Phiếu Báo Lỗi Kỹ Thuật
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Tiếp nhận sự cố tự động theo chuyên môn ITIL v4 và phân công KTV phụ trách.</p>
        </div>
    </div>

    <!-- THÔNG BÁO LỖI NẾU CÓ -->
    <?php if (!empty($errors)): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
            <div class="font-bold flex items-center gap-1.5 text-rose-900">
                <span>⚠️</span> Vui lòng kiểm tra lại các thông tin sau:
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- FORM NHẬP LIỆU -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <form method="POST" action="create.php" enctype="multipart/form-data" class="space-y-4 text-xs">
            
            <!-- Người gửi (Tự động theo tài khoản đang đăng nhập) -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-slate-400 block text-[11px]">Tài khoản người gửi yêu cầu:</span>
                    <strong class="text-slate-800 text-xs"><?php echo htmlspecialchars($currentUser['name']); ?></strong>
                    <span class="text-indigo-600 font-mono text-[11px]">(<?php echo htmlspecialchars($currentUser['user_code']); ?>)</span>
                </div>
                <span class="px-2.5 py-1 rounded-xl bg-indigo-100 text-indigo-700 font-bold text-[10.5px]">
                    <?php echo $userType === 'student' ? '🎓 Sinh viên' : '👨‍🏫 Giảng viên'; ?>
                </span>
            </div>

            <!-- Danh mục sự cố -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Dịch vụ đang gặp sự cố: <span class="text-rose-500">*</span></label>
                <select name="category_id" id="categorySelect" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold">
                    <option value="">-- Chọn dịch vụ đang gặp lỗi --</option>
                    <?php 
                    $sysMapping = [
                        'CAT_CANVAS' => 'Học tập trực tuyến Canvas LMS & MS Teams',
                        'CAT_QLDT'   => 'Cổng đào tạo QLĐT (qldt.daihocthudo.edu.vn)',
                        'CAT_MAIL'   => 'Microsoft 365 & Email trường (Outlook)',
                        'CAT_WIFI'   => 'Hệ thống Wi-Fi & VPN Trường (HNMU-STUDENT / HNMU-STAFF)',
                        'CAT_SSO'    => 'Cổng xác thực tập trung SSO Portal',
                        'CAT_OTHER'  => '',
                    ];
                    foreach ($categories as $cat): 
                        // Page 2: Sinh viên đã đăng nhập vào web KHÔNG còn hiện lỗi xác thực/tài khoản (CAT_SSO) để chọn
                        if ($userType === 'student' && $cat['code'] === 'CAT_SSO') {
                            continue;
                        }
                        $autoSys = $sysMapping[$cat['code']] ?? '';
                    ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                data-code="<?php echo htmlspecialchars($cat['code']); ?>"
                                data-system="<?php echo htmlspecialchars($autoSys); ?>"
                                <?php echo isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?> (SLA chuẩn: <?php echo $cat['sla_hours']; ?>h)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Phân luồng Mức ưu tiên: Sinh viên Auto-Priority, Giảng viên được chọn HIGH/URGENT (Page 2: fix chữ tràn viền) -->
            <?php if ($userType === 'student'): ?>
                <div class="w-full max-w-full p-4 bg-indigo-50/90 border border-indigo-200 rounded-2xl text-indigo-900 text-xs space-y-1.5 overflow-hidden break-words whitespace-normal shadow-2xs">
                    <div class="font-bold flex items-center gap-1.5 text-indigo-950 flex-wrap">
                        <span class="text-base">⚡</span> <span>Cơ Chế Tự Động Gán Mức Ưu Tiên (Auto-Priority Mapping):</span>
                    </div>
                    <p class="text-indigo-800/90 leading-relaxed text-[11.5px] break-words">
                        Theo tiêu chuẩn ITIL v4, hệ thống sẽ tự động gán mức ưu tiên khách quan dựa trên loại sự cố bạn chọn (Canvas LMS/QLĐT: <strong class="text-indigo-950 font-bold">Cao - SLA 2h</strong> | Email/Wifi/Khác: <strong class="text-indigo-950 font-bold">Trung bình - SLA 2h–4h</strong>) để đảm bảo công bằng cho toàn trường.
                    </p>
                </div>
            <?php else: ?>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mức độ ưu tiên (Giảng viên đào tạo):</label>
                    <select name="priority_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-bold text-rose-700">
                        <option value="1">Khẩn cấp (URGENT - Giờ giảng dạy / Hạn chốt điểm thi)</option>
                        <option value="2" selected>Cao (HIGH - Lỗi phòng học trực tuyến Teams / Canvas)</option>
                        <option value="3">Trung bình (MED - Nghiên cứu khoa học / Cấp bản quyền)</option>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Hệ thống bị ảnh hưởng (Page 2: Tự động điền theo thư viện, chỉ nhập tay khi chọn Khác) -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Ứng dụng / Hệ thống bị ảnh hưởng:
                    <span id="systemAffectedTag" class="ml-1 text-[11px] font-normal text-slate-400">(Tự động điền theo danh mục)</span>
                </label>
                <input type="text" name="system_affected" id="systemAffectedInput" 
                       value="<?php echo htmlspecialchars($_POST['system_affected'] ?? ''); ?>" 
                       class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold text-slate-700"
                       placeholder="Chọn dịch vụ gặp sự cố ở trên để hệ thống tự điền..." />
                <p id="systemAffectedHelp" class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                    🔒 Thông tin ứng dụng được lấy tự động theo danh mục thư viện sự cố chuẩn.
                </p>
            </div>

            <!-- Tiêu đề sự cố -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tiêu đề tóm tắt sự cố: <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold"
                       placeholder="Ví dụ: Không thể đăng nhập vào lớp học phần môn Lập trình Web" />
            </div>

            <!-- Nội dung mô tả chi tiết -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nội dung mô tả chi tiết: <span class="text-rose-500">*</span></label>
                <textarea name="description" rows="4" required
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs leading-relaxed"
                          placeholder="Mô tả cụ thể thông báo lỗi, thời điểm gặp sự cố và mong muốn KTV hỗ trợ..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <!-- ĐÍNH KÈM TỆP ẢNH / TÀI LIỆU MINH CHỨNG SỰ CỐ -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">📎 Tệp ảnh / Tài liệu minh chứng sự cố (Tùy chọn):</label>
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <input type="file" name="attachment" id="createAttachInput" accept="image/png,image/jpeg,image/webp,image/gif,application/pdf"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-300 rounded-xl bg-white p-1" />
                    <span class="text-[10.5px] text-slate-400 block">Định dạng hỗ trợ: JPG, PNG, WEBP, GIF, PDF (Tối đa 10MB). Đính kèm ảnh chụp màn hình lỗi giúp KTV chẩn đoán và xử lý nhanh hơn 50%.</span>
                    
                    <div id="createPreviewBox" class="hidden pt-2">
                        <p class="text-[11px] font-bold text-slate-600 mb-1">Xem trước tệp minh chứng:</p>
                        <img id="createPreviewImg" src="" alt="Minh chứng" class="max-h-40 rounded-xl border border-slate-300 shadow-sm object-contain bg-white p-1" />
                    </div>
                </div>
            </div>

            <!-- Nút gửi -->
            <div class="pt-2 flex items-center justify-end gap-3">
                <a href="index.php" class="px-5 py-2.5 border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold rounded-2xl transition">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition shadow-md shadow-indigo-100 flex items-center gap-1.5">
                    <span>🚀</span> Gửi Yêu Cầu Hỗ Trợ
                </button>
            </div>

        </form>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tự động điền ứng dụng / hệ thống bị ảnh hưởng theo danh mục (Page 2)
    const catSelect = document.getElementById('categorySelect');
    const sysInput  = document.getElementById('systemAffectedInput');
    const sysHelp   = document.getElementById('systemAffectedHelp');
    const sysTag    = document.getElementById('systemAffectedTag');

    function updateSystemAffected() {
        if (!catSelect || !sysInput) return;
        const selected = catSelect.options[catSelect.selectedIndex];
        if (!selected || !selected.value) {
            sysInput.value = '';
            sysInput.readOnly = false;
            sysInput.placeholder = 'Chọn dịch vụ gặp sự cố ở trên để hệ thống tự điền...';
            if (sysTag) sysTag.textContent = '(Tự động điền theo danh mục)';
            if (sysHelp) sysHelp.textContent = '🔒 Thông tin ứng dụng được lấy tự động theo danh mục thư viện sự cố chuẩn.';
            return;
        }
        const catCode = selected.getAttribute('data-code');
        const autoSys = selected.getAttribute('data-system');

        if (catCode === 'CAT_OTHER') {
            sysInput.readOnly = false;
            sysInput.value = '';
            sysInput.placeholder = 'Vui lòng nhập tên ứng dụng / thiết bị gặp sự cố...';
            sysInput.classList.remove('bg-slate-100', 'text-slate-700');
            sysInput.classList.add('bg-white', 'border-indigo-400');
            sysInput.focus();
            if (sysTag) sysTag.textContent = '(Nhập tay cho trường hợp Khác)';
            if (sysHelp) sysHelp.textContent = '✏️ Bạn đã chọn danh mục "Khác": Vui lòng nhập tên ứng dụng / hệ thống cụ thể bị sự cố.';
        } else {
            sysInput.readOnly = true;
            sysInput.value = autoSys || '';
            sysInput.classList.remove('bg-white', 'border-indigo-400');
            sysInput.classList.add('bg-slate-100', 'text-slate-700');
            if (sysTag) sysTag.textContent = '(Hệ thống tự động điền)';
            if (sysHelp) sysHelp.textContent = '🔒 Thông tin ứng dụng được hệ thống tự động điền theo danh mục chuẩn (ITIL v4).';
        }
    }

    if (catSelect) {
        catSelect.addEventListener('change', updateSystemAffected);
        if (catSelect.value) {
            updateSystemAffected();
        }
    }

    const input = document.getElementById('createAttachInput');
    const box = document.getElementById('createPreviewBox');
    const img = document.getElementById('createPreviewImg');
    if (input && box && img) {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    box.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                box.classList.add('hidden');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
