<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 480px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 22px; text-align: center; border-bottom: 3px solid var(--primary);">
        <h2 style="font-size: 18px; color: #38bdf8; margin-bottom: 4px;">Khôi Phục Mật Khẩu</h2>
        <p style="font-size: 12.5px; color: #94a3b8;">Nhận liên kết đổi mật khẩu bảo mật qua Email trường (Hiệu lực 15 phút)</p>
    </div>

    <div style="padding: 24px;">
        <form method="POST" action="/forgot-password">
            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Email trường cấp (@daihocthudo.edu.vn):</label>
                <input type="email" name="email" required class="form-control" placeholder="VD: 224001729@daihocthudo.edu.vn" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px; box-sizing: border-box;" autofocus>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 5px;">* Vui lòng nhập chính xác địa chỉ Email trường cấp có đuôi @daihocthudo.edu.vn</div>
            </div>

            <button type="submit" style="width:100%; padding:12px; background:var(--primary); color:#fff; font-size:14px; font-weight:700; border:none; border-radius:6px; cursor:pointer;">
                Gửi Liên Kết Đổi Mật Khẩu ➔
            </button>

            <div style="text-align: center; margin-top: 18px; font-size: 13px;">
                <a href="/login" style="color: var(--primary); text-decoration: none; font-weight: 600;">← Quay lại trang Đăng nhập</a>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($simulatedEmail)): ?>
    <!-- Khung Hòm Thư Sinh Viên Mô Phỏng (Đã nhận email chứa link đổi mật khẩu) -->
    <div style="max-width: 540px; margin: 20px auto 35px; background: #fff; border: 1px solid #bae6fd; border-radius: 12px; box-shadow: 0 10px 25px rgba(2,132,199,0.12); overflow: hidden;">
        <div style="background: #f0f9ff; border-bottom: 1px solid #bae6fd; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">📬</span>
                <span style="font-weight: 700; color: #0369a1; font-size: 13.5px;">Hòm Thư Sinh Viên (Webmail Đại Học Thủ Đô)</span>
            </div>
            <span style="font-size: 11.5px; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 10px; font-weight: 600;">1 thư mới</span>
        </div>
        <div style="padding: 18px 22px;">
            <div style="font-size: 12px; color: #64748b; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #e2e8f0; line-height: 1.6;">
                <div><strong>Người gửi:</strong> Trung tâm CNTT - ĐH Thủ Đô Hà Nội &lt;helpdesk@daihocthudo.edu.vn&gt;</div>
                <div><strong>Người nhận:</strong> <?= htmlspecialchars($simulatedEmail['name']) ?> &lt;<?= htmlspecialchars($simulatedEmail['email']) ?>&gt;</div>
                <div><strong>Tiêu đề:</strong> [Helpdesk TUD] Hướng dẫn khôi phục mật khẩu tài khoản sinh viên</div>
                <div><strong>Thời gian nhận:</strong> <?= htmlspecialchars($simulatedEmail['created_time']) ?></div>
            </div>
            
            <div style="font-size: 13px; color: #1e293b; line-height: 1.6;">
                <p style="margin: 0 0 8px;">Xin chào <strong><?= htmlspecialchars($simulatedEmail['name']) ?></strong>,</p>
                <p style="margin: 0 0 14px;">Hệ thống vừa tiếp nhận yêu cầu đặt lại mật khẩu cho tài khoản <strong><?= htmlspecialchars($simulatedEmail['email']) ?></strong>. Bạn hãy bấm vào nút bên dưới để chuyển sang tab đặt lại mật khẩu mới:</p>
                
                <div style="text-align: center; margin: 16px 0;">
                    <a href="<?= htmlspecialchars($simulatedEmail['link']) ?>" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 11px 24px; background: #0284c7; color: #fff; font-size: 13.5px; font-weight: 700; text-decoration: none; border-radius: 6px; box-shadow: 0 4px 10px rgba(2,132,199,0.3); transition: background 0.2s;">
                        🔑 Đặt Lại Mật Khẩu Ngay (Mở Tab Mới) ➔
                    </a>
                </div>

                <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 6px; padding: 10px 12px; font-size: 12px; color: #854d0e; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                    <span>⏳ Đường link có hiệu lực đến <strong><?= htmlspecialchars($simulatedEmail['expire_time']) ?></strong></span>
                    <span style="font-weight: 700; color: #b91c1c;">(Tự động hết hạn sau 15 phút)</span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

