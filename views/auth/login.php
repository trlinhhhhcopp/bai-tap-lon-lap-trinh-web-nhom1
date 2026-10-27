<?php require __DIR__ . '/../layouts/header.php'; ?>

<style>
    .auth-container {
        max-width: 460px;
        margin: 30px auto;
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
        margin-top: 15px;
        font-size: 12.5px;
    }
    .auth-links a { color: var(--primary); text-decoration: none; font-weight: 600; }
    .auth-links a:hover { text-decoration: underline; }
    
    /* Quick Demo Box */
    .demo-box {
        margin-top: 20px;
        padding: 15px;
        background: #f8fafc;
        border: 1px dashed var(--border-color);
        border-radius: 8px;
        font-size: 12px;
    }
    .demo-title { font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 5px; }
    .demo-btn-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .btn-demo {
        padding: 5px 8px;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-size: 11px;
        text-align: left;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
    }
    .btn-demo:hover { background: var(--primary-light); border-color: var(--primary); }
</style>

<div class="auth-container">
    <div class="auth-header">
        <h2>Cổng Đăng Nhập Xác Thực</h2>
        <p>Hệ thống Tiếp nhận & Xử lý Hỗ trợ CNTT Trực tuyến</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="/login">
            <div class="form-group">
                <label class="form-label">Mã SV / Mã GV hoặc Email trường:</label>
                <input type="text" name="identifier" id="identifier" class="form-control" required placeholder="VD: 224001729 hoặc email" autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Mật khẩu:</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Nhập mật khẩu (mặc định: 123456)">
            </div>

            <button type="submit" class="btn-login">Đăng Nhập Ngay ➔</button>

            <div class="auth-links">
                <a href="/forgot-password">🔑 Quên mật khẩu (SSPR)?</a>
                <a href="/guest/ticket" style="color: #dc2626;">🆘 Bị khóa tài khoản?</a>
            </div>
        </form>

        <!-- Nút bấm điền nhanh tài khoản kiểm thử cho Thầy Cô & Sinh viên -->
        <div class="demo-box">
            <div class="demo-title">💡 Chọn nhanh tài khoản kiểm thử (Demo 1-Click):</div>
            <div class="demo-btn-grid">
                <button type="button" class="btn-demo" onclick="fillDemo('224001729', '123456')">
                    <strong>🎓 SV Trần Thục Linh</strong>
                    <span>MSV: 224001729</span>
                </button>
                <button type="button" class="btn-demo" onclick="fillDemo('admin@daihocthudo.edu.vn', '123456')">
                    <strong>🏛️ Quản Trị Viên (Admin)</strong>
                    <span>admin@daihocthudo.edu.vn</span>
                </button>
                <button type="button" class="btn-demo" onclick="fillDemo('ktv1@daihocthudo.edu.vn', '123456')">
                    <strong>🛠️ Kỹ Thuật Viên 1</strong>
                    <span>KTV Nguyễn Văn An (SSO)</span>
                </button>
                <button type="button" class="btn-demo" onclick="fillDemo('224001999', '123456')">
                    <strong style="color:red;">🔒 SV Bị Kỷ Luật Khóa</strong>
                    <span>Test chặn khóa cứng</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(username, password) {
    document.getElementById('identifier').value = username;
    document.getElementById('password').value = password;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

