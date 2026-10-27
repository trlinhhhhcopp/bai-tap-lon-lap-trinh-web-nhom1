<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Helpdesk CNTT - Đại Học Thủ Đô Hà Nội') ?></title>
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-light: #e0f2fe;
            --navy-dark: #0f172a;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #cbd5e1;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --success: #15803d;
            --success-bg: #dcfce7;
            --danger: #b91c1c;
            --danger-bg: #fee2e2;
            --warning: #b45309;
            --warning-bg: #fef3c7;
            --info: #0369a1;
            --info-bg: #e0f2fe;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }
        .brand-logo {
            width: 38px;
            height: 38px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            color: #fff;
        }
        .brand-text h1 { font-size: 15px; color: #38bdf8; font-weight: 700; text-transform: uppercase; }
        .brand-text p { font-size: 11px; color: #94a3b8; }
        .navbar-menu { display: flex; align-items: center; gap: 14px; list-style: none; }
        .navbar-menu a {
            color: #e2e8f0;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .navbar-menu a:hover { background: rgba(255,255,255,0.1); color: #38bdf8; }
        .user-pill {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-pill strong { color: #38bdf8; }
        .container { max-width: 1200px; margin: 24px auto; padding: 0 16px; flex: 1; width: 100%; }
        
        .alert {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 5px solid;
            animation: fadeIn 0.3s ease;
        }
        .alert-success { background: var(--success-bg); color: var(--success); border-color: var(--success); }
        .alert-danger  { background: var(--danger-bg);  color: var(--danger);  border-color: var(--danger); }
        .alert-warning { background: var(--warning-bg); color: var(--warning); border-color: var(--warning); }
        .alert-info    { background: var(--info-bg);    color: var(--info);    border-color: var(--info); }

        .footer {
            background: #0f172a;
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
            padding: 16px;
            border-top: 1px solid #1e293b;
            margin-top: auto;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="/" class="navbar-brand">
        <div class="brand-logo">TUD</div>
        <div class="brand-text">
            <h1>Helpdesk CNTT Online</h1>
            <p>Trường Đại học Thủ Đô Hà Nội · Nhóm 1</p>
        </div>
    </a>

    <div class="navbar-menu">
        <?php if ($currentUser): ?>
            <div class="user-pill">
                <span>👤 <?= htmlspecialchars($currentUser['name']) ?></span>
                <span style="opacity: 0.6;">|</span>
                <span style="color: #cbd5e1;"><?= strtoupper($currentUser['role']) ?></span>
            </div>

            <?php if ($currentUser['role'] === 'admin'): ?>
                <a href="/admin/users">👥 Quản trị Người dùng</a>
            <?php else: ?>
                <a href="/student/dashboard">📋 Phiếu của tôi</a>
            <?php endif; ?>

            <a href="/logout" style="color: #f87171;" onclick="return confirm('Bạn có chắc muốn đăng xuất?');">🚪 Đăng xuất</a>
        <?php else: ?>
            <a href="/guest/ticket">🆘 Báo khóa tài khoản</a>
            <a href="/guest/lookup">🔍 Tra cứu PIN</a>
            <a href="/login" style="background: var(--primary); color: #fff;">🔑 Đăng nhập</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <span>📢</span>
            <div><?= $flash['message'] ?></div>
        </div>
    <?php endif; ?>

    <!-- Nội dung view con sẽ chèn vào đây -->

