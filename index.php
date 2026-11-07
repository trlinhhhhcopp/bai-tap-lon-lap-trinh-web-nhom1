<?php
/**
 * ==============================================================================
 * BÀI TẬP LỚN LẬP TRÌNH WEB - NHÓM 1
 * HỆ THỐNG HELPDESK CNTT TRỰC TUYẾN - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
 * ------------------------------------------------------------------------------
 * FRONT CONTROLLER CHÍNH: index.php (CHUẨN HÓA TOÀN BỘ THEO LUỒNG 2: ROUTER MVC)
 * Toàn bộ yêu cầu được phân phối tự động tới các Controller chuyên biệt:
 *  - AuthController: Đăng nhập, Đăng xuất, Đổi mật khẩu lần đầu, Quên mật khẩu SSPR
 *  - GuestTicketController: Trạm cứu hộ tài khoản khách (Webcam + Tệp) & Tra cứu
 *  - StudentDashboardController: Cổng sinh viên, gửi phiếu (Webcam + Tệp), đổi avatar
 *  - CourseController: Học phần & Nộp bài tập sinh viên
 *  - RatingController: Đánh giá CSAT 5 sao
 *  - AgentDashboardController: Bàn làm việc Kỹ thuật viên (Workbench)
 *  - AdminDashboardController: Trung tâm điều hành Quản trị viên
 *  - AdminUserController: Quản trị tài khoản người dùng & Phân quyền
 *  - HolidayController: Quản lý danh mục ngày nghỉ lễ
 *  - CourseAssignmentController & LecturerLessonController: Cổng giảng viên
 *  - TicketController: Chi tiết phiếu & Nghiệm thu giải pháp
 * ==============================================================================
 */

require_once __DIR__ . '/app/bootstrap.php';

$router = App\bootstrapRouter();
$router->dispatch();
