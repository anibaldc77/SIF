@echo off
setlocal
php "%~dp0sif-builder" %*
exit /b %ERRORLEVEL%
