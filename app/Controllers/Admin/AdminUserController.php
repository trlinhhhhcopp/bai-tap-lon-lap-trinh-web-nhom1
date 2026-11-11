<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Models\MasterData;
use Exception;

class AdminUserController extends Controller {
    public function index(): void {
        $kw      = trim($_GET['kw'] ?? '');
        $type    = trim($_GET['type'] ?? '');
        if (!in_array($type, ['student', 'lecturer', 'agent', 'admin'], true)) {
            $type = '';
        }
        $classId = (int)($_GET['class_id'] ?? 0);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 8;

        $filters    = ['kw' => $kw, 'type' => $type, 'class_id' => $classId];
        $totalUsers = User::countActiveUsers($filters);
        $totalPages = max(1, (int)ceil($totalUsers / $perPage));
        if ($page > $totalPages) $page = $totalPages;

        $users   = User::findActiveUsers($filters, $page, $perPage);
        $classes = MasterData::getClasses();
        $counts  = User::countCountsByType();

        $this->view('admin/users/index', [
            'users'      => $users,
            'classes'    => $classes,
            'kw'         => $kw,
            'type'       => $type,
            'classId'    => $classId,
            'counts'     => $counts,
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'title'      => 'Quản Trị Tài Khoản & Phân Quyền - Admin Center',
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
            $role = $_POST['role'] ?? 'requester';
            $userType = 'student';
            if ($role === 'agent') {
                $userType = 'staff';
            } elseif ($role === 'lecturer') {
                $role = 'requester';
                $userType = 'lecturer';
            } elseif ($role === 'admin') {
                $userType = 'staff';
            }

            $rawClassId = $_POST['class_id'] ?? '';
            $classId = null;
            $isActive = 1;
            if ($rawClassId === 'retired' || $rawClassId === 'resigned') {
                $isActive = 0;
            } elseif ($rawClassId === 'working' || $rawClassId === 'active') {
                $isActive = 1;
            } elseif (!empty($rawClassId) && is_numeric($rawClassId)) {
                $classId = (int)$rawClassId;
            }

            $newId = User::create([
                'user_code'       => $_POST['user_code'] ?? '',
                'name'            => $_POST['name'] ?? '',
                'email'           => $_POST['email'] ?? '',
                'role'            => $role,
                'user_type'       => $userType,
                'class_id'        => $classId,
                'department'      => $_POST['department'] ?? 'Khoa: Toán-CNTT',
                'phone'           => $_POST['phone'] ?? '',
                'password_hash'   => password_hash('123456', PASSWORD_BCRYPT),
                'cohort'          => $_POST['cohort'] ?? 'D24',
                'academic_status' => 'studying',
                'is_active'       => $isActive,
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
            $rawClassId = $_POST['class_id'] ?? '';
            $classId = null;
            if ($rawClassId === 'retired' || $rawClassId === 'resigned') {
                $classId = null;
                Database::getConnection()->prepare("UPDATE users SET is_active = 0, lock_reason = 'resigned' WHERE id = :id")->execute([':id' => $id]);
            } elseif ($rawClassId === 'working' || $rawClassId === 'active') {
                $classId = null;
                Database::getConnection()->prepare("UPDATE users SET is_active = 1, lock_reason = 'none' WHERE id = :id")->execute([':id' => $id]);
            } elseif (!empty($rawClassId) && is_numeric($rawClassId)) {
                $classId = (int)$rawClassId;
            }

            User::update($id, [
                'name'       => $_POST['name'] ?? '',
                'email'      => $_POST['email'] ?? '',
                'phone'      => $_POST['phone'] ?? '',
                'department' => $_POST['department'] ?? '',
                'class_id'   => $classId,
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

    public function toggleDuty(): void {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $user = User::findById($id);
            if (!$user || $user['role'] !== 'agent') {
                $this->flash('danger', 'Chỉ kỹ thuật viên (KTV) mới có trạng thái ca trực!');
                $this->redirect('/admin/users');
                return;
            }
            $newDuty = ($user['is_on_duty'] == 1) ? 0 : 1;
            db()->prepare("UPDATE users SET is_on_duty = :duty WHERE id = :id")->execute([':duty' => $newDuty, ':id' => $id]);
            $msg = ($newDuty == 1) ? "KTV {$user['name']} đã BẬT ca trực (sẵn sàng tiếp nhận vé)!" : "KTV {$user['name']} đã chuyển sang trạng thái NGHỈ CA / BÁO ỐM!";
            $this->flash('info', $msg);
        } catch (Exception $e) {
            $this->flash('danger', 'Lỗi: ' . $e->getMessage());
        }
        $this->redirect('/admin/users');
    }
}
