@echo off
chcp 65001 >nul
title KÍCH HOẠT ĐƯỜNG TRUYỀN INTERNET CÔNG KHAI - HELPDESK TUD
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0run_tunnel.ps1"
pause
