<?php
$pageTitle = 'Thêm Đánh Giá CSAT 5★';
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
                ⭐ ĐÁNH GIÁ CHẤT LƯỢNG CSAT
            </span>
            <h1 class="text-2xl font-black text-slate-900 mt-1">
                <?= htmlspecialchars($pageTitle) ?>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Tiêu chuẩn ITIL v4: Mỗi phiếu hỗ trợ kỹ thuật được đánh giá <strong>1 lần duy nhất</strong> từ 1 đến 5 sao để nghiệm thu kết quả xử lý.
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 space-y-1">
                <p class="font-bold">⚠️ Có lỗi phát sinh khi lưu:</p>
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?action=rating_create" novalidate class="space-y-4 text-xs">
            <div>
                <label for="ticket_id" class="block font-bold text-slate-700 mb-1">
                    Phiếu Hỗ Trợ Cần Nghiệm Thu: <span class="text-rose-500">*</span>
                </label>
                <select id="ticket_id" name="ticket_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800">
                    <option value="">-- Chọn phiếu hỗ trợ --</option>
                    <?php foreach ($tickets as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= ($old['ticket_id'] == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ticket_code'] . ' – ' . $t['title'] . ' (' . $t['requester_name'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (count($students) === 1): ?>
                <input type="hidden" name="user_id" value="<?= (int)$students[0]['id'] ?>" />
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Sinh Viên Đánh Giá:</label>
                    <div class="px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl font-bold text-indigo-700">
                        <?= htmlspecialchars($students[0]['name'] . ' (' . $students[0]['user_code'] . ')') ?>
                    </div>
                </div>
            <?php else: ?>
                <div>
                    <label for="user_id" class="block font-bold text-slate-700 mb-1">
                        Sinh Viên Đánh Giá: <span class="text-rose-500">*</span>
                    </label>
                    <select id="user_id" name="user_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-800">
                        <option value="">-- Chọn sinh viên --</option>
                        <?php foreach ($students as $sv): ?>
                            <option value="<?= (int)$sv['id'] ?>" <?= ($old['user_id'] == $sv['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sv['name'] . ' (' . $sv['user_code'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

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
                    ⭐ Lưu Đánh Giá CSAT
                </button>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../../../includes/footer.php'; ?>
</body>
</html>
