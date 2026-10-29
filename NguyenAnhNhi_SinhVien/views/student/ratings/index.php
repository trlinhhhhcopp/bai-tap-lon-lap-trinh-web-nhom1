<?php
$pageTitle = 'Danh sách Đánh giá CSAT';
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
            --text:#1e293b; --muted:#64748b;
            --ok-bg:#dcfce7; --ok-text:#15803d; --err-bg:#fee2e2; --err-text:#b91c1c;
        }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
        .navbar{background:var(--navy);color:#fff;padding:.75rem 1.5rem;display:flex;justify-content:space-between;align-items:center}
        .navbar a{color:#fff;text-decoration:none;font-weight:600}
        .container{max-width:1100px;margin:1.5rem auto;padding:0 1rem}
        .card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:1.25rem;margin-bottom:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        h1{font-size:1.35rem;margin-bottom:1rem;color:var(--primary-dark)}
        .btn{display:inline-block;padding:.4rem .9rem;border-radius:6px;border:none;cursor:pointer;font-size:.9rem;text-decoration:none;color:#fff;background:var(--primary)}
        .btn:hover{background:var(--primary-dark)}
        .btn-secondary{background:var(--muted)} .btn-danger{background:#dc2626} .btn-sm{padding:.25rem .6rem;font-size:.8rem}
        table{width:100%;border-collapse:collapse;font-size:.9rem}
        th,td{padding:.55rem .75rem;text-align:left;border-bottom:1px solid var(--border)}
        th{background:var(--primary-light);color:var(--primary-dark);font-weight:600}
        tr:hover td{background:#f1f5f9}
        .alert{padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.9rem}
        .alert-success{background:var(--ok-bg);color:var(--ok-text);border:1px solid #86efac}
        .alert-danger{background:var(--err-bg);color:var(--err-text);border:1px solid #fca5a5}
        .search-bar{display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;align-items:center}
        .search-bar input,.search-bar select{padding:.45rem .7rem;border:1px solid var(--border);border-radius:6px}
        .pagination{display:flex;gap:.4rem;margin-top:1rem;flex-wrap:wrap}
        .pagination a,.pagination span{padding:.3rem .65rem;border:1px solid var(--border);border-radius:4px;text-decoration:none;color:var(--text);font-size:.85rem}
        .pagination .current{background:var(--primary);color:#fff;border-color:var(--primary)}
        .actions{display:flex;gap:.35rem;flex-wrap:wrap}
        .text-muted{color:var(--muted);font-size:.85rem}
        .note{background:var(--primary-light);border-left:4px solid var(--primary);padding:.6rem .9rem;font-size:.875rem;margin-bottom:1rem;border-radius:0 6px 6px 0}
        .stars{color:#f59e0b;font-weight:600}
    </style>
</head>
<body>
<nav class="navbar">
    <a href="index.php?action=index">Helpdesk CNTT · Đánh giá CSAT</a>
    <span style="color:#94a3b8;font-size:.85rem">Nguyễn Ánh Nhi – ĐH Thủ Đô Hà Nội</span>
</nav>
<div class="container">
    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem">
            <h1>Đánh giá CSAT (ticket_ratings)</h1>
            <a href="index.php?action=create" class="btn">+ Thêm đánh giá</a>
        </div>

        <div class="note">
            <strong>Quy tắc:</strong> Mỗi phiếu chỉ được đánh giá <strong>1 lần duy nhất</strong> (1–5 sao).
        </div>

        <!-- Bộ lọc -->
        <form method="get" action="index.php" class="search-bar">
            <input type="hidden" name="action" value="index">
            <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm mã phiếu, tiêu đề, tên SV...">
            
            <select name="score">
                <option value="">-- Tất cả điểm --</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>" <?= (isset($_GET['score']) && (int)$_GET['score'] === $i) ? 'selected' : '' ?>>
                        <?= $i ?> sao
                    </option>
                <?php endfor; ?>
            </select>

            <select name="student_id">
                <option value="">-- Tất cả sinh viên --</option>
                <?php foreach ($students as $sv): ?>
                    <option value="<?= (int)$sv['id'] ?>" <?= (isset($_GET['student_id']) && (int)$_GET['student_id'] === (int)$sv['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sv['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn">Lọc</button>
            <a href="index.php?action=index" class="btn btn-secondary">Xóa lọc</a>
        </form>

        <p class="text-muted" style="margin-bottom:.5rem">
            Tìm thấy <strong><?= $total ?></strong> đánh giá · Trang <?= $page ?>/<?= $totalPages ?>
        </p>

        <?php if (empty($ratings)): ?>
            <p class="text-muted">Không có dữ liệu.</p>
        <?php else: ?>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Tiêu đề</th>
                            <th>Sinh viên</th>
                            <th>Điểm</th>
                            <th>Nhận xét</th>
                            <th>Thời gian</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ratings as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['ticket_code']) ?></strong></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['student_name']) ?><br>
                                    <span class="text-muted"><?= htmlspecialchars($row['student_email']) ?></span>
                                </td>
                                <td class="stars"><?= (int)$row['score'] ?> ★</td>
                                <td><?= htmlspecialchars($row['comment'] ?? '—') ?></td>
                                <td class="text-muted"><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="index.php?action=edit&id=<?= (int)$row['id'] ?>" class="btn btn-sm">Sửa</a>
                                        <form method="post" action="index.php?action=delete" style="display:inline">
                                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Xóa đánh giá này?');">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="index.php?action=index&page=<?= $i ?>&q=<?= urlencode($keyword) ?>&score=<?= $score ?? '' ?>&student_id=<?= $studentId ?? '' ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>