<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php
    $totalCount = count($myTickets);
    $activeCount = 0;
    $resolvedCount = 0;
    foreach ($myTickets as $tk) {
        if (in_array($tk['status'], ['new', 'in_progress', 'waiting_requester'])) {
            $activeCount++;
        } elseif (in_array($tk['status'], ['resolved', 'closed'])) {
            $resolvedCount++;
        }
    }
?>

<!-- BỐ CỤC DASHBOARD HIỆN ĐẠI 2 CỘT -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- CỘT TRÁI: HỒ SƠ SINH VIÊN (4 COLS) -->
    <div class="lg:col-span-4 space-y-6">
        <div class="bg-white border border-slate-200/80 rounded-3xl shadow-xl shadow-slate-200/40 overflow-hidden">
            <!-- Banner màu nền trường Thủ Đô -->
            <div class="h-28 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 relative">
                <div class="absolute -right-8 -top-8 w-28 h-28 bg-sky-500/20 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute top-3 right-3">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">
                        🎓 CỔNG SINH VIÊN
                    </span>
                </div>
            </div>

            <!-- Avatar & Tên Sinh Viên -->
            <div class="px-6 pb-6 pt-0 relative">
                <div class="-mt-14 mb-4 flex items-end justify-between">
                    <div class="relative group cursor-pointer" onclick="openAvatarModal()" title="Bấm vào để đổi ảnh đại diện">
                        <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 p-0.5 shadow-xl shadow-sky-500/20">
                            <div class="w-full h-full rounded-[14px] overflow-hidden bg-slate-100 flex items-center justify-center relative">
                                <?php if (!empty($user['avatar_path'])): ?>
                                    <img src="/tickets/attachment?file=<?= urlencode($user['avatar_path']) ?>" alt="Avatar" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-3xl font-black text-indigo-700">
                                        <?= mb_substr($user['name'], 0, 1) ?>
                                    </span>
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center text-white text-[11px] font-bold gap-0.5">
                                    <span>📷</span>
                                    <span>Đổi ảnh</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="absolute -bottom-1 -right-1 w-7 h-7 bg-white rounded-full border border-slate-200 shadow-md flex items-center justify-center text-xs text-sky-600 hover:scale-110 transition">
                            📷
                        </button>
                    </div>

                    <button type="button" onclick="openAvatarModal()" 
                            class="px-3 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <span>Thay Avatar</span>
                    </button>
                </div>

                <div class="space-y-1">
                    <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <?= htmlspecialchars($user['name']) ?>
                    </h2>
                    <p class="text-xs font-mono font-bold text-sky-600 bg-sky-50 inline-block px-2.5 py-0.5 rounded-lg border border-sky-100">
                        MSV: <?= htmlspecialchars($user['user_code']) ?>
                    </p>
                </div>

                <!-- Danh sách thông tin học vụ chi tiết -->
                <div class="mt-6 pt-4 border-t border-slate-100 space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5">
                            <span>🏛️</span> Lớp hành chính:
                        </span>
                        <span class="font-extrabold text-indigo-600">Toán Ứng Dụng D2024</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5">
                            <span>✉️</span> Email trường:
                        </span>
                        <span class="font-semibold text-slate-800 truncate max-w-[180px]" title="<?= htmlspecialchars($user['email']) ?>">
                            <?= htmlspecialchars($user['email']) ?>
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5">
                            <span>📚</span> Khoa đào tạo:
                        </span>
                        <span class="font-semibold text-slate-800">Toán - CNTT</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5">
                            <span>✅</span> Trạng thái học vụ:
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Đang học (D2024)
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5">
                            <span>🛡️</span> Vai trò hệ thống:
                        </span>
                        <span class="font-mono font-bold text-slate-700 uppercase bg-slate-100 px-2 py-0.5 rounded-md">
                            <?= strtoupper($user['role']) ?>
                        </span>
                    </div>
                </div>

                <div class="mt-5 p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl text-[11.5px] text-slate-500 leading-relaxed flex items-start gap-2">
                    <span class="text-sm">🔒</span>
                    <span>Cổng sinh viên được cách ly bảo mật dữ liệu tuyệt đối theo tiêu chuẩn ITIL v4. Bạn chỉ xem được hồ sơ và phiếu sự cố của chính mình.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: THỐNG KÊ, BỘ LỌC & DANH SÁCH PHIẾU (8 COLS) -->
    <div class="lg:col-span-8 space-y-6">

        <!-- 4 THẺ THỐNG KÊ NHANH (KPI CARDS) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <div class="bg-white border border-slate-200/80 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between text-xs text-slate-500 font-bold mb-1">
                    <span>Tổng Phiếu</span>
                    <span>🎫</span>
                </div>
                <div class="text-2xl font-black text-slate-900"><?= $totalCount ?></div>
                <div class="text-[11px] text-slate-400 mt-0.5">Đã gửi vào hệ thống</div>
            </div>

            <div class="bg-white border border-amber-200/80 p-4 rounded-2xl shadow-sm hover:shadow-md transition bg-gradient-to-br from-white to-amber-50/40">
                <div class="flex items-center justify-between text-xs text-amber-700 font-bold mb-1">
                    <span>Đang Xử Lý</span>
                    <span>⏳</span>
                </div>
                <div class="text-2xl font-black text-amber-600"><?= $activeCount ?></div>
                <div class="text-[11px] text-amber-600/80 mt-0.5">KTV đang tiếp nhận</div>
            </div>

            <div class="bg-white border border-emerald-200/80 p-4 rounded-2xl shadow-sm hover:shadow-md transition bg-gradient-to-br from-white to-emerald-50/40">
                <div class="flex items-center justify-between text-xs text-emerald-700 font-bold mb-1">
                    <span>Hoàn Thành</span>
                    <span>✓</span>
                </div>
                <div class="text-2xl font-black text-emerald-600"><?= $resolvedCount ?></div>
                <div class="text-[11px] text-emerald-600/80 mt-0.5">Đã giải quyết xong</div>
            </div>

            <div class="bg-white border border-sky-200/80 p-4 rounded-2xl shadow-sm hover:shadow-md transition bg-gradient-to-br from-white to-sky-50/40">
                <div class="flex items-center justify-between text-xs text-sky-700 font-bold mb-1">
                    <span>Chuẩn SLA</span>
                    <span>⚡</span>
                </div>
                <div class="text-2xl font-black text-sky-600">&le; 4h</div>
                <div class="text-[11px] text-sky-600/80 mt-0.5">Thời gian xử lý TB</div>
            </div>
        </div>

        <!-- KHUNG CHÍNH: QUẢN LÝ PHIẾU HỖ TRỢ -->
        <div class="bg-white border border-slate-200/80 rounded-3xl shadow-xl shadow-slate-200/40 p-6 space-y-6">
            
            <!-- Tiêu đề & Nút Báo Sự Cố -->
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>📋</span> Danh Sách Phiếu Báo Sự Cố Của Bạn
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Theo dõi tiến độ giải quyết trực tiếp từ 5 Kỹ thuật viên phụ trách
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="student/index.php?action=courses" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold text-xs rounded-xl border border-indigo-200 transition flex items-center gap-1.5 shadow-sm">
                        <span>📚</span> Học Phần & Bài Tập
                    </a>
                    <button id="btnOpenTicketForm" type="button" onclick="openTicketForm()" 
                        class="px-4 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-sky-500/20 transition flex items-center gap-1.5 transform hover:-translate-y-0.5 active:translate-y-0">
                    <span>➕</span> Báo Sự Cố Mới
                </button></div>
            </div>

            <!-- FORM BÁO SỰ CỐ MỚI (TỰ ĐỘNG MỞ KHI BẤM NÚT) -->
            <div id="ticketFormBox" style="display: none;" class="bg-slate-50/90 border border-slate-200 rounded-2xl p-5 md:p-6 space-y-4 shadow-sm animate-fade-in">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center font-bold text-sm">🚀</span>
                        <div>
                            <h4 class="font-black text-slate-900 text-sm">Gửi Yêu Cầu Hỗ Trợ Kỹ Thuật CNTT</h4>
                            <p class="text-[11px] text-slate-500">Đơn sẽ chuyển đến KTV chuyên trách theo danh mục</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeTicketForm()" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition">
                        ✕ Đóng lại
                    </button>
                </div>

                <form method="POST" action="/student/tickets/create" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                1. Chọn Nhóm dịch vụ sự cố (*):
                            </label>
                            <select name="category_id" id="categorySelect" onchange="onCategoryChange()" 
                                    class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == 4 ? 'selected' : '' ?> data-code="<?= $cat['code'] ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                2. Hệ thống bị ảnh hưởng:
                            </label>
                            <input type="text" name="system_affected" id="systemAffectedInput" readonly required 
                                   class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            3. Chọn nhanh hiện tượng lỗi mẫu (Hoặc tự nhập):
                        </label>
                        <select id="issuePresetSelect" onchange="onIssuePresetChange()" 
                                class="w-full px-3.5 py-2.5 bg-sky-50 border border-sky-300 rounded-xl text-xs font-bold text-sky-900 focus:outline-none focus:ring-4 focus:ring-sky-500/20">
                            <!-- Options tự động nạp theo Category -->
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            4. Tiêu đề sự cố ngắn gọn (*):
                        </label>
                        <input type="text" name="title" id="ticketTitleInput" required 
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition"
                               placeholder="VD: Không nộp được bài tập lớn môn Lập trình Web">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            5. Mô tả chi tiết hiện tượng lỗi (*):
                        </label>
                        <textarea name="description" id="ticketDescriptionInput" rows="3" required 
                                  class="w-full p-3 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition leading-relaxed"
                                  placeholder="Mô tả cụ thể thông báo lỗi, tên môn học, lớp học phần và thời điểm gặp lỗi..."></textarea>
                    </div>

                    <!-- ĐÍNH KÈM MINH CHỨNG (TẢI TỆP HOẶC CHỤP ẢNH WEBCAM) -->
                    <div class="p-4 bg-white border border-dashed border-slate-300 rounded-2xl space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700">
                                6. Đính kèm minh chứng lỗi (Ảnh chụp màn hình lỗi / Ảnh minh chứng):
                            </label>
                            <span class="text-[11px] text-slate-400">PNG, JPG, JPEG, WEBP, PDF</span>
                        </div>

                        <div class="flex gap-2 flex-wrap">
                            <label class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl text-xs font-bold cursor-pointer transition">
                                <span>📁</span> Tải tệp từ máy
                                <input type="file" name="attachment" id="fileAttachmentInput" accept="image/*,.pdf" class="hidden" onchange="onFileSelected(this)">
                            </label>

                            <button type="button" onclick="startCamera()" 
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition">
                                <span>📸</span> Bật Camera chụp ảnh lỗi
                            </button>
                        </div>

                        <!-- Khung Video Camera Live Stream -->
                        <div id="cameraBox" style="display: none;" class="mt-3 p-3 bg-slate-900 rounded-xl text-center space-y-2.5">
                            <video id="webcamVideo" autoplay playsinline class="w-full max-w-md mx-auto max-h-56 rounded-lg bg-black"></video>
                            <div class="flex justify-center gap-2">
                                <button type="button" onclick="captureSnapshot()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition">
                                    📸 Chụp Ảnh Ngay
                                </button>
                                <button type="button" onclick="stopCamera()" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl text-xs transition">
                                    ✕ Tắt Camera
                                </button>
                            </div>
                            <canvas id="snapshotCanvas" class="hidden"></canvas>
                        </div>

                        <!-- Khung xem trước minh chứng -->
                        <div id="previewBox" style="display: none;" class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-emerald-700" id="previewStatusText">✓ Đã đính kèm ảnh:</span>
                                <button type="button" onclick="clearAttachment()" class="text-rose-600 hover:text-rose-700 font-bold hover:underline">
                                    🗑️ Xóa / Chọn lại
                                </button>
                            </div>
                            <div class="text-center py-1">
                                <img id="previewImage" src="" alt="Ảnh minh chứng" class="max-h-48 mx-auto rounded-lg shadow-sm border border-slate-200">
                            </div>
                        </div>

                        <input type="hidden" name="captured_image" id="capturedImageInput" value="">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" onclick="closeTicketForm()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-sky-500/20 transition flex items-center gap-1.5">
                            <span>Gửi Phiếu Báo Sự Cố</span>
                            <span>➔</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- THANH TÌM KIẾM & BỘ LỌC ĐA TIÊU CHÍ -->
            <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <!-- Từ khóa -->
                    <div class="sm:col-span-5">
                        <label for="filterKeyword" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                            🔍 Tìm kiếm mã phiếu / Tiêu đề:
                        </label>
                        <input type="text" id="filterKeyword" oninput="applyTicketFilter()" 
                               placeholder="VD: HD-2026... hoặc Canvas, Mạng..."
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition">
                    </div>

                    <!-- Ngày gửi -->
                    <div class="sm:col-span-3">
                        <label for="filterDate" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                            📅 Ngày gửi:
                        </label>
                        <input type="date" id="filterDate" onchange="applyTicketFilter()" 
                               class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition">
                    </div>

                    <!-- Trạng thái -->
                    <div class="sm:col-span-3">
                        <label for="filterStatus" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                            🏷️ Trạng thái:
                        </label>
                        <select id="filterStatus" onchange="applyTicketFilter()" 
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:ring-4 focus:ring-sky-500/20 focus:border-sky-500 transition">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="new">● Mới tạo (Chờ KTV)</option>
                            <option value="in_progress">⏳ Đang xử lý</option>
                            <option value="resolved">✓ Đã giải quyết</option>
                            <option value="cancelled">✕ Đã hủy</option>
                        </select>
                    </div>

                    <!-- Nút reset -->
                    <div class="sm:col-span-1">
                        <button type="button" onclick="resetTicketFilter()" 
                                class="w-full py-2 bg-white hover:bg-slate-100 border border-slate-300 text-slate-600 rounded-xl text-xs font-bold transition flex items-center justify-center shadow-sm"
                                title="Đặt lại bộ lọc về mặc định">
                            🔄
                        </button>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px] text-slate-500">
                    <div>
                        Hiển thị: <strong id="filterResultCount" class="text-sky-600 font-extrabold"><?= $totalCount ?> / <?= $totalCount ?> phiếu</strong>
                    </div>
                    <div id="activeFilterTags" class="font-semibold text-sky-700"></div>
                </div>
            </div>

            <!-- BẢNG DANH SÁCH PHIẾU HIỆN ĐẠI -->
            <?php if (empty($myTickets)): ?>
                <div class="text-center py-12 px-4 bg-slate-50 border border-dashed border-slate-200 rounded-2xl space-y-2">
                    <span class="text-3xl">📭</span>
                    <h4 class="font-bold text-slate-700 text-sm">Chưa có phiếu hỗ trợ nào</h4>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">
                        Khi bạn gặp sự cố về tài khoản, nộp bài Canvas, đăng ký tín chỉ hoặc mạng Wi-Fi, hãy bấm nút "➕ Báo sự cố mới" ở trên nhé!
                    </p>
                </div>
            <?php else: ?>
                <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs" id="studentTicketTable">
                            <thead class="bg-slate-100/80 text-slate-600 uppercase text-[10.5px] font-black tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Mã Phiếu</th>
                                    <th class="px-3 py-3">Dịch Vụ</th>
                                    <th class="px-3 py-3">Tiêu Đề</th>
                                    <th class="px-3 py-3">Minh Chứng</th>
                                    <th class="px-3 py-3">KTV Tiếp Nhận</th>
                                    <th class="px-3 py-3">Trạng Thái</th>
                                    <th class="px-3 py-3">Thời Gian</th>
                                    <th class="px-4 py-3 text-center">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="studentTicketTbody">
                                <?php foreach ($myTickets as $t): ?>
                                    <?php
                                        $createdTime = strtotime($t['created_at']);
                                        $elapsedSec = time() - $createdTime;
                                        $canCancel = ($t['status'] === 'new' && $elapsedSec <= 3600);
                                        $remMinutes = max(0, ceil((3600 - $elapsedSec) / 60));
                                        $ticketJson = htmlspecialchars(json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                        $statusFilterValue = ($t['status'] === 'closed' && ($t['closed_reason'] ?? '') === 'cancelled') ? 'cancelled' : $t['status'];
                                        $dateFilterValue = substr($t['created_at'], 0, 10);
                                    ?>
                                    <tr class="ticket-row hover:bg-sky-50/40 transition group"
                                        data-code="<?= strtolower(htmlspecialchars($t['ticket_code'])) ?>" 
                                        data-title="<?= strtolower(htmlspecialchars($t['title'])) ?>" 
                                        data-date="<?= $dateFilterValue ?>" 
                                        data-status="<?= $statusFilterValue ?>">
                                        
                                        <!-- Mã phiếu Monospace bảo mật xxxBxxd -->
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="javascript:void(0)" onclick="viewTicketDetail(<?= $ticketJson ?>)" 
                                               class="font-mono font-bold text-sky-700 hover:text-sky-900 hover:underline flex items-center gap-1">
                                                <span>🎫</span> <?= htmlspecialchars($t['ticket_code']) ?>
                                            </a>
                                        </td>

                                        <td class="px-3 py-3 whitespace-nowrap font-medium text-slate-700">
                                            <?= htmlspecialchars($t['category_name']) ?>
                                        </td>

                                        <td class="px-3 py-3 font-semibold text-slate-800 max-w-[200px] truncate" title="<?= htmlspecialchars($t['title']) ?>">
                                            <?= htmlspecialchars($t['title']) ?>
                                        </td>

                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <?php if (!empty($t['attachment_path'])): ?>
                                                <a href="/tickets/attachment?file=<?= urlencode($t['attachment_path']) ?>" target="_blank" 
                                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-[11px] font-bold transition">
                                                    📎 Xem file
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-[11px]">Không có</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-3 py-3 whitespace-nowrap font-medium text-slate-600">
                                            <?= htmlspecialchars($t['agent_name']) ?>
                                        </td>

                                        <!-- Badge trạng thái mềm mại -->
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <?php if ($t['status'] === 'resolved'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Đã giải quyết
                                                </span>
                                            <?php elseif ($t['status'] === 'in_progress'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    Đang xử lý
                                                </span>
                                            <?php elseif ($t['status'] === 'closed' && ($t['closed_reason'] ?? '') === 'cancelled'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Đã hủy
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-sky-50 text-sky-700 border border-sky-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                                                    Chờ tiếp nhận
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-3 py-3 whitespace-nowrap text-slate-500 text-[11px]">
                                            <?= htmlspecialchars(substr($t['created_at'], 0, 16)) ?>
                                        </td>

                                        <!-- Thao tác xem & Hủy phiếu 1h -->
                                        <td class="px-4 py-3 whitespace-nowrap text-center space-x-1">
                                            <button type="button" onclick="viewTicketDetail(<?= $ticketJson ?>)" 
                                                    class="px-2.5 py-1 bg-white hover:bg-sky-50 text-sky-700 border border-slate-200 hover:border-sky-300 rounded-lg font-bold text-[11px] transition shadow-sm">
                                                🔍 Chi tiết
                                            </button>

                                            <?php if ($canCancel): ?>
                                                <form method="POST" action="/student/tickets/cancel" class="inline-block" 
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn hủy phiếu <?= htmlspecialchars($t['ticket_code']) ?> không? (Sau khi hủy, KTV sẽ không tiếp nhận xử lý phiếu này nữa)');">
                                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                                    <button type="submit" 
                                                            class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-bold text-[11px] transition shadow-sm" 
                                                            title="Bạn còn <?= $remMinutes ?> phút để hủy phiếu nếu KTV chưa tiếp nhận">
                                                        🚫 Hủy (<?= $remMinutes ?>p)
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr id="filterEmptyRow" style="display: none;">
                                    <td colspan="8" class="text-center py-10 px-4 text-slate-400 italic bg-slate-50">
                                        🔍 Không tìm thấy phiếu hỗ trợ nào phù hợp với bộ lọc tìm kiếm.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT LOGIC CHO TOÀN BỘ CỔNG SINH VIÊN -->
<!-- ========================================================================= -->
<script>
// Danh mục hệ thống & Các sự cố chuẩn hóa theo Tài liệu Đặc tả đề tài 9
const standardSpecs = {
    1: { // CAT_SSO - KTV1 Nguyễn Văn An
        system: "Cổng Xác Thực SSO & Tài Khoản Trường",
        issues: [
            {
                title: "Quên mật khẩu / Yêu cầu cấp lại mật khẩu tài khoản sinh viên",
                desc: "Em quên mật khẩu đăng nhập portal trường. Em đã kiểm tra email trường nhưng chưa nhận được link đổi mật khẩu. Kính nhờ thầy cô và KTV cấp lại mật khẩu giúp em."
            },
            {
                title: "Tài khoản bị khóa mềm tạm thời sau 5 lần nhập sai",
                desc: "Em gõ nhầm mật khẩu cũ quá 5 lần liên tiếp khiến tài khoản bị khóa mềm 30 phút. Em cần vào nộp bài gấp, kính nhờ KTV mở khóa tài khoản sớm giúp em."
            },
            {
                title: "Lỗi đồng bộ thông tin sinh viên giữa Cổng Đào Tạo và Canvas",
                desc: "Thông tin cá nhân của em trên hệ thống Canvas LMS bị sai lệch so với dữ liệu Cổng Quản lý Đào tạo. Em nhờ KTV đồng bộ lại tài khoản giúp em."
            }
        ]
    },
    2: { // CAT_MAIL - KTV2 Trần Thị Bích
        system: "Email Sinh Viên & Microsoft 365 Edu",
        issues: [
            {
                title: "Kích hoạt tài khoản bản quyền Microsoft 365 A3 Edu",
                desc: "Em là sinh viên lớp Toán Ứng Dụng D2024, khi đăng nhập vào portal.office.com bằng email trường cấp thì báo tài khoản chưa được gán giấy phép Office 365 A3. Nhờ KTV kích hoạt giúp em."
            },
            {
                title: "Hòm thư sinh viên @daihocthudo.edu.vn bị khóa gửi nhận",
                desc: "Hòm thư sinh viên của em không thể gửi hoặc nhận email từ giảng viên. Nhờ KTV kiểm tra xem hòm thư có bị đánh dấu spam nhầm không."
            },
            {
                title: "Yêu cầu mở rộng dung lượng lưu trữ OneDrive 1TB",
                desc: "OneDrive của em báo sắp hết dung lượng lưu trữ đồ án và bài tập. Kính nhờ KTV nâng cấp gói lưu trữ sinh viên 1TB theo tiêu chuẩn trường."
            }
        ]
    },
    3: { // CAT_QLDT - KTV3 Lê Văn Cường
        system: "Cổng Quản Lý Đào Tạo (qldt.daihocthudo.edu.vn)",
        issues: [
            {
                title: "Nghẽn mạng / Báo lỗi khi thao tác đăng ký tín chỉ học phần",
                desc: "Em đang đăng ký tín chỉ học phần thì hệ thống báo lỗi quá tải kết nối cơ sở dữ liệu. Nhờ KTV kiểm tra phiên làm việc tài khoản giúp em."
            },
            {
                title: "Không tra cứu được lịch thi / phòng thi học kỳ hiện tại",
                desc: "Trên cổng QLĐT của em không hiển thị lịch thi và số báo danh các môn học trong kỳ. Kính nhờ KTV kiểm tra dữ liệu phân phòng thi."
            },
            {
                title: "Lỗi sai sót / không hiển thị điểm tổng kết học phần",
                desc: "Điểm thi kết thúc môn của em đã có bảng điểm giấy của Khoa nhưng cổng QLĐT chưa cập nhật điểm hệ 4 và hệ 10."
            }
        ]
    },
    4: { // CAT_CANVAS - KTV4 Phạm Hoàng Dũng
        system: "Hệ Thống Học Tập Canvas LMS & Teams",
        issues: [
            {
                title: "Không thấy lớp học phần trên trang chủ Canvas LMS",
                desc: "Em đã đăng ký học phần Lập trình Web thành công trên cổng QLĐT nhưng trên Dashboard Canvas LMS vẫn chưa thấy hiển thị lớp học phần này."
            },
            {
                title: "Sự cố không nộp được bài tập lớn môn Lập trình Web",
                desc: "Khi em nộp file zip bài tập lớn lên Canvas LMS thì hệ thống báo lỗi quá thời gian tải lên (Upload Timeout). Mong thầy cô và KTV hỗ trợ mở lại cổng nộp bài."
            },
            {
                title: "Hạn chốt nộp bài / Lịch học bị trùng ngày nghỉ lễ toàn trường",
                desc: "Lịch nộp bài tập lớn rơi đúng vào ngày nghỉ lễ theo lịch công tác toàn trường. Em kiến nghị dời lịch nộp bài sang buổi học kế tiếp."
            },
            {
                title: "Lỗi không vào được phòng học trực tuyến Microsoft Teams",
                desc: "Em bấm vào liên kết phòng học Teams của lớp học phần nhưng hệ thống báo tài khoản không thuộc tổ chức cuộc họp."
            }
        ]
    },
    5: { // CAT_WIFI - KTV5 Vũ Thị Én
        system: "Mạng Wi-Fi Trường (THUDO-UNI) & VPN",
        issues: [
            {
                title: "Lỗi không xác thực được mạng Wi-Fi THUDO-UNI",
                desc: "Máy tính của em kết nối Wi-Fi THUDO-UNI nhưng báo lỗi 'Can not connect to this network' khi nhập tài khoản mật khẩu sinh viên."
            },
            {
                title: "Bị từ chối kết nối / Tràn cấp phát địa chỉ IP tại Ký túc xá",
                desc: "Tại phòng Ký túc xá, thiết bị liên tục báo 'Obtaining IP address' và không thể lấy được địa chỉ IP mạng trường."
            },
            {
                title: "Yêu cầu cấp file cấu hình OpenVPN truy cập Thư viện số từ xa",
                desc: "Em cần tra cứu tài liệu học tập tại Thư viện số của trường từ nhà. Kính nhờ KTV cấp tài khoản và file cấu hình VPN giúp em."
            }
        ]
    }
};

function openTicketForm() {
    document.getElementById('ticketFormBox').style.display = 'block';
    document.getElementById('btnOpenTicketForm').style.display = 'none';
    onCategoryChange();
}

function closeTicketForm() {
    document.getElementById('ticketFormBox').style.display = 'none';
    document.getElementById('btnOpenTicketForm').style.display = 'inline-flex';
    stopCamera();
}

function onCategoryChange() {
    const catSelect = document.getElementById('categorySelect');
    const catId = parseInt(catSelect.value);
    const spec = standardSpecs[catId] || standardSpecs[1];

    document.getElementById('systemAffectedInput').value = spec.system;

    const presetSelect = document.getElementById('issuePresetSelect');
    presetSelect.innerHTML = '<option value="">-- Bấm để chọn sự cố mẫu chuẩn... --</option>';

    spec.issues.forEach((item, index) => {
        const opt = document.createElement('option');
        opt.value = index;
        opt.textContent = `• ${item.title}`;
        presetSelect.appendChild(opt);
    });

    const optOther = document.createElement('option');
    optOther.value = 'other';
    optOther.textContent = '✏️ Sự cố khác (Tự nhập tiêu đề & mô tả riêng)...';
    presetSelect.appendChild(optOther);
}

function onIssuePresetChange() {
    const catId = parseInt(document.getElementById('categorySelect').value);
    const presetVal = document.getElementById('issuePresetSelect').value;
    const spec = standardSpecs[catId] || standardSpecs[1];

    if (presetVal === '') return;

    if (presetVal === 'other') {
        document.getElementById('ticketTitleInput').value = '';
        document.getElementById('ticketDescriptionInput').value = '';
        document.getElementById('ticketTitleInput').focus();
        return;
    }

    const item = spec.issues[parseInt(presetVal)];
    if (item) {
        document.getElementById('ticketTitleInput').value = item.title;
        document.getElementById('ticketDescriptionInput').value = item.desc;
    }
}

// Xử lý đính kèm minh chứng lỗi (File hoặc Camera)
let mediaStream = null;

function startCamera() {
    const cameraBox = document.getElementById('cameraBox');
    const video = document.getElementById('webcamVideo');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Trình duyệt hoặc thiết bị của bạn không hỗ trợ camera trực tiếp. Vui lòng chọn 'Tải tệp từ thiết bị'!");
        return;
    }

    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then(stream => {
            mediaStream = stream;
            video.srcObject = stream;
            cameraBox.style.display = 'block';
        })
        .catch(err => {
            alert("Không thể truy cập Camera. Vui lòng cấp quyền Camera trên trình duyệt!");
        });
}

function stopCamera() {
    if (mediaStream) {
        mediaStream.getTracks().forEach(track => track.stop());
        mediaStream = null;
    }
    const cameraBox = document.getElementById('cameraBox');
    if (cameraBox) cameraBox.style.display = 'none';
}

function captureSnapshot() {
    const video = document.getElementById('webcamVideo');
    const canvas = document.getElementById('snapshotCanvas');
    if (!video || !video.videoWidth) {
        alert("Camera chưa sẵn sàng, vui lòng đợi 1 giây!");
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

    document.getElementById('capturedImageInput').value = dataUrl;
    const fileInput = document.getElementById('fileAttachmentInput');
    if (fileInput) fileInput.value = '';

    document.getElementById('previewImage').src = dataUrl;
    document.getElementById('previewStatusText').textContent = '📸 Đã chụp ảnh thành công từ Camera:';
    document.getElementById('previewBox').style.display = 'block';

    stopCamera();
}

function onFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('capturedImageInput').value = '';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('previewStatusText').textContent = `📁 Đã chọn ảnh: ${file.name} (${Math.round(file.size / 1024)} KB)`;
                document.getElementById('previewBox').style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('previewImage').src = '';
            document.getElementById('previewStatusText').textContent = `📄 Đã chọn tệp: ${file.name} (${Math.round(file.size / 1024)} KB)`;
            document.getElementById('previewBox').style.display = 'block';
        }
    }
}

function clearAttachment() {
    const fileInput = document.getElementById('fileAttachmentInput');
    if (fileInput) fileInput.value = '';
    const capInput = document.getElementById('capturedImageInput');
    if (capInput) capInput.value = '';
    const previewBox = document.getElementById('previewBox');
    if (previewBox) previewBox.style.display = 'none';
    const previewImg = document.getElementById('previewImage');
    if (previewImg) previewImg.src = '';
    stopCamera();
}

// Xử lý Cập nhật Ảnh đại diện (Avatar)
let avatarMediaStream = null;

function openAvatarModal() {
    document.getElementById('avatarModal').style.display = 'flex';
}

function closeAvatarModal() {
    stopAvatarCamera();
    document.getElementById('avatarModal').style.display = 'none';
}

function onAvatarFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('capturedAvatarInput').value = '';
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreviewImg');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('avatarPreviewFallback').style.display = 'none';
            document.getElementById('avatarStatusText').textContent = `📁 Đã chọn file: ${file.name} (${Math.round(file.size / 1024)} KB)`;
        };
        reader.readAsDataURL(file);
    }
}

