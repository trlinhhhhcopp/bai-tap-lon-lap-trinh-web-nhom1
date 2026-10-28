<?php
$pageTitle = 'Sửa Phân công giảng dạy';
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
        input[type=text],select{width:100%;padding:.45rem .7rem;border:1px solid var(--border);border-radius:6px;font-size:.95rem}
        input:focus,select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-light)}
        .error-list{color:var(--err);font-size:.875rem;margin-bottom:1rem}
        .error-list li{margin-left:1.2rem}
    </style>
</head>
<body>
<nav class="navbar">
    <a href="index.php?action=index">Helpdesk CNTT · Phân công giảng dạy</a>
    <span style="color:#94a3b8;font-size:.85rem">Lê Hà My – ĐH Thủ Đô Hà Nội</span>
</nav>
<div class="container">
    <div class="card">
        <h1>Sửa Phân công giảng dạy</h1>

        <?php if (!empty($errors)): ?>
            <ul class="error-list">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="index.php?action=edit&id=<?= (int)$assignment['id'] ?>" novalidate>
            <div class="form-group">
                <label for="class_id">Lớp học <span style="color:#b91c1c">*</span></label>
                <select id="class_id" name="class_id" required>
                    <option value="">-- Chọn lớp --</option>
                    <?php foreach ($classes as $cl): ?>
                        <option value="<?= (int)$cl['id'] ?>" <?= ($old['class_id'] == $cl['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cl['class_code'] . ' – ' . $cl['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="course_id">Môn học <span style="color:#b91c1c">*</span></label>
                <select id="course_id" name="course_id" required>
                    <option value="">-- Chọn môn học --</option>
                    <?php foreach ($courses as $co): ?>
                        <option value="<?= (int)$co['id'] ?>" <?= ($old['course_id'] == $co['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($co['course_code'] . ' – ' . $co['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="lecturer_id">Giảng viên phụ trách <span style="color:#b91c1c">*</span></label>
                <select id="lecturer_id" name="lecturer_id" required>
                    <option value="">-- Chọn giảng viên --</option>
                    <?php foreach ($lecturers as $gv): ?>
                        <option value="<?= (int)$gv['id'] ?>" <?= ($old['lecturer_id'] == $gv['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($gv['name'] . ' (' . $gv['user_code'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="semester">Học kỳ <span style="color:#b91c1c">*</span></label>
                <input type="text" id="semester" name="semester" value="<?= htmlspecialchars($old['semester']) ?>" required>
            </div>
            <div class="form-group">
                <label for="academic_year">Năm học <span style="color:#b91c1c">*</span></label>
                <input type="text" id="academic_year" name="academic_year" value="<?= htmlspecialchars($old['academic_year']) ?>" required>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:1.25rem">
                <button type="submit" class="btn">Cập nhật</button>
                <a href="index.php?action=index" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>