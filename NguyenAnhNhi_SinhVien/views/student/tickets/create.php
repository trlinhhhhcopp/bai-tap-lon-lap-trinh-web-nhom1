<?php
$pageTitle = 'Báo sự cố CNTT';
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
        .navbar a{color:#fff;text-decoration:none;font-weight:600;margin-right:1rem}
        .container{max-width:720px;margin:1.5rem auto;padding:0 1rem}
        .card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        h1{font-size:1.35rem;margin-bottom:1rem;color:var(--primary-dark)}
        .btn{display:inline-block;padding:.45rem 1rem;border-radius:6px;border:none;cursor:pointer;font-size:.9rem;text-decoration:none;color:#fff;background:var(--primary)}
        .btn:hover{background:var(--primary-dark)} .btn-secondary{background:var(--muted)}
        .form-group{margin-bottom:1rem}
        label{display:block;font-weight:600;margin-bottom:.3rem;font-size:.9rem}
        input,select,textarea{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:6px;font-size:.95rem}
        textarea{min-height:110px;resize:vertical}
        .error-list{color:var(--err);font-size:.875rem;margin-bottom:1rem}
        .error-list li{margin-left:1.2rem}
        .note{background:var(--primary-light);border-left:4px solid var(--primary);padding:.7rem 1rem;font-size:.875rem;margin-bottom:1.25rem;border-radius:0 6px 6px 0}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        @media(max-width:600px){.row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<nav class="navbar">
    <div>
        <a href="index.php?action=tickets">Phiếu của tôi</a>
        <a href="index.php?action=ticket_create">Báo sự cố</a>
        <a href="index.php?action=ratings">Đánh giá CSAT</a>
    </div>
    <span style="color:#94a3b8;font-size:.85rem">Nguyễn Ánh Nhi – Sinh viên</span>
</nav>
<div class="container">
    <div class="card">
        <h1>Báo sự cố CNTT</h1>

        <div class="note">
            <strong>Lưu ý:</strong> Bạn <strong>không cần chọn mức ưu tiên</strong>. 
            Hệ thống sẽ tự động gán mức ưu tiên và hạn xử lý theo nhóm dịch vụ bạn chọn.
        </div>

        <?php if (!empty($errors)): ?>
            <ul class="error-list">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="index.php?action=ticket_store" novalidate>
            <div class="form-group">
                <label for="category_id">Nhóm dịch vụ gặp sự cố <span style="color:#b91c1c">*</span></label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Chọn nhóm dịch vụ --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($old['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?> 
                            (SLA <?= $cat['sla_hours'] ?>h – <?= $cat['default_priority_code'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="form-group">
                    <label for="class_id">Lớp học (nếu liên quan)</label>
                    <select id="class_id" name="class_id">
                        <option value="">-- Không chọn --</option>
                        <?php foreach ($classes as $cl): ?>
                            <option value="<?= (int)$cl['id'] ?>" <?= ($old['class_id'] == $cl['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cl['class_code'] . ' – ' . $cl['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="course_id">Môn học (nếu liên quan)</label>
                    <select id="course_id" name="course_id">
                        <option value="">-- Không chọn --</option>
                        <?php foreach ($courses as $co): ?>
                            <option value="<?= (int)$co['id'] ?>" <?= ($old['course_id'] == $co['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($co['course_code'] . ' – ' . $co['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="system_affected">Hệ thống bị ảnh hưởng <span style="color:#b91c1c">*</span></label>
                <select id="system_affected" name="system_affected" required>
                    <option value="">-- Chọn hệ thống --</option>
                    <option value="SSO" <?= ($old['system_affected'] ?? '') === 'SSO' ? 'selected' : '' ?>>Tài khoản & SSO</option>
                    <option value="Email" <?= ($old['system_affected'] ?? '') === 'Email' ? 'selected' : '' ?>>Email & Microsoft 365</option>
                    <option value="QLĐT" <?= ($old['system_affected'] ?? '') === 'QLĐT' ? 'selected' : '' ?>>Cổng Quản lý Đào tạo</option>
                    <option value="Canvas LMS" <?= ($old['system_affected'] ?? '') === 'Canvas LMS' ? 'selected' : '' ?>>Canvas LMS / MS Teams</option>
                    <option value="Wi-Fi" <?= ($old['system_affected'] ?? '') === 'Wi-Fi' ? 'selected' : '' ?>>Wi-Fi / VPN</option>
                    <option value="Khác" <?= ($old['system_affected'] ?? '') === 'Khác' ? 'selected' : '' ?>>Khác</option>
                </select>
            </div>

            <div class="form-group">
                <label for="title">Tiêu đề sự cố <span style="color:#b91c1c">*</span></label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" 
                       placeholder="Ví dụ: Không đăng nhập được Canvas" required>
            </div>

            <div class="form-group">
                <label for="description">Mô tả chi tiết <span style="color:#b91c1c">*</span></label>
                <textarea id="description" name="description" 
                          placeholder="Mô tả rõ hiện tượng lỗi, thời điểm xảy ra, ảnh chụp màn hình (nếu có)..." required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;gap:.5rem;margin-top:1.5rem">
                <button type="submit" class="btn">Gửi phiếu báo sự cố</button>
                <a href="index.php?action=tickets" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>