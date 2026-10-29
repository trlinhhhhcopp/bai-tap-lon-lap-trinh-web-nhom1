<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 650px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 22px; text-align: center; border-bottom: 3px solid var(--primary);">
        <h2 style="font-size: 19px; color: #38bdf8; margin-bottom: 4px;">🔍 Tra Cứu Tiến Độ Phiếu Cứu Hộ</h2>
        <p style="font-size: 13px; color: #94a3b8;">Nhập duy nhất Mã phiếu cứu hộ bạn đã nhận để kiểm tra kết quả</p>
    </div>

    <div style="padding: 26px;">
        <form method="GET" action="/guest/lookup" style="display: flex; gap: 10px; margin-bottom: 24px;">
            <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" required placeholder="Nhập mã phiếu cứu hộ (VD: HD-2026-2910-849201)" style="flex:1; padding:12px 14px; border:1px solid var(--border-color); border-radius:6px; font-weight:700; font-size:14px; text-transform:uppercase;">
            <button type="submit" style="padding:12px 24px; background:var(--primary); color:#fff; font-weight:700; font-size:14px; border:none; border-radius:6px; cursor:pointer; white-space:nowrap;">🔍 Tra Cứu Ngay</button>
        </form>

        <?php if ($ticket): ?>
            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 14px;">
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Mã Phiếu Cứu Hộ Duy Nhất</span>
                        <h3 style="font-size: 20px; color: #0284c7; margin: 0; font-weight: 800; letter-spacing: 0.5px;"><?= htmlspecialchars($ticket['ticket_code']) ?></h3>
                    </div>
                    <div>
                        <?php if ($ticket['status'] === 'resolved'): ?>
                            <span style="background: #dcfce7; color: #15803d; padding: 6px 14px; border-radius: 12px; font-weight: 700; font-size: 12.5px;">✓ ĐÃ MỞ KHÓA THÀNH CÔNG</span>
                        <?php elseif ($ticket['status'] === 'in_progress'): ?>
                            <span style="background: #fef3c7; color: #b45309; padding: 6px 14px; border-radius: 12px; font-weight: 700; font-size: 12.5px;">⏳ KTV ĐANG XỬ LÝ</span>
                        <?php else: ?>
                            <span style="background: #e0f2fe; color: #0284c7; padding: 6px 14px; border-radius: 12px; font-weight: 700; font-size: 12.5px;">● MỚI TIẾP NHẬN</span>
                        <?php endif; ?>
                    </div>
                </div>

                <p><strong>Tiêu đề:</strong> <?= htmlspecialchars($ticket['title']) ?></p>
                <p style="margin-top: 8px;"><strong>Chi tiết:</strong> <?= htmlspecialchars($ticket['description']) ?></p>
                <p style="margin-top: 8px;"><strong>Kỹ thuật viên phụ trách:</strong> <?= htmlspecialchars($ticket['agent_name']) ?></p>
                <p style="margin-top: 8px;"><strong>Thời gian gửi phiếu:</strong> <?= htmlspecialchars($ticket['created_at']) ?></p>
                <?php if (!empty($ticket['attachment_path'])): ?>
                    <p style="margin-top: 8px;">
                        <strong>Minh chứng đã gửi:</strong>
                        <a href="/tickets/attachment?file=<?= urlencode($ticket['attachment_path']) ?>" target="_blank" style="color: #0284c7; font-weight: 600; text-decoration: underline; margin-left: 6px;">
                            📎 Bấm vào đây để xem ảnh minh chứng
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php elseif (!empty($code)): ?>
            <div style="text-align: center; color: #dc2626; padding: 20px; background: #fee2e2; border-radius: 8px; border: 1px solid #fca5a5;">
                ❌ Không tìm thấy phiếu cứu hộ nào với Mã phiếu: <strong><?= htmlspecialchars($code) ?></strong>! Vui lòng kiểm tra lại mã đã nhận.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

