# 📋 Guía de Construcción — CapyControl

> **Sistema**: CapyControl (Panel de Administración e Inventario)  
> **Framework**: Laravel 11  
> **Última actualización**: 2026-07-08

---

## 📁 Estructura General del Proyecto

```
capycontrol/
├── app/
│   ├── Http/Controllers/
│   │   ├── Controller.php
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   ├── DepartmentController.php
│   │   ├── CategoryController.php
│   │   ├── BrandController.php
│   │   ├── ProviderController.php
│   │   ├── ProductController.php
│   │   ├── SettingController.php
│   │   ├── CurrencyController.php
│   │   └── PaymentMethodController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Department.php
│   │   ├── Category.php
│   │   ├── Brand.php
│   │   ├── Provider.php
│   │   ├── Product.php
│   │   ├── Setting.php
│   │   ├── Currency.php
│   │   └── PaymentMethod.php
│   └── Providers/
├── database/migrations/
├── resources/views/
│   ├── auth/
│   ├── layouts/
│   ├── inventory/
│   ├── finances/
│   ├── home.blade.php
│   └── welcome.blade.php
├── routes/
│   ├── web.php
│   └── console.php
└── public/
```

---

## 🔐 Módulo de Autenticación

### AuthController (`app/Http/Controllers/AuthController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `showLogin()` | `/login` | GET | Muestra el formulario de login. Si el usuario ya está autenticado, redirige al home. |
| `login(Request $request)` | `/login` | POST | Procesa el inicio de sesión. Valida `username` y `password`. Usa `Auth::attempt()` con opción "recordarme". Mensajes de error en español. |
| `logout(Request $request)` | `/logout` | POST | Cierra la sesión del usuario, invalida la sesión y regenera el token CSRF. |
| `toggleDarkMode(Request $request)` | `/toggle-dark-mode` | POST | Alterna el modo oscuro del usuario autenticado. Retorna JSON con el estado actual de `dark_mode`. |

---

## 🏠 Módulo Home

### HomeController (`app/Http/Controllers/HomeController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/` | GET | Renderiza la vista principal `home`. Requiere autenticación. |

---

## 🏢 Módulo de Departamentos

### DepartmentController (`app/Http/Controllers/DepartmentController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/departments` | GET | Lista todos los departamentos ordenados por nombre. Renderiza vista `inventory.departments.index`. |
| `store(Request $request)` | `/departments` | POST | Crea un nuevo departamento. Valida nombre (único, máx. 255) y descripción (máx. 500). Soporta respuestas AJAX y redirección. |
| `update(Request $request, Department)` | `/departments/{department}` | PUT | Actualiza un departamento existente. Valida unicidad excluyendo el ID actual. Soporta AJAX y redirección. |
| `destroy(Request $request, Department)` | `/departments/{department}` | DELETE | Elimina un departamento. Soporta AJAX y redirección. |

**Rutas excluidas del resource:** `show`

---

## 📂 Módulo de Categorías

### CategoryController (`app/Http/Controllers/CategoryController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/categories` | GET | Lista categorías con su departamento asociado (eager loading). Carga departamentos activos para el formulario. |
| `store(Request $request)` | `/categories` | POST | Crea una nueva categoría. Valida nombre (único), descripción y `department_id` (debe existir). Soporta AJAX. |
| `getByDepartment($department_id)` | `/departments/{department}/categories` | GET | Retorna JSON con categorías activas filtradas por departamento. Usado para carga dinámica de selects. |
| `update(Request $request, Category)` | `/categories/{category}` | PUT | Actualiza una categoría existente. Valida unicidad excluyendo el ID actual. |
| `destroy(Request $request, Category)` | `/categories/{category}` | DELETE | Elimina una categoría. Soporta AJAX y redirección. |

**Rutas excluidas del resource:** `create`, `show`

---

## 🏷️ Módulo de Marcas

### BrandController (`app/Http/Controllers/BrandController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/brands` | GET | Lista todas las marcas ordenadas por nombre. Renderiza vista `inventory.brands.index`. |
| `store(Request $request)` | `/brands` | POST | Crea una nueva marca. Valida nombre (único, máx. 255) y descripción. Soporta AJAX. |
| `update(Request $request, Brand)` | `/brands/{brand}` | PUT | Actualiza una marca existente. |
| `destroy(Request $request, Brand)` | `/brands/{brand}` | DELETE | Elimina una marca. **Protege la marca "Genérico"** de ser eliminada (retorna error 403). |

