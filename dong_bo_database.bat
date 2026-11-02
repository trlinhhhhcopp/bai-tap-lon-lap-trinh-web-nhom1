@echo off
chcp 65001 >nul
title DONG BO DATABASE HELPDESK CNTT - NHOM 1
color 0A

echo ==============================================================================
echo   🛠️ DONG BO DATABASE HELPDESK CNTT DAI HOC THU DO HA NOI (NHOM 1)
echo   Chuan ma hoa: UTF-8 Tieng Viet (Khong bi loi phong chu / font)
echo ==============================================================================
echo.

set "PHP_CMD="

if exist "C:\New folder\php\php.exe" (
    set "PHP_CMD=C:\New folder\php\php.exe"
) else if exist "C:\xampp\php\php.exe" (
    set "PHP_CMD=C:\xampp\php\php.exe"
) else if exist "D:\xampp\php\php.exe" (
    set "PHP_CMD=D:\xampp\php\php.exe"
) else (
    where php >nul 2>nul
    if %errorlevel% equ 0 (
        set "PHP_CMD=php"
    )
)

if "%PHP_CMD%"=="" (
    echo [!] Khong tim thay php.exe tu dong. Dang thu nap qua mysql truc tiep...
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        "C:\xampp\mysql\bin\mysql.exe" --default-character-set=utf8mb4 -u root < "%~dp0database\database_full_nhom1.sql"
        echo [OK] Da nap database qua mysql.exe thanh cong!
    ) else if exist "C:\New folder\mysql\bin\mysql.exe" (
        "C:\New folder\mysql\bin\mysql.exe" --default-character-set=utf8mb4 -u root < "%~dp0database\database_full_nhom1.sql"
        echo [OK] Da nap database qua mysql.exe thanh cong!
    ) else (
        echo [X] Vui long mo phpMyAdmin va Import file database/database_full_nhom1.sql!
    )
    goto finish
)

echo Dang chay script dong bo qua PHP...
"%PHP_CMD%" "%~dp0database\dong_bo.php"

:finish
echo.
echo ==============================================================================
echo   Nhan phim bat ky de dong cua so nay...
echo ==============================================================================
pause >nul
