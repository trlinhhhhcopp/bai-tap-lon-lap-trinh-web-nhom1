<?php
namespace App\Services;

use DateTime;
use PDO;

/**
 * ==============================================================================
 * DỊCH VỤ TÍNH TOÁN SLA THEO GIỜ HÀNH CHÍNH (BUSINESS HOURS SLA ENGINE)
 * Phân hệ Quản lý Lịch lễ & SLA Đại học Thủ Đô Hà Nội (TUD)
 * Phụ trách: Phan Công Thành (224001746) & Nhóm 1
 * ------------------------------------------------------------------------------
 * Quy định giờ làm việc chuẩn mực:
 * 1. Ngày làm việc: Thứ 2 đến Thứ 6 (Monday to Friday). Thứ 7, Chủ Nhật nghỉ.
 * 2. Khung giờ hành chính:
 *    - Sáng:  08:00 - 12:00 (4 tiếng làm việc = 240 phút)
 *    - Trưa:  12:00 - 13:30 (Nghỉ trưa 90 phút - SLA ĐÓNG BĂNG KHÔNG TÍNH)
 *    - Chiều: 13:30 - 17:30 (4 tiếng làm việc = 240 phút)
 *    - Đêm:   17:30 - 08:00 hôm sau (Ngoài giờ - SLA ĐÓNG BĂNG KHÔNG TÍNH)
 * 3. Ngày nghỉ lễ: Loại trừ toàn bộ ngày lễ theo danh mục bảng `holidays` (kể cả lễ cố định hàng năm).
 * ==============================================================================
 */
class SlaService {

    const WORK_START_HOUR   = 8;
    const WORK_START_MIN    = 0;

    const LUNCH_START_HOUR  = 12;
    const LUNCH_START_MIN   = 0;

    const LUNCH_END_HOUR    = 13;
    const LUNCH_END_MIN     = 30;

    const WORK_END_HOUR     = 17;
    const WORK_END_MIN      = 30;

