# Guía de Instalación y Activación (CapyPOS / CapyControl)

Esta guía detalla los pasos estructurados para instalar, configurar y asegurar el sistema Laravel (Punto de Venta o Administración) en la computadora / servidor del cliente final.

## 1. Instalación de Entorno
1. Descargar e instalar **XAMPP** (o Laragon) con PHP 8.2+ y MySQL.
2. Configurar los servicios para que inicien automáticamente.
3. Colocar la carpeta del sistema (`capypos` o `capycontrol`) dentro de `htdocs`.
4. Ingresar a `phpmyadmin`, crear la base de datos correspondiente (ej. `capypos_db`).
5. Copiar el archivo `.env.example` a `.env` y configurar las credenciales de la BD.
6. Abrir una consola en la ruta del proyecto y ejecutar:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```

## 2. Configuración de Seguridad (Licencia)
El sistema incluye un sistema de protección criptográfico por licencias anclado al hardware (MAC Address) y al nombre de la empresa.

### Archivo `.env` y `APP_INTEGRITY_HASH`
Dentro del archivo `.env`, existe una variable llamada `APP_INTEGRITY_HASH`. Esta variable es la "llave maestra" que cifra las licencias. **Por seguridad, esta variable no debe ser modificada** una vez que el sistema está en producción, ya que invalidará cualquier licencia activa.

### Activación del Sistema
1. Al intentar acceder al sistema por primera vez desde el navegador, el usuario será redirigido a la pantalla de **Activación de Licencia**, ya que la base de datos está vacía y no hay licencia activa.
2. En esta pantalla, se mostrará un **ID de Sistema** (Ej: `CPY-A1B2-C3D4-E5F6`). Cópialo.
3. Abre el generador de llaves respectivo (`capypos_keygen` o `capycontrol_keygen`).
4. Ingresa el **ID de Sistema** copiado.
5. En el campo **Organización**, debes colocar EXACTAMENTE el nombre de la empresa que está configurado por defecto en el sistema (generalmente "CapyPOS" o lo que hayas configurado en los ajustes iniciales).
6. Genera la llave, pégala en la pantalla de bloqueo y activa el sistema.
7. Un correo silencioso será enviado a la administración confirmando que esta PC activó el software.

## 3. Ofuscación (Opcional pero Recomendado)
Si deseas evitar que el cliente desactive la validación puenteando el código fuente de Laravel, es recomendable ofuscar los siguientes archivos utilizando ionCube u herramientas similares antes de entregar:
- `app/Services/SysGuard.php` (Lógica de Hardware y Firmas)
- `app/Http/Middleware/CheckLicense.php` (El candado global de las rutas)
- `app/Http/Controllers/LicenseController.php`
