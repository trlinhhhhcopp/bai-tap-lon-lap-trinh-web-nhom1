<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 680px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 24px; text-align: center; border-bottom: 3px solid var(--primary);">
        <h2 style="font-size: 20px; color: #38bdf8; margin: 0 0 6px 0; font-weight: 800; letter-spacing: 0.3px;">🔍 Tra Cứu Tiến Độ Phiếu Hỗ Trợ</h2>
        <p style="font-size: 13.5px; color: #94a3b8; margin: 0;">Nhập mã định danh phiếu của bạn để kiểm tra trạng thái xử lý thời gian thực</p>
    </div>

    <div style="padding: 28px;">
        <form method="GET" action="<?= $appBase ?>public/index.php/guest/lookup" style="display: flex; gap: 10px; margin-bottom: 24px;">
            <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" required placeholder="Nhập mã phiếu (VD: HD-2026-2910-L0H004)" style="flex:1; padding:12px 16px; border:1.5px solid var(--border-color); border-radius:8px; font-weight:700; font-size:14px; text-transform:uppercase; letter-spacing:0.5px; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
            <button type="submit" style="padding:12px 24px; background:var(--primary); color:#fff; font-weight:700; font-size:14px; border:none; border-radius:8px; cursor:pointer; white-space:nowrap; transition:background 0.2s;">🔍 Tra Cứu Ngay</button>
        </form>

        <?php if (!empty($isInternalTicket)): ?>
            <!-- BẢO MẬT: PHIẾU NỘI BỘ YÊU CẦU ĐĂNG NHẬP TRÁNH BỊ NGƯỜI NGOÀI XEM LÉN -->
            <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 26px 20px; text-align: center; box-shadow: 0 4px 15px rgba(217, 119, 6, 0.08);">
                <div style="width: 58px; height: 58px; line-height: 58px; font-size: 30px; background: #fef3c7; border-radius: 50%; margin: 0 auto 14px auto; color: #d97706; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.15);">🔒</div>
                <h3 style="font-size: 18px; color: #92400e; margin: 0 0 8px 0; font-weight: 800;">Phiếu Hỗ Trợ Nội Bộ Sinh Viên / Giảng Viên</h3>
                <p style="font-size: 13.5px; color: #78350f; line-height: 1.6; margin: 0 auto 20px auto; max-width: 520px;">
                    Mã phiếu <strong><?= htmlspecialchars($code) ?></strong> được gửi từ tài khoản nội bộ Nhà trường.<br>
                    Để đảm bảo tính riêng tư và bảo mật thông tin cá nhân, tính năng tra cứu công khai không hiển thị nội dung này. Vui lòng đăng nhập vào tài khoản để xem chi tiết!
                </p>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= $appBase ?>index.php" style="padding: 10px 22px; background: var(--primary); color: #fff; font-weight: 700; border-radius: 8px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 3px 8px rgba(2, 132, 199, 0.25);">
                        🔑 Đăng Nhập Cổng Sinh Viên
                    </a>
                    <a href="<?= $appBase ?>public/index.php/guest/lookup" style="padding: 10px 18px; background: #f1f5f9; color: #475569; font-weight: 600; border-radius: 8px; text-decoration: none; font-size: 14px; border: 1px solid #cbd5e1;">
                        Tra Cứu Mã Khác
                    </a>
                </div>
            </div>

        <?php elseif ($ticket): ?>
            <!-- PHIẾU CỨU HỘ KHẨN CẤP / GUEST TICKET -->
            <?php
                $maskedEmail = '';
                if (!empty($ticket['contact_email'])) {
                    $emParts = explode('@', $ticket['contact_email']);
                    if (count($emParts) === 2) {
                        $p = $emParts[0];
                        $maskedP = (strlen($p) > 4) ? substr($p, 0, 3) . '***' . substr($p, -1) : substr($p, 0, 1) . '***';
                        $maskedEmail = $maskedP . '@' . $emParts[1];
                    }
                }
            ?>
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 22px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.5px;">Mã Phiếu Cứu Hộ Bảo Mật</span>
                        <h3 style="font-size: 21px; color: #0284c7; margin: 2px 0 0 0; font-weight: 800; letter-spacing: 0.8px; font-family: monospace;"><?= htmlspecialchars($ticket['ticket_code']) ?></h3>
                    </div>
                    <div>
                        <?php if ($ticket['status'] === 'resolved'): ?>
                            <span style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 4px;">✓ ĐÃ MỞ KHÓA THÀNH CÔNG</span>
                        <?php elseif ($ticket['status'] === 'in_progress'): ?>
                            <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 4px;">⏳ KTV ĐANG XỬ LÝ</span>
                        <?php elseif ($ticket['status'] === 'closed'): ?>
                            <span style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 4px;">🔒 ĐÃ ĐÓNG</span>
                        <?php else: ?>
                            <span style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 4px;">● MỚI TIẾP NHẬN</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="font-size: 14px; line-height: 1.7; color: #334155;">
                    <p style="margin: 0 0 8px 0;"><strong>Tiêu đề sự cố:</strong> <?= htmlspecialchars($ticket['title']) ?></p>
                    <p style="margin: 0 0 8px 0;"><strong>Kỹ thuật viên phụ trách:</strong> <span style="color: #0284c7; font-weight: 600;"><?= htmlspecialchars($ticket['agent_name']) ?></span></p>
                    <p style="margin: 0 0 8px 0;"><strong>Thời gian gửi phiếu:</strong> <?= htmlspecialchars($ticket['created_at']) ?></p>
                    <?php if (!empty($maskedEmail)): ?>
                        <p style="margin: 0 0 8px 0;"><strong>Email nhận thông báo:</strong> <span style="font-family: monospace; background: #e2e8f0; padding: 2px 8px; border-radius: 4px;"><?= htmlspecialchars($maskedEmail) ?></span></p>
                    <?php endif; ?>
                    <p style="margin: 0 0 8px 0;"><strong>Chi tiết yêu cầu:</strong> <?= nl2br(htmlspecialchars($ticket['description'])) ?></p>

                    <?php if (!empty($ticket['attachment_path'])): ?>
                        <p style="margin: 12px 0 0 0; padding-top: 10px; border-top: 1px dashed #cbd5e1;">
                            <strong>Minh chứng đã gửi:</strong>
                            <a href="/tickets/attachment?file=<?= urlencode($ticket['attachment_path']) ?>" target="_blank" style="color: #0284c7; font-weight: 600; text-decoration: underline; margin-left: 6px; display: inline-inline-flex; align-items: center; gap: 4px;">
                                📎 Bấm vào đây để xem ảnh minh chứng
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif (!empty($code)): ?>
            <div style="text-align: center; color: #b91c1c; padding: 22px; background: #fef2f2; border-radius: 12px; border: 1.5px solid #fecaca; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.05);">
                <div style="font-size: 26px; margin-bottom: 6px;">❌</div>
                <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Không tìm thấy phiếu hỗ trợ nào</div>
                <div style="font-size: 13.5px; color: #7f1d1d;">Mã phiếu <strong><?= htmlspecialchars($code) ?></strong> không tồn tại trong hệ thống. Vui lòng kiểm tra lại chính xác từng ký tự trong mã bạn đã nhận!</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