function startAvatarCamera() {
    const box = document.getElementById('avatarCameraBox');
    const video = document.getElementById('avatarWebcamVideo');
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Thiết bị hoặc trình duyệt không hỗ trợ Camera trực tiếp!");
        return;
    }
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
        .then(stream => {
            avatarMediaStream = stream;
            video.srcObject = stream;
            box.style.display = 'block';
        })
        .catch(err => {
            alert("Không thể bật Camera. Vui lòng cấp quyền Camera trên trình duyệt!");
        });
}

function stopAvatarCamera() {
    if (avatarMediaStream) {
        avatarMediaStream.getTracks().forEach(t => t.stop());
        avatarMediaStream = null;
    }
    const box = document.getElementById('avatarCameraBox');
    if (box) box.style.display = 'none';
}

function captureAvatarSnapshot() {
    const video = document.getElementById('avatarWebcamVideo');
    const canvas = document.getElementById('avatarSnapshotCanvas');
    if (!video || !video.videoWidth) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

    document.getElementById('capturedAvatarInput').value = dataUrl;
    const fileInput = document.getElementById('avatarFileInput');
    if (fileInput) fileInput.value = '';

    const preview = document.getElementById('avatarPreviewImg');
    preview.src = dataUrl;
    preview.style.display = 'block';
    document.getElementById('avatarPreviewFallback').style.display = 'none';
    document.getElementById('avatarStatusText').textContent = '📸 Đã chụp ảnh đại diện từ Webcam thành công!';

    stopAvatarCamera();
}

