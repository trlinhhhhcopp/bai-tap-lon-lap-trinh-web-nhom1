<?php require __DIR__ . '/../layouts/header.php'; ?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 24px;
    }
    @media (max-width: 850px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }
    .profile-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .profile-avatar {
        width: 76px;
        height: 76px;
        background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 8px;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
    }
    .profile-avatar .avatar-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
        border-radius: 50%;
    }
    .profile-avatar:hover .avatar-overlay {
        opacity: 1;
    }
    .profile-name { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
    .profile-code { font-size: 13px; color: #64748b; margin-bottom: 16px; font-family: monospace; }
    .profile-item {
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        display: flex;
        justify-content: space-between;
    }
    .profile-item:last-child { border-bottom: none; }
    .profile-label { color: #64748b; font-weight: 500; }
    .profile-val { font-weight: 600; color: #1e293b; text-align: right; }

    .content-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .ticket-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .ticket-table th {
        background: #f8fafc;
        padding: 10px 12px;
        border-bottom: 2px solid var(--border-color);
        text-align: left;
        color: #475569;
        font-size: 11.5px;
        text-transform: uppercase;
    }
    .ticket-table td {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
</style>

<div class="dashboard-grid">
    <!-- Cột trái: Thông tin cá nhân CỦA CHÍNH SINH VIÊN -->
    <div class="profile-card">
        <div class="profile-avatar" onclick="openAvatarModal()" title="Bấm vào đây để đổi ảnh đại diện">
            <?php if (!empty($user['avatar_path'])): ?>
                <img src="/tickets/attachment?file=<?= urlencode($user['avatar_path']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
                <?= mb_substr($user['name'], 0, 1) ?>
            <?php endif; ?>
            <div class="avatar-overlay">📷 Đổi ảnh</div>
        </div>
        <button type="button" onclick="openAvatarModal()" style="background: none; border: 1px dashed #38bdf8; color: #0284c7; padding: 3px 10px; border-radius: 12px; font-size: 11.5px; font-weight: 600; cursor: pointer; margin-bottom: 12px; transition: background 0.2s;">
            📷 Đổi ảnh đại diện
        </button>
        <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="profile-code">Mã SV: <?= htmlspecialchars($user['user_code']) ?></div>

        <div style="background: #e0f2fe; color: #0284c7; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-block; margin-bottom: 16px;">
            🎓 CỔNG SINH VIÊN CHÍNH THỨC
        </div>

        <div class="profile-item">
            <span class="profile-label">Lớp hành chính:</span>
            <span class="profile-val" style="color: var(--primary); font-weight:700;">Toán Ứng Dụng D2024</span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Email trường:</span>
            <span class="profile-val"><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Khoa phụ trách:</span>
            <span class="profile-val">Khoa: Toán-CNTT</span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Trạng thái học vụ:</span>
            <span class="profile-val" style="color: #15803d;">● Đang học (D2024)</span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Quyền hạn (RBAC):</span>
            <span class="profile-val"><?= strtoupper($user['role']) ?></span>
        </div>

        <div style="margin-top: 20px; padding: 12px; background: #f8fafc; border-radius: 8px; font-size: 11.5px; color: #64748b; line-height: 1.5;">
            🔒 <em>Cổng sinh viên được cách ly bảo mật tuyệt đối. Bạn chỉ có thể xem hồ sơ và các phiếu sự cố của chính mình.</em>
        </div>
    </div>

    <!-- Cột phải: Danh sách phiếu & Form gửi báo lỗi -->
    <div class="content-card">
        <div class="section-title">
            <span>📋 Các Phiếu Hỗ Trợ Đã Gửi Của Bạn</span>
            <button id="btnOpenTicketForm" type="button" onclick="openTicketForm()" style="background:var(--primary); color:#fff; border:none; padding:8px 14px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">
                ➕ Báo sự cố mới
            </button>
        </div>

        <!-- Form tạo phiếu mới (Tự động ẩn nút khi mở, tích hợp danh mục sự cố chuẩn & đính kèm minh chứng) -->
        <div id="ticketFormBox" style="display: none; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <h4 style="color: var(--primary-dark); font-size: 15px; margin: 0;">🚀 Gửi Yêu Cầu Hỗ Trợ Kỹ Thuật CNTT</h4>
                <button type="button" onclick="closeTicketForm()" style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; border-radius:4px; padding:3px 8px; cursor:pointer; font-size:13px; font-weight:700;">✕ Đóng</button>
            </div>

            <form method="POST" action="/student/tickets/create" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">1. Chọn Nhóm dịch vụ sự cố:</label>
                        <select name="category_id" id="categorySelect" onchange="onCategoryChange()" style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:6px; background:#fff; font-size:13px;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == 4 ? 'selected' : '' ?> data-code="<?= $cat['code'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">2. Hệ thống bị ảnh hưởng:</label>
                        <input type="text" name="system_affected" id="systemAffectedInput" readonly required style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:6px; background:#f1f5f9; color:#334155; font-size:13px; font-weight:600;">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">3. Chọn loại sự cố gặp phải:</label>
                    <select id="issuePresetSelect" onchange="onIssuePresetChange()" style="width:100%; padding:8px 10px; border:1px solid #38bdf8; border-radius:6px; background:#f0f9ff; color:#0369a1; font-size:13px; font-weight:600;">
                        <!-- Options sẽ tự động nạp theo Category được chọn -->
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">4. Tiêu đề ngắn gọn sự cố:</label>
                    <input type="text" name="title" id="ticketTitleInput" required style="width:100%; padding:8px 10px; border:1px solid var(--border-color); border-radius:6px; font-size:13px;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">5. Mô tả hiện tượng lỗi chi tiết (Kèm thông tin môn học, mã SV):</label>
                    <textarea name="description" id="ticketDescriptionInput" rows="4" required style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px; resize:vertical; font-size:13px; line-height:1.5;"></textarea>
                </div>

                <!-- 6. Đính kèm minh chứng lỗi (Cho phép chọn tải tệp từ thiết bị hoặc chụp ảnh camera) -->
                <div style="margin-bottom: 18px; padding: 14px; background: #fff; border: 1px dashed var(--border-color); border-radius: 8px;">
                    <label style="display:block; font-size: 12.5px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                        6. Đính kèm minh chứng lỗi (Ảnh chụp màn hình lỗi / Ảnh minh chứng):
                    </label>
                    <div style="font-size: 12px; color: #64748b; margin-bottom: 10px;">
                        Bạn có thể chọn <strong>Tải ảnh từ máy tính/điện thoại</strong> hoặc bấm <strong>Chụp ảnh trực tiếp từ Camera</strong>:
                    </div>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                        <!-- Nút 1: Tải tệp từ thiết bị -->
                        <label style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                            📁 Tải tệp từ thiết bị
                            <input type="file" name="attachment" id="fileAttachmentInput" accept="image/*,.pdf" style="display: none;" onchange="onFileSelected(this)">
                        </label>

                        <!-- Nút 2: Chụp ảnh từ Camera -->
                        <button type="button" onclick="startCamera()" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a; border-radius: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                            📸 Chụp ảnh từ Camera / Webcam
                        </button>
                    </div>

                    <!-- Khung Video Camera Live Stream (ẩn khi chưa bật camera) -->
                    <div id="cameraBox" style="display: none; margin-top: 12px; padding: 12px; background: #0f172a; border-radius: 8px; text-align: center;">
                        <video id="webcamVideo" autoplay playsinline style="width: 100%; max-width: 480px; max-height: 280px; border-radius: 6px; background: #000;"></video>
                        <div style="margin-top: 10px; display: flex; justify-content: center; gap: 10px;">
                            <button type="button" onclick="captureSnapshot()" style="padding: 7px 18px; background: #16a34a; color: #fff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer;">
                                📸 Bấm Chụp Ngay
                            </button>
                            <button type="button" onclick="stopCamera()" style="padding: 7px 14px; background: #64748b; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                                ✕ Đóng Camera
                            </button>
                        </div>
                        <canvas id="snapshotCanvas" style="display: none;"></canvas>
                    </div>

                    <!-- Khung xem trước minh chứng đã chọn hoặc vừa chụp (Preview Box) -->
                    <div id="previewBox" style="display: none; margin-top: 12px; padding: 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 700; color: #15803d;" id="previewStatusText">✓ Đã đính kèm minh chứng lỗi:</span>
                            <button type="button" onclick="clearAttachment()" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 3px 8px; border-radius: 4px; font-size: 11.5px; font-weight: 600; cursor: pointer;">
                                🗑️ Xóa / Chọn lại
                            </button>
                        </div>
                        <div style="text-align: center;">
                            <img id="previewImage" src="" alt="Ảnh minh chứng lỗi" style="max-width: 100%; max-height: 200px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        </div>
                    </div>

                    <input type="hidden" name="captured_image" id="capturedImageInput" value="">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeTicketForm()" style="padding:9px 16px; background:#e2e8f0; color:#475569; font-weight:600; border:none; border-radius:6px; cursor:pointer;">Hủy bỏ</button>
                    <button type="submit" style="padding:9px 20px; background:var(--primary); color:#fff; font-weight:700; border:none; border-radius:6px; cursor:pointer;">Gửi Phiếu Hỗ Trợ Ngay ➔</button>
                </div>
            </form>
        </div>

        <!-- Bảng danh sách phiếu của riêng sinh viên này -->
        <?php if (empty($myTickets)): ?>
            <div style="text-align: center; padding: 40px; color: #94a3b8; background: #f8fafc; border-radius: 8px;">
                Bạn chưa gửi phiếu hỗ trợ nào. Bấm nút "<strong>➕ Báo sự cố mới</strong>" ở trên khi cần Trung tâm CNTT hỗ trợ nhé!
            </div>
        <?php else: ?>
            <!-- Thanh Công Cụ Tìm Kiếm & Bộ Lọc Phiếu -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                    
                    <!-- 1. Tìm kiếm theo mã phiếu hoặc tên sự cố -->
                    <div style="flex: 1 1 240px; min-width: 210px;">
                        <label for="filterKeyword" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            🔍 Tìm mã phiếu / Tiêu đề sự cố:
                        </label>
                        <input type="text" id="filterKeyword" oninput="applyTicketFilter()" placeholder="Nhập mã phiếu (VD: HD-...) hoặc tên sự cố..." 
                               style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff; box-sizing: border-box; outline: none;">
                    </div>

                    <!-- 2. Lọc theo ngày tháng năm (suộc chọn ngày) -->
                    <div style="flex: 0 1 170px; min-width: 150px;">
                        <label for="filterDate" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            📅 Lọc theo ngày gửi:
                        </label>
                        <input type="date" id="filterDate" onchange="applyTicketFilter()" 
                               style="width: 100%; padding: 7.5px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff; box-sizing: border-box; outline: none;">
                    </div>

                    <!-- 3. Lọc theo trạng thái -->
                    <div style="flex: 0 1 190px; min-width: 170px;">
                        <label for="filterStatus" style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            🏷️ Lọc theo trạng thái:
                        </label>
                        <select id="filterStatus" onchange="applyTicketFilter()" 
                                style="width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff; box-sizing: border-box; outline: none;">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="new">● Mới tạo (Chờ tiếp nhận)</option>
                            <option value="in_progress">⏳ Đang xử lý</option>
                            <option value="resolved">✓ Đã giải quyết</option>
                            <option value="cancelled">✕ Đã hủy</option>
                        </select>
                    </div>

                    <!-- 4. Nút Đặt lại bộ lọc -->
                    <div style="flex: 0 0 auto;">
                        <button type="button" onclick="resetTicketFilter()" 
                                style="padding: 8px 14px; background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background 0.2s;" 
                                title="Xóa bộ lọc để hiện lại tất cả các phiếu">
                            🔄 Đặt lại
                        </button>
                    </div>

                </div>

                <!-- Thống kê kết quả lọc -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 8px; border-top: 1px dashed #e2e8f0; font-size: 12px; color: #64748b;">
                    <span>Hiển thị: <strong id="filterResultCount" style="color: var(--primary);"><?= count($myTickets) ?> / <?= count($myTickets) ?> phiếu</strong></span>
                    <span id="activeFilterTags" style="font-size: 11.5px; color: #0284c7; font-weight: 600;"></span>
                </div>
            </div>

            <table class="ticket-table" id="studentTicketTable">
                <thead>
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Dịch Vụ</th>
                        <th>Tiêu Đề</th>
                        <th>Minh Chứng</th>
                        <th>KTV Tiếp Nhận</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian</th>
                        <th style="text-align: center;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody id="studentTicketTbody">
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
                        <tr class="ticket-row" 
                            data-code="<?= strtolower(htmlspecialchars($t['ticket_code'])) ?>" 
                            data-title="<?= strtolower(htmlspecialchars($t['title'])) ?>" 
                            data-date="<?= $dateFilterValue ?>" 
                            data-status="<?= $statusFilterValue ?>">
                            <td>
                                <a href="javascript:void(0)" onclick="viewTicketDetail(<?= $ticketJson ?>)" style="color: var(--primary); font-weight: 700; text-decoration: underline;" title="Bấm vào để xem chi tiết thông tin đã điền">
                                    <?= htmlspecialchars($t['ticket_code']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($t['category_name']) ?></td>
                            <td>
                                <span style="display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; max-width: 170px;" title="<?= htmlspecialchars($t['title']) ?>">
                                    <?= htmlspecialchars($t['title']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($t['attachment_path'])): ?>
                                    <a href="<?= $appBase ?><?= htmlspecialchars($t['attachment_path']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; background: #f0f9ff; color: #0284c7; padding: 3px 8px; border-radius: 4px; text-decoration: none; font-size: 11.5px; font-weight: 600; border: 1px solid #bae6fd;">
                                        📎 Xem file
                                    </a>
                                <?php else: ?>
                                    <span style="color: #cbd5e1; font-size: 11.5px;">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($t['agent_name']) ?></td>
                            <td>
                                <?php if ($t['status'] === 'resolved'): ?>
                                    <span style="background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">✓ Đã giải quyết</span>
                                <?php elseif ($t['status'] === 'in_progress'): ?>
                                    <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">⏳ Đang xử lý</span>
                                <?php elseif ($t['status'] === 'closed' && ($t['closed_reason'] ?? '') === 'cancelled'): ?>
                                    <span style="background: #fee2e2; color: #b91c1c; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">✕ Đã hủy</span>
                                <?php else: ?>
                                    <span style="background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">● Chờ tiếp nhận</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: #64748b; font-size: 12px;"><?= htmlspecialchars(substr($t['created_at'], 0, 16)) ?></td>
                            <td style="text-align: center; white-space: nowrap;">
                                <button type="button" onclick="viewTicketDetail(<?= $ticketJson ?>)" style="padding: 4px 8px; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 5px; font-size: 11.5px; font-weight: 600; cursor: pointer;">
                                    🔍 Chi tiết
                                </button>
                                <?php if ($canCancel): ?>
                                    <form method="POST" action="/student/tickets/cancel" style="display: inline-block; margin-left: 3px;" onsubmit="return confirm('Bạn có chắc chắn muốn hủy phiếu <?= htmlspecialchars($t['ticket_code']) ?> không? (Sau khi hủy, KTV sẽ không tiếp nhận xử lý phiếu này nữa)');">
                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                        <button type="submit" style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 5px; font-size: 11.5px; font-weight: 600; cursor: pointer;" title="Bạn còn <?= $remMinutes ?> phút để hủy phiếu này nếu KTV chưa tiếp nhận">
                                            🚫 Hủy (còn <?= $remMinutes ?>p)
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="filterEmptyRow" style="display: none;">
                        <td colspan="8" style="text-align: center; padding: 30px; color: #94a3b8; font-style: italic; background: #f8fafc;">
                            🔍 Không tìm thấy phiếu hỗ trợ nào phù hợp với điều kiện tìm kiếm và bộ lọc của bạn.
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

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
    document.getElementById('btnOpenTicketForm').style.display = 'inline-block';
    stopCamera();
}

function onCategoryChange() {
    const catSelect = document.getElementById('categorySelect');
    const catId = parseInt(catSelect.value);
    const spec = standardSpecs[catId] || standardSpecs[1];

    // Cập nhật Hệ thống bị ảnh hưởng
    document.getElementById('systemAffectedInput').value = spec.system;

    // Cập nhật Danh sách sự cố chuẩn
    const presetSelect = document.getElementById('issuePresetSelect');
    presetSelect.innerHTML = '<option value="">-- Bấm để chọn sự cố... --</option>';

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

    if (presetVal === '') {
        return;
    }

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
            alert("Không thể truy cập Camera. Vui lòng cấp quyền cho Camera hoặc dùng nút 'Tải tệp từ thiết bị'!");
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

    // Lưu vào input hidden
    document.getElementById('capturedImageInput').value = dataUrl;
    // Reset file input nếu có
    const fileInput = document.getElementById('fileAttachmentInput');
    if (fileInput) fileInput.value = '';

    // Hiển thị preview
    document.getElementById('previewImage').src = dataUrl;
    document.getElementById('previewStatusText').textContent = '📸 Đã chụp ảnh thành công từ Camera:';
    document.getElementById('previewBox').style.display = 'block';

    stopCamera();
}

function onFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        // Reset dữ liệu chụp camera
        document.getElementById('capturedImageInput').value = '';

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('previewStatusText').textContent = `📁 Đã chọn file ảnh: ${file.name} (${Math.round(file.size / 1024)} KB)`;
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
    
    // Trạng thái
    const badgeBox = document.getElementById('dtlStatusBadge');
    if (ticket.status === 'resolved') {
        badgeBox.innerHTML = '<span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">✓ Đã giải quyết</span>';
    } else if (ticket.status === 'in_progress') {
        badgeBox.innerHTML = '<span style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">⏳ Đang xử lý</span>';
    } else if (ticket.status === 'closed' && ticket.closed_reason === 'cancelled') {
        badgeBox.innerHTML = '<span style="background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">✕ Đã hủy bởi sinh viên</span>';
    } else {
        badgeBox.innerHTML = '<span style="background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">● Mới tạo (Chờ tiếp nhận)</span>';
    }

    document.getElementById('dtlCategory').textContent = ticket.category_name || 'Hỗ trợ kỹ thuật';
    document.getElementById('dtlSystem').textContent = ticket.system_affected || 'Hệ thống chung';
    document.getElementById('dtlAgent').textContent = ticket.agent_name || 'Đang chờ phân công';
    document.getElementById('dtlCreatedAt').textContent = ticket.created_at;
    document.getElementById('dtlTitle').textContent = ticket.title;
    document.getElementById('dtlDesc').textContent = ticket.description;

    // Minh chứng đính kèm
    const attachBox = document.getElementById('dtlAttachmentBox');
    if (ticket.attachment_path) {
        const fileUrl = '<?= $appBase ?>' + ticket.attachment_path;
        const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(ticket.attachment_path);
        
        let html = '';
        if (isImg) {
            html += `<div style="margin-bottom: 10px; text-align: center;">
                        <img src="${fileUrl}" alt="Minh chứng lỗi" style="max-width: 100%; max-height: 260px; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.08); object-fit: contain;">
                     </div>`;
        }
        html += `<a href="${fileUrl}" target="_blank" download style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; font-size: 12.5px; font-weight: 600; text-decoration: none; transition: background 0.2s;">
                    📥 Tải xuống / Mở tệp minh chứng gốc
                 </a>`;
        attachBox.innerHTML = html;
    } else {
        attachBox.innerHTML = '<span style="color: #94a3b8; font-style: italic; font-size: 12.5px;">(Phiếu này không đính kèm tệp hay ảnh chụp màn hình lỗi)</span>';
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

        // Khớp từ khóa: tìm theo mã phiếu HOẶC tiêu đề sự cố
        const matchKeyword = !keyword || code.includes(keyword) || title.includes(keyword);
        // Khớp ngày tháng: so sánh theo ngày YYYY-MM-DD
        const matchDate = !selectedDate || rowDate === selectedDate;
        // Khớp trạng thái: new, in_progress, resolved, cancelled
        const matchStatus = !selectedStatus || rowStatus === selectedStatus;

        if (matchKeyword && matchDate && matchStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Cập nhật số lượng phiếu hiển thị
    const countBadge = document.getElementById('filterResultCount');
    if (countBadge) {
        countBadge.textContent = `${visibleCount} / ${rows.length} phiếu`;
    }

    // Hiển thị dòng thông báo khi không có kết quả nào khớp
    const emptyRow = document.getElementById('filterEmptyRow');
    if (emptyRow) {
        emptyRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }

    // Hiển thị các nhãn đang lọc để người dùng dễ theo dõi
    const tags = [];
    if (keyword) tags.push(`Từ khóa: "${keyword}"`);
    if (selectedDate) {
        // Định dạng ngày hiển thị dd/mm/yyyy
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

<!-- Modal Xem Chi Tiết Phiếu Hỗ Trợ Đã Gửi -->
<div id="modalTicketDetail" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 15px; backdrop-filter: blur(2px);" onclick="if(event.target === this) closeTicketDetailModal()">
    <div style="background: #fff; border-radius: 12px; max-width: 620px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); display: flex; flex-direction: column;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--primary); position: sticky; top: 0; z-index: 10;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">📄</span>
                <div>
                    <h4 style="margin: 0; font-size: 15px; color: #fff;">Chi Tiết Phiếu Hỗ Trợ: <span id="dtlCode" style="color: #38bdf8; font-weight: 700;"></span></h4>
                    <span style="font-size: 11.5px; color: #94a3b8;">Thông tin chi tiết bạn đã điền và minh chứng đính kèm</span>
                </div>
            </div>
            <button type="button" onclick="closeTicketDetailModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer; line-height: 1;">✕</button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 22px; display: flex; flex-direction: column; gap: 16px;">
            <!-- Hàng thông tin tóm tắt -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div>
                    <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">Trạng thái xử lý:</div>
                    <div id="dtlStatusBadge" style="margin-top: 4px;"></div>
                </div>
                <div>
                    <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">Kỹ thuật viên tiếp nhận:</div>
                    <div id="dtlAgent" style="font-size: 13px; font-weight: 700; color: #1e293b; margin-top: 4px;"></div>
                </div>
                <div>
                    <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">Nhóm dịch vụ sự cố:</div>
                    <div id="dtlCategory" style="font-size: 13px; font-weight: 600; color: #0284c7; margin-top: 4px;"></div>
                </div>
                <div>
                    <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">Hệ thống bị ảnh hưởng:</div>
                    <div id="dtlSystem" style="font-size: 13px; font-weight: 600; color: #334155; margin-top: 4px;"></div>
                </div>
                <div style="grid-column: 1 / -1;">
                    <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">Thời gian nộp phiếu:</div>
                    <div id="dtlCreatedAt" style="font-size: 12.5px; color: #475569; margin-top: 2px;"></div>
                </div>
            </div>

            <!-- Tiêu đề sự cố -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 4px; text-transform: uppercase;">Tiêu đề sự cố:</label>
                <div id="dtlTitle" style="padding: 10px 12px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: 700; color: #1e293b; font-size: 13.5px;"></div>
            </div>

            <!-- Mô tả chi tiết -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 4px; text-transform: uppercase;">Nội dung mô tả chi tiết lỗi:</label>
                <div id="dtlDesc" style="padding: 12px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #1e293b; line-height: 1.6; white-space: pre-wrap; max-height: 180px; overflow-y: auto;"></div>
            </div>

            <!-- Minh chứng đính kèm -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase;">Minh chứng đính kèm (Ảnh chụp lỗi / Tệp):</label>
                <div id="dtlAttachmentBox" style="padding: 12px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;"></div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div id="dtlCancelBox" style="display: none;">
                <form method="POST" action="/student/tickets/cancel" onsubmit="return confirm('Bạn có chắc chắn muốn hủy phiếu hỗ trợ này không?');">
                    <input type="hidden" name="ticket_id" id="dtlCancelTicketId" value="">
                    <button type="submit" id="dtlCancelBtn" style="padding: 8px 14px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 6px; font-size: 12.5px; font-weight: 700; cursor: pointer;">
                        🚫 Hủy phiếu này
                    </button>
                </form>
            </div>
            <button type="button" onclick="closeTicketDetailModal()" style="margin-left: auto; padding: 8px 18px; background: #64748b; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- Modal cập nhật Ảnh đại diện (Avatar) -->
<div id="avatarModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 15px; backdrop-filter: blur(2px);">
    <div style="background: #fff; border-radius: 12px; max-width: 420px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--primary);">
            <h4 style="margin: 0; font-size: 15px; color: #38bdf8;">📷 Cập Nhật Ảnh Đại Diện (Avatar)</h4>
            <button type="button" onclick="closeAvatarModal()" style="background: none; border: none; color: #94a3b8; font-size: 18px; cursor: pointer; line-height: 1;">✕</button>
        </div>

        <form method="POST" action="/student/avatar" enctype="multipart/form-data" style="padding: 22px;">
            <div style="text-align: center; margin-bottom: 18px;">
                <div style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 10px; border: 3px solid #38bdf8; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <img id="avatarPreviewImg" src="<?= !empty($user['avatar_path']) ? '/tickets/attachment?file=' . urlencode($user['avatar_path']) : '' ?>" alt="Xem trước" style="width: 100%; height: 100%; object-fit: cover; <?= empty($user['avatar_path']) ? 'display: none;' : '' ?>">
                    <span id="avatarPreviewFallback" style="font-size: 42px; font-weight: 700; color: var(--primary); <?= !empty($user['avatar_path']) ? 'display: none;' : '' ?>">
                        <?= mb_substr($user['name'], 0, 1) ?>
                    </span>
                </div>
                <div id="avatarStatusText" style="font-size: 12px; color: #64748b;">Xem trước ảnh đại diện của bạn</div>
            </div>

            <div style="display: flex; gap: 8px; justify-content: center; margin-bottom: 14px; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; border-radius: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    📁 Tải ảnh từ máy
                    <input type="file" name="avatar" id="avatarFileInput" accept="image/*" style="display: none;" onchange="onAvatarFileSelected(this)">
                </label>
                <button type="button" onclick="startAvatarCamera()" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef3c7; color: #b45309; border: 1px solid #fde68a; border-radius: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    📸 Chụp ảnh Webcam
                </button>
            </div>

            <!-- Khung chụp ảnh live qua webcam -->
            <div id="avatarCameraBox" style="display: none; margin-bottom: 14px; padding: 10px; background: #0f172a; border-radius: 8px; text-align: center;">
                <video id="avatarWebcamVideo" autoplay playsinline style="width: 100%; max-height: 200px; border-radius: 6px; background: #000;"></video>
                <div style="margin-top: 8px; display: flex; justify-content: center; gap: 8px;">
                    <button type="button" onclick="captureAvatarSnapshot()" style="padding: 6px 14px; background: #16a34a; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">
                        📸 Chụp Ngay
                    </button>
                    <button type="button" onclick="stopAvatarCamera()" style="padding: 6px 12px; background: #64748b; color: #fff; border: none; border-radius: 6px; font-size: 12px; cursor: pointer;">
                        ✕ Đóng Camera
                    </button>
                </div>
                <canvas id="avatarSnapshotCanvas" style="display: none;"></canvas>
            </div>

            <input type="hidden" name="captured_avatar" id="capturedAvatarInput" value="">

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <button type="button" onclick="closeAvatarModal()" style="padding: 8px 14px; background: #f1f5f9; color: #475569; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 8px 18px; background: var(--primary); color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer;">💾 Lưu Ảnh Đại Diện</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
