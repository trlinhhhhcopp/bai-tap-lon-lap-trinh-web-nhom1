<?php

require_once __DIR__ . '/../../Models/Holiday.php';

class HolidayController
{
    public function index(): void
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;

        $total = Holiday::count($keyword);
        $totalPages = max(1, (int) ceil($total / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $holidays = Holiday::findAll($keyword, $page, $perPage);

        require __DIR__ . '/../../../views/admin/holidays/index.php';
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validation = $this->validateInput($_POST);
            if ($validation['error']) {
                set_flash('error', $validation['error']);
                $holiday = $_POST;
                require __DIR__ . '/../../../views/admin/holidays/form.php';
                return;
            }

            Holiday::create($validation['data']);
            set_flash('success', 'Đã thêm ngày nghỉ lễ mới thành công!');
            header('Location: holidays.php');
            exit;
        }

        $holiday = null;
        require __DIR__ . '/../../../views/admin/holidays/form.php';
    }

    public function edit(): void
    {
        $id = max(0, (int) ($_GET['id'] ?? 0));
        $holiday = Holiday::findById($id);

        if (!$holiday) {
            set_flash('error', 'Không tìm thấy ngày nghỉ lễ cần sửa!');
            header('Location: holidays.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validation = $this->validateInput($_POST);
            if ($validation['error']) {
                set_flash('error', $validation['error']);
                $holiday = array_merge($_POST, ['id' => $id]);
                require __DIR__ . '/../../../views/admin/holidays/form.php';
                return;
            }

            Holiday::update($id, $validation['data']);
            set_flash('success', 'Đã cập nhật ngày nghỉ lễ thành công!');
            header('Location: holidays.php');
            exit;
        }

        require __DIR__ . '/../../../views/admin/holidays/form.php';
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            set_flash('error', 'Phương thức không được hỗ trợ.');
            header('Location: holidays.php');
            exit;
        }

        $id = max(0, (int) ($_POST['id'] ?? 0));

        if ($id > 0) {
            Holiday::delete($id);
            set_flash('success', 'Đã xóa ngày nghỉ lễ thành công!');
        }

        header('Location: holidays.php');
        exit;
    }

    private function validateInput(array $input): array
    {
        $name = trim($input['name'] ?? '');
        $startDate = trim($input['start_date'] ?? '');
        $endDate = trim($input['end_date'] ?? '');
        $isRecurring = (int) ($input['is_recurring'] ?? 0);

        if ($name === '') {
            return ['error' => 'Tên ngày nghỉ lễ không được để trống.', 'data' => []];
        }

        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) ||
            !checkdate((int) substr($startDate, 5, 2), (int) substr($startDate, 8, 2), (int) substr($startDate, 0, 4)) ||
            !checkdate((int) substr($endDate, 5, 2), (int) substr($endDate, 8, 2), (int) substr($endDate, 0, 4)) ||
            $startDate > $endDate
        ) {
            return ['error' => 'Ngày bắt đầu hoặc ngày kết thúc không hợp lệ (Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc).', 'data' => []];
        }

        if (!in_array($isRecurring, [0, 1], true)) {
            return ['error' => 'Giá trị lặp lại không hợp lệ.', 'data' => []];
        }

        return [
            'error' => null,
            'data' => [
                'name' => $name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_recurring' => $isRecurring,
            ]
        ];
    }
}
