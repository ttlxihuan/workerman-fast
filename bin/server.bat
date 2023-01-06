@echo off
cd /d %~dp0
set APP_ENV=local
php ..\start\start_register.php ..\start\start_gateway.php ..\start\start_worker.php
pause