// Xử lý Modal Xem Chi Tiết Phiếu Hỗ Trợ
function viewTicketDetail(ticket) {
    document.getElementById('dtlCode').textContent = ticket.ticket_code;
    
    const badgeBox = document.getElementById('dtlStatusBadge');
    if (ticket.status === 'resolved') {
        badgeBox.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✓ Đã giải quyết</span>';
    } else if (ticket.status === 'in_progress') {
        badgeBox.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">⏳ Đang xử lý</span>';
    } else if (ticket.status === 'closed' && ticket.closed_reason === 'cancelled') {
        badgeBox.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">✕ Đã hủy bởi sinh viên</span>';
    } else {
        badgeBox.innerHTML = '<span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">● Mới tạo (Chờ KTV)</span>';
    }

    document.getElementById('dtlCategory').textContent = ticket.category_name || 'Hỗ trợ kỹ thuật';
    document.getElementById('dtlSystem').textContent = ticket.system_affected || 'Hệ thống chung';
    document.getElementById('dtlAgent').textContent = ticket.agent_name || 'Đang chờ phân công';
    document.getElementById('dtlCreatedAt').textContent = ticket.created_at;
    document.getElementById('dtlTitle').textContent = ticket.title;
    document.getElementById('dtlDesc').textContent = ticket.description;

    const attachBox = document.getElementById('dtlAttachmentBox');
    if (ticket.attachment_path) {
        const fileUrl = '<?= $appBase ?>' + ticket.attachment_path;
        const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(ticket.attachment_path);
        
        let html = '';
        if (isImg) {
            html += `<div class="mb-2 text-center">
                        <img src="${fileUrl}" alt="Minh chứng lỗi" class="max-h-56 mx-auto rounded-xl border border-slate-200 shadow-sm object-contain">
                     </div>`;
        }
        html += `<a href="${fileUrl}" target="_blank" download class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl text-xs font-bold transition">
                    📥 Mở / Tải về tệp minh chứng gốc
                 </a>`;
        attachBox.innerHTML = html;
    } else {
        attachBox.innerHTML = '<span class="text-slate-400 italic text-xs">(Phiếu này không đính kèm tệp hay ảnh chụp màn hình lỗi)</span>';
    }

    // Kiểm tra quyền hủy phiếu trong 1 giờ khi trạng thái còn là 'new'
    const cancelBox = document.getElementById('dtlCancelBox');
    const createdDate = new Date(ticket.created_at.replace(/-/g, '/'));
    const elapsedSec = Math.floor((Date.now() - createdDate.getTime()) / 1000);
    const canCancel = (ticket.status === 'new' && elapsedSec <= 3600);

    if (canCancel) {
        cancelBox.style.display = 'block';
        document.getElementById('dtlCancelTicketId').value = ticket.id;
        const remMin = Math.max(1, Math.ceil((3600 - elapsedSec) / 60));
        document.getElementById('dtlCancelBtn').textContent = `🚫 Hủy phiếu này (Còn ${remMin} phút)`;
    } else {
        cancelBox.style.display = 'none';
    }

    document.getElementById('modalTicketDetail').style.display = 'flex';
}

