<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 650px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 22px; text-align: center; border-bottom: 3px solid var(--primary);">
        <h2 style="font-size: 19px; color: #38bdf8; margin-bottom: 4px;">🔍 Tra Cứu Tiến Độ Phiếu Cứu Hộ</h2>
        <p style="font-size: 13px; color: #94a3b8;">Nhập Mã phiếu và Mã PIN 4 số đã được cấp</p>
    </div>

    <div style="padding: 26px;">
        <form method="GET" action="/guest/lookup" style="display: flex; gap: 10px; margin-bottom: 24px;">
            <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" required placeholder="Mã phiếu (HD-2026-xxxxx)" style="flex:2; padding:10px; border:1px solid var(--border-color); border-radius:6px; font-weight:bold;">
            <input type="password" name="pin" value="<?= htmlspecialchars($pin) ?>" required maxlength="4" placeholder="Mã PIN 4 số" style="flex:1; padding:10px; border:1px solid var(--border-color); border-radius:6px; text-align:center; letter-spacing:2px;">
            <button type="submit" style="padding:10px 20px; background:var(--primary); color:#fff; font-weight:700; border:none; border-radius:6px; cursor:pointer;">Tra Cứu</button>
        </form>

        <?php if ($ticket): ?>
            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 14px;">
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Mã Phiếu Hỗ Trợ</span>
                        <h3 style="font-size: 18px; color: #0284c7; margin: 0;"><?= htmlspecialchars($ticket['ticket_code']) ?></h3>
                    </div>
                    <div>
                        <?php if ($ticket['status'] === 'resolved'): ?>
                            <span style="background: #dcfce7; color: #15803d; padding: 5px 12px; border-radius: 12px; font-weight: 700; font-size: 12.5px;">✓ ĐÃ MỞ KHÓA THÀNH CÔNG</span>
                        <?php elseif ($ticket['status'] === 'in_progress'): ?>
                            <span style="background: #fef3c7; color: #b45309; padding: 5px 12px; border-radius: 12px; font-weight: 700; font-size: 12.5px;">⏳ KTV ĐANG XỬ LÝ</span>
                        <?php else: ?>
                            <span style="background: #e0f2fe; color: #0284c7; padding: 5px 12px; border-radius: 12px; font-weight: 700; font-size: 12.5px;">● MỚI TIẾP NHẬN</span>
                        <?php endif; ?>
                    </div>
                </div>

                <p><strong>Tiêu đề:</strong> <?= htmlspecialchars($ticket['title']) ?></p>
                <p style="margin-top: 6px;"><strong>Chi tiết:</strong> <?= htmlspecialchars($ticket['description']) ?></p>
                <p style="margin-top: 6px;"><strong>Kỹ thuật viên phụ trách:</strong> <?= htmlspecialchars($ticket['agent_name']) ?></p>
                <p style="margin-top: 6px;"><strong>Thời gian gửi:</strong> <?= htmlspecialchars($ticket['created_at']) ?></p>
            </div>
        <?php elseif (!empty($code)): ?>
            <div style="text-align: center; color: #dc2626; padding: 20px; background: #fee2e2; border-radius: 8px;">
                ❌ Không tìm thấy phiếu cứu hộ nào khớp với Mã phiếu <strong><?= htmlspecialchars($code) ?></strong> và Mã PIN tương ứng!
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

