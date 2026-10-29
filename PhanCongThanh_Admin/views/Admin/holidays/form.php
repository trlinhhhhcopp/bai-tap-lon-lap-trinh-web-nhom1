
<?php
$holiday = $holiday ?? null;

function holidayFormEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$isEdit = is_array($holiday);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isEdit ? 'Sửa ngày nghỉ lễ' : 'Thêm ngày nghỉ lễ' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 650px;
            margin: 35px auto;
            padding: 0 16px;
            color: #222;
        }

        h1 { color: #174a7e; }

        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="date"] {
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
        }

        button, .button {
            display: inline-block;
            margin-top: 22px;
            padding: 10px 14px;
            background: #174a7e;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        .button { background: #666; }
    </style>
</head>
<body>

<h1><?= $isEdit ? 'Sửa ngày nghỉ lễ' : 'Thêm ngày nghỉ lễ' ?></h1>

<form method="POST">
    <label for="name">Tên ngày nghỉ lễ</label>
    <input
        type="text"
        id="name"
        name="name"
        maxlength="100"
        required
        value="<?= holidayFormEscape($holiday['name'] ?? '') ?>"
    >

    <label for="start_date">Ngày bắt đầu</label>
    <input
        type="date"
        id="start_date"
        name="start_date"
        required
        value="<?= holidayFormEscape($holiday['start_date'] ?? '') ?>"
    >

    <label for="end_date">Ngày kết thúc</label>
    <input
        type="date"
        id="end_date"
        name="end_date"
        required
        value="<?= holidayFormEscape($holiday['end_date'] ?? '') ?>"
    >

    <label>
        <input
            type="checkbox"
            name="is_recurring"
            value="1"
            <?= (int) ($holiday['is_recurring'] ?? 0) === 1 ? 'checked' : '' ?>
        >
        Lặp lại hằng năm
    </label>

    <button type="submit">
        <?= $isEdit ? 'Lưu thay đổi' : 'Thêm ngày nghỉ lễ' ?>
    </button>

    <a class="button" href="index.php?controller=holiday&action=index">
        Quay lại
    </a>
</form>

</body>
</html>
