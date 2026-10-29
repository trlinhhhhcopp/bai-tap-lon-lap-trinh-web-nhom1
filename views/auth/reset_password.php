<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 480px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 22px; text-align: center; border-bottom: 3px solid var(--primary);">
        <h2 style="font-size: 18px; color: #38bdf8; margin-bottom: 4px;">Đặt Lại Mật Khẩu Mới</h2>
        <p style="font-size: 12.5px; color: #94a3b8;">Nhập mật khẩu mới để bảo vệ an toàn cho tài khoản của bạn</p>
    </div>

    <div style="padding: 24px;">
        <form method="POST" action="/reset-password">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Mật khẩu mới (tối thiểu 6 ký tự):</label>
                <input type="password" name="new_password" required minlength="6" class="form-control" placeholder="Nhập mật khẩu mới" autofocus style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Xác nhận lại mật khẩu mới:</label>
                <input type="password" name="confirm_password" required minlength="6" class="form-control" placeholder="Gõ lại mật khẩu mới" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px;">
            </div>

            <button type="submit" style="width:100%; padding:12px; background:var(--primary); color:#fff; font-size:14px; font-weight:700; border:none; border-radius:6px; cursor:pointer;">
                Xác Nhận Đổi Mật Khẩu ➔
            </button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

