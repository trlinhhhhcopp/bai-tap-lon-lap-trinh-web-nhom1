# bai-tap-lon-lap-trinh-web-nhom1
# Chủ đề 9: HỆ THỐNG TIẾP NHẬN VÀ XỬ LÝ HỖ TRỢ ( HELPDESK) NHÓM 1

## Danh sách thành viên:
1. Phạm Thị Thanh Ngân (224001736) - Trưởng nhóm
2. Trần Thục Linh (224001729) 
3. Lê Hà My (224001736)
4. Nguyễn Ánh Nhi (224001742)
5. Phan Công Thành (224001746) 

## Phân công nhiệm vụ:
1. Phạm Thị Thanh Ngân (224001736) - Trưởng nhóm
2. Trần Thục Linh (224001729) 
3. Lê Hà My (224001736)
4. Nguyễn Ánh Nhi (224001742)
5. Phan Công Thành (224001746) 

## Danh sách 18 Route thống nhất toàn hệ thống

| STT | Method | Đường dẫn | Controller@Action | Vai trò được phép | Tầng |
|---|---|---|---|---|---|
| 1 | GET | `/login` | `AuthController@showLogin` | Khách | P0 |
| 2 | POST | `/login` | `AuthController@login` | Khách | P0 |
| 3 | POST | `/logout` | `AuthController@logout` | Đã đăng nhập | P0 |
| 4 | GET | `/tickets/create` | `TicketController@cgit add .reate` | requester, admin | P0 |
| 5 | POST | `/tickets/create` | `TicketController@store` | requester, admin | P0 |
| 6 | GET | `/tickets/my` | `TicketController@myTickets` | requester | P0 |
| 7 | GET | `/tickets/show?code=...` | `TicketController@show` | Chủ phiếu, agent được giao, admin | P0 |
| 8 | POST | `/tickets/reply` | `TicketController@reply` | Chủ phiếu, agent được giao, admin | P0 |
| 9 | GET | `/tickets/attachment?code=...` | `AttachmentController@download` | Chủ phiếu, agent được giao, admin | P1 |
| 10 | POST | `/tickets/rate` | `TicketController@rate` | requester (chủ phiếu) | P1 |
| 11 | GET | `/agent/queue` | `AgentController@queue` | agent, admin | P0 |
| 12 | POST | `/tickets/status` | `AgentController@updateStatus` | agent (được giao), admin | P0 |
| 13 | GET | `/admin/tickets` | `AdminController@tickets` | admin | P0 |
| 14 | POST | `/admin/tickets/assign` | `AdminController@assign` | admin | P0 |
| 15 | GET/POST | `/admin/master-data` | `AdminController@masterData` | admin | P0 |
| 16 | GET | `/admin/dashboard` | `AdminController@dashboard` | admin | P0 |
| 17 | GET | `/admin/reports/export-csv` | `AdminController@exportCsv` | admin | P1 |
| 18 | GET | `/api/tickets/open-count`, `/api/tickets/history`, `/api/kb/search` | `ApiController@openCount`, `@history`, `@kbSearch` | Đã đăng nhập (theo quyền) | P0 / P2 |