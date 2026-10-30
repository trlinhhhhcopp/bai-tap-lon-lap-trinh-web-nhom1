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

set PHP_BIN=
where php >nul 2>nul
if %ERRORLEVEL% equ 0 (
    set PHP_BIN=php
) else if exist "C:\New folder\php\php.exe" (
    set PHP_BIN="C:\New folder\php\php.exe"
) else if exist "C:\xampp\php\php.exe" (
    set PHP_BIN="C:\xampp\php\php.exe"
) else (
    echo [!] Khong tim thay chuong trinh PHP tren may cua ban!
    echo [!] Vui long cai dat XAMPP hoac them PHP vao bien moi truong PATH.
    pause
    exit /b 1
)

echo [*] Su dung trinh bien dich PHP tai: %PHP_BIN%
echo [*] Dang mo trinh duyet den trang chu he thong...
start "" "http://localhost:8080"

echo [*] May chu dang hoat dong tai: http://localhost:8080
echo [*] Luu y: Giu nguyen cua so nay trong suot qua trinh su dung.
echo [*] De tat may chu, ban chi can nhan: Ctrl + C (hoac dong cua so nay)
echo =========================================================================
echo.

if exist "router.php" (
    %PHP_BIN% -S localhost:8080 router.php
) else (
    %PHP_BIN% -S localhost:8080
)

echo.
echo [*] May chu da dung.
pause
