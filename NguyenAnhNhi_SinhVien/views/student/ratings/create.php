<?php
$pageTitle = 'Thêm Đánh giá CSAT';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Helpdesk CNTT – ĐH Thủ Đô</title>
    <style>
        :root {
            --primary:#0284c7; --primary-dark:#0369a1; --primary-light:#e0f2fe;
            --navy:#0f172a; --bg:#f8fafc; --card:#fff; --border:#cbd5e1;
            --text:#1e293b; --muted:#64748b; --err:#b91c1c;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
        .navbar{background:var(--navy);color:#fff;padding:.75rem 1.5rem;display:flex;justify-content:space-between;align-items:center}
        .navbar a{color:#fff;text-decoration:none;font-weight:600}
        .container{max-width:700px;margin:1.5rem auto;padding:0 1rem}
        .card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        h1{font-size:1.35rem;margin-bottom:1rem;color:var(--primary-dark)}
        .btn{display:inline-block;padding:.4rem .9rem;border-radius:6px;border:none;cursor:pointer;font-size:.9rem;text-decoration:none;color:#fff;background:var(--primary)}
        .btn:hover{background:var(--primary-dark)} .btn-secondary{background:var(--muted)}
        .form-group{margin-bottom:1rem}
        label{display:block;font-weight:600;margin-bottom:.3rem;font-size:.9rem}
        input,select,textarea{width:100%;padding:.45rem .7rem;border:1px solid var(--border);border-radius:6px;font-size:.95rem}
        .error-list{color:var(--err);font-size:.875rem;margin-bottom:1rem}
        .error-list li{margin-left:1.2rem}
        .note{background:var(--primary-light);border-left:4px solid var(--primary);padding:.6rem .9rem;font-size:.875rem;margin-bottom:1rem;border-radius:0 6px 6px 0}
    </style>
</head>
<body>
<nav class="navbar">
    <a href="index.php?action=index">Helpdesk CNTT · Đánh giá CSAT</a>
    <span style="color:#94a3b8;font-size:.85rem">Nguyễn Ánh Nhi – ĐH Thủ Đô Hà Nội</span>
</nav>
<div class="container">
    <div class="card">
        <h1>Thêm Đánh giá CSAT</h1>
        <div class="note">Mỗi phiếu chỉ được đánh giá <strong>1 lần duy nhất</strong> (điểm 1–5 sao).</div>

        <?php if (!empty($errors)): ?>
            <ul class="error-list">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="index.php?action=create" novalidate>
            <div class="form-group">
                <label for="ticket_id">Phiếu hỗ trợ <span style="color:#b91c1c">*</span></label>
                <select id="ticket_id" name="ticket_id" required>
                    <option value="">-- Chọn phiếu đã giải quyết --</option>
                    <?php foreach ($tickets as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= ($old['ticket_id'] == $t['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['ticket_code'] . ' – ' . $t['title'] . ' (' . $t['requester_name'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="user_id">Sinh viên đánh giá <span style="color:#b91c1c">*</span></label>
                <select id="user_id" name="user_id" required>
                    <option value="">-- Chọn sinh viên --</option>
                    <?php foreach ($students as $sv): ?>
                        <option value="<?= (int)$sv['id'] ?>" <?= ($old['user_id'] == $sv['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sv['name'] . ' (' . $sv['user_code'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="score">Điểm CSAT (1–5) <span style="color:#b91c1c">*</span></label>
                <input type="number" id="score" name="score" min="1" max="5" value="<?= (int)$old['score'] ?>" required>
            </div>
            <div class="form-group">
                <label for="comment">Nhận xét</label>
                <textarea id="comment" name="comment" rows="3"><?= htmlspecialchars($old['comment']) ?></textarea>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:1.25rem">
                <button type="submit" class="btn">Lưu đánh giá</button>
                <a href="index.php?action=index" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>