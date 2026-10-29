<?php
// ==============================================================================
// FORM TẠO PHIẾU HỖ TRỢ - CHUẨN ĐẶC TẢ ITIL V4 & AUTO-PRIORITY MAPPING
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
$userType    = $currentUser['user_type'] ?? 'student';
$categories  = TicketRepository::getCategories();
$priorities  = TicketRepository::getPriorities();

$errors = [];

// XỬ LÝ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId     = (int)($_POST['category_id'] ?? 0);
    $title          = trim($_POST['title'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $systemAffected = trim($_POST['system_affected'] ?? 'Canvas LMS');

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
        // Giảng viên hoặc KTV/Admin: Được phép chọn mức ưu tiên (HIGH hoặc URGENT)
        $priorityId = (int)($_POST['priority_id'] ?? 2);
    }

    // Validate
    if ($categoryId <= 0) {
        $errors[] = "Vui lòng chọn nhóm dịch vụ gặp sự cố.";
    }
    if (empty($title)) {
        $errors[] = "Tiêu đề sự cố không được để trống.";
    }
    if (empty($description)) {
        $errors[] = "Vui lòng nhập nội dung mô tả chi tiết lỗi bạn đang gặp phải.";
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
            ]);

            set_flash('success', "Gửi phiếu hỗ trợ thành công! Mã phiếu của bạn là #HD-" . str_pad($ticketId, 5, '0', STR_PAD_LEFT) . ".");
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

<div class="flex-1 max-w-3xl mx-auto w-full p-4 md:p-6 space-y-6">

    <!-- HEADER FORM -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Gửi Yêu Cầu Hỗ Trợ Kỹ Thuật</h1>
            <p class="text-xs text-slate-500 mt-0.5">Tiếp nhận và điều phối sự cố trực tuyến theo chuẩn ITIL v4</p>
        </div>
        <a href="../index.php" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
            ← Quay lại
        </a>
    </div>

    <!-- HIỂN THỊ LỖI -->
    <?php if (!empty($errors)): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
            <div class="font-bold flex items-center gap-1.5">
                <span>⚠️</span> Không thể gửi phiếu do các lỗi sau:
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- FORM NHẬP LIỆU -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <form method="POST" action="create.php" class="space-y-4 text-xs">
            
            <!-- Người gửi (Tự động theo tài khoản đang đăng nhập) -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                <div>
                    <span class="text-slate-400 block text-[11px]">Tài khoản người gửi yêu cầu:</span>
                    <strong class="text-slate-800 text-xs"><?php echo htmlspecialchars($currentUser['name']); ?></strong>
                    <span class="text-indigo-600 font-mono text-[11px]">(<?php echo htmlspecialchars($currentUser['user_code']); ?>)</span>
                </div>
                <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 font-bold text-[10.5px]">
                    <?php echo $userType === 'student' ? 'Sinh viên' : 'Giảng viên'; ?>
                </span>
            </div>

            <!-- Danh mục sự cố -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Dịch vụ đang gặp sự cố: <span class="text-rose-500">*</span></label>
                <select name="category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs font-semibold">
                    <option value="">-- Chọn dịch vụ đang gặp lỗi --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?> (SLA chuẩn: <?php echo $cat['sla_hours']; ?>h)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Phân luồng Mức ưu tiên: Sinh viên Auto-Priority, Giảng viên được chọn HIGH/URGENT -->
            <?php if ($userType === 'student'): ?>
                <div class="p-3 bg-indigo-50/80 border border-indigo-200 rounded-xl text-indigo-900 text-[11.5px] space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>⚡</span> Cơ Chế Tự Động Gán Mức Ưu Tiên (Auto-Priority Mapping):
                    </div>
                    <p class="text-indigo-800/80 leading-relaxed text-[11px]">
                        Theo tiêu chuẩn ITIL v4, hệ thống sẽ tự động gán mức ưu tiên khách quan dựa trên loại sự cố bạn chọn (Canvas LMS/QLĐT: <strong>Cao - SLA 2h</strong>; Tài khoản/Email/Wifi: <strong>Trung bình - SLA 2h–4h</strong>) để đảm bảo công bằng cho toàn trường.
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

            <!-- Hệ thống bị ảnh hưởng -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Ứng dụng / Hệ thống bị ảnh hưởng:</label>
                <input type="text" name="system_affected" value="<?php echo htmlspecialchars($_POST['system_affected'] ?? 'Canvas LMS'); ?>" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs"
                       placeholder="Ví dụ: Canvas LMS, Cổng QLĐT, Wi-Fi Ký túc xá, Microsoft 365..." />
            </div>

            <!-- Tiêu đề sự cố -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tiêu đề tóm tắt sự cố: <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs"
                       placeholder="Ví dụ: Không thể đăng nhập vào lớp học phần môn Lập trình Web" />
            </div>

            <!-- Nội dung mô tả chi tiết -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nội dung mô tả chi tiết: <span class="text-rose-500">*</span></label>
                <textarea name="description" rows="4" required
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-xs"
                          placeholder="Mô tả cụ thể thông báo lỗi, thời điểm gặp sự cố và mong muốn KTV hỗ trợ..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <!-- Nút gửi -->
            <div class="pt-2 flex items-center justify-end gap-3">
                <a href="../index.php" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl transition">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm">
                    Gửi Yêu Cầu Hỗ Trợ ➔
                </button>
            </div>

        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
