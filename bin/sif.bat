@echo off
setlocal
php "%~dp0sif" %*
exit /b %ERRORLEVEL%
