<?php
$pageTitle = 'Chi tiết phiếu ' . ($ticket['ticket_code'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Helpdesk CNTT</title>
    <style>
        :root {
            --primary:#0284c7; --primary-dark:#0369a1; --primary-light:#e0f2fe;
            --navy:#0f172a; --bg:#f8fafc; --card:#fff; --border:#cbd5e1;
            --text:#1e293b; --muted:#64748b;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
        .navbar{background:var(--navy);color:#fff;padding:.75rem 1.5rem;display:flex;justify-content:space-between;align-items:center}
        .navbar a{color:#fff;text-decoration:none;font-weight:600;margin-right:1rem}
        .container{max-width:800px;margin:1.5rem auto;padding:0 1rem}
        .card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:1.5rem;margin-bottom:1rem;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        h1{font-size:1.3rem;margin-bottom:.5rem;color:var(--primary-dark)}
        .meta{color:var(--muted);font-size:.9rem;margin-bottom:1rem}
        .btn{display:inline-block;padding:.4rem .9rem;border-radius:6px;border:none;cursor:pointer;font-size:.9rem;text-decoration:none;color:#fff;background:var(--primary)}
        .btn-secondary{background:var(--muted)}
        .label{font-weight:600;color:var(--muted);font-size:.85rem}
        .value{margin-bottom:.8rem}
        .reply{border-left:3px solid var(--primary);padding:.75rem 1rem;margin-bottom:.75rem;background:#f8fafc;border-radius:0 6px 6px 0}
        .reply .who{font-weight:600;font-size:.9rem}
        .reply .time{color:var(--muted);font-size:.8rem}
    </style>
</head>
<body>
<nav class="navbar">
    <div>
        <a href="index.php?action=tickets">Phiếu của tôi</a>
        <a href="index.php?action=ticket_create">Báo sự cố</a>
    </div>
    <span style="color:#94a3b8;font-size:.85rem"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
</nav>
<div class="container">
    <div class="card">
        <h1><?= htmlspecialchars($ticket['ticket_code']) ?> – <?= htmlspecialchars($ticket['title']) ?></h1>
        <div class="meta">
            Trạng thái: <strong><?= htmlspecialchars($ticket['status']) ?></strong> · 
            Ưu tiên: <?= htmlspecialchars($ticket['priority_name']) ?> · 
            Gửi lúc: <?= htmlspecialchars($ticket['created_at']) ?>
        </div>

        <div class="label">Nhóm dịch vụ</div>
        <div class="value"><?= htmlspecialchars($ticket['category_name']) ?></div>

        <div class="label">Hệ thống bị ảnh hưởng</div>
        <div class="value"><?= htmlspecialchars($ticket['system_affected']) ?></div>

        <div class="label">Mô tả chi tiết</div>
        <div class="value" style="white-space:pre-wrap"><?= htmlspecialchars($ticket['description']) ?></div>

        <a href="index.php?action=tickets" class="btn btn-secondary">← Quay lại</a>
    </div>

    <div class="card">
        <h2 style="font-size:1.1rem;margin-bottom:1rem">Lịch sử trao đổi</h2>
        <?php if (empty($replies)): ?>
            <p style="color:var(--muted)">Chưa có phản hồi từ KTV.</p>
        <?php else: ?>
            <?php foreach ($replies as $r): ?>
                <div class="reply">
                    <div class="who"><?= htmlspecialchars($r['user_name']) ?> 
                        <span class="time">(<?= htmlspecialchars($r['created_at']) ?>)</span>
                    </div>
                    <div><?= nl2br(htmlspecialchars($r['message'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>