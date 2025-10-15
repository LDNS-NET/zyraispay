@echo off
echo Starting MikroTik Queue Worker...
:start
php artisan queue:work database --queue=mikrotik --tries=3 --timeout=300 --sleep=3 --max-jobs=50 --max-time=3600
timeout /t 5
goto start
