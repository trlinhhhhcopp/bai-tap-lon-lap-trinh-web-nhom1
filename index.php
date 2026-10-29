<?php
// ==============================================================================
// BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1 - ĐẠI HỌC THỦ ĐÔ HÀ NỘI
// CỔNG TRUNG TÂM DỰ ÁN: HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN
// Trưởng nhóm: Phạm Thị Thanh Ngân (224001736 - Lớp D24-CNTT1)
// ==============================================================================

require_once __DIR__ . '/config/database.php';

// Kiểm tra kết nối CSDL tự động
$dbStatus = 'OK';
$dbMessage = '';
$totalTickets = 0;
$totalUsers = 0;
$totalAgents = 0;

try {
    $pdo = db();
    $totalTickets = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
    $totalUsers   = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalAgents  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'agent'")->fetchColumn();
    $dbMessage = "Đã kết nối thành công tới database 'helpdesk_thudo' (MariaDB/MySQL)";
} catch (Exception $e) {
    $dbStatus = 'ERROR';
    $dbMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTL Lập Trình Web - Nhóm 1 - Helpdesk ĐH Thủ Đô</title>
    <script src="https://www.gstatic.com/antigravity/web/dev/tailwindcss.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen p-4 md:p-8">

<div class="max-w-6xl mx-auto space-y-6">

    <!-- HERO HEADER -->
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-indigo-800 text-white rounded-3xl p-6 md:p-8 shadow-lg shadow-indigo-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-sm">
                        ĐH Thủ Đô Hà Nội (TUD) • Khóa D24
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-400 text-emerald-950">
                        Hệ Thống Helpdesk CNTT Hoàn Chỉnh
                    </span>
                </div>
                <h1 class="text-3xl md:text-4xl font-black tracking-tight">Hệ Thống Helpdesk CNTT Trực Tuyến</h1>
                <p class="text-indigo-100 text-xs md:text-sm max-w-2xl leading-relaxed">
                    Đề tài Bài tập lớn môn Lập trình Web — Nhóm 1. Xây dựng phân hệ tiếp nhận sự cố, phân luồng chuyên môn KTV, tự động hóa hạn SLA và điều phối tràn tải thông minh.
                </p>
                <div class="pt-2 text-xs text-indigo-200 flex flex-wrap items-center gap-4">
                    <span>👑 Trưởng nhóm: <strong class="text-white">Phạm Thị Thanh Ngân</strong> (224001736)</span>
                    <span>🏫 Lớp: <strong class="text-white">D24-CNTT1</strong></span>
                    <span>👥 Nhóm 1: 5 Thành viên</span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 shrink-0">
                <div class="bg-white/10 border border-white/20 backdrop-blur-md rounded-2xl p-3.5 text-center">
                    <span class="text-2xl font-black block"><?php echo $totalTickets; ?></span>
                    <span class="text-[10.5px] text-indigo-200 uppercase tracking-wider block">Phiếu Sự Cố</span>
                </div>
                <div class="bg-white/10 border border-white/20 backdrop-blur-md rounded-2xl p-3.5 text-center">
                    <span class="text-2xl font-black block"><?php echo $totalAgents; ?> KTV</span>
                    <span class="text-[10.5px] text-indigo-200 uppercase tracking-wider block">Chuyên Trách</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TRẠNG THÁI HỆ THỐNG & CSDL -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full <?php echo $dbStatus === 'OK' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'; ?>"></span>
            <div>
                <strong class="text-slate-900">Trạng Thái Kết Nối CSDL (MySQL PDO):</strong>
                <span class="text-slate-500 ml-1"><?php echo htmlspecialchars($dbMessage); ?></span>
            </div>
        </div>
        <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono font-bold <?php echo $dbStatus === 'OK' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'; ?>">
            <?php echo $dbStatus === 'OK' ? '● PDO KẾT NỐI TỐT' : 'CHƯA KẾT NỐI'; ?>
        </span>
    </div>

    <!-- DANH MỤC CÁC PHÂN HỆ CHÍNH -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- THẺ 1: DEMO BÀI 5 (CRUD TICKETS) -->
        <div class="bg-white border-2 border-indigo-500/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4 relative overflow-hidden">
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase">
                Bài 5 PDO & CRUD
            </span>
            <div class="space-y-2.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl">
                    🎫
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Module Bài 5: CRUD Tickets</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Xây dựng trọn vẹn luồng CRUD cho thực thể chính <code>tickets</code> bằng PDO. Có tìm kiếm, phân trang an toàn (LIMIT/OFFSET), chat timeline và đính kèm ảnh 2 chiều.
                </p>
                <div class="text-[11px] text-slate-400 space-y-1">
                    <div>✓ Xem danh sách & Phân trang (Slide 11)</div>
                    <div>✓ Thêm phiếu mới POST-Redirect-GET (Slide 8)</div>
                    <div>✓ Sửa & Đổi trạng thái (Slide 7)</div>
                    <div>✓ Phản hồi 2 chiều & Đính kèm ảnh minh chứng</div>
                </div>
            </div>
            <a href="tickets/index.php" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl text-center transition shadow-sm">
                Vào Quản Lý Phiếu CRUD →
            </a>
        </div>

        <!-- THẺ 2: AGENT WORKSPACE (BÀN LÀM VIỆC KTV & TRÀN TẢI) -->
        <div class="bg-white border-2 border-emerald-500/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4 relative overflow-hidden">
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                Phân hệ Thanh Ngân
            </span>
            <div class="space-y-2.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-xl">
                    👨‍💻
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Bàn Làm Việc KTV & Tràn Tải</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Hàng đợi xử lý theo hạn SLA (<code>due_at ASC</code>), chuyển đổi 5 KTV chuyên trách, nghiệm thu giải pháp và kiểm thử thuật toán điều phối Tràn Tải Tự Động.
                </p>
                <div class="text-[11px] text-slate-400 space-y-1">
                    <div>✓ Hàng đợi SLA do Trưởng nhóm lập trình</div>
                    <div>✓ Giám sát tải 5 KTV (Xanh/Vàng/Đỏ)</div>
                    <div>✓ Thuật toán phân công & Tràn tải tự động</div>
                    <div>✓ Lập biên bản giải pháp kỹ thuật</div>
                </div>
            </div>
            <a href="agent/index.php" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl text-center transition shadow-sm">
                Vào Bàn Làm Việc KTV →
            </a>
        </div>

        <!-- THẺ 3: CỔNG GIẢNG VIÊN (PHÂN CÔNG MÔN - LÊ HÀ MY) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4 relative overflow-hidden">
            <span class="absolute top-3 right-3 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 uppercase">
                Phân hệ Lê Hà My
            </span>
            <div class="space-y-2.5">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl">
                    📚
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Cổng Giảng Viên & Học Phần</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Quản lý danh mục lớp sinh hoạt, môn học học kỳ I và phân công giảng dạy: Đảm bảo quy tắc nghiệp vụ <strong>1 lớp học phần chỉ có 1 giáo viên phụ trách</strong>.
                </p>
                <div class="text-[11px] text-slate-400 space-y-1">
                    <div>✓ Module của bạn Lê Hà My (224001735)</div>
                    <div>✓ Phân công 6 môn cho 6 giảng viên TUD</div>
                    <div>✓ Ràng buộc UNIQUE (class_id, course_id, semester)</div>
                </div>
            </div>
            <a href="LeHaMy_GiangVien/index.php" class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl text-center transition shadow-sm">
                Vào Cổng Giảng Viên →
            </a>
        </div>

        <!-- THẺ 4: CẨM NANG 12 BẢNG & TRẮC NGHIỆM -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div class="space-y-2.5">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center text-xl">
                    📖
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Cẩm Nang 12 Bảng & Quiz</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Giao diện tra cứu chi tiết toàn bộ 12 bảng trong CSDL <code>helpdesk_thudo</code>, giải thích từng thuộc tính và 10 câu hỏi kiểm tra nhanh có chấm điểm.
                </p>
                <div class="text-[11px] text-slate-400 space-y-1">
                    <div>✓ Tra cứu nhanh tên cột một chạm</div>
                    <div>✓ 4 câu lệnh SQL mẫu của Trưởng nhóm</div>
                    <div>✓ 10 câu trắc nghiệm thực tế</div>
                </div>
            </div>
            <a href="Thanhngan/cam_nang_toan_bo_csdl_helpdesk.html" class="w-full py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs rounded-xl text-center transition shadow-sm">
                Mở Cẩm Nang & Làm Quiz →
            </a>
        </div>

        <!-- THẺ 5: TÀI LIỆU ĐẶC TẢ PDF -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div class="space-y-2.5">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-xl">
                    📄
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Tài Liệu Đặc Tả Dự Án</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Hồ sơ đặc tả kỹ thuật hệ thống Helpdesk phiên bản mới nhất, phân công công việc 5 thành viên nhóm và mô tả chi tiết 20 route chuẩn Clean MVC.
                </p>
                <div class="text-[11px] text-slate-400 space-y-1">
                    <div>✓ Báo cáo cá nhân Trưởng nhóm</div>
                    <div>✓ Sơ đồ ERD thực thể 12 bảng</div>
                    <div>✓ Quy tắc bảo mật ITIL v4 Service Desk</div>
                </div>
            </div>
            <a href="Thanhngan/Tai_Lieu_Dac_Ta_Helpdesk_DaiHocThuDo_Moi_Nhat.pdf" target="_blank" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl text-center transition shadow-sm">
                Xem Tài Liệu PDF →
            </a>
        </div>

        <!-- THẺ 6: CỔNG SINH VIÊN (STUDENT PORTAL) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div class="space-y-2.5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl">
                    🎓
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Cổng Sinh Viên & Cứu Hộ</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Phân hệ dành cho sinh viên tạo phiếu hỗ trợ, đổi mật khẩu lần đầu + liên kết email cá nhân cứu hộ, và gửi form cứu hộ tài khoản bị khóa cứng (Guest Ticket).
                </p>
                <div class="text-[11px] text-slate-400 space-y-1">
                    <div>✓ Phân hệ của bạn Trần Thục Linh (224001729)</div>
                    <div>✓ Đổi mật khẩu lần đầu + Email khẩn cấp</div>
                    <div>✓ Cứu hộ tài khoản mở khóa theo mã PIN</div>
                </div>
            </div>
            <a href="tickets/create.php" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl text-center transition shadow-sm">
                Tạo Phiếu Hỗ Trợ Mới →
            </a>
        </div>

    </div>

</div>

</body>
</html>
