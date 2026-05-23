@echo off
echo ===================================================
echo   Mood-Based Movie Recommendation System Startup
echo ===================================================

echo.
echo [1/2] Starting Python AI Backend...
echo (Ensure Python is installed and added to PATH)
start cmd /k "cd python_ai_backend && python app.py"

echo.
echo [2/2] Checking for XAMPP...
if exist "C:\xampp\xampp-control.exe" (
    echo XAMPP found at C:\xampp.
    echo Please ensure Apache and MySQL are running in XAMPP Control Panel.
) else (
    echo XAMPP not found in default path.
    echo Please ensure your local PHP server and MySQL are running.
)

echo.
echo ===================================================
echo Project should now be accessible at:
echo http://localhost/ (or your configured XAMPP port)
echo ===================================================
pause