**Rutas excluidas del resource:** `create`, `show`

---

## 🚚 Módulo de Proveedores

### ProviderController (`app/Http/Controllers/ProviderController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/providers` | GET | Lista todos los proveedores ordenados por nombre. Renderiza vista `inventory.providers.index`. |
| `store(Request $request)` | `/providers` | POST | Crea un nuevo proveedor. Valida nombre (único, máx. 255) y descripción. Soporta AJAX. |
| `update(Request $request, Provider)` | `/providers/{provider}` | PUT | Actualiza un proveedor existente. |
| `destroy(Request $request, Provider)` | `/providers/{provider}` | DELETE | Elimina un proveedor. **Protege el proveedor "Genérico"** de ser eliminado (retorna error 403). |

**Rutas excluidas del resource:** `create`, `show`

---

## 📦 Módulo de Productos

### ProductController (`app/Http/Controllers/ProductController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index(Request $request)` | `/products` | GET | Lista productos con filtros avanzados: búsqueda por código (`search_code`), filtro por `category_id`, `brand_id`, rango de precio (`price_min`, `price_max`). Carga relaciones `department`, `category`, `brand`, `provider` con eager loading. Prepara datos para el modal de creación. |
| `create()` | `/products/create` | GET | Muestra formulario de creación. Carga departamentos activos, categorías activas, genera código privado automático y obtiene modo de código. |
| `store(Request $request)` | `/products` | POST | Crea un producto. Valida todos los campos incluyendo imagen (máx. 2MB, formatos jpeg/png/jpg/gif/webp). Asigna automáticamente `department_id` desde la categoría. Asigna marca y proveedor "Genérico" si no se especifican. Almacena imagen en disco `public`. |
| `edit(Product $product)` | `/products/{product}/edit` | GET | Retorna datos del producto para edición. Soporta respuesta JSON (AJAX) o vista Blade. Carga departamentos, categorías, marcas y proveedores. |
| `update(Request $request, Product)` | `/products/{product}` | PUT | Actualiza un producto existente. Elimina imagen anterior si se sube una nueva. Misma lógica de validación y genéricos que `store`. |
| `destroy(Request $request, Product)` | `/products/{product}` | DELETE | Elimina un producto y su imagen asociada del disco. |

**Rutas excluidas del resource:** `show`

**Lógica especial:**
- Generación automática de código privado (incremental o personalizado)
- Asignación automática de marca/proveedor "Genérico" cuando no se especifica
- Derivación automática de `department_id` desde la categoría seleccionada
- Gestión de imágenes con almacenamiento en disco público

---

## ⚙️ Módulo de Configuración

### SettingController (`app/Http/Controllers/SettingController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/settings` | GET | Muestra la configuración actual: `private_code_start` y `private_code_mode`. |
| `update(Request $request)` | `/settings` | POST | Actualiza la configuración. Valida que `private_code_start` sea entero ≥ 1 y `private_code_mode` sea `incremental` o `personalizado`. |

---

## 💰 Módulo de Finanzas

### CurrencyController (`app/Http/Controllers/CurrencyController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/currencies` | GET | Renderiza la vista `finances.currencies.index`. |
| `fetchAll()` | `/api/currencies` | GET | Retorna JSON con todas las monedas y sus métodos de pago asociados (eager loading). Ordenadas por código. |
| `store(Request $request)` | `/api/currencies` | POST | Crea una nueva moneda. Valida código (único), descripción, símbolo, decimales, tasa de cambio, código ISO, observación y flags (`is_default`, `is_active`, `used_in_pos`). Si se marca como predeterminada, desmarca las demás. |
| `update(Request $request, Currency)` | `/api/currencies/{currency}` | PUT | Actualiza una moneda. Misma validación que `store`. Gestiona la moneda predeterminada. |
| `destroy(Currency)` | `/api/currencies/{currency}` | DELETE | Elimina una moneda. |

### PaymentMethodController (`app/Http/Controllers/PaymentMethodController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `store(Request $request)` | `/api/payment-methods` | POST | Crea un método de pago asociado a una moneda. Valida: `currency_id`, `code`, `description`, `value`, límites de cambio/compra, y múltiples flags booleanos (denominación real, permite cambio, verificación electrónica, adelanto efectivo, serial admin, auto-declarar, auto-depositar, facturación admin). |
| `update(Request $request, PaymentMethod)` | `/api/payment-methods/{paymentMethod}` | PUT | Actualiza un método de pago existente. |
| `destroy(PaymentMethod)` | `/api/payment-methods/{paymentMethod}` | DELETE | Elimina un método de pago. |

