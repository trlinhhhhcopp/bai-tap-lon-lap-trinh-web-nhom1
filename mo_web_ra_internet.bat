@echo off
chcp 65001 >nul
title KÍCH HOẠT ĐƯỜNG TRUYỀN INTERNET CÔNG KHAI - HELPDESK TUD
cls
echo ===============================================================================
echo       HỆ THỐNG HELPDESK CNTT - TRƯỜNG ĐẠI HỌC THỦ ĐÔ HÀ NỘI
echo       CÔNG CỤ ĐƯA WEBSITE LOCAL LÊN INTERNET CHO MỌI NGƯỜI TRUY CẬP
echo ===============================================================================
echo.
echo Đang kết nối mạng Cloudflare Tunnel tới localhost:80 (Apache XAMPP)...
echo.
echo Lưu ý: Hãy copy đường link có đuôi ".trycloudflare.com" hiện bên dưới,
echo        sau đó thêm đuôi: /web01/bai-tap-lon-lap-trinh-web-nhom1/
echo        để gửi cho Thầy/Cô hoặc bạn bè trải nghiệm trực tiếp!
echo.
echo ===============================================================================
echo.

"c:\New folder\cloudflared.exe" tunnel --url http://localhost:80

pause
