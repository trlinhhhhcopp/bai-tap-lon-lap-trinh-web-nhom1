<?php
/**
 * Controller CRUD Đánh giá CSAT
 * Phần của: Nguyễn Ánh Nhi
 */
require_once __DIR__ . '/../../Models/Rating.php';

class RatingController
{
    private const PER_PAGE = 8;

    public function index(): void
    {
        $keyword   = trim($_GET['q'] ?? '');
        $score       = isset($_GET['score']) && $_GET['score'] !== '' ? (int)$_GET['score'] : null;
        $scoreFilter = $score;
        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);
        $userRole = $_SESSION['user']['role'] ?? '';
        $userType = $_SESSION['user']['user_type'] ?? '';
        $isStudent = ($userRole === 'requester' || $userType === 'student');
        $isLecturer = ($userType === 'lecturer');
        $isRequesterRole = ($isStudent || $isLecturer);

        // Sinh viên và Giảng viên xem đánh giá của chính mình
        $filterUserId = $isRequesterRole ? $currentUserId : (isset($_GET['student_id']) && $_GET['student_id'] !== '' ? (int)$_GET['student_id'] : null);
        $page      = max(1, (int)($_GET['page'] ?? 1));

        $total      = Rating::count($keyword, $score, $filterUserId);
        $totalPages = max(1, (int)ceil($total / self::PER_PAGE));
        $page       = min($page, $totalPages);

        $ratings  = Rating::findAll($keyword, $page, self::PER_PAGE, $score, $filterUserId);
        $students = Rating::getAllStudents();

        $pendingRatings = [];
        if ($isRequesterRole && $currentUserId > 0) {
            $pendingRatings = Rating::getPendingEvaluationTickets($currentUserId);
        }

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../../../views/student/ratings/index.php';
    }

    public function create(): void
    {
        $errors = [];
        $old = ['ticket_id' => '', 'user_id' => '', 'score' => 5, 'comment' => ''];
        $userRole = $_SESSION['user']['role'] ?? '';
        $userType = $_SESSION['user']['user_type'] ?? '';
        $isStudent = ($userRole === 'requester' || $userType === 'student');
        $myId = $isStudent ? (int)$_SESSION['user']['id'] : null;
        if ($isStudent) {
            $old['user_id'] = $myId;
        }
        $tickets  = Rating::getAvailableTickets($myId);
        $students = $isStudent ? [$_SESSION['user']] : Rating::getAllStudents();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->sanitize($_POST);
            $errors = $this->validate($old);

            if (empty($errors)) {
                try {
                    Rating::create($old);

                    // Bắn thông báo thời gian thực tới KTV phụ trách
                    try {
                        require_once __DIR__ . '/../../Services/NotificationService.php';
                        $tStmt = db()->prepare("SELECT t.id, t.ticket_code, t.assigned_to, u.name as requester_name FROM tickets t LEFT JOIN users u ON u.id = t.requester_id WHERE t.id = :tid LIMIT 1");
                        $tStmt->execute([':tid' => $old['ticket_id']]);
                        $tData = $tStmt->fetch(PDO::FETCH_ASSOC);
                        if ($tData && !empty($tData['assigned_to'])) {
                            $ktvId = (int)$tData['assigned_to'];
                            $reqName = $tData['requester_name'] ?: 'Sinh viên';
                            $scoreStars = str_repeat('⭐', (int)$old['score']);
                            NotificationService::send(
                                $ktvId,
                                "{$scoreStars} Đánh giá CSAT mới từ người dùng",
                                "{$reqName} vừa đánh giá {$old['score']} sao cho phiếu #{$tData['ticket_code']}: \"{$old['comment']}\". Cảm ơn bạn đã tận tâm hỗ trợ!",
                                "agent/ratings.php?agent_id={$ktvId}",
                                'rating',
                                'agent'
                            );
                        }
                    } catch (\Throwable $e) {}

                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Thêm đánh giá CSAT thành công.'];
                    header('Location: ratings.php');
                    exit;
                } catch (PDOException $e) {
                    $errors[] = ($e->getCode() == 23000)
                        ? 'Phiếu này đã được đánh giá rồi.'
                        : 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../../../views/student/ratings/create.php';
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $rating = Rating::findById($id);

        if (!$rating) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy đánh giá.'];
            header('Location: ratings.php');
            exit;
        }

        $errors = [];
        $old    = $rating;
        $userRole = $_SESSION['user']['role'] ?? '';
        $userType = $_SESSION['user']['user_type'] ?? '';
        $isStudent = ($userRole === 'requester' || $userType === 'student');
        $myId = $isStudent ? (int)$_SESSION['user']['id'] : null;
        if ($isStudent) {
            $old['user_id'] = $myId;
        }
        $tickets  = Rating::getAvailableTickets($myId);
        $students = $isStudent ? [$_SESSION['user']] : Rating::getAllStudents();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->sanitize($_POST);
            $errors = $this->validate($old, $id);

            if (empty($errors)) {
                try {
                    Rating::update($id, $old);

                    // Bắn thông báo cập nhật đánh giá tới KTV
                    try {
                        require_once __DIR__ . '/../../Services/NotificationService.php';
                        $tStmt = db()->prepare("SELECT t.id, t.ticket_code, t.assigned_to, u.name as requester_name FROM tickets t LEFT JOIN users u ON u.id = t.requester_id WHERE t.id = :tid LIMIT 1");
                        $tStmt->execute([':tid' => $old['ticket_id']]);
                        $tData = $tStmt->fetch(PDO::FETCH_ASSOC);
                        if ($tData && !empty($tData['assigned_to'])) {
                            $ktvId = (int)$tData['assigned_to'];
                            $reqName = $tData['requester_name'] ?: 'Sinh viên';
                            $scoreStars = str_repeat('⭐', (int)$old['score']);
                            NotificationService::send(
                                $ktvId,
                                "{$scoreStars} Cập nhật đánh giá CSAT",
                                "{$reqName} vừa cập nhật đánh giá sang {$old['score']} sao cho phiếu #{$tData['ticket_code']}: \"{$old['comment']}\".",
                                "agent/ratings.php?agent_id={$ktvId}",
                                'rating',
                                'agent'
                            );
                        }
                    } catch (\Throwable $e) {}

                    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Cập nhật đánh giá thành công.'];
                    header('Location: ratings.php');
                    exit;
                } catch (PDOException $e) {
                    $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }
        require __DIR__ . '/../../../views/student/ratings/edit.php';
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!Rating::findById($id)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Không tìm thấy đánh giá.'];
        } else {
            Rating::delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Đã xóa đánh giá CSAT.'];
        }
        header('Location: ratings.php');
        exit;
    }

    private function sanitize(array $input): array
    {
        return [
            'ticket_id' => (int)($input['ticket_id'] ?? 0),
            'user_id'   => (int)($input['user_id'] ?? 0),
            'score'     => (int)($input['score'] ?? 0),
            'comment'   => trim($input['comment'] ?? ''),
        ];
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        if ($data['ticket_id'] <= 0) $errors[] = 'Vui lòng chọn Phiếu hỗ trợ.';
        if ($data['user_id'] <= 0)   $errors[] = 'Vui lòng chọn Sinh viên.';
        if ($data['score'] < 1 || $data['score'] > 5) {
            $errors[] = 'Điểm CSAT phải từ 1 đến 5 sao.';
        }
        if ($data['ticket_id'] > 0 && Rating::existsForTicket($data['ticket_id'], $excludeId)) {
            $errors[] = 'Phiếu này đã được đánh giá rồi.';
        }
        return $errors;
    }
}