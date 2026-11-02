<?php
$pageTitle = 'Đánh Giá Chất Lượng Dịch Vụ (CSAT)';
require_once __DIR__ . '/../../../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
$appBase = get_app_base_url();

$userRole = $_SESSION['user']['role'] ?? '';
$userType = $_SESSION['user']['user_type'] ?? '';
$isRequesterRole = ($userRole === 'requester' || $userType === 'student' || $userType === 'lecturer');
$currentUserId = (int)($_SESSION['user']['id'] ?? 0);

if (!isset($pendingRatings) && $isRequesterRole && $currentUserId > 0) {
    require_once __DIR__ . '/../../../app/Models/Rating.php';
    $pendingRatings = Rating::getPendingEvaluationTickets($currentUserId);
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

<main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- HEADER BANNER -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    ⭐ CHỈ SỐ HÀI LÒNG KHÁCH HÀNG (CSAT)
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    NGHIỆM THU DỊCH VỤ ITIL v4
                </span>
            </div>
            <h1 class="text-2xl font-black text-white">
                Đánh Giá Chất Lượng Hỗ Trợ Kỹ Thuật
            </h1>
            <p class="text-xs text-indigo-200/80 mt-0.5">Khảo sát mức độ hài lòng từ 1 đến 5 sao và nhận xét phản hồi dịch vụ số trường học.</p>
        </div>
    </div>

    <!-- KHỐI TRÊN ĐẦU: CHƯA ĐÁNH GIÁ (ĐÃ HOÀN TẤT, CẦN ĐÁNH GIÁ) CHO ROLE SV & GV -->
    <?php if ($isRequesterRole): ?>
        <div id="unrated_tickets_section" class="bg-gradient-to-r from-amber-50/90 via-orange-50/60 to-amber-50/90 border-2 border-amber-300 rounded-3xl p-5 shadow-sm space-y-3.5 transition">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-lg shadow-xs">
                        ⭐
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">
                                Chưa Đánh Giá (Đã hoàn tất, cần đánh giá)
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black <?= !empty($pendingRatings) ? 'bg-amber-500 text-slate-950 animate-pulse' : 'bg-slate-200 text-slate-600' ?>">
                                <?= count($pendingRatings ?? []) ?> phiếu cần đánh giá
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">Danh sách các phiếu sự cố kỹ thuật của bạn đã được Kỹ thuật viên hoàn tất xử lý, đang chờ bạn chấm điểm nghiệm thu CSAT.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (!empty($pendingRatings)): ?>
                        <span class="text-[11px] font-bold text-amber-900 bg-amber-200/80 border border-amber-300 px-3 py-1 rounded-xl shadow-2xs">
                            🔔 Cần bạn nghiệm thu & đánh giá chất lượng
                        </span>
                        <!-- NÚT ẨN / THU GỌN KHỐI PHIẾU CHƯA ĐÁNH GIÁ -->
                        <button type="button" 
                                id="btnToggleUnratedSection" 
                                onclick="toggleUnratedSection()" 
                                title="Ẩn hoặc hiện danh sách phiếu chưa đánh giá" 
                                class="px-3.5 py-1.5 bg-amber-200/90 hover:bg-amber-300 text-amber-950 font-bold rounded-xl text-xs transition flex items-center gap-1.5 border border-amber-300 cursor-pointer shadow-2xs">
                            <span id="iconToggleUnrated">✕</span>
                            <span id="textToggleUnrated">Ẩn danh sách</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($pendingRatings)): ?>
                <div id="unrated_tickets_grid" data-count="<?= count($pendingRatings) ?> phiếu" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 pt-1 transition-all duration-200">
                    <?php foreach ($pendingRatings as $pTicket): ?>
                        <div class="bg-white border border-amber-200 hover:border-amber-400 rounded-2xl p-4 shadow-2xs transition hover:shadow-md flex flex-col justify-between space-y-3">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <a href="../tickets/show.php?id=<?= $pTicket['id'] ?>" class="font-mono font-black text-indigo-700 text-xs bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg border border-indigo-200 transition">
                                        <?= htmlspecialchars($pTicket['ticket_code']) ?>
                                    </a>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        ✓ <?= $pTicket['status'] === 'resolved' ? 'Đã giải quyết' : 'Đã đóng' ?>
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-900 text-xs leading-snug line-clamp-2" title="<?= htmlspecialchars($pTicket['title']) ?>">
                                    <a href="../tickets/show.php?id=<?= $pTicket['id'] ?>" class="hover:text-indigo-600 transition">
                                        <?= htmlspecialchars($pTicket['title']) ?>
                                    </a>
                                </h3>
                                <div class="text-[11px] text-slate-500 space-y-1 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <div class="truncate">📁 Chuyên mục: <strong class="text-slate-700"><?= htmlspecialchars($pTicket['category_name']) ?></strong></div>
                                    <?php if (!empty($pTicket['agent_name'])): ?>
                                        <div class="truncate">👨‍💻 KTV xử lý: <strong class="text-slate-700"><?= htmlspecialchars($pTicket['agent_name']) ?></strong></div>
                                    <?php endif; ?>
                                    <?php if (!empty($pTicket['resolved_at'])): ?>
                                        <div>⏱️ Hoàn tất: <span class="font-mono text-slate-600"><?= date('d/m/Y H:i', strtotime($pTicket['resolved_at'])) ?></span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100">
                                <a href="../tickets/show.php?id=<?= $pTicket['id'] ?>#csat-box" 
                                   class="w-full text-center py-2 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black rounded-xl text-xs shadow-xs transition flex items-center justify-center gap-1.5">
                                    <span>⭐</span> Chấm Điểm & Nghiệm Thu Ngay →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-3.5 bg-white/90 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs">
                    <span class="text-emerald-600 text-lg">✅</span>
                    <div>
                        <span class="font-bold text-emerald-900">Không có phiếu nào cần đánh giá.</span>
                        <span class="text-slate-500 block text-[11.5px]">Tất cả các phiếu yêu cầu hỗ trợ kỹ thuật đã hoàn tất của bạn đều đã được đánh giá CSAT đầy đủ!</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash)): ?>
        <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <!-- KHUNG LỌC & DANH SÁCH ĐÁNH GIÁ (HỖ TRỢ AJAX KHÔNG LOAD LẠI TRANG) -->
    <div id="student_ratings_container" class="space-y-4 transition-opacity duration-200">
    <!-- BỘ NÚT LỌC NHANH SỐ SAO (5 SAO, 4 SAO, 3 SAO, 2 SAO, 1 SAO) -->
    <div class="flex items-center gap-2 flex-wrap">
        <a href="ratings.php?q=<?= urlencode($keyword) ?>" onclick="filterStudentRatingsTab(event, this)" 
           class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 <?= ($scoreFilter === null || $scoreFilter === '') ? 'bg-slate-900 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100' ?>">
            <span>Tất cả số sao</span>
        </a>
        <?php for ($s = 5; $s >= 1; $s--): ?>
            <?php $isActive = ((string)$scoreFilter === (string)$s); ?>
            <a href="ratings.php?score=<?= $s ?>&q=<?= urlencode($keyword) ?>" onclick="filterStudentRatingsTab(event, this)" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?= $isActive ? 'bg-slate-900 text-white shadow-sm ring-2 ring-amber-400' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100' ?>">
                <span class="<?= $isActive ? 'text-amber-400' : 'text-amber-500' ?> font-black"><?= str_repeat('★', $s) ?></span>
                <span class="<?= $isActive ? 'text-white' : 'text-slate-800' ?>"><?= $s ?> sao</span>
            </a>
        <?php endfor; ?>
    </div>

    <!-- THANH TÌM KIẾM -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex items-center justify-between flex-wrap gap-3 text-xs">
        <form method="get" action="ratings.php" class="flex items-center gap-2 flex-1 max-w-lg">
            <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm kiếm theo mã vé, sinh viên, nhận xét..." 
                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium" />
            <select name="score" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                <option value="">Tất cả số sao</option>
                <?php for ($s = 5; $s >= 1; $s--): ?>
                    <option value="<?= $s ?>" <?= ($scoreFilter == $s) ? 'selected' : '' ?>><?= $s ?> sao</option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl transition">
                Lọc
            </button>
        </form>
        <div class="flex items-center gap-3">
            <button type="button" data-target-table="ratings_table_body" onclick="toggleTableCollapse('ratings_table_body', this)" title="Ẩn hoặc hiện bảng đánh giá" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1 border border-slate-200 cursor-pointer">
                <span class="toggle-table-icon">✕</span>
                <span class="toggle-table-text">Ẩn bảng</span>
            </button>
            <div class="text-slate-500 font-mono text-[11.5px]">
                Tổng số: <strong class="text-slate-800"><?= $total ?></strong> đánh giá · Trang <?= $page ?>/<?= $totalPages ?>
            </div>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH (CÓ THỂ ẨN / THU GỌN) -->
    <div id="ratings_table_body" data-collapsible-table class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                        <th class="py-3.5 px-4">Mã Phiếu</th>
                        <th class="py-3.5 px-4">Tiêu Đề Sự Cố</th>
                        <th class="py-3.5 px-4">Người Đánh Giá</th>
                        <th class="py-3.5 px-4 text-center">Điểm CSAT</th>
                        <th class="py-3.5 px-4">Nhận Xét / Góp Ý</th>
                        <th class="py-3.5 px-4 text-center">Thời Gian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($ratings)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Chưa có đánh giá CSAT nào phù hợp.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ratings as $row): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                                    <a href="../tickets/show.php?id=<?= $row['ticket_id'] ?>" class="hover:underline">
                                        <?= htmlspecialchars($row['ticket_code']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800 max-w-xs truncate">
                                    <a href="../tickets/show.php?id=<?= $row['ticket_id'] ?>" class="hover:text-indigo-600 transition">
                                        <?= htmlspecialchars($row['ticket_title']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($row['user_name']) ?></div>
                                    <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($row['user_code']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-mono font-bold text-amber-500 text-sm">
                                        <?= str_repeat('★', (int)$row['score']) . str_repeat('☆', 5 - (int)$row['score']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 italic">
                                    <?= !empty($row['comment']) ? '"' . htmlspecialchars($row['comment']) . '"' : '<span class="text-slate-300">Không có nhận xét</span>' ?>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-400 font-mono text-[11px]">
                                    <?= htmlspecialchars($row['created_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-slate-100 flex items-center justify-center gap-1.5 text-xs">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="ratings.php?page=<?= $i ?>&q=<?= urlencode($keyword) ?>&score=<?= urlencode((string)$scoreFilter) ?>" 
                       class="px-3 py-1.5 rounded-lg border <?= $i == $page ? 'bg-indigo-600 text-white border-indigo-600 font-bold' : 'border-slate-300 text-slate-700 hover:bg-slate-100' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
    </div>

</main>

<script>
function toggleUnratedSection() {
    const grid = document.getElementById('unrated_tickets_grid');
    const btn = document.getElementById('btnToggleUnratedSection');
    const text = document.getElementById('textToggleUnrated');
    const icon = document.getElementById('iconToggleUnrated');
    if (!grid) return;
    
    const isHidden = grid.classList.toggle('hidden');
    const count = grid.dataset.count || '';
    if (text) text.textContent = isHidden ? `Hiện danh sách (${count})` : 'Ẩn danh sách';
    if (icon) icon.textContent = isHidden ? '👁️' : '✕';
    
    if (btn) {
        if (isHidden) {
            btn.classList.add('bg-amber-400', 'text-slate-950', 'ring-2', 'ring-amber-300');
            btn.classList.remove('bg-amber-200/90', 'text-amber-950');
        } else {
            btn.classList.remove('bg-amber-400', 'text-slate-950', 'ring-2', 'ring-amber-300');
            btn.classList.add('bg-amber-200/90', 'text-amber-950');
        }
    }
    try {
        localStorage.setItem('collapse_unrated_section', isHidden ? '1' : '0');
    } catch (e) {}
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        if (localStorage.getItem('collapse_unrated_section') === '1') {
            const grid = document.getElementById('unrated_tickets_grid');
            const btn = document.getElementById('btnToggleUnratedSection');
            const text = document.getElementById('textToggleUnrated');
            const icon = document.getElementById('iconToggleUnrated');
            if (grid) {
                grid.classList.add('hidden');
                const count = grid.dataset.count || '';
                if (text) text.textContent = `Hiện danh sách (${count})`;
                if (icon) icon.textContent = '👁️';
                if (btn) {
                    btn.classList.add('bg-amber-400', 'text-slate-950', 'ring-2', 'ring-amber-300');
                    btn.classList.remove('bg-amber-200/90', 'text-amber-950');
                }
            }
        }
    } catch (e) {}
});

function filterStudentRatingsTab(event, link) {
    event.preventDefault();
    const url = link.href;
    const container = document.getElementById('student_ratings_container');
    if (!container) {
        window.location.href = url;
        return;
    }
    container.style.opacity = '0.4';
    fetch(url)
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('student_ratings_container');
            if (newContent) {
                container.innerHTML = newContent.innerHTML;
                container.style.opacity = '1';
                window.history.pushState(null, '', url);
            } else {
                window.location.href = url;
            }
        })
        .catch(() => {
            window.location.href = url;
        });
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
</body>
</html>
