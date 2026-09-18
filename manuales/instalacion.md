# Guía Completa de Instalación y Activación (CapyPOS / CapyControl)

Esta guía detalla exhaustivamente todos los pasos necesarios para instalar, configurar y activar el sistema (CapyPOS o CapyControl) en el entorno local o servidor del cliente.

## 1. Requisitos del Sistema
- **Servidor Web:** XAMPP, Laragon, o equivalente.
- **PHP:** Versión 8.1 o superior.
- **Extensiones de PHP Requeridas:** `mbstring`, `openssl`, `curl`, `pdo_mysql`, `zip`, `gd`.
- **Base de Datos:** MySQL o MariaDB.
- **Composer:** Instalado (opcional si ya subes el `vendor` pre-compilado, pero altamente recomendado).

## 2. Preparación del Servidor y Base de Datos
1. Inicia los servicios de **Apache** y **MySQL** en tu panel de control (ej. XAMPP).
2. Abre tu navegador e ingresa a `http://localhost/phpmyadmin`.
3. Crea una nueva base de datos vacía. 
   - Para CapyPOS, puedes llamarla `capypos_db` (o el nombre que prefieras).
   - Para CapyControl, puedes llamarla `capycontrol_db` o `vad1` según el entorno de producción.
4. Mueve la carpeta raíz del sistema (la que contiene todo el código de Laravel) a tu directorio público, por ejemplo, `C:\xampp\htdocs\capycontrol`.

## 3. Configuración del Entorno de Laravel (.env)
1. Dentro de la carpeta del sistema, haz una copia del archivo `.env.example` y renómbrala a `.env`.
2. Abre el archivo `.env` en un editor de código y configura las siguientes variables clave:
   ```env
   APP_NAME="CapyControl"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://localhost/capycontrol/public

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nombre_de_tu_bd_creada
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. **IMPORTANTE:** Verifica que la variable `APP_INTEGRITY_HASH` exista y tenga la llave maestra oficial (`xK9P#m2vL$8nQp5wT!rY@bC7jH*dA3gF`). Esto es vital para que las firmas del sistema de licencias hagan match con los keygens.

## 4. Instalación de Dependencias y Migraciones
1. Abre una consola/terminal (`cmd` o PowerShell) apuntando a la ruta del proyecto (`C:\xampp\htdocs\capycontrol`).
2. Ejecuta los siguientes comandos en orden:
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan key:generate
   php artisan migrate --seed
   php artisan optimize:clear
   ```
   *(Nota: El flag `--seed` es indispensable porque insertará los datos iniciales, roles de usuario, permisos y configuraciones vitales en la base de datos).*

## 5. Preparación del Keygen (Panel de Administrador)
El sistema incluye un **Keygen** (Generador de Licencias) que debe ser utilizado por ti o por la Administración central, **NUNCA** se le debe dejar al cliente final.
1. Aloja la carpeta del keygen (`capypos_keygen` o `capycontrol_keygen`) en tu computadora de uso personal o en un hosting en la nube oculto.
2. Crea una base de datos para este Keygen (ej. `bd_keygen_capycontrol`).
3. Importa el archivo `database.sql` (que viene incluido en la carpeta del keygen) dentro de esa nueva base de datos para crear la tabla de control de clientes.
4. Edita el archivo `.env` del keygen asegurándote de colocar las credenciales de la base de datos recién creada, y confirmando que el `APP_INTEGRITY_HASH` coincida a la perfección con el que usaste en el paso 3.

## 6. Proceso de Activación del Sistema (Cliente)
1. Ingresa al sistema desde el navegador web del cliente (`http://localhost/capycontrol/public`).
2. El sistema detectará que no hay licencia (al ser una base de datos recién migrada) y bloqueará el acceso, redirigiendo a la **Pantalla de Activación**.
3. En la pantalla verás un **ID del Sistema** (un hardware ID alfanumérico único para esa computadora). Cópialo.
4. En los campos de **Organización** y **Sucursal** de esa misma pantalla, ingresa o edita el nombre real de la empresa del cliente (ej. "Traki") y la sucursal (ej. "Barinas").
   - *Nota de uso: Lo que escribas aquí se auto-guardará en el sistema como la configuración oficial de la empresa al momento de darle al botón activar.*
5. Ve a tu computadora personal y abre tu **Keygen** en el navegador.
6. Pega el ID del sistema que copiaste, y escribe **EL EXACTO MISMO** nombre de Organización y Sucursal que escribiste en la pantalla del cliente (sensible a mayúsculas, minúsculas y espacios).
7. Selecciona el módulo correspondiente y el tiempo de vigencia de la licencia (Prueba, Mensual, Anual, Lifetime) y haz clic en "Generar Licencia".
8. Copia la llave larga cifrada (en color azul) que arrojará el Keygen.
9. Vuelve a la pantalla del cliente, pega la llave generada en el cuadro grande de texto y haz clic en **Activar Sistema**.
10. ¡El sistema verificará la firma internamente, se desbloqueará, y te permitirá iniciar sesión (usualmente con `admin` / `password` o las credenciales por defecto)!

## 7. Mantenimiento, Errores y Seguridad (Opcional)
- Si después de migrar no puedes iniciar sesión o la pantalla se ve rara, prueba vaciando la caché de Laravel con: `php artisan optimize:clear`.
- Si el cliente te reporta licencia inválida o corrupta, asegúrate de que no haya cambiado accidentalmente el nombre de la empresa desde el panel de Configuraciones dentro del software, ya que esto rompe el cifrado y lo bloquea por seguridad.
- **Ofuscación:** Si deseas que el código del sistema de licencias sea completamente indescifrable para que programadores del cliente no puedan saltárselo eliminando los `if`, se recomienda compilar con `ionCube` los siguientes archivos antes del despliegue:
  - `app/Services/SysGuard.php`
  - `app/Http/Middleware/CheckLicense.php`
  - `app/Http/Controllers/LicenseController.php`
