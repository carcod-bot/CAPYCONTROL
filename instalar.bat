@echo off
color 0b
echo =======================================================
echo     Instalador del Sistema - Preparacion Inicial
echo =======================================================
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
echo 2. Configura las credenciales de tu base de datos MySQL:
echo    - DB_DATABASE (ej. capycontrol_db)
echo    - DB_USERNAME (ej. root)
echo    - DB_PASSWORD (dejalo vacio si usas XAMPP por defecto)
echo 3. Guarda y cierra el archivo.
echo =======================================================
echo.
echo Presiona ENTER cuando hayas terminado el paso anterior 
echo para continuar con las migraciones...
pause >nul

echo.
echo Ejecutando migraciones de la base de datos...
call php artisan migrate --seed

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
