@echo off
cd /d %~dp0
set APP_ENV=local
php server.php
pause
