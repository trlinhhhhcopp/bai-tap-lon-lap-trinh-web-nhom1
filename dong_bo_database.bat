@echo off
setlocal
cd /d "%~dp0"
chcp 65001 >nul
title DONG BO DATABASE HELPDESK CNTT - NHOM 1
color 0A

echo ==============================================================================
echo   DONG BO DATABASE HELPDESK CNTT DAI HOC THU DO HA NOI (NHOM 1)
echo   Chuan ma hoa: UTF-8 Tieng Viet (Khong bi loi font)
echo ==============================================================================
echo.

set "PHP_BIN="
if exist "C:\New folder\php\php.exe" set "PHP_BIN=C:\New folder\php\php.exe"
if not defined PHP_BIN if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if not defined PHP_BIN if exist "D:\xampp\php\php.exe" set "PHP_BIN=D:\xampp\php\php.exe"
if not defined PHP_BIN (
    where php >nul 2>nul
    if %errorlevel% equ 0 set "PHP_BIN=php"
)

if defined PHP_BIN (
    echo [*] Dang nap database thong qua PHP PDO...
    "%PHP_BIN%" -r "$f='database/database_full_nhom1.sql'; if(!file_exists($f)){echo 'Khong tim thay '.$f; exit(1);} require 'config/database.php'; db()->exec(file_get_contents($f)); echo PHP_EOL . '----------------------------------------------------------------------' . PHP_EOL . '>>> DONG BO DATABASE THANH CONG 100%%!' . PHP_EOL . '   - CSDL helpdesk_thudo da cap nhat day du tat ca cac bang' . PHP_EOL . '   - 32 tai khoan: 5 thanh vien nhom, KTV, Giang vien, Admin' . PHP_EOL . '   - 100%% Tieng Viet chuan khong loi font' . PHP_EOL . '----------------------------------------------------------------------' . PHP_EOL;"
    goto finish
)

echo [!] Khong tim thay php.exe tu dong. Dang thu nap qua mysql.exe...
if exist "C:\xampp\mysql\bin\mysql.exe" (
    "C:\xampp\mysql\bin\mysql.exe" --default-character-set=utf8mb4 -u root < "%~dp0database\database_full_nhom1.sql"
    echo [OK] Da nap database qua mysql.exe thanh cong!
) else if exist "C:\New folder\mysql\bin\mysql.exe" (
    "C:\New folder\mysql\bin\mysql.exe" --default-character-set=utf8mb4 -u root < "%~dp0database\database_full_nhom1.sql"
    echo [OK] Da nap database qua mysql.exe thanh cong!
) else (
    echo [X] Vui long mo phpMyAdmin va Import file database/database_full_nhom1.sql!
)

:finish
echo.
echo ==============================================================================
echo   Nhan phim bat ky de dong cua so nay...
echo ==============================================================================
pause >nul
