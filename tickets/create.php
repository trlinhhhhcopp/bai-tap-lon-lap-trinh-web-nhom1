<?php
// ==============================================================================
// BÀI 5: TẠO PHIẾU HỖ TRỢ MỚI - ÁP DỤNG POST-REDIRECT-GET (SLIDE 2, 7 & 8)
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

$categories = TicketRepository::getCategories();
$priorities = TicketRepository::getPriorities();
$users      = TicketRepository::getUsers('requester'); // Lấy danh sách SV & GV

$errors = [];

// XỬ LÝ POST KHI NGƯỜI DÙNG BẤM GỬI FORM (SLIDE 8)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requesterId    = (int)($_POST['requester_id'] ?? 0);
    $categoryId     = (int)($_POST['category_id'] ?? 0);
    $priorityId     = (int)($_POST['priority_id'] ?? 0);
    $title          = trim($_POST['title'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $systemAffected = trim($_POST['system_affected'] ?? 'Canvas LMS');

    // 1. Kiểm tra tính hợp lệ dữ liệu (Validate) - Test case 1 & 2
    if ($requesterId <= 0) {
        $errors[] = "Vui lòng chọn người gửi yêu cầu (Sinh viên hoặc Giảng viên).";
    }
    if ($categoryId <= 0) {
        $errors[] = "Vui lòng chọn nhóm dịch vụ gặp sự cố.";
    }
    if ($priorityId <= 0) {
        $errors[] = "Vui lòng chọn mức độ ưu tiên.";
    }
    if (empty($title)) {
        $errors[] = "Tiêu đề sự cố không được để trống (Test case 1).";
    }
    if (empty($description)) {
        $errors[] = "Vui lòng nhập nội dung mô tả chi tiết lỗi bạn đang gặp phải.";
    }

    // 2. Nếu không có lỗi -> Gọi Repository thực hiện INSERT (Slide 8)
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

            // 3. POST - Redirect - GET: Gán Flash Message và Chuyển hướng an toàn
            set_flash('success', "Tạo phiếu hỗ trợ mới thành công! Phiếu đã được chuyển đến KTV chuyên trách.");
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $errors[] = "Lỗi hệ thống khi lưu CSDL: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Phiếu Hỗ Trợ Mới - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 md:p-6">

<div class="max-w-3xl mx-auto space-y-6">

    <!-- HEADER -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">ĐH Thủ Đô Hà Nội</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Slide 8: Demo INSERT</span>
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Tạo Phiếu Hỗ Trợ Sự Cố Kỹ Thuật</h1>
            <p class="text-xs text-slate-500 mt-0.5">Dữ liệu được bảo vệ 100% bằng PDO Prepared Statement chống SQL Injection</p>
        </div>
        <a href="index.php" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
            ← Quay lại danh sách
        </a>
    </div>

    <!-- HIỂN THỊ LỖI NẾU CÓ -->
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

    <!-- FORM NHẬP LIỆU (POST METHOD) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <form method="POST" action="create.php" class="space-y-4 text-xs">
            
            <!-- Người gửi -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Người gửi yêu cầu (Sinh viên / Giảng viên): <span class="text-rose-500">*</span></label>
                <select name="requester_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                    <option value="">-- Chọn tài khoản người gửi --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo (isset($_POST['requester_id']) && (int)$_POST['requester_id'] === (int)$u['id']) || (!isset($_POST['requester_id']) && $u['user_code'] === '224001736') ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['name']); ?> (Mã: <?php echo htmlspecialchars($u['user_code']); ?> - <?php echo $u['user_type'] === 'student' ? 'Sinh viên' : 'Giảng viên'; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="text-slate-400 text-[11px] mt-0.5 block">Mặc định chọn tài khoản của Trưởng nhóm <strong>Phạm Thị Thanh Ngân (224001736)</strong></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Danh mục dịch vụ -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nhóm dịch vụ gặp sự cố: <span class="text-rose-500">*</span></label>
                    <select name="category_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="">-- Chọn danh mục dịch vụ --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?> (SLA chuẩn: <?php echo $cat['sla_hours']; ?>h)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Mức ưu tiên -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mức độ ưu tiên: <span class="text-rose-500">*</span></label>
                    <select name="priority_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="">-- Chọn mức độ ưu tiên --</option>
                        <?php foreach ($priorities as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo (isset($_POST['priority_id']) && (int)$_POST['priority_id'] === (int)$p['id']) || (!isset($_POST['priority_id']) && $p['code'] === 'MED') ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Hệ thống bị ảnh hưởng -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Hệ thống ứng dụng bị ảnh hưởng:</label>
                <input type="text" name="system_affected" value="<?php echo htmlspecialchars($_POST['system_affected'] ?? 'Canvas LMS'); ?>" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition"
                       placeholder="Ví dụ: Canvas LMS, Cổng QLĐT, Wi-Fi Ký túc xá, Microsoft 365..." />
            </div>

            <!-- Tiêu đề sự cố -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tiêu đề tóm tắt sự cố: <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition"
                       placeholder="Ví dụ: Không thấy mục nộp bài tập lớn môn Lập trình Web lớp D24-CNTT1" />
            </div>

            <!-- Mô tả chi tiết -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nội dung mô tả chi tiết: <span class="text-rose-500">*</span></label>
                <textarea name="description" rows="4" 
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition"
                          placeholder="Mô tả cụ thể lỗi bạn đang gặp phải, các bước dẫn đến lỗi và mong muốn KTV hỗ trợ..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <!-- Nút bấm -->
            <div class="pt-2 flex items-center justify-end gap-3">
                <a href="index.php" class="px-4 py-2.5 border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl transition">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm shadow-indigo-200 transition">
                    🚀 Gửi Phiếu Hỗ Trợ
                </button>
            </div>

        </form>
    </div>

</div>

</body>
</html>

