<?php
$holiday = $holiday ?? null;

function holidayFormEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$isEdit = is_array($holiday);
$pageTitle = $isEdit ? 'Chỉnh Sửa Ngày Nghỉ Lễ' : 'Thêm Mới Ngày Nghỉ Lễ';

require_once __DIR__ . '/../../../config/database.php';
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
if (empty($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
<?php require_once __DIR__ . '/../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">

    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        
        <div class="border-b border-slate-100 pb-4">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                <?= $isEdit ? '✏️ HIỆU CHỈNH' : '➕ TẠO MỚI' ?>
            </span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">
                <?= htmlspecialchars($pageTitle) ?>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Các ngày nghỉ lễ sẽ được trừ khỏi cam kết dịch vụ kỹ thuật (SLA) và đồng bộ với hệ thống thời khóa biểu Canvas LMS.
            </p>
        </div>

        <form method="POST" class="space-y-5 text-xs">
            <div>
                <label for="name" class="block font-bold text-slate-700 mb-1">
                    Tên Ngày Nghỉ Lễ / Sự Kiện: <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    maxlength="100"
                    required
                    placeholder="VD: Nghỉ Tết Nguyên Đán, Giỗ Tổ Hùng Vương, Nghỉ Lễ 30/4..."
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-slate-800"
                    value="<?= holidayFormEscape($holiday['name'] ?? '') ?>"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block font-bold text-slate-700 mb-1">
                        Ngày Bắt Đầu: <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-slate-800"
                        value="<?= holidayFormEscape($holiday['start_date'] ?? '') ?>"
                    >
                </div>

                <div>
                    <label for="end_date" class="block font-bold text-slate-700 mb-1">
                        Ngày Kết Thúc: <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-slate-800"
                        value="<?= holidayFormEscape($holiday['end_date'] ?? '') ?>"
                    >
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-3">
                <input
                    type="checkbox"
                    id="is_recurring"
                    name="is_recurring"
                    value="1"
                    class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500"
                    <?= (int) ($holiday['is_recurring'] ?? 0) === 1 ? 'checked' : '' ?>
                >
                <label for="is_recurring" class="text-slate-700 font-bold cursor-pointer">
                    🔄 Lặp lại hằng năm (Áp dụng cho các ngày lễ cố định theo năm dương lịch hoặc âm lịch)
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="holidays.php" 
                   class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Hủy bỏ
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-100 flex items-center gap-1.5">
                    💾 <?= $isEdit ? 'Lưu Thay Đổi' : 'Thêm Ngày Nghỉ Lễ' ?>
                </button>
            </div>
        </form>

    </div>

</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>
