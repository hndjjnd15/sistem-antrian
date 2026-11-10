@echo off
setlocal

REM ========================================
REM KONFIGURASI
REM ========================================

set MYSQL=C:\xampp\mysql\bin\mysqldump.exe
set DB=sistem_antrian
set USER=root
set PASS=
set BACKUP=C:\backup-antrian\data

REM ========================================
REM TANGGAL
REM ========================================

for /f "tokens=1-3 delims=/" %%a in ("%date%") do (
    set TGL=%%c-%%b-%%a
)

if not exist "%BACKUP%" mkdir "%BACKUP%"

REM ========================================
REM BACKUP
REM ========================================

"%MYSQL%" ^
-u%USER% ^
%DB% ^
> "%BACKUP%\antrian-%TGL%.sql"

echo Backup selesai.
echo %BACKUP%\antrian-%TGL%.sql

REM ========================================
REM HAPUS BACKUP > 30 HARI
REM ========================================

forfiles /p "%BACKUP%" /m *.sql /d -30 /c "cmd /c del @path" 2>nul

endlocal