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
        width: 68px;
        height: 68px;
        background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%);
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 800;
        margin-bottom: 14px;
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
    <!-- Cột trái: Thông tin cá nhân CỦA CHÍNH SINH VIÊN (Bảo mật 100%, không thấy ai khác) -->
    <div class="profile-card">
        <div class="profile-avatar">
            <?= mb_substr($user['name'], 0, 1) ?>
        </div>
        <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="profile-code">Mã SV: <?= htmlspecialchars($user['user_code']) ?></div>

        <div style="background: #e0f2fe; color: #0284c7; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-block; margin-bottom: 16px;">
            🎓 CỔNG SINH VIÊN CHÍNH THỨC
        </div>

        <div class="profile-item">
            <span class="profile-label">Lớp sinh hoạt:</span>
            <span class="profile-val"><?= htmlspecialchars($user['class_name']) ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Email trường:</span>
            <span class="profile-val"><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Khoa phụ trách:</span>
            <span class="profile-val"><?= htmlspecialchars($user['department'] ?: 'Khoa: Toán-CNTT') ?></span>
        </div>
        <div class="profile-item">
            <span class="profile-label">Trạng thái học vụ:</span>
            <span class="profile-val" style="color: #15803d;">● Đang học (<?= htmlspecialchars($user['cohort']) ?>)</span>
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
            <button type="button" onclick="document.getElementById('ticketFormBox').style.display='block'" style="background:var(--primary); color:#fff; border:none; padding:8px 14px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;">
                ➕ Báo sự cố mới
            </button>
        </div>

        <!-- Form tạo phiếu mới (Ẩn / Hiện) -->
        <div id="ticketFormBox" style="display: none; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="color: var(--primary-dark); font-size: 15px; margin: 0;">Gửi Yêu Cầu Hỗ Trợ Kỹ Thuật CNTT</h4>
                <button type="button" onclick="document.getElementById('ticketFormBox').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:16px;">✕</button>
            </div>

            <form method="POST" action="/student/tickets/create">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Nhóm dịch vụ sự cố:</label>
                        <select name="category_id" style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:6px;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (SLA <?= $cat['sla_hours'] ?>h)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Hệ thống bị ảnh hưởng:</label>
                        <input type="text" name="system_affected" placeholder="VD: Mạng Wifi KTX, Cổng QLĐT, Canvas LMS..." required style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:6px;">
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Tiêu đề ngắn gọn sự cố:</label>
                    <input type="text" name="title" placeholder="VD: Lỗi không nộp được bài tập lớn môn Lập trình Web..." required style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:6px;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display:block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Mô tả hiện tượng lỗi chi tiết:</label>
                    <textarea name="description" rows="3" placeholder="Mô tả cụ thể hiện tượng lỗi bạn gặp phải để KTV hỗ trợ nhanh nhất..." required style="width:100%; padding:8px; border:1px solid var(--border-color); border-radius:6px; resize:vertical;"></textarea>
                </div>

                <button type="submit" style="padding:10px 18px; background:var(--primary); color:#fff; font-weight:700; border:none; border-radius:6px; cursor:pointer;">
                    Gửi Phiếu Hỗ Trợ Ngay ➔
                </button>
            </form>
        </div>

        <!-- Bảng danh sách phiếu của riêng sinh viên này -->
        <?php if (empty($myTickets)): ?>
            <div style="text-align: center; padding: 40px; color: #94a3b8; background: #f8fafc; border-radius: 8px;">
                Bạn chưa gửi phiếu hỗ trợ nào. Bấm nút "<strong>➕ Báo sự cố mới</strong>" ở trên khi cần Trung tâm CNTT hỗ trợ nhé!
            </div>
        <?php else: ?>
            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Dịch Vụ</th>
                        <th>Tiêu Đề</th>
                        <th>KTV Tiếp Nhận</th>
                        <th>Trạng Thái</th>
                        <th>Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myTickets as $t): ?>
                        <tr>
                            <td><strong style="color: var(--primary);"><?= htmlspecialchars($t['ticket_code']) ?></strong></td>
                            <td><?= htmlspecialchars($t['category_name']) ?></td>
                            <td><?= htmlspecialchars($t['title']) ?></td>
                            <td><?= htmlspecialchars($t['agent_name']) ?></td>
                            <td>
                                <?php if ($t['status'] === 'resolved'): ?>
                                    <span style="background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">✓ Đã giải quyết</span>
                                <?php elseif ($t['status'] === 'in_progress'): ?>
                                    <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">⏳ Đang xử lý</span>
                                <?php else: ?>
                                    <span style="background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;">● Chờ tiếp nhận</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: #64748b; font-size: 12px;"><?= htmlspecialchars(substr($t['created_at'], 0, 16)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

