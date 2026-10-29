<?php
// ==============================================================================
// BÀI TẬP LỚN LẬP TRÌNH WEB - HỌC KỲ I - ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD)
// KHOA: CÔNG NGHỆ THÔNG TIN - LỚP: D24-CNTT1 - NHÓM 1
// CHỦ ĐỀ 9: HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN (HELPDESK ONLINE)
// TRƯỞNG NHÓM PHỤ TRÁCH: PHẠM THỊ THANH NGÂN (MSSV: 224001736)
// ------------------------------------------------------------------------------
// CỔNG TỔNG HỢP LIÊN KẾT TOÀN DIỆN CẢ 5 PHÂN HỆ THÀNH VIÊN NHÓM 1
// ==============================================================================

require_once __DIR__ . '/config/database.php';

$dbStatus = 'OK';
$dbMessage = '';
$totalTickets = 0;
$totalUsers = 0;
$totalAgents = 0;
$totalAssignments = 0;
$totalRatings = 0;
$avgRating = 5.0;

try {
    $pdo = db();
    $totalTickets     = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
    $totalUsers       = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalAgents      = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'agent'")->fetchColumn();
    $totalAssignments = (int)$pdo->query("SELECT COUNT(*) FROM course_assignments")->fetchColumn();
    $totalRatings     = (int)$pdo->query("SELECT COUNT(*) FROM ticket_ratings")->fetchColumn();
    $avgRating        = round((float)$pdo->query("SELECT AVG(score) FROM ticket_ratings")->fetchColumn() ?: 5.0, 1);
    $dbMessage        = "Đã kết nối thành công CSDL 'helpdesk_thudo' (MariaDB/MySQL utf8mb4)";
} catch (Exception $e) {
    $dbStatus  = 'ERROR';
    $dbMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Tổng Hợp Dự Án Helpdesk CNTT - BTL Nhóm 1 - ĐH Thủ Đô Hà Nội</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 md:p-8">

<div class="max-w-6xl mx-auto space-y-6">

    <!-- HERO HEADER TỔNG HỢP -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 md:p-8 shadow-xl border border-indigo-900/50 relative overflow-hidden">
        <!-- Trang trí nền -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-indigo-900/60 pb-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        🏛️ TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI (TUD)
                    </span>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        KHOA CÔNG NGHỆ THÔNG TIN • LỚP D24-CNTT1
                    </span>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        BÀI TẬP LỚN LẬP TRÌNH WEB — NHÓM 1
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full <?php echo $dbStatus === 'OK' ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500'; ?>"></span>
                    <span>CSDL: <strong><?php echo $dbStatus === 'OK' ? 'Kết Nối Tốt' : 'Lỗi'; ?></strong></span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                        HỆ THỐNG TIẾP NHẬN & XỬ LÝ HỖ TRỢ CNTT TRỰC TUYẾN
                    </h1>
                    <p class="text-xs md:text-sm text-indigo-200/90 leading-relaxed max-w-3xl">
                        Cổng tích hợp thống nhất toàn bộ 5 phân hệ chuyên trách của Nhóm 1. Xây dựng theo tiêu chuẩn kiến trúc dịch vụ số ITIL v4: Tiếp nhận lỗi tinh gọn, Auto-Priority Mapping chống gian lận SLA, bàn làm việc KTV điều phối tràn tải thông minh, quản lý phân công giảng dạy và vòng đời tài khoản học thuật.
                    </p>
                    <div class="pt-1 text-xs text-indigo-300 flex flex-wrap items-center gap-4">
                        <span>👑 Trưởng nhóm: <strong class="text-white">Phạm Thị Thanh Ngân</strong> (224001736)</span>
                        <span>👥 Sĩ số nhóm: <strong class="text-white">5 Thành viên</strong></span>
                        <span>🗄️ Kiến trúc CSDL: <strong class="text-white">12 Bảng Chuẩn Hóa 3NF</strong></span>
                    </div>
                </div>

                <!-- Thống kê động từ CSDL -->
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-2 gap-2.5 shrink-0 text-center">
                    <div class="bg-white/5 border border-white/10 rounded-xl p-2.5 backdrop-blur-sm">
                        <span class="text-xl font-black block text-indigo-300"><?php echo $totalTickets; ?></span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider">Phiếu Sự Cố</span>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-2.5 backdrop-blur-sm">
                        <span class="text-xl font-black block text-emerald-300"><?php echo $totalUsers; ?></span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider">Tài Khoản</span>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-2.5 backdrop-blur-sm">
                        <span class="text-xl font-black block text-amber-300"><?php echo $totalAssignments; ?></span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider">Lớp Học Phần</span>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-2.5 backdrop-blur-sm">
                        <span class="text-xl font-black block text-rose-300"><?php echo $totalRatings > 0 ? "{$avgRating} ★" : "5.0 ★"; ?></span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider">Điểm CSAT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MA TRẬN 5 PHÂN HỆ THÀNH VIÊN NHÓM 1 (RACI MATRIX LINKS) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <h2 class="text-base font-black text-slate-900 uppercase tracking-wide flex items-center gap-2">
                <span>📂</span> Danh Mục 5 Phân Hệ Bàn Giao Của 5 Thành Viên Nhóm 1:
            </h2>
            <span class="text-xs text-slate-500">Mỗi phân hệ phụ trách độc quyền các bảng và Model riêng</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <!-- PHÂN HỆ 1: TRƯỞNG NHÓM PHẠM THỊ THANH NGÂN -->
            <div class="bg-white border-2 border-indigo-600 rounded-2xl p-5 shadow-sm space-y-4 flex flex-col justify-between relative overflow-hidden">
                <span class="absolute top-0 right-0 bg-indigo-600 text-white text-[10px] font-black uppercase px-3 py-1 rounded-bl-xl tracking-wider">
                    👑 Trưởng Nhóm
                </span>
                <div class="space-y-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-center text-lg">
                            👨‍💻
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-slate-900">Phân hệ: Core Architecture & Agent Workspace</h3>
                            <p class="text-xs text-indigo-700 font-bold">Phạm Thị Thanh Ngân (MSSV: 224001736)</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Chịu trách nhiệm kiến trúc lõi CSDL, Model độc quyền <code>Ticket.php</code>, <code>Reply.php</code> và Service điều phối <code>TicketService.php</code>.
                    </p>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-[11px] text-slate-600 space-y-1">
                        <div>• <strong>Bàn làm việc KTV (Agent Workspace):</strong> Sắp xếp hàng đợi theo SLA <code>due_at ASC</code>, điều phối 5 KTV chuyên trách, tự động kích hoạt <strong>tràn tải khi đạt ngưỡng 15 phiếu</strong>.</div>
                        <div>• <strong>Quản lý phiếu hỗ trợ (CRUD Bài 5):</strong> Phân trang LIMIT/OFFSET, tìm kiếm, sửa trạng thái, xóa POST, trao đổi phản hồi 2 chiều & đính kèm ảnh minh chứng.</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <a href="agent/index.php" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition text-center shadow-sm">
                        👨‍💻 Bàn Làm Việc KTV →
                    </a>
                    <a href="tickets/index.php" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition text-center shadow-sm">
                        🎫 Quản Lý Phiếu (Bài 5) →
                    </a>
                </div>
            </div>

            <!-- PHÂN HỆ 2: NGUYỄN ÁNH NHI -->
            <div class="bg-white border border-slate-200 hover:border-sky-400 rounded-2xl p-5 shadow-sm space-y-4 flex flex-col justify-between relative transition">
                <span class="absolute top-0 right-0 bg-sky-500 text-white text-[10px] font-black uppercase px-3 py-1 rounded-bl-xl tracking-wider">
                    Thành Viên 2
                </span>
                <div class="space-y-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center text-lg">
                            🎓
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-slate-900">Phân hệ: Student Portal & CSAT Ratings</h3>
                            <p class="text-xs text-sky-700 font-bold">Nguyễn Ánh Nhi (MSSV: 224001742)</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Phụ trách giao diện trải nghiệm sinh viên, Model độc quyền <code>Rating.php</code> (bảng <code>ticket_ratings</code>) và quy trình báo sự cố số tinh gọn.
                    </p>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-[11px] text-slate-600 space-y-1">
                        <div>• <strong>Báo sự cố tinh gọn:</strong> Form tạo phiếu không có ô Priority (tự động Auto-Priority theo dịch vụ), chọn đúng Môn học và Lớp học phần đang học.</div>
                        <div>• <strong>Nghiệm thu & Đánh giá CSAT:</strong> Sinh viên chấm điểm 1–5 sao và nhận xét chất lượng dịch vụ sau khi KTV đóng phiếu (ràng buộc 1-1).</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <a href="NguyenAnhNhi_SinhVien/index.php?action=tickets" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition text-center shadow-sm">
                        🎓 Cổng Sinh Viên Báo Lỗi →
                    </a>
                    <a href="NguyenAnhNhi_SinhVien/index.php?action=ratings" class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition text-center shadow-sm">
                        ⭐ Nghiệm Thu CSAT →
                    </a>
                </div>
            </div>

            <!-- PHÂN HỆ 3: LÊ HÀ MY -->
            <div class="bg-white border border-slate-200 hover:border-emerald-400 rounded-2xl p-5 shadow-sm space-y-4 flex flex-col justify-between relative transition">
                <span class="absolute top-0 right-0 bg-emerald-600 text-white text-[10px] font-black uppercase px-3 py-1 rounded-bl-xl tracking-wider">
                    Thành Viên 3
                </span>
                <div class="space-y-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-lg">
                            👨‍🏫
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-slate-900">Phân hệ: Academic & Lecturer Portal</h3>
                            <p class="text-xs text-emerald-700 font-bold">Lê Hà My (MSSV: 224001736)</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Phụ trách phân hệ Giảng viên đào tạo, Model độc quyền <code>CourseAssignment.php</code> (bảng <code>course_assignments</code>) liên kết Lớp học và Môn học.
                    </p>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-[11px] text-slate-600 space-y-1">
                        <div>• <strong>Nguyên tắc 1 Lớp HP = 1 GV:</strong> Ràng buộc toàn vẹn <code>UNIQUE (class_id, course_id, semester)</code> chống chồng chéo giảng viên trong cùng học kỳ.</div>
                        <div>• <strong>CRUD Phân công:</strong> Xem danh sách, tạo mới, chỉnh sửa và hủy phân công giảng dạy cho giảng viên.</div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <a href="LeHaMy_GiangVien/index.php" class="w-full block py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition text-center shadow-sm">
                        👨‍🏫 Cổng Quản Lý Phân Công Giảng Dạy →
                    </a>
                </div>
            </div>

            <!-- PHÂN HỆ 4: TRẦN THỤC LINH -->
            <div class="bg-white border border-slate-200 hover:border-purple-400 rounded-2xl p-5 shadow-sm space-y-4 flex flex-col justify-between relative transition">
                <span class="absolute top-0 right-0 bg-purple-600 text-white text-[10px] font-black uppercase px-3 py-1 rounded-bl-xl tracking-wider">
                    Thành Viên 4
                </span>
                <div class="space-y-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-lg">
                            👥
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-slate-900">Phân hệ: User Management & Auth Security</h3>
                            <p class="text-xs text-purple-700 font-bold">Trần Thục Linh (MSSV: 224001729)</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Phụ trách quản trị người dùng tập trung của Admin, Model độc quyền <code>UserRepository.php</code> (bảng <code>users</code> và <code>password_resets</code>).
                    </p>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-[11px] text-slate-600 space-y-1">
                        <div>• <strong>Quản trị vòng đời tài khoản:</strong> Đang học, Tốt nghiệp, Buộc thôi học (Expelled Hard Lock), Khóa mềm (Soft Lock sau 5 lần nhập sai).</div>
                        <div>• <strong>CRUD Người dùng:</strong> Tìm kiếm, phân trang an toàn, thêm mới, xem chi tiết và kích hoạt khóa tài khoản.</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <a href="TranLinh/users.php" class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition text-center shadow-sm">
                        👥 Quản Trị Người Dùng →
                    </a>
                    <a href="TranLinh/index.html" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition text-center shadow-sm">
                        📊 Sơ Đồ ERD & Auth →
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- KHỐI TÀI LIỆU KỸ THUẬT & CSDL (PHỤC VỤ GIẢNG VIÊN ĐÁNH GIÁ CHẤM ĐIỂM) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-4">
        <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide flex items-center gap-2">
            <span>📚</span> Hồ Sơ Kỹ Thuật, Tài Liệu Đặc Tả & CSDL Hệ Thống Nhóm 1:
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <!-- Tài liệu đặc tả PDF -->
            <a href="Thanhngan/Tai_Lieu_Dac_Ta_Helpdesk_DaiHocThuDo_Moi_Nhat.pdf" target="_blank" 
               class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition block space-y-1.5 group">
                <span class="text-xl block">📄</span>
                <strong class="text-slate-900 group-hover:text-indigo-600 transition block">Đặc Tả Thiết Kế (PDF)</strong>
                <span class="text-slate-400 text-[11px] block leading-tight">Bản chuẩn ĐH Thủ Đô: 12 bảng DDL, 20 route Clean MVC.</span>
            </a>

            <!-- Báo cáo cá nhân trưởng nhóm -->
            <a href="Thanhngan/Bao_Cao_Ca_Nhan_Truong_Nhom_Thanh_Ngan.pdf" target="_blank" 
               class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition block space-y-1.5 group">
                <span class="text-xl block">📋</span>
                <strong class="text-slate-900 group-hover:text-indigo-600 transition block">Báo Cáo Trưởng Nhóm</strong>
                <span class="text-slate-400 text-[11px] block leading-tight">Phạm Thị Thanh Ngân: Phân định lớp SH vs lớp HP, 4 câu truy vấn SQL.</span>
            </a>

            <!-- Cẩm nang 12 bảng & Quiz -->
            <a href="Thanhngan/cam_nang_toan_bo_csdl_helpdesk.html" 
               class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition block space-y-1.5 group">
                <span class="text-xl block">🎓</span>
                <strong class="text-slate-900 group-hover:text-indigo-600 transition block">Cẩm Nang 12 Bảng & Quiz</strong>
                <span class="text-slate-400 text-[11px] block leading-tight">Tra cứu chi tiết từng cột trong 12 bảng & 10 câu trắc nghiệm.</span>
            </a>

            <!-- File CSDL SQL -->
            <a href="database/seed.sql" target="_blank" 
               class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition block space-y-1.5 group">
                <span class="text-xl block">🗄️</span>
                <strong class="text-slate-900 group-hover:text-indigo-600 transition block">Mã Nguồn CSDL SQL</strong>
                <span class="text-slate-400 text-[11px] block leading-tight">Xem file schema.sql (12 bảng) và seed.sql đầy đủ dữ liệu mẫu.</span>
            </a>
        </div>
    </div>

    <!-- FOOTER BẢN QUYỀN NHÓM 1 -->
    <div class="text-center text-xs text-slate-400 pb-4 space-y-1">
        <p>BÀI TẬP LỚN LẬP TRÌNH WEB — HỌC KỲ I (NĂM HỌC 2026–2027) — TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI</p>
        <p>Đội ngũ Nhóm 1: Phạm Thị Thanh Ngân (Trưởng nhóm) • Trần Thục Linh • Lê Hà My • Nguyễn Ánh Nhi • Phan Công Thành</p>
    </div>

</div>

</body>
</html>
