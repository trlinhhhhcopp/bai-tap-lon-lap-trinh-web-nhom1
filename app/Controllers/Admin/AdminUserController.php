<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Models\MasterData;
use Exception;

class AdminUserController extends Controller {
    public function index(): void {
        $kw      = trim($_GET['kw'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 6;

        $filters    = ['kw' => $kw];
        $totalUsers = User::countActiveUsers($filters);
        $totalPages = max(1, (int)ceil($totalUsers / $perPage));
        if ($page > $totalPages) $page = $totalPages;

        $users   = User::findActiveUsers($filters, $page, $perPage);
        $classes = MasterData::getClasses();

        $this->view('admin/users/index', [
            'users'      => $users,
            'classes'    => $classes,
            'kw'         => $kw,
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'title'      => 'Admin Center - Quản Trị Người Dùng & Phân Quyền',
        ]);
    }

    public function detail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $user = User::findById($id);

        if (!$user) {
            $this->flash('danger', 'Không tìm thấy hồ sơ người dùng!');
            $this->redirect('/admin/users');
        }

        $this->view('admin/users/detail', [
            'user'  => $user,
            'title' => 'Chi Tiết Hồ Sơ: ' . $user['name'],
        ]);
    }

    public function create(): void {
        try {
            $newId = User::create([
                'user_code'       => $_POST['user_code'] ?? '',
                'name'            => $_POST['name'] ?? '',
                'email'           => $_POST['email'] ?? '',
                'role'            => $_POST['role'] ?? 'requester',
                'user_type'       => $_POST['user_type'] ?? 'student',
                'class_id'        => $_POST['class_id'] ?? null,
                'department'      => $_POST['department'] ?? 'Khoa: Toán-CNTT',
                'phone'           => $_POST['phone'] ?? '',
                'password_hash'   => password_hash('123456', PASSWORD_BCRYPT),
                'cohort'          => $_POST['cohort'] ?? 'D24',
                'academic_status' => 'studying',
            ]);

            $this->flash('success', "Thêm người dùng mới #{$newId} thành công! Mật khẩu khởi tạo: 123456.");
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/admin/users');
    }

    public function update(): void {
        $id = (int)($_POST['id'] ?? 0);
        try {
            User::update($id, [
                'name'       => $_POST['name'] ?? '',
                'email'      => $_POST['email'] ?? '',
                'phone'      => $_POST['phone'] ?? '',
                'department' => $_POST['department'] ?? '',
                'class_id'   => $_POST['class_id'] ?? null,
            ]);

            $this->flash('success', "Cập nhật thông tin tài khoản #{$id} thành công!");
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/admin/users');
    }

    public function toggleLock(): void {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $res = User::toggleLock($id);
            $type = ($res['is_active'] == 1) ? 'success' : 'warning';
            $this->flash($type, "[Tài khoản #{$id}] " . $res['message']);
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/admin/users');
    }
}

