<?php
$pageTitle = 'Phiếu của tôi';
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
        .navbar a{color:#fff;text-decoration:none;font-weight:600;margin-right:1rem}
        .container{max-width:1100px;margin:1.5rem auto;padding:0 1rem}
        .card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        h1{font-size:1.35rem;margin-bottom:1rem;color:var(--primary-dark)}
        .btn{display:inline-block;padding:.4rem .9rem;border-radius:6px;border:none;cursor:pointer;font-size:.9rem;text-decoration:none;color:#fff;background:var(--primary)}
        .btn:hover{background:var(--primary-dark)} .btn-sm{padding:.25rem .6rem;font-size:.8rem}
        table{width:100%;border-collapse:collapse;font-size:.9rem}
        th,td{padding:.55rem .75rem;text-align:left;border-bottom:1px solid var(--border)}
        th{background:var(--primary-light);color:var(--primary-dark);font-weight:600}
        tr:hover td{background:#f1f5f9}
        .alert{padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.9rem}
        .alert-success{background:var(--ok-bg);color:var(--ok-text)}
        .alert-danger{background:var(--err-bg);color:var(--err-text)}
        .badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.75rem;font-weight:600}
        .badge-new{background:#e0f2fe;color:#0284c7}
        .badge-progress{background:#fef3c7;color:#b45309}
        .badge-resolved{background:#dcfce7;color:#15803d}
        .badge-closed{background:#f1f5f9;color:#475569}
        .text-muted{color:var(--muted);font-size:.85rem}
        .search-bar{display:flex;gap:.5rem;margin-bottom:1rem}
        .search-bar input{flex:1;padding:.45rem .7rem;border:1px solid var(--border);border-radius:6px}
    </style>
</head>
<body>
<nav class="navbar">
    <div>
        <a href="index.php?action=tickets">Phiếu của tôi</a>
        <a href="index.php?action=ticket_create">Báo sự cố</a>
        <a href="index.php?action=ratings">Đánh giá CSAT</a>
    </div>
    <span style="color:#94a3b8;font-size:.85rem"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
</nav>
<div class="container">
    <?php if (!empty($flash)): ?>
        <div data-auto-dismiss="4500" class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem">
            <h1>Phiếu hỗ trợ của tôi</h1>
            <a href="index.php?action=ticket_create" class="btn">+ Báo sự cố mới</a>
        </div>

        <form method="get" action="index.php" class="search-bar">
            <input type="hidden" name="action" value="tickets">
            <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm theo mã phiếu, tiêu đề...">
            <button type="submit" class="btn">Tìm</button>
        </form>

        <p class="text-muted">Tìm thấy <strong><?= $total ?></strong> phiếu · Trang <?= $page ?>/<?= $totalPages ?></p>

        <?php if (empty($tickets)): ?>
            <p class="text-muted">Bạn chưa có phiếu nào.</p>
        <?php else: ?>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Tiêu đề</th>
                            <th>Nhóm dịch vụ</th>
                            <th>Ưu tiên</th>
                            <th>Trạng thái</th>
                            <th>Ngày gửi</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['ticket_code']) ?></strong></td>
                                <td><?= htmlspecialchars($t['title']) ?></td>
                                <td><?= htmlspecialchars($t['category_name']) ?></td>
                                <td><?= htmlspecialchars($t['priority_name']) ?></td>
                                <td>
                                    <?php
                                    $badge = match($t['status']) {
                                        'new' => 'badge-new',
                                        'in_progress', 'waiting_requester' => 'badge-progress',
                                        'resolved' => 'badge-resolved',
                                        default => 'badge-closed'
                                    };
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($t['status']) ?></span>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($t['created_at']) ?></td>
                                <td>
                                    <a href="index.php?action=ticket_show&id=<?= (int)$t['id'] ?>" class="btn btn-sm">Xem</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>