<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 480px; margin: 30px auto; background: #fff; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
    <div style="background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%); color: #fff; padding: 22px; text-align: center; border-bottom: 3px solid var(--primary);">
        <h2 style="font-size: 18px; color: #38bdf8; margin-bottom: 4px;">Khôi Phục Mật Khẩu (SSPR)</h2>
        <p style="font-size: 12.5px; color: #94a3b8;">Cấp mã xác thực tự động qua Email trường (Hiệu lực 15 phút)</p>
    </div>

    <div style="padding: 24px;">
        <form method="POST" action="/forgot-password">
            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Email trường học của bạn:</label>
                <input type="email" name="email" required class="form-control" placeholder="VD: 224001729@daihocthudo.edu.vn" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px;" autofocus>
            </div>

            <button type="submit" style="width:100%; padding:12px; background:var(--primary); color:#fff; font-size:14px; font-weight:700; border:none; border-radius:6px; cursor:pointer;">
                Gửi Mã Khôi Phục 15 Phút ➔
            </button>

            <div style="text-align: center; margin-top: 18px; font-size: 13px;">
                <a href="/login" style="color: var(--primary); text-decoration: none; font-weight: 600;">← Quay lại trang Đăng nhập</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

