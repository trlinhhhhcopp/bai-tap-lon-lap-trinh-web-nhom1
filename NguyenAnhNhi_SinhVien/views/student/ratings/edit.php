<?php
$pageTitle = 'Sửa Đánh Giá CSAT 5★';
require_once __DIR__ . '/../../../../config/database.php';
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
<?php require_once __DIR__ . '/../../../../includes/navbar.php'; ?>

<main class="flex-1 max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        
        <div class="border-b border-slate-100 pb-4">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                ✏️ HIỆU CHỈNH ĐÁNH GIÁ CSAT
            </span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">
                <?= htmlspecialchars($pageTitle) ?> #<?= (int)$rating['id'] ?>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Cập nhật số sao hài lòng hoặc bổ sung ý kiến đóng góp cho dịch vụ hỗ trợ kỹ thuật.
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
                <p class="font-bold">⚠️ Có lỗi phát sinh khi cập nhật:</p>
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?action=rating_edit&id=<?= (int)$rating['id'] ?>" novalidate class="space-y-4 text-xs">
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
                <div>
                    <span class="text-slate-500 font-semibold">Mã Phiếu Hỗ Trợ:</span>
                    <strong class="text-slate-800 font-mono ml-2"><?= htmlspecialchars($rating['ticket_code'] . ' – ' . $rating['title']) ?></strong>
                </div>
                <div>
                    <span class="text-slate-500 font-semibold">Sinh Viên Nghiệm Thu:</span>
                    <strong class="text-slate-800 ml-2"><?= htmlspecialchars($rating['student_name']) ?></strong>
                </div>
            </div>

            <input type="hidden" name="ticket_id" value="<?= (int)$rating['ticket_id'] ?>">
            <input type="hidden" name="user_id" value="<?= (int)$rating['user_id'] ?>">

            <div>
                <label for="score" class="block font-bold text-slate-700 mb-1">
                    Mức Độ Hài Lòng (1 - 5 Sao): <span class="text-rose-500">*</span>
                </label>
                <select id="score" name="score" required class="w-full px-3.5 py-2.5 bg-amber-50 border border-amber-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold text-amber-900">
                    <option value="5" <?= (int)$old['score'] === 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ 5 Sao - Rất hài lòng / Xử lý tuyệt vời</option>
                    <option value="4" <?= (int)$old['score'] === 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ 4 Sao - Hài lòng / Đáp ứng yêu cầu</option>
                    <option value="3" <?= (int)$old['score'] === 3 ? 'selected' : '' ?>>⭐⭐⭐ 3 Sao - Bình thường / Đạt mức chấp nhận</option>
                    <option value="2" <?= (int)$old['score'] === 2 ? 'selected' : '' ?>>⭐⭐ 2 Sao - Chưa hài lòng / Cần cải thiện</option>
                    <option value="1" <?= (int)$old['score'] === 1 ? 'selected' : '' ?>>⭐ 1 Sao - Rất thất vọng / Chưa xử lý được</option>
                </select>
            </div>

            <div>
                <label for="comment" class="block font-bold text-slate-700 mb-1">
                    Ý Kiến Nhận Xét / Phản Hồi Dịch Vụ:
                </label>
                <textarea id="comment" name="comment" rows="3" placeholder="Nhập lời khen hoặc phản hồi góp ý dành cho KTV xử lý..."
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800"><?= htmlspecialchars($old['comment'] ?? '') ?></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="index.php?action=ratings" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition shadow-md shadow-amber-100 flex items-center gap-1.5">
                    ⭐ Cập Nhật Đánh Giá
                </button>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../../../includes/footer.php'; ?>
</body>
</html>