function closeTicketDetailModal() {
    document.getElementById('modalTicketDetail').style.display = 'none';
}

// Xử lý Bộ lọc & Tìm kiếm Danh sách Phiếu
function applyTicketFilter() {
    const keywordInput = document.getElementById('filterKeyword');
    const dateInput = document.getElementById('filterDate');
    const statusSelect = document.getElementById('filterStatus');

    if (!keywordInput || !dateInput || !statusSelect) return;

    const keyword = keywordInput.value.trim().toLowerCase();
    const selectedDate = dateInput.value;
    const selectedStatus = statusSelect.value;

    const rows = document.querySelectorAll('.ticket-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const code = row.getAttribute('data-code') || '';
        const title = row.getAttribute('data-title') || '';
        const rowDate = row.getAttribute('data-date') || '';
        const rowStatus = row.getAttribute('data-status') || '';

        const matchKeyword = !keyword || code.includes(keyword) || title.includes(keyword);
        const matchDate = !selectedDate || rowDate === selectedDate;
        const matchStatus = !selectedStatus || rowStatus === selectedStatus;

        if (matchKeyword && matchDate && matchStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countBadge = document.getElementById('filterResultCount');
    if (countBadge) {
        countBadge.textContent = `${visibleCount} / ${rows.length} phiếu`;
    }

    const emptyRow = document.getElementById('filterEmptyRow');
    if (emptyRow) {
        emptyRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }

    const tags = [];
    if (keyword) tags.push(`Từ khóa: "${keyword}"`);
    if (selectedDate) {
        const parts = selectedDate.split('-');
        const dateFormatted = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : selectedDate;
        tags.push(`Ngày: ${dateFormatted}`);
    }
    if (selectedStatus) {
        const statusMap = {
            new: 'Chờ tiếp nhận',
            in_progress: 'Đang xử lý',
            resolved: 'Đã giải quyết',
            cancelled: 'Đã hủy'
        };
        tags.push(`Trạng thái: ${statusMap[selectedStatus] || selectedStatus}`);
    }
    const tagsBox = document.getElementById('activeFilterTags');
    if (tagsBox) {
        tagsBox.textContent = tags.length > 0 ? `Đang lọc: ${tags.join(' • ')}` : '';
    }
}

function resetTicketFilter() {
    const keywordInput = document.getElementById('filterKeyword');
    const dateInput = document.getElementById('filterDate');
    const statusSelect = document.getElementById('filterStatus');

    if (keywordInput) keywordInput.value = '';
    if (dateInput) dateInput.value = '';
    if (statusSelect) statusSelect.value = '';

    applyTicketFilter();
}
</script>

<!-- MODAL XEM CHI TIẾT PHIẾU HỖ TRỢ -->
<div id="modalTicketDetail" style="display: none;" 
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 items-center justify-center p-4 transition-all" 
     onclick="if(event.target === this) closeTicketDetailModal()">
    <div class="bg-white border border-slate-200 rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-5 flex items-center justify-between border-b-2 border-sky-500 sticky top-0 z-10">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-300 flex items-center justify-center text-sm font-bold">📄</span>
                <div>
                    <h4 class="font-black text-sm text-white flex items-center gap-1.5">
                        Chi Tiết Phiếu: <span id="dtlCode" class="text-sky-300 font-mono"></span>
                    </h4>
                    <span class="text-[11px] text-slate-400">Thông tin phản ánh và minh chứng đính kèm</span>
                </div>
            </div>
            <button type="button" onclick="closeTicketDetailModal()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition flex items-center justify-center text-sm">
                ✕
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-4 text-xs">
            <!-- Grid tóm tắt -->
            <div class="grid grid-cols-2 gap-3 bg-slate-50 border border-slate-200/80 p-3.5 rounded-2xl">
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Trạng thái:</span>
                    <div id="dtlStatusBadge"></div>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-1">KTV Tiếp nhận:</span>
                    <div id="dtlAgent" class="font-extrabold text-slate-900 text-sm"></div>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Nhóm Dịch vụ:</span>
                    <div id="dtlCategory" class="font-bold text-sky-700"></div>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-1">Hệ thống ảnh hưởng:</span>
                    <div id="dtlSystem" class="font-semibold text-slate-700"></div>
                </div>
                <div class="col-span-2 pt-2 border-t border-slate-200/60">
                    <span class="text-slate-400 font-bold block mb-0.5">Thời gian gửi phiếu:</span>
                    <div id="dtlCreatedAt" class="font-mono text-slate-600"></div>
                </div>
            </div>

            <!-- Tiêu đề -->
            <div>
                <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1">Tiêu đề sự cố:</span>
                <div id="dtlTitle" class="p-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-900 text-sm"></div>
            </div>

            <!-- Mô tả -->
            <div>
                <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1">Mô tả hiện tượng lỗi:</span>
                <div id="dtlDesc" class="p-3 bg-white border border-slate-200 rounded-xl font-medium text-slate-700 leading-relaxed whitespace-pre-wrap max-h-44 overflow-y-auto"></div>
            </div>

            <!-- Minh chứng -->
            <div>
                <span class="text-slate-500 font-bold uppercase tracking-wider block mb-1.5">Minh chứng đính kèm:</span>
                <div id="dtlAttachmentBox" class="p-3 bg-slate-50 border border-dashed border-slate-300 rounded-xl"></div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <div id="dtlCancelBox" style="display: none;">
                <form method="POST" action="/student/tickets/cancel" onsubmit="return confirm('Bạn có chắc chắn muốn hủy phiếu này không?');">
                    <input type="hidden" name="ticket_id" id="dtlCancelTicketId" value="">
                    <button type="submit" id="dtlCancelBtn" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl font-bold text-xs transition">
                        🚫 Hủy phiếu này
                    </button>
                </form>
            </div>
            <button type="button" onclick="closeTicketDetailModal()" class="ml-auto px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- MODAL CẬP NHẬT AVATAR -->
<div id="avatarModal" style="display: none;" 
     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 items-center justify-center p-4 transition-all" 
     onclick="if(event.target === this) closeAvatarModal()">
    <div class="bg-white border border-slate-200 rounded-3xl max-w-sm w-full shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-5 flex items-center justify-between border-b-2 border-sky-500">
            <h4 class="font-black text-sm text-white flex items-center gap-1.5">
                <span>📷</span> Cập Nhật Ảnh Đại Diện
            </h4>
            <button type="button" onclick="closeAvatarModal()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition flex items-center justify-center text-xs">
                ✕
            </button>
        </div>

        <form method="POST" action="/student/avatar" enctype="multipart/form-data" class="p-5 space-y-4">
            <div class="text-center space-y-2">
                <div class="w-24 h-24 rounded-full mx-auto border-4 border-sky-400 overflow-hidden bg-slate-100 flex items-center justify-center shadow-lg">
                    <img id="avatarPreviewImg" src="<?= !empty($user['avatar_path']) ? '/tickets/attachment?file=' . urlencode($user['avatar_path']) : '' ?>" alt="Xem trước" class="w-full h-full object-cover <?= empty($user['avatar_path']) ? 'hidden' : '' ?>">
                    <span id="avatarPreviewFallback" class="text-4xl font-black text-indigo-700 <?= !empty($user['avatar_path']) ? 'hidden' : '' ?>">
                        <?= mb_substr($user['name'], 0, 1) ?>
                    </span>
                </div>
                <div id="avatarStatusText" class="text-xs text-slate-500 font-medium">Xem trước ảnh đại diện sinh viên</div>
            </div>

            <div class="flex gap-2 justify-center flex-wrap">
                <label class="px-3 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl text-xs font-bold cursor-pointer transition flex items-center gap-1">
                    <span>📁</span> Tải ảnh từ máy
                    <input type="file" name="avatar" id="avatarFileInput" accept="image/*" class="hidden" onchange="onAvatarFileSelected(this)">
                </label>
                <button type="button" onclick="startAvatarCamera()" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center gap-1">
                    <span>📸</span> Chụp Webcam
                </button>
            </div>

            <div id="avatarCameraBox" style="display: none;" class="p-3 bg-slate-900 rounded-2xl text-center space-y-2">
                <video id="avatarWebcamVideo" autoplay playsinline class="w-full max-h-48 rounded-lg bg-black"></video>
                <div class="flex justify-center gap-2">
                    <button type="button" onclick="captureAvatarSnapshot()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition">
                        📸 Bấm Chụp
                    </button>
                    <button type="button" onclick="stopAvatarCamera()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl text-xs transition">
                        ✕ Tắt
                    </button>
                </div>
                <canvas id="avatarSnapshotCanvas" class="hidden"></canvas>
            </div>

            <input type="hidden" name="captured_avatar" id="capturedAvatarInput" value="">

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAvatarModal()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-sky-500/20 transition">
                    💾 Lưu Ảnh Avatar
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