---

## 🧾 Módulo de Control POS (Puntos de Venta)

### CashRegisterController (`app/Http/Controllers/CashRegisterController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/pos-control` | GET | Muestra el dashboard de monitoreo de cajas con estadísticas y listado. |
| `store(Request $request)` | `/pos-control/registers` | POST | Crea una nueva caja registradora. |
| `update(Request $request, CashRegister)` | `/pos-control/registers/{cashRegister}` | PUT | Actualiza información de una caja. |
| `destroy(Request $request, CashRegister)` | `/pos-control/registers/{cashRegister}` | DELETE | Elimina una caja. |
| `sessions(CashRegister)` | `/pos-control/registers/{cashRegister}/sessions` | GET | Retorna el historial de sesiones de una caja en formato JSON. |

### CashSessionController (`app/Http/Controllers/CashSessionController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `open(Request $request)` | `/pos-control/sessions/open` | POST | Abre un nuevo turno en una caja registradora con un fondo inicial. |
| `close(Request $request, CashSession)` | `/pos-control/sessions/{cashSession}/close` | POST | Cierra el turno actual, registrando monto real y diferencia. |
| `withdraw(Request $request, CashSession)` | `/pos-control/sessions/{cashSession}/withdraw` | POST | Registra un retiro de dinero en efectivo de la caja. |
| `deposit(Request $request, CashSession)` | `/pos-control/sessions/{cashSession}/deposit` | POST | Registra un depósito de dinero en la caja. |
| `show(CashSession)` | `/pos-control/sessions/{cashSession}` | GET | Devuelve los detalles de una sesión específica. |

---

## 📦 Modelos

### User (`app/Models/User.php`)

| Campo | Tipo |
|-------|------|
| `username` | string |
| `password` | hashed |
| `role` | string |
| `permissions` | array (JSON) |
| `dark_mode` | boolean |

**Métodos personalizados:**

| Método | Retorno | Descripción |
|--------|---------|-------------|
| `isAdmin()` | bool | Verifica si el rol del usuario es `'admin'`. |

---

### Department (`app/Models/Department.php`)

| Campo | Tipo |
|-------|------|
| `name` | string |
| `description` | string |
| `active` | boolean |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `categories()` | hasMany | Category |
| `products()` | hasMany | Product |

---

### Category (`app/Models/Category.php`)

| Campo | Tipo |
|-------|------|
| `name` | string |
| `description` | string |
| `active` | boolean |
| `department_id` | integer (FK) |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `department()` | belongsTo | Department |
| `products()` | hasMany | Product |

---

### Brand (`app/Models/Brand.php`)

| Campo | Tipo |
|-------|------|
| `name` | string |
| `description` | string |
| `active` | boolean |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `products()` | hasMany | Product |

---

### Provider (`app/Models/Provider.php`)

| Campo | Tipo |
|-------|------|
| `name` | string |
| `description` | string |
| `active` | boolean |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `products()` | hasMany | Product |

---

### Product (`app/Models/Product.php`)

| Campo | Tipo |
|-------|------|
| `name` | string |
| `description` | string |
| `ean_code` | string |
| `private_code` | string |
| `size_type` | string |
| `department_id` | integer (FK) |
| `category_id` | integer (FK) |
| `price_usd` | decimal:2 |
| `image` | string |
| `active` | boolean |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `department()` | belongsTo | Department |
| `category()` | belongsTo | Category |
| `brand()` | belongsTo | Brand |
| `provider()` | belongsTo | Provider |

**Métodos personalizados:**

| Método | Retorno | Descripción |
|--------|---------|-------------|
| `generatePrivateCode()` | string (estático) | Genera el siguiente código privado basado en la configuración (`incremental` o `personalizado`). Calcula el máximo código existente y retorna el siguiente valor. |

---

### Setting (`app/Models/Setting.php`)

| Campo | Tipo |
|-------|------|
| `key` | string |
| `value` | string |

**Métodos personalizados:**

| Método | Retorno | Descripción |
|--------|---------|-------------|
| `get(string $key, string $default)` | string (estático) | Obtiene el valor de una configuración por clave. Retorna el valor por defecto si no existe. |
| `set(string $key, string $value)` | void (estático) | Crea o actualiza una configuración (usa `updateOrCreate`). |

