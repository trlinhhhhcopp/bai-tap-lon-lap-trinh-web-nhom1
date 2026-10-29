<?php
/**
 * UNIFIED SYSTEM FOOTER
 * Tiêu chuẩn giao diện chuyên nghiệp Nhóm 1 - ĐH Thủ Đô Hà Nội
 * Chuẩn hóa thông tin phân hệ thành viên & CSDL đồng nhất helpdesk_thudo
 */
?>
<footer class="bg-slate-900 border-t border-slate-800 text-slate-300 py-8 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 text-xs">
            <!-- Cột 1: Tên hệ thống & Tóm tắt đề tài (5 cột) -->
            <div class="md:col-span-5 space-y-3">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-600 text-white font-black rounded-xl text-xs shadow-md">TUD</span>
                    <h3 class="font-bold text-white text-sm uppercase tracking-wider">HỆ THỐNG HELPDESK CNTT - ĐẠI HỌC THỦ ĐÔ HÀ NỘI</h3>
                </div>
                <p class="text-slate-400 text-xs leading-relaxed">
                    Bài tập lớn môn Lập trình Web - Học kỳ I. Kiến trúc phần mềm phân tầng Clean MVC kết hợp tiêu chuẩn dịch vụ ITIL v4: Tiếp nhận lỗi đa kênh, Auto-Priority Mapping, Bàn làm việc KTV điều phối tràn tải ≥ 15 phiếu, Quản lý phân công đào tạo & Vòng đời tài khoản học thuật.
                </p>
                <div class="pt-1 text-[11.5px] text-amber-400 font-semibold flex items-center gap-1.5">
                    <span>👑</span>
                    <span>Trưởng nhóm phụ trách Core & Tích hợp: <strong class="text-white">Phạm Thị Thanh Ngân</strong> (MSSV: 224001736 - Lớp D24-CNTT1)</span>
                </div>
            </div>

            <!-- Cột 2: Phân hệ nhóm 1 (4 cột) -->
            <div class="md:col-span-4 space-y-2">
                <h4 class="font-bold text-white uppercase tracking-wider text-xs flex items-center gap-1.5">
                    <span>👥</span> PHÂN HỆ NHÓM 1
                </h4>
                <ul class="space-y-1.5 text-slate-400 text-xs">
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span><strong class="text-slate-200">Phạm Thị Thanh Ngân:</strong> Core MVC & KTV Workspace</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span><strong class="text-slate-200">Phạm Thị Thanh Ngân:</strong> Quản Lý Phiếu (Bài 5) & Chat 2 chiều</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span><strong class="text-slate-200">Lê Hà My:</strong> Giảng Viên & Lớp Học Phần</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span><strong class="text-slate-200">Nguyễn Ánh Nhi:</strong> Sinh Viên & Đánh Giá CSAT 5★</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span><strong class="text-slate-200">Trần Thục Linh:</strong> Quản Trị User & Phân Ca Trực</span>
                    </li>
                </ul>
            </div>

            <!-- Cột 3: Thông tin kỹ thuật (3 cột) -->
            <div class="md:col-span-3 space-y-2">
                <h4 class="font-bold text-white uppercase tracking-wider text-xs flex items-center gap-1.5">
                    <span>⚙️</span> THÔNG TIN KỸ THUẬT
                </h4>
                <ul class="space-y-1.5 text-slate-400 text-xs">
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span>CSDL: MySQL / MariaDB (Chuẩn hóa 12 bảng 3NF)</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span>Ngôn ngữ: PHP 8.2 + PDO Prepared Statements</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span>Giao diện: Tailwind CSS Responsive</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-400">•</span>
                        <span>Ngưỡng điều phối tràn tải: <strong class="text-rose-400">≥ 15 phiếu / KTV</strong></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Dòng bản quyền cuối trang -->
        <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
            <div>
                © 2026 Nhóm 1 - Khoa Công Nghệ Thông Tin - Trường Đại Học Thủ Đô Hà Nội.
            </div>
            <div class="flex items-center gap-1">
                <span>Hệ thống hoạt động trên CSDL đồng nhất:</span>
                <span class="font-mono font-bold text-emerald-400">helpdesk_thudo</span>
            </div>
        </div>
    </div>
</footer>
