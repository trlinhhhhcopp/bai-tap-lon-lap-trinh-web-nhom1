
<?php
$keyword = $keyword ?? '';
$holidays = $holidays ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;

function holidayEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý ngày nghỉ lễ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 16px;
            color: #222;
        }

        h1 { color: #174a7e; }

        .toolbar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        input, button, .button {
            padding: 9px 12px;
            font-size: 14px;
        }

        .button, button {
            background: #174a7e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .delete { background: #b42318; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 11px;
            text-align: left;
        }

        th { background: #edf3f8; }

        .pagination {
            margin-top: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .message { margin: 15px 0; }
    </style>
</head>
<body>

<h1>Quản lý ngày nghỉ lễ</h1>
<p>Danh sách ngày nghỉ lễ trong hệ thống Helpdesk.</p>

<div class="toolbar">
    <form method="GET" action="index.php">
        <input type="hidden" name="controller" value="holiday">
        <input type="hidden" name="action" value="index">

        <input
            type="text"
            name="keyword"
            placeholder="Nhập tên ngày nghỉ lễ..."
            value="<?= holidayEscape($keyword) ?>"
        >

        <button type="submit">Tìm kiếm</button>
        <a class="button" href="index.php?controller=holiday&action=index">
            Làm mới
        </a>
    </form>

    <a class="button" href="index.php?controller=holiday&action=create">
        + Thêm ngày nghỉ lễ
    </a>
</div>

<p class="message">
    Tìm thấy <strong><?= (int) $total ?></strong> ngày nghỉ lễ.
</p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên ngày nghỉ lễ</th>
            <th>Ngày bắt đầu</th>
            <th>Ngày kết thúc</th>
            <th>Lặp lại hằng năm</th>
            <th>Thao tác</th>
        </tr>
    </thead>

    <tbody>
    <?php if (empty($holidays)): ?>
        <tr>
            <td colspan="6">Không tìm thấy ngày nghỉ lễ nào.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($holidays as $holiday): ?>
            <tr>
                <td><?= (int) $holiday['id'] ?></td>
                <td><?= holidayEscape($holiday['name']) ?></td>
                <td><?= holidayEscape($holiday['start_date']) ?></td>
                <td><?= holidayEscape($holiday['end_date']) ?></td>
                <td>
                    <?= (int) $holiday['is_recurring'] === 1 ? 'Có' : 'Không' ?>
                </td>
                <td>
                    <a class="button"
                       href="index.php?controller=holiday&action=edit&id=<?= (int) $holiday['id'] ?>">
                        Sửa
                    </a>

                    <form method="POST"
                          action="index.php?controller=holiday&action=delete"
                          style="display:inline"
                          onsubmit="return confirm('Bạn có chắc muốn xóa ngày nghỉ lễ này không?');">
                        <input type="hidden" name="id"
                               value="<?= (int) $holiday['id'] ?>">
                        <button class="delete" type="submit">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?controller=holiday&action=index&page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>">
            Trang trước
        </a>
    <?php endif; ?>

    <span>Trang <?= (int) $page ?> / <?= (int) $totalPages ?></span>

    <?php if ($page < $totalPages): ?>
        <a href="?controller=holiday&action=index&page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>">
            Trang sau
        </a>
    <?php endif; ?>
</div>

</body>
</html>