---

### Currency (`app/Models/Currency.php`)

| Campo | Tipo |
|-------|------|
| `code` | string |
| `description` | string |
| `symbol` | string |
| `max_decimals` | integer |
| `is_default` | boolean |
| `is_active` | boolean |
| `exchange_rate` | numeric |
| `iso_code` | string |
| `observation` | string |
| `used_in_pos` | boolean |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `paymentMethods()` | hasMany | PaymentMethod |

---

### PaymentMethod (`app/Models/PaymentMethod.php`)

| Campo | Tipo |
|-------|------|
| `currency_id` | integer (FK) |
| `code` | string |
| `description` | string |
| `value` | numeric |
| `max_change_amount` | numeric |
| `min_purchase_amount` | numeric |
| `is_real_denomination` | boolean |
| `allows_change` | boolean |
| `used_in_pos` | boolean |
| `electronic_verification` | boolean |
| `cash_advance` | boolean |
| `admin_serial` | boolean |
| `auto_declare` | boolean |
| `auto_deposit` | boolean |
| `used_in_admin_billing` | boolean |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `currency()` | belongsTo | Currency |

---

### CashRegister (`app/Models/CashRegister.php`)

| Campo | Tipo |
|-------|------|
| `number` | string |
| `name` | string |
| `location` | string |
| `active` | boolean |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `sessions()` | hasMany | CashSession |
| `activeSession()` | hasOne | CashSession |

---

### CashSession (`app/Models/CashSession.php`)

| Campo | Tipo |
|-------|------|
| `cash_register_id` | integer (FK) |
| `user_id` | integer (FK) |
| `status` | enum |
| `turn_number` | integer |
| `opening_amount` | decimal:2 |
| `expected_amount` | decimal:2 |
| `actual_amount` | decimal:2 |
| `difference` | decimal:2 |
| `total_sales` | integer |
| `total_returns` | integer |
| `total_withdrawals` | integer |
| `pending_invoices` | integer |
| `opened_at` | datetime |
| `closed_at` | datetime |
| `closing_notes` | text |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `cashRegister()` | belongsTo | CashRegister |
| `user()` | belongsTo | User |
| `movements()` | hasMany | CashMovement |

---

### CashMovement (`app/Models/CashMovement.php`)

| Campo | Tipo |
|-------|------|
| `cash_session_id` | integer (FK) |
| `user_id` | integer (FK) |
| `type` | enum |
| `amount` | decimal:2 |
| `reason` | string |
| `notes` | text |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `cashSession()` | belongsTo | CashSession |
| `user()` | belongsTo | User |

---

### InventoryAdjustment (`app/Models/InventoryAdjustment.php`)

| Campo | Tipo |
|-------|------|
| `product_id` | integer (FK) |
| `user_id` | integer (FK) |
| `type` | enum (`in`, `out`, `set`) |
| `quantity` | decimal:3 |
| `previous_stock` | decimal:3 |
| `new_stock` | decimal:3 |
| `reason` | string |
| `notes` | text |

**Relaciones:**

| Relación | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `product()` | belongsTo | Product |
| `user()` | belongsTo | User |

---

### InventoryAdjustmentController (`app/Http/Controllers/InventoryAdjustmentController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index(Request $request)` | `/inventory-adjustments` | GET | Muestra el historial de ajustes y conteos físicos. Permite filtrar por tipo y producto. |
| `store(Request $request)` | `/inventory-adjustments` | POST | Registra un nuevo ajuste (entrada, salida) o conteo físico, y actualiza el stock del producto de forma atómica (usando DB Transaction). |
| `searchProducts(Request $request)` | `/inventory-adjustments/search-products` | GET | Retorna resultados de búsqueda JSON (AJAX) para seleccionar productos en el formulario de ajuste. |

---

## 🗺️ Rutas (`routes/web.php`)

### Rutas Públicas
| Método HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/login` | `AuthController@showLogin` | `login` |
| POST | `/login` | `AuthController@login` | — |
| POST | `/logout` | `AuthController@logout` | `logout` |

### Rutas Protegidas (middleware `auth`)
| Método HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/` | `HomeController@index` | `home` |
| POST | `/toggle-dark-mode` | `AuthController@toggleDarkMode` | `toggle-dark-mode` |

