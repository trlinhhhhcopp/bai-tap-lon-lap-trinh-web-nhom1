<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Holiday;

class HolidayController extends Controller
{
    public function index(): void
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $type = trim($_GET['type'] ?? '');
        $isRecurring = match ($type) {
            'recurring', '1' => 1,
            'once', '0' => 0,
            default => null,
        };

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $total = Holiday::count($keyword, $isRecurring);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);

        $this->view('admin/holidays/index', [
            'keyword' => $keyword,
            'type' => $type,
            'countAll' => Holiday::count($keyword),
            'countRecurring' => Holiday::count($keyword, 1),
            'countOnce' => Holiday::count($keyword, 0),
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'holidays' => Holiday::findAll($keyword, $page, $perPage, $isRecurring),
            'title' => 'Quản Lý Lịch Nghỉ Lễ & Sự Kiện',
        ]);
    }

    public function create(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->view('admin/holidays/form', ['holiday' => null]);
            return;
        }

        $validation = $this->validateInput($_POST);
        if ($validation['error'] !== null) {
            $this->flash('danger', $validation['error']);
            $this->view('admin/holidays/form', ['holiday' => $_POST]);
            return;
        }

        Holiday::create($validation['data']);
        $this->flash('success', 'Đã thêm ngày nghỉ lễ mới thành công!');
        $this->redirect('/admin/holidays');
    }

    public function edit(): void
    {
        $id = max(0, (int)($_GET['id'] ?? $_POST['id'] ?? 0));
        $holiday = Holiday::findById($id);
        if (!$holiday) {
            $this->flash('danger', 'Không tìm thấy ngày nghỉ lễ cần sửa!');
            $this->redirect('/admin/holidays');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->view('admin/holidays/form', ['holiday' => $holiday]);
            return;
        }

        $validation = $this->validateInput($_POST);
        if ($validation['error'] !== null) {
            $this->flash('danger', $validation['error']);
            $this->view('admin/holidays/form', [
                'holiday' => array_merge($_POST, ['id' => $id]),
            ]);
            return;
        }

        Holiday::update($id, $validation['data']);
        $this->flash('success', 'Đã cập nhật ngày nghỉ lễ thành công!');
        $this->redirect('/admin/holidays');
    }

    public function delete(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            $this->flash('danger', 'Phương thức không được hỗ trợ.');
            $this->redirect('/admin/holidays');
        }

        $id = max(0, (int)($_POST['id'] ?? 0));
        if ($id > 0 && Holiday::delete($id)) {
            $this->flash('success', 'Đã xóa ngày nghỉ lễ thành công!');
        } else {
            $this->flash('danger', 'Không tìm thấy ngày nghỉ lễ cần xóa.');
        }

        $this->redirect('/admin/holidays');
    }

    private function validateInput(array $input): array
    {
        $name = trim($input['name'] ?? '');
        $startDate = trim($input['start_date'] ?? '');
        $endDate = trim($input['end_date'] ?? '');
        $isRecurring = (int)($input['is_recurring'] ?? 0);

        if ($name === '') {
            return ['error' => 'Tên ngày nghỉ lễ không được để trống.', 'data' => []];
        }

        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) ||
            !checkdate((int)substr($startDate, 5, 2), (int)substr($startDate, 8, 2), (int)substr($startDate, 0, 4)) ||
            !checkdate((int)substr($endDate, 5, 2), (int)substr($endDate, 8, 2), (int)substr($endDate, 0, 4)) ||
            $startDate > $endDate
        ) {
            return ['error' => 'Ngày bắt đầu hoặc ngày kết thúc không hợp lệ (Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc).', 'data' => []];
        }

        if ((strtotime($endDate) - strtotime($startDate)) / 86400 > 30) {
            return ['error' => 'Thời gian nghỉ lễ không được vượt quá 30 ngày (tránh làm sai lệch SLA vận hành trường học).', 'data' => []];
        }

        if ($isRecurring === 1 && (int)substr($endDate, 0, 4) - (int)substr($startDate, 0, 4) > 1) {
            return ['error' => 'Ngày nghỉ lễ lặp lại hàng năm chỉ được thiết lập trong phạm vi 1 năm dương lịch.', 'data' => []];
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
            ],
        ];
    }
}
