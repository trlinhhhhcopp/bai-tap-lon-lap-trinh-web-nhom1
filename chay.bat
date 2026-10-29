@echo off
title HE THONG HELPDESK CNTT - DAI HOC THU DO HA NOI (NHOM 1)
color 0B
cls

echo =========================================================================
echo    HE THONG HELPDESK CNTT TRUC TUYEN - DAI HOC THU DO HA NOI (NHOM 1)
echo =========================================================================
echo.
echo [*] Dang chuyen vao thu muc du an...
cd /d "%~dp0"

echo [*] Dang mo trinh duyet den trang chu he thong...
start "" "http://localhost:8080"

echo [*] May chu dang hoat dong tai: http://localhost:8080
echo [*] Luu y: Giu nguyen cua so nay trong suot qua trinh su dung.
echo [*] De tat may chu, ban chi can nhan: Ctrl + C (hoac dong cua so nay)
echo =========================================================================
echo.

C:\xampp\php\php.exe -S localhost:8080 router.php

echo.
echo [*] May chu da dung.
pause
