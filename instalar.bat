@echo off
color 0b
echo =======================================================
echo     Instalador del Sistema - Preparacion Inicial
echo =======================================================
echo.
echo Creando base de datos automaticamente en XAMPP...
c:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS vad1;" 2>nul
if %errorlevel% neq 0 (
    echo [AVISO] No se pudo crear la BD automaticamente. Si le pusiste clave a root en XAMPP, creala manualmente.
) else (
    echo Base de datos 'vad1' lista.
)

echo.
echo Instalando dependencias de PHP (por favor espere)...
call composer install --optimize-autoloader --no-dev

echo.
echo Instalando dependencias de Frontend (Node.js)...
call npm install
call npm run build

echo.
echo =======================================================
echo ACCION REQUERIDA:
echo 1. Abre el archivo .env en un editor de texto.
echo 2. Asegurate de que los datos esten asi:
echo    - DB_DATABASE=vad1
echo    - DB_USERNAME=root
echo    - DB_PASSWORD= (coloca tu clave si le pusiste a XAMPP)
echo 3. Guarda y cierra el archivo.
echo =======================================================
echo.
echo Presiona ENTER cuando hayas terminado el paso anterior 
echo para continuar con las migraciones...
pause >nul

echo.
echo Ejecutando migraciones de la base de datos...
call php artisan migrate --seed --force

echo.
echo Creando enlaces de almacenamiento...
call php artisan storage:link

echo.
echo Limpiando cache del sistema...
call php artisan optimize:clear

echo.
echo =======================================================
echo Instalacion completada!
echo Ya puedes acceder al sistema desde tu navegador.
echo =======================================================
pause
