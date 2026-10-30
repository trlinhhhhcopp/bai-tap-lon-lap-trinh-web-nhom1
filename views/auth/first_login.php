<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 480px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, #b45309 0%, #d97706 100%); color: #fff; padding: 22px; text-align: center;">
        <h2 style="font-size: 18px; margin-bottom: 4px;">⚠️ Yêu Cầu Đổi Mật Khẩu Lần Đầu</h2>
        <p style="font-size: 12.5px; opacity: 0.9;">Tài khoản mới kích hoạt bắt buộc phải đổi mật khẩu khởi tạo (123456)</p>
    </div>

    <div style="padding: 24px;">
        <div style="background: #fef3c7; border: 1px solid #fde68a; color: #92400e; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px;">
            Xin chào <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['user_code']) ?>). Để đảm bảo an toàn thông tin cá nhân, bạn phải cập nhật mật khẩu mới trước khi vào hệ thống.
        </div>

        <form method="POST" action="/first-login">
            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Mật khẩu mới (tối thiểu 6 ký tự):</label>
                <div style="position:relative;">
                    <input type="password" name="new_password" id="first-login-new-password" required minlength="6" autocomplete="new-password" class="form-control" placeholder="Nhập mật khẩu mới an toàn" autofocus style="width:100%; padding:10px 42px 10px 10px; border:1px solid var(--border-color); border-radius:6px;">
                    <button type="button" data-password-toggle aria-controls="first-login-new-password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden" style="position:absolute; top:50%; right:12px; transform:translateY(-50%); border:0; background:transparent; color:#475569; cursor:pointer; padding:6px;">
                        <svg data-password-show-icon aria-hidden="true" style="width:20px; height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        <svg data-password-hide-icon aria-hidden="true" class="hidden" style="width:20px; height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Xác nhận lại mật khẩu mới:</label>
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="first-login-confirm-password" required minlength="6" autocomplete="new-password" class="form-control" placeholder="Gõ lại mật khẩu mới" style="width:100%; padding:10px 42px 10px 10px; border:1px solid var(--border-color); border-radius:6px;">
                    <button type="button" data-password-toggle aria-controls="first-login-confirm-password" aria-pressed="false" aria-label="Hiện mật khẩu" class="hidden" style="position:absolute; top:50%; right:12px; transform:translateY(-50%); border:0; background:transparent; color:#475569; cursor:pointer; padding:6px;">
                        <svg data-password-show-icon aria-hidden="true" style="width:20px; height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        <svg data-password-hide-icon aria-hidden="true" class="hidden" style="width:20px; height:20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-3.1 3.8M6.2 6.3C3.9 7.8 2.5 12 2.5 12s3.5 6 9.5 6a10.7 10.7 0 0 0 3.3-.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" style="width:100%; padding:12px; background:var(--primary); color:#fff; font-size:14px; font-weight:700; border:none; border-radius:6px; cursor:pointer;">
                Lưu Mật Khẩu & Kích Hoạt Tài Khoản ➔
            </button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