    /**
     * Kiểm tra một ngày cụ thể có phải là ngày nghỉ lễ không
     */
    public static function isHoliday(string $dateYmd): bool {
        try {
            $pdo = db();
            $sql = "SELECT COUNT(*) FROM holidays 
                    WHERE (:dt BETWEEN start_date AND end_date) 
                       OR (is_recurring = 1 AND DATE_FORMAT(:dt2, '%m-%d') BETWEEN DATE_FORMAT(start_date, '%m-%d') AND DATE_FORMAT(end_date, '%m-%d'))";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':dt' => $dateYmd, ':dt2' => $dateYmd]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Kiểm tra ngày đó có phải là ngày làm việc của KTV hay không (T2 - T6 và không trùng lễ)
     */
    public static function isWorkingDay(DateTime $dt): bool {
        $dayOfWeek = (int)$dt->format('N'); // 1 = Thứ 2, 7 = Chủ Nhật
        if ($dayOfWeek >= 6) {
            return false; // Thứ 7, Chủ Nhật
        }
        if (self::isHoliday($dt->format('Y-m-d'))) {
            return false; // Trùng ngày nghỉ lễ
        }
        return true;
    }

    /**
     * Dời mốc thời gian vào ca làm việc hợp lệ tiếp theo (nếu mốc ban đầu nằm ngoài giờ)
     */
    public static function moveToNextWorkingTime(DateTime $dt): DateTime {
        $current = clone $dt;
        $maxLoops = 365; // Phòng lặp vô hạn
        $loops = 0;

        while ($loops++ < $maxLoops) {
            if (!self::isWorkingDay($current)) {
                $current->modify('+1 day');
                $current->setTime(self::WORK_START_HOUR, self::WORK_START_MIN, 0);
                continue;
            }

            // Ngày làm việc hợp lệ
            $h = (int)$current->format('H');
            $m = (int)$current->format('i');
            $timeMins = $h * 60 + $m;

            $workStartMins  = self::WORK_START_HOUR * 60 + self::WORK_START_MIN;   // 480 (08:00)
            $lunchStartMins = self::LUNCH_START_HOUR * 60 + self::LUNCH_START_MIN; // 720 (12:00)
            $lunchEndMins   = self::LUNCH_END_HOUR * 60 + self::LUNCH_END_MIN;     // 810 (13:30)
            $workEndMins    = self::WORK_END_HOUR * 60 + self::WORK_END_MIN;       // 1050 (17:30)

            if ($timeMins < $workStartMins) {
                // Trước 08:00 sáng
                $current->setTime(self::WORK_START_HOUR, self::WORK_START_MIN, 0);
                return $current;
            } elseif ($timeMins >= $workStartMins && $timeMins < $lunchStartMins) {
                // Trong ca sáng (08:00 - 12:00)
                return $current;
            } elseif ($timeMins >= $lunchStartMins && $timeMins < $lunchEndMins) {
                // Trong giờ nghỉ trưa (12:00 - 13:30) -> Chuyển đến 13:30
                $current->setTime(self::LUNCH_END_HOUR, self::LUNCH_END_MIN, 0);
                return $current;
            } elseif ($timeMins >= $lunchEndMins && $timeMins < $workEndMins) {
                // Trong ca chiều (13:30 - 17:30)
                return $current;
            } else {
                // Sau 17:30 chiều -> Dời sang 08:00 sáng ngày hôm sau
                $current->modify('+1 day');
                $current->setTime(self::WORK_START_HOUR, self::WORK_START_MIN, 0);
                continue;
            }
        }

        return $current;
    }

    /**
     * Cộng thêm số phút làm việc hành chính chuẩn vào một mốc thời gian
     */
    public static function addBusinessMinutes(DateTime $start, int $minutesToAdd): DateTime {
        $current = self::moveToNextWorkingTime($start);

        $lunchStartMins = self::LUNCH_START_HOUR * 60 + self::LUNCH_START_MIN; // 720 (12:00)
        $lunchEndMins   = self::LUNCH_END_HOUR * 60 + self::LUNCH_END_MIN;     // 810 (13:30)
        $workEndMins    = self::WORK_END_HOUR * 60 + self::WORK_END_MIN;       // 1050 (17:30)

        $guard = 0;
        while ($minutesToAdd > 0 && $guard++ < 200) {
            $h = (int)$current->format('H');
            $m = (int)$current->format('i');
            $timeMins = $h * 60 + $m;

            if ($timeMins < $lunchStartMins) {
                // Đang trong ca sáng (08:00 - 12:00)
                $availMins = $lunchStartMins - $timeMins;
                if ($minutesToAdd <= $availMins) {
                    $current->modify("+{$minutesToAdd} minutes");
                    $minutesToAdd = 0;
                    break;
                } else {
                    $minutesToAdd -= $availMins;
                    // Chuyển sang đầu ca chiều 13:30
                    $current->setTime(self::LUNCH_END_HOUR, self::LUNCH_END_MIN, 0);
                }
            } elseif ($timeMins >= $lunchEndMins && $timeMins < $workEndMins) {
                // Đang trong ca chiều (13:30 - 17:30)
                $availMins = $workEndMins - $timeMins;
                if ($minutesToAdd <= $availMins) {
                    $current->modify("+{$minutesToAdd} minutes");
                    $minutesToAdd = 0;
                    break;
                } else {
                    $minutesToAdd -= $availMins;
                    // Hết giờ chiều -> Dời sang 08:00 ngày làm việc tiếp theo
                    $current->modify('+1 day');
                    $current->setTime(self::WORK_START_HOUR, self::WORK_START_MIN, 0);
                    $current = self::moveToNextWorkingTime($current);
                }
            } else {
                // Trường hợp biên: dời vào giờ làm việc tiếp theo
                $current = self::moveToNextWorkingTime($current);
            }
        }

        return $current;
    }

    /**
     * Tính toán toàn bộ hạn chót SLA (Triage 30 phút và Giải quyết Due At) theo giờ hành chính
     */
    public static function calculateSlaDeadlines(int $categoryId, int $priorityId, ?DateTime $startTime = null): array {
        $pdo = db();

        $catStmt = $pdo->prepare("SELECT sla_hours FROM categories WHERE id = :id");
        $catStmt->execute([':id' => $categoryId]);
        $slaHours = (float)($catStmt->fetchColumn() ?: 4.0);

        $prioStmt = $pdo->prepare("SELECT sla_factor FROM priorities WHERE id = :id");
        $prioStmt->execute([':id' => $priorityId]);
        $slaFactor = (float)($prioStmt->fetchColumn() ?: 1.0);

        $effectiveMinutes = (int)round($slaHours * $slaFactor * 60);
        if ($effectiveMinutes <= 0) {
            $effectiveMinutes = 120; // Tối thiểu 2 giờ
        }

        $now = $startTime ?: new DateTime();
        $triageDueDt = self::addBusinessMinutes($now, 30);
        $dueDt       = self::addBusinessMinutes($now, $effectiveMinutes);

        return [
            'effective_minutes' => $effectiveMinutes,
            'sla_started_at'    => $now->format('Y-m-d H:i:s'),
            'triage_due_at'     => $triageDueDt->format('Y-m-d H:i:s'),
            'due_at'            => $dueDt->format('Y-m-d H:i:s'),
        ];
    }
}

