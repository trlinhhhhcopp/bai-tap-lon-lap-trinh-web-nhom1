<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 650px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); color: #fff; padding: 22px; text-align: center;">
        <h2 style="font-size: 19px; margin-bottom: 4px;">🆘 Form Cứu Hộ Mở Khóa Tài Khoản (Guest Ticket)</h2>
        <p style="font-size: 13px; opacity: 0.9;">Dành riêng cho sinh viên bị khóa tài khoản không thể đăng nhập</p>
    </div>

    <div style="padding: 26px;">
        <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
            ⚠️ <strong>Lưu ý:</strong> Sau khi gửi đơn, hệ thống sẽ cấp cho bạn một <strong>Mã phiếu</strong> và <strong>Mã PIN 4 số</strong>. Bạn không cần đăng nhập vẫn có thể tra cứu xem KTV đã mở khóa tài khoản cho mình hay chưa!
        </div>

        <form method="POST" action="/guest/ticket">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Mã Sinh Viên (*):</label>
                    <input type="text" name="user_code" required class="form-control" placeholder="VD: 224001729" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Họ và tên (*):</label>
                    <input type="text" name="name" required class="form-control" placeholder="VD: Trần Thục Linh" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:6px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Email cá nhân nhận kết quả (Gmail...):</label>
                    <input type="email" name="contact_email" required class="form-control" placeholder="VD: linh.personal@gmail.com" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Số điện thoại liên hệ:</label>
                    <input type="text" name="phone" required class="form-control" placeholder="VD: 0901234567" style="width:100%; padding:9px; border:1px solid var(--border-color); border-radius:6px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Mô tả hiện tượng lỗi / Lý do bị khóa:</label>
                <textarea name="description" rows="4" required class="form-control" placeholder="VD: Em nhập sai mật khẩu quá 5 lần trên cổng đào tạo làm tài khoản bị khóa cứng, nhờ thầy cô và anh chị KTV mở lại tài khoản giúp em..." style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px; resize:vertical;"></textarea>
            </div>

            <button type="submit" style="width:100%; padding:12px; background: #dc2626; color:#fff; font-size:14.5px; font-weight:700; border:none; border-radius:6px; cursor:pointer;">
                Gửi Yêu Cầu Cứu Hộ Ngay ➔
            </button>

            <div style="text-align: center; margin-top: 18px; font-size: 13px;">
                Đã có mã phiếu và mã PIN? <a href="/guest/lookup" style="color: var(--primary); text-decoration: none; font-weight: 600;">Bấm vào đây để tra cứu tiến độ</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

