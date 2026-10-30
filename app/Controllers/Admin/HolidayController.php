
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
            $data = $this->validateInput($_POST);
            Holiday::create($data);

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
            http_response_code(404);
            exit('Không tìm thấy ngày nghỉ lễ.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->validateInput($_POST);
            Holiday::update($id, $data);

            header('Location: holidays.php');
            exit;
        }

        require __DIR__ . '/../../../views/admin/holidays/form.php';
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Phương thức không được hỗ trợ.');
        }

        $id = max(0, (int) ($_POST['id'] ?? 0));

        if ($id > 0) {
            Holiday::delete($id);
        }

        header('Location: holidays.php');
        exit;
    }

    private function validateInput(array $input): array
    {
        $name = trim($input['name'] ?? '');
        $startDate = $input['start_date'] ?? '';
        $endDate = $input['end_date'] ?? '';
        $isRecurring = (int) ($input['is_recurring'] ?? 0);

        if ($name === '') {
            exit('Tên ngày nghỉ lễ không được để trống.');
        }

        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) ||
            !checkdate((int) substr($startDate, 5, 2), (int) substr($startDate, 8, 2), (int) substr($startDate, 0, 4)) ||
            !checkdate((int) substr($endDate, 5, 2), (int) substr($endDate, 8, 2), (int) substr($endDate, 0, 4)) ||
            $startDate > $endDate
        ) {
            exit('Ngày bắt đầu hoặc ngày kết thúc không hợp lệ.');
        }

        if (!in_array($isRecurring, [0, 1], true)) {
            exit('Giá trị lặp lại không hợp lệ.');
        }

        return [
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_recurring' => $isRecurring,
        ];
    }
}
