<?php
namespace App\Controllers\Agent;

use App\Core\Controller;
use App\Core\Session;

class AgentDashboardController extends Controller {
    public function index(): void {
        $currentUser = Session::user();
        if (!$currentUser || !in_array($currentUser['role'] ?? '', ['agent', 'admin'])) {
            $this->flash('danger', 'Bạn không có quyền truy cập vào Bàn làm việc Kỹ thuật viên!');
            $this->redirect('/login');
            return;
        }

        // Delegate to agent workbench script
        require __DIR__ . '/../../../agent/index.php';
    }
}