### Inventario (Resources)
| Recurso | Controlador | Rutas excluidas |
|---------|-------------|-----------------|
| `departments` | DepartmentController | `show` |
| `categories` | CategoryController | `create`, `show` |
| `brands` | BrandController | `create`, `show` |
| `providers` | ProviderController | `create`, `show` |
| `products` | ProductController | `show` |

### Ruta Adicional de Inventario
| Método HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/departments/{department}/categories` | `CategoryController@getByDepartment` | `departments.categories` |

### Configuración
| Método HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/settings` | `SettingController@index` | `settings.index` |
| POST | `/settings` | `SettingController@update` | `settings.update` |

### Finanzas (API)
| Método HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/currencies` | `CurrencyController@index` | `currencies.index` |
| GET | `/api/currencies` | `CurrencyController@fetchAll` | — |
| POST | `/api/currencies` | `CurrencyController@store` | — |
| PUT | `/api/currencies/{currency}` | `CurrencyController@update` | — |
| DELETE | `/api/currencies/{currency}` | `CurrencyController@destroy` | — |
| POST | `/api/payment-methods` | `PaymentMethodController@store` | — |
| PUT | `/api/payment-methods/{paymentMethod}` | `PaymentMethodController@update` | — |
| DELETE | `/api/payment-methods/{paymentMethod}` | `PaymentMethodController@destroy` | — |

---

## 🎨 Vistas (`resources/views/`)

| Vista | Descripción |
|-------|-------------|
| `auth/` | Vistas de autenticación (login) |
| `layouts/` | Layouts base del sistema |
| `home.blade.php` | Dashboard principal |
| `inventory/` | Vistas del módulo de inventario |
| `finances/` | Vistas del módulo de finanzas |
| `welcome.blade.php` | Vista de bienvenida predeterminada de Laravel |

---

## 🗄️ Migraciones

| Migración | Descripción |
|-----------|-------------|
| `0001_01_01_000000_create_users_table.php` | Tabla de usuarios del sistema |
| `0001_01_01_000001_create_cache_table.php` | Tabla de caché de Laravel |
| `0001_01_01_000002_create_jobs_table.php` | Tabla de trabajos en cola |
| `0001_01_01_000003_create_inventory_tables.php` | Tablas base de inventario (departamentos, categorías, productos, settings) |
| `2026_06_30_020129_add_department_id_to_categories_table.php` | Agrega `department_id` a categorías |
| `2026_06_30_023400_create_brands_table.php` | Tabla de marcas |
| `2026_06_30_023401_create_providers_table.php` | Tabla de proveedores |
| `2026_06_30_023402_add_brand_and_provider_to_products_table.php` | Agrega `brand_id` y `provider_id` a productos |
| `2026_07_01_003835_create_currencies_table.php` | Tabla de monedas |
| `2026_07_01_003844_create_payment_methods_table.php` | Tabla de métodos de pago |

---

## 🔗 Diagrama de Relaciones entre Modelos

```
Department (1) ──→ (N) Category (1) ──→ (N) Product
                                              ↑
Brand (1) ────────────────────────────────── (N)
Provider (1) ─────────────────────────────── (N)

Currency (1) ──→ (N) PaymentMethod

Setting (clave-valor independiente)
User (independiente con roles y permisos)
```

---

## ⚙️ Características del Sistema

- ✅ Autenticación por usuario y contraseña
- ✅ Modo oscuro por usuario (toggle vía AJAX)
- ✅ CRUD completo de Departamentos
- ✅ CRUD completo de Categorías (con relación a Departamentos)
- ✅ CRUD completo de Marcas (protección de "Genérico")
- ✅ CRUD completo de Proveedores (protección de "Genérico")
- ✅ CRUD completo de Productos con:
  - Filtros avanzados (código, categoría, marca, rango de precio)
  - Generación automática de código privado
  - Gestión de imágenes
  - Asignación automática de genéricos
- ✅ Sistema de configuración clave-valor
- ✅ Gestión de Monedas (con moneda predeterminada)
- ✅ Gestión de Métodos de Pago (asociados a monedas)
- ✅ Sistema de roles (`admin`) y permisos (JSON)
- ✅ Soporte dual: respuestas AJAX/JSON y redirecciones tradicionales
- ✅ Carga dinámica de categorías por departamento
- ✅ Protección de rutas con middleware `auth`
- ✅ Mensajes y validaciones en español
