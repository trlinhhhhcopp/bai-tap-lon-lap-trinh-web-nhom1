<?php
// ==============================================================================
// BÀI 5: CHỈNH SỬA PHIẾU HỖ TRỢ (SLIDE 7 - EDIT & UPDATE)
// Trưởng nhóm phụ trách: Phạm Thị Thanh Ngân (224001736 - D24-CNTT1)
// ==============================================================================

require_once __DIR__ . '/../app/Repositories/TicketRepository.php';

$id = (int)($_GET['id'] ?? 0);
$ticket = TicketRepository::findById($id);

if (!$ticket) {
    set_flash('error', "Không tìm thấy phiếu hỗ trợ với ID #{$id}.");
    header('Location: index.php');
    exit;
}

$categories = TicketRepository::getCategories();
$priorities = TicketRepository::getPriorities();
$agents     = TicketRepository::getUsers('agent');
$errors     = [];

// XỬ LÝ POST CẬP NHẬT DỮ LIỆU
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title          = trim($_POST['title'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $systemAffected = trim($_POST['system_affected'] ?? '');
    $categoryId     = (int)($_POST['category_id'] ?? 0);
    $priorityId     = (int)($_POST['priority_id'] ?? 0);
    $assignedTo     = (int)($_POST['assigned_to'] ?? 0);
    $status         = trim($_POST['status'] ?? $ticket['status']);

    if (empty($title)) {
        $errors[] = "Tiêu đề sự cố không được để trống.";
    }
    if (empty($description)) {
        $errors[] = "Mô tả chi tiết không được để trống.";
    }
    if ($categoryId <= 0) {
        $errors[] = "Vui lòng chọn danh mục dịch vụ.";
    }
    if ($priorityId <= 0) {
        $errors[] = "Vui lòng chọn mức độ ưu tiên.";
    }

    if (empty($errors)) {
        try {
            TicketRepository::update($id, [
                'title'           => $title,
                'description'     => $description,
                'system_affected' => $systemAffected,
                'category_id'     => $categoryId,
                'priority_id'     => $priorityId,
                'assigned_to'     => $assignedTo > 0 ? $assignedTo : null,
                'status'          => $status,
            ]);

            set_flash('success', "Cập nhật phiếu {$ticket['ticket_code']} thành công!");
            header("Location: show.php?id={$id}");
            exit;
        } catch (Exception $e) {
            $errors[] = "Lỗi khi cập nhật CSDL: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Phiếu <?php echo htmlspecialchars($ticket['ticket_code']); ?> - Helpdesk</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 md:p-6">

<div class="max-w-3xl mx-auto space-y-6">

    <!-- HEADER -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono font-bold text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                    <?php echo htmlspecialchars($ticket['ticket_code']); ?>
                </span>
                <span class="text-xs font-semibold text-slate-500">Chỉnh sửa thông tin phiếu</span>
            </div>
            <h1 class="text-xl font-black text-slate-900">Cập Nhật Phiếu Hỗ Trợ</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="show.php?id=<?php echo $ticket['id']; ?>" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                ← Chi tiết phiếu
            </a>
        </div>
    </div>

    <!-- LỖI VALIDATION -->
    <?php if (!empty($errors)): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
            <div class="font-bold">⚠️ Có lỗi xảy ra:</div>
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- FORM EDIT -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <form method="POST" action="edit.php?id=<?php echo $ticket['id']; ?>" class="space-y-4 text-xs">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Danh mục -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nhóm dịch vụ: <span class="text-rose-500">*</span></label>
                    <select name="category_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (int)$ticket['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Mức ưu tiên -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Mức độ ưu tiên: <span class="text-rose-500">*</span></label>
                    <select name="priority_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($priorities as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo (int)$ticket['priority_id'] === (int)$p['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Trạng thái phiếu -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Trạng thái phiếu:</label>
                    <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="new" <?php echo $ticket['status'] === 'new' ? 'selected' : ''; ?>>Mới tiếp nhận (new)</option>
                        <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>Đang xử lý (in_progress)</option>
                        <option value="waiting_requester" <?php echo $ticket['status'] === 'waiting_requester' ? 'selected' : ''; ?>>Chờ phản hồi (waiting)</option>
                        <option value="resolved" <?php echo $ticket['status'] === 'resolved' ? 'selected' : ''; ?>>Đã giải quyết (resolved)</option>
                        <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Đã đóng hoàn tất (closed)</option>
                    </select>
                </div>
            </div>

            <!-- Phân công KTV -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Kỹ thuật viên phụ trách xử lý:</label>
                <select name="assigned_to" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="0">-- Chưa phân công (Để trống) --</option>
                    <?php foreach ($agents as $ag): ?>
                        <option value="<?php echo $ag['id']; ?>" <?php echo (int)$ticket['assigned_to'] === (int)$ag['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ag['name']); ?> (Mã: <?php echo htmlspecialchars($ag['user_code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Hệ thống bị ảnh hưởng -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Hệ thống ứng dụng bị ảnh hưởng:</label>
                <input type="text" name="system_affected" value="<?php echo htmlspecialchars($ticket['system_affected']); ?>" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <!-- Tiêu đề -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tiêu đề sự cố: <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($ticket['title']); ?>" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <!-- Mô tả -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nội dung mô tả chi tiết: <span class="text-rose-500">*</span></label>
                <textarea name="description" rows="4" 
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($ticket['description']); ?></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <!-- Nút Xóa phiếu bằng POST (Slide 7 - Delete an toàn) -->
                <button type="button" onclick="confirmDelete()" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl transition">
                    🗑️ Xóa Phiếu Này
                </button>

                <div class="flex items-center gap-3">
                    <a href="show.php?id=<?php echo $ticket['id']; ?>" class="px-4 py-2 border border-slate-300 hover:bg-slate-100 rounded-xl text-slate-700 font-semibold transition">
                        Hủy
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <span>💾</span> Lưu Thay Đổi
                    </button>
                </div>
            </div>

        </form>

        <!-- FORM ẨN ĐỂ XÓA BẰNG POST (BẢO MẬT CHUẨN SLIDE 7) -->
        <form id="deleteForm" method="POST" action="delete.php" class="hidden">
            <input type="hidden" name="id" value="<?php echo $ticket['id']; ?>" />
        </form>
    </div>

</div>

<script>
function confirmDelete() {
    if (confirm("Bạn có chắc chắn muốn xóa phiếu <?php echo $ticket['ticket_code']; ?> không? Thao tác này sẽ xóa toàn bộ tin nhắn liên quan!")) {
        document.getElementById('deleteForm').submit();
    }
}
</script>

</body>
</html>
