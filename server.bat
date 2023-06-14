@echo off
cd /d %~dp0
set APP_ENV=local
set APP_NODE=
php server.php
pause
