@echo off
color 0b
echo =======================================================
echo     Instalador del Sistema - Preparacion Inicial
echo =======================================================
echo.
echo Verificando dependencias del sistema...

:: Priorizar PHP de XAMPP siempre, para evitar conflictos con versiones en el PATH
:: Limpiar variables de entorno que obliguen a leer otro php.ini
set PHPRC=
set PHP_INI_SCAN_DIR=
if exist "c:\xampp\php\php.exe" (
    set PHP_BIN=c:\xampp\php\php.exe
    set "PATH=c:\xampp\php;%PATH%"
) else (
    set PHP_BIN=php
    php -v >nul 2>&1
    if %errorlevel% neq 0 (
        echo [ERROR CRITICO] No se encontro PHP. Instala XAMPP o agregalo al PATH.
        pause
        exit /b 1
    )
)

echo.
echo Verificando extensiones de PHP requeridas...
%PHP_BIN% -r "if(!extension_loaded('fileinfo')) exit(1);"
if %errorlevel% neq 0 echo [ADVERTENCIA] Extension 'fileinfo' no esta habilitada en php.ini.
%PHP_BIN% -r "if(!extension_loaded('zip')) exit(1);"
if %errorlevel% neq 0 echo [ADVERTENCIA] Extension 'zip' no esta habilitada en php.ini.
%PHP_BIN% -r "if(!extension_loaded('mbstring')) exit(1);"
if %errorlevel% neq 0 echo [ADVERTENCIA] Extension 'mbstring' no esta habilitada en php.ini.
%PHP_BIN% -r "if(!extension_loaded('openssl')) exit(1);"
if %errorlevel% neq 0 echo [ADVERTENCIA] Extension 'openssl' no esta habilitada en php.ini.

echo.
echo Creando base de datos automaticamente en XAMPP...
c:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS vad1;" 2>nul
if %errorlevel% neq 0 (
    echo [AVISO] No se pudo crear la BD automaticamente. Si le pusiste clave a root en XAMPP, creala manualmente.
) else (
    echo Base de datos 'vad1' lista.
)

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
call %PHP_BIN% artisan migrate --seed --force

echo.
echo Creando enlaces de almacenamiento...
call %PHP_BIN% artisan storage:link

echo.
echo Limpiando cache del sistema...
call %PHP_BIN% artisan optimize:clear

echo.
echo =======================================================
echo Instalacion completada!
echo Ya puedes acceder al sistema desde tu navegador.
echo =======================================================
pause
