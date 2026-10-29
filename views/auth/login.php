<?php require __DIR__ . '/../layouts/header.php'; ?>

<style>
    .auth-container {
        max-width: 460px;
        margin: 35px auto;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }
    .auth-header {
        background: linear-gradient(135deg, var(--navy-dark) 0%, #1e293b 100%);
        color: #fff;
        padding: 24px;
        text-align: center;
        border-bottom: 3px solid var(--primary);
    }
    .auth-header h2 { font-size: 18px; color: #38bdf8; text-transform: uppercase; margin-bottom: 4px; }
    .auth-header p { font-size: 12.5px; color: #94a3b8; }
    .auth-body { padding: 25px; }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border 0.2s;
    }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary-light); }
    .btn-login {
        width: 100%;
        padding: 11px;
        background: var(--primary);
        color: #fff;
        font-size: 14.5px;
        font-weight: 700;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-login:hover { background: var(--primary-dark); }
    .auth-links {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 18px;
        font-size: 12.5px;
        gap: 10px;
    }
    .auth-links a { color: var(--primary); text-decoration: none; font-weight: 600; }
    .auth-links a:hover { text-decoration: underline; }
    .helper-text { font-size: 11.5px; color: #64748b; margin-top: 4px; display: block; }
</style>

<div class="auth-container">
    <div class="auth-header">
        <h2>Cổng Đăng Nhập Xác Thực</h2>
        <p>Hệ thống Tiếp nhận & Xử lý Hỗ trợ CNTT Trực tuyến</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="/login">
            <div class="form-group">
                <label class="form-label">Mã sinh viên / Mã tài khoản:</label>
                <input type="text" name="identifier" id="identifier" class="form-control" required placeholder="VD: 224001729 hoặc ADM001" autofocus>
                <span class="helper-text">Sinh viên nhập Mã sinh viên (VD: 224001729) hoặc Email trường cấp</span>
            </div>

            <div class="form-group">
                <label class="form-label">Mật khẩu:</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Nhập mật khẩu (mặc định: 123456)">
            </div>

            <button type="submit" class="btn-login">Đăng Nhập Ngay ➔</button>

            <div class="auth-links">
                <a href="/forgot-password">🔑 Quên mật khẩu?</a>
                <a href="/guest/ticket" style="color: #dc2626;">🆘 Báo cứu hộ tài khoản (Bị khóa)?</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
