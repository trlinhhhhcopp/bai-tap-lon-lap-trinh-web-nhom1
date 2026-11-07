<?php
require_once __DIR__ . '/../../config/database.php';
$appBase = get_app_base_url();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung Tâm Quản Trị Hệ Thống - Admin Control Center</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
    <style>
        @media (max-width: 767px) {
            .admin-ticket-table,
            .admin-ticket-table tbody {
                display: block;
                width: 100%;
            }

            .admin-ticket-table thead {
                display: none;
            }

            .admin-ticket-table tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                padding: 1rem;
                border-bottom: 1px solid #e2e8f0;
            }

            .admin-ticket-table tbody td {
                display: flex;
                min-width: 0;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
                padding: 0;
                overflow-wrap: anywhere;
                text-align: left;
            }

            .admin-ticket-table tbody td::before {
                content: attr(data-label);
                color: #64748b;
                font-size: 0.75rem;
                font-weight: 700;
            }

            .admin-ticket-table .admin-ticket-code,
            .admin-ticket-table .admin-ticket-title,
            .admin-ticket-table .admin-ticket-requester {
                grid-column: 1 / -1;
            }

            .admin-ticket-table .admin-ticket-title a {
                max-width: none;
                overflow: visible;
                text-overflow: clip;
                white-space: normal;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between">
    
    <?php require __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Banner Admin Control Center -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                    🛡️ TRUNG TÂM QUẢN TRỊ VIÊN (ADMIN CONTROL CENTER)
                </span>
                <h1 class="text-2xl font-black text-white">
                    Bảng Điều Hành Hệ Thống Helpdesk
                </h1>
                <p class="text-xs text-indigo-200/80">Quản trị người dùng, phân công đào tạo, nhập dữ liệu Excel hàng loạt và điều phối tràn tải.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <?php if ($pendingGuestCount > 0): ?>
                    <a href="<?php echo $appBase; ?>tickets/index.php?has_warning=guest" class="px-3 py-1.5 rounded-xl bg-rose-500/30 hover:bg-rose-500/50 text-rose-200 border border-rose-400/40 font-bold text-xs flex items-center gap-1.5 transition">
                        <span>🚑</span> <span>Trạm Cứu Hộ: <?php echo $pendingGuestCount; ?> phiếu</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($flash): ?>
            <div data-auto-dismiss="4500" class="p-3.5 rounded-2xl text-xs font-semibold <?php echo $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- 4 THẺ KPI TOÀN TRƯỜNG -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tổng Phiếu Sự Cố</span>
                <span class="text-2xl font-black text-slate-900"><?php echo $totalTickets; ?></span>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Đang Chờ Xử Lý</span>
                <span class="text-2xl font-black text-amber-600"><?php echo $activeTickets; ?></span>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block mb-1">Khẩn Cấp / Cao</span>
                <span class="text-2xl font-black text-rose-600"><?php echo $urgentTickets; ?></span>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                <span class="text-[11px] font-bold text-purple-600 uppercase tracking-wider block mb-1">Tổng Số Tài Khoản</span>
                <span class="text-2xl font-black text-purple-600"><?php echo $totalUsers; ?></span>
            </div>
        </div>

        <!-- BANNER THÔNG BÁO HỆ THỐNG MỚI NHẤT (ĐẶT SAU PHẦN TỔNG QUAN) -->
        <?php require __DIR__ . '/../../includes/notification_banner.php'; ?>

        <!-- BẢNG SỰ CỐ DIỆN RỘNG (MAJOR INCIDENTS) DO KTV BÁO CÁO CẦN ADMIN XỬ LÝ -->
        <?php if (!empty($majorIncidents)): ?>
            <div class="bg-gradient-to-r from-rose-50 via-red-50 to-amber-50 border-2 border-rose-400 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2 border-b border-rose-200 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🔥</span>
                        <div>
                            <h2 class="text-sm font-black text-rose-950 flex items-center gap-2">
                                <span>SỰ CỐ DIỆN RỘNG (MAJOR INCIDENTS) DO KTV BÁO CÁO CẦN ADMIN XỬ LÝ</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-600 text-white animate-pulse">
                                    <?php echo $majorIncidentCount; ?> sự cố khẩn cấp
                                </span>
                            </h2>
                            <p class="text-[11.5px] text-rose-800/90 mt-0.5">
                                Kỹ thuật viên phát hiện sự cố hệ thống cấp cao ảnh hưởng diện rộng toàn trường. Quản trị viên (Admin) xem xét chỉ đạo và kích hoạt phát cảnh báo toàn trường.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto bg-white rounded-2xl border border-rose-200 shadow-2xs">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-rose-50/80 text-rose-950 font-bold border-b border-rose-200 text-[11px]">
                                <th class="py-3 px-4">Mã Vé Sự Cố</th>
                                <th class="py-3 px-4">Tiêu Đề & Hệ Thống</th>
                                <th class="py-3 px-4">KTV Báo Cáo</th>
                                <th class="py-3 px-4">Thời Điểm Báo</th>
                                <th class="py-3 px-4">Cảnh Báo Toàn Trường</th>
                                <th class="py-3 px-4 text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($majorIncidents as $mi): ?>
                                <tr class="hover:bg-rose-50/40 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-rose-700">
                                        <?php echo htmlspecialchars($mi['ticket_code']); ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?php echo htmlspecialchars($mi['title']); ?></div>
                                        <div class="text-[11px] text-slate-500">Ứng dụng: <strong class="text-rose-600"><?php echo htmlspecialchars($mi['system_affected']); ?></strong></div>
                                    </td>
                                    <td class="py-3 px-4 font-medium text-slate-700">
                                        <?php echo htmlspecialchars($mi['agent_name'] ?? 'Chưa phân công'); ?>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-500">
                                        <?php echo htmlspecialchars($mi['created_at']); ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (($mi['admin_broadcast_status'] ?? '') === 'broadcasted'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">✓ Đã phát cảnh báo</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">⏳ Chờ Admin phát cảnh báo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $mi['id']; ?>&view_as=admin" 
                                               class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-2xs inline-flex items-center gap-1">
                                                <span>⚡</span> Xem & Chỉ Đạo →
                                            </a>
                                            <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $mi['id']; ?>&view_as=admin&open_revoke=1" 
                                               class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-2xs inline-flex items-center gap-1"
                                               title="Thu hồi cảnh báo khẩn cấp khi sự cố đã khắc phục xong">
                                                <span>✅</span> Thu Hồi / Đã Xong
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- BẢNG PHIẾU CỨU HỘ KHẨN CẤP KTV ĐÃ DUYỆT CẦN ADMIN PHÊ DUYỆT MỞ KHÓA -->
        <?php if (!empty($pendingGuestTickets)): ?>
            <div class="bg-gradient-to-r from-rose-50 via-amber-50 to-orange-50 border-2 border-rose-300 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2 border-b border-rose-200 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🚑</span>
                        <div>
                            <h2 class="text-sm font-black text-rose-950 flex items-center gap-2">
                                <span>PHIẾU CỨU HỘ KHẨN CẤP KTV ĐÃ DUYỆT CẦN ADMIN PHÊ DUYỆT MỞ KHÓA</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-600 text-white animate-pulse">
                                    <?php echo $pendingGuestCount; ?> phiếu đã duyệt
                                </span>
                            </h2>
                            <p class="text-[11.5px] text-rose-800/90 mt-0.5">
                                Kỹ thuật viên đã kiểm tra xác minh thẻ SV/CMND hợp lệ. Quản trị viên (Admin) xem xét và phê duyệt mở khóa tài khoản & cấp mật khẩu mới.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" data-target-table="admin_rescue_table_body" onclick="toggleTableCollapse('admin_rescue_table_body', this)" title="Ẩn hoặc hiện bảng này" class="shrink-0 px-3 py-1.5 bg-white/80 hover:bg-white text-rose-800 font-bold rounded-xl text-xs transition flex items-center gap-1 border border-rose-200 cursor-pointer shadow-xs">
                            <span class="toggle-table-icon">✕</span>
                            <span class="toggle-table-text">Ẩn bảng</span>
                        </button>
                        <a href="<?php echo $appBase; ?>tickets/index.php?has_warning=guest" class="shrink-0 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-xs">
                            Xem Tất Cả Trạm Cứu Hộ →
                        </a>
                    </div>
                </div>

                <div id="admin_rescue_table_body" data-collapsible-table class="overflow-x-auto bg-white rounded-2xl border border-rose-200 shadow-2xs">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-rose-50/80 text-rose-950 font-bold border-b border-rose-200 text-[11px]">
                                <th class="py-3 px-4">Mã Vé Cứu Hộ</th>
                                <th class="py-3 px-4">Người Gửi (SV/CB)</th>
                                <th class="py-3 px-4">Lớp / Đơn Vị</th>
                                <th class="py-3 px-4">Trạng Thái Tài Khoản</th>
                                <th class="py-3 px-4 text-center">Xác Thực KTV</th>
                                <th class="py-3 px-4 text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100">
                            <?php foreach ($pendingGuestTickets as $gt): ?>
                                <tr class="hover:bg-rose-50/40 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-rose-700">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $gt['id']; ?>" class="hover:underline">
                                            <?php echo htmlspecialchars($gt['ticket_code']); ?>
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?php echo htmlspecialchars($gt['student_name'] ?: 'Khách vãng lai'); ?></div>
                                        <div class="text-[11px] font-mono text-slate-500">Mã: <?php echo htmlspecialchars($gt['student_code'] ?: 'N/A'); ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 font-medium">
                                        <?php echo htmlspecialchars($gt['class_code']); ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ((int)($gt['is_locked'] ?? 0) === 1): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                                🔒 Đang Bị Khóa
                                            </span>
                                            <?php if (!empty($gt['lock_reason'])): ?>
                                                <div class="text-[10.5px] text-rose-600 mt-0.5 truncate max-w-xs" title="<?php echo htmlspecialchars($gt['lock_reason']); ?>">
                                                    Lý do: <?php echo htmlspecialchars($gt['lock_reason']); ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                Bình thường / Quên mật khẩu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ((int)($gt['rescue_verified'] ?? 0) === 1): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                ✅ KTV Đã Xác Nhận
                                            </span>
                                        <?php elseif ((int)($gt['rescue_verified'] ?? 0) === 2): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                ❌ KTV Bác Bỏ
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                ⏳ Chờ KTV Xác Nhận
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $gt['id']; ?>" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-2xs inline-flex items-center gap-1 cursor-pointer">
                                            <span>🔓</span> Xem & Xử Lý →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- 2 CỘT: DANH SÁCH PHIẾU CẦN TRIAGE & GIÁM SÁT TẢI 5 KTV -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Danh sách phiếu gần đây (8 cột) -->
            <div class="lg:col-span-8 bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden space-y-3">
                <div class="p-3 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span>🎫</span>
                            <span class="sm:hidden">Phiếu cần xử lý</span>
                            <span class="hidden sm:inline">PHIẾU HỖ TRỢ CẦN TRIAGE & ĐIỀU PHỐI</span>
                        </h2>
                        <p class="hidden sm:block text-[11.5px] text-slate-500 mt-0.5">Quản trị viên có thẩm quyền nâng mức URGENT diện rộng và điều phối KTV.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" data-target-table="admin_triage_table_body" onclick="toggleTableCollapse('admin_triage_table_body', this)" title="Ẩn hoặc hiện bảng này" class="shrink-0 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1 border border-slate-200 cursor-pointer">
                            <span class="toggle-table-icon">✕</span>
                            <span class="toggle-table-text">Ẩn bảng</span>
                        </button>
                        <a href="<?php echo $appBase; ?>tickets/index.php" class="shrink-0 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                            <span class="sm:hidden">Tất cả →</span>
                            <span class="hidden sm:inline">Xem Tất Cả →</span>
                        </a>
                    </div>
                </div>

                <div id="admin_triage_table_body" data-collapsible-table class="overflow-x-auto">
                    <table class="admin-ticket-table w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                                <th class="py-3 px-4">Mã Vé</th>
                                <th class="py-3 px-4">Tiêu Đề</th>
                                <th class="py-3 px-4">Người Gửi</th>
                                <th class="py-3 px-4 text-center">Ưu Tiên</th>
                                <th class="py-3 px-4 text-center">Trạng Thái</th>
                                <th class="py-3 px-4 text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($recentTickets as $t): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td data-label="Mã vé" class="admin-ticket-code py-3 px-4 font-mono font-bold text-indigo-600">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:underline">
                                            <?php echo htmlspecialchars($t['ticket_code']); ?>
                                        </a>
                                    </td>
                                    <td data-label="Tiêu đề" class="admin-ticket-title py-3 px-4 font-semibold text-slate-800">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="hover:text-indigo-600 transition block max-w-xs truncate">
                                            <?php echo htmlspecialchars($t['title']); ?>
                                        </a>
                                    </td>
                                    <td data-label="Người gửi" class="admin-ticket-requester py-3 px-4 text-slate-600"><?php echo htmlspecialchars($t['requester_name']); ?></td>
                                    <td data-label="Ưu tiên" class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?php echo htmlspecialchars($t['priority_name']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Trạng thái" class="py-3 px-4 text-center">
                                        <?= render_status_badge($t['status']); ?>
                                    </td>
                                    <td data-label="Chi tiết" class="py-3 px-4 text-center">
                                        <a href="<?php echo $appBase; ?>tickets/show.php?id=<?php echo $t['id']; ?>" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg transition">
                                            Chi tiết →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tình trạng tải 5 KTV (4 cột) -->
            <div class="lg:col-span-4 bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span>👨‍💻</span> Giám Sát Tải KTV (Ngưỡng 15 Phiếu)
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" data-target-table="admin_agents_load_body" onclick="toggleTableCollapse('admin_agents_load_body', this)" title="Ẩn hoặc hiện danh sách tải KTV" class="text-slate-500 hover:text-slate-800 text-[11px] font-bold flex items-center gap-1 cursor-pointer">
                            <span class="toggle-table-icon">✕</span>
                            <span class="toggle-table-text">Ẩn</span>
                        </button>
                        <a href="<?php echo $appBase; ?>agent/index.php" class="text-indigo-600 hover:underline text-[11px] font-bold">Xem Workspace</a>
                    </div>
                </div>

                <div id="admin_agents_load_body" data-collapsible-table class="space-y-3">
                    <?php foreach ($agents as $ag): ?>
                        <?php 
                        $maxCapacity = 15;
                        $loadPercent = min(100, round(($ag['active_load'] / $maxCapacity) * 100));
                        $isOverloaded = ($ag['active_load'] >= $maxCapacity);
                        ?>
                        <div class="p-3 rounded-2xl border <?php echo $isOverloaded ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50 border-slate-200'; ?> space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800"><?php echo htmlspecialchars($ag['name']); ?></span>
                                <span class="font-mono font-bold <?php echo $isOverloaded ? 'text-rose-600' : 'text-slate-600'; ?>">
                                    <?php echo $ag['active_load']; ?> / <?php echo $maxCapacity; ?>
                                </span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full <?php echo $isOverloaded ? 'bg-rose-600' : ($loadPercent > 60 ? 'bg-amber-500' : 'bg-emerald-500'); ?>" style="width: <?php echo $loadPercent; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </main>

    <?php require __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>