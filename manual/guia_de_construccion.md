# 📋 Guía de Construcción — CapyControl

> **Sistema**: CapyControl (Panel de Administración e Inventario)  
> **Framework**: Laravel 11  
> **Última actualización**: 2026-07-09

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
│   │   ├── ParameterController.php
│   │   ├── SettingController.php
│   │   ├── CurrencyController.php
│   │   ├── PaymentMethodController.php
│   │   ├── CustomerController.php
│   │   ├── Finances/
│   │   │   └── CreditController.php
│   │   ├── CashRegisterController.php
│   │   ├── CashSessionController.php
│   │   ├── Administration/
│   │   │   ├── CuadreController.php
│   │   │   └── InvoiceController.php
│   │   └── Api/PosIntegrationController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Department.php
│   │   ├── Category.php
│   │   ├── Brand.php
│   │   ├── Provider.php
│   │   ├── Product.php
│   │   ├── Setting.php
│   │   ├── Currency.php
│   │   ├── PaymentMethod.php
│   │   ├── Customer.php
│   │   ├── CreditAccount.php
│   │   ├── CreditPayment.php
│   │   ├── CashRegister.php
│   │   ├── CashSession.php
│   │   ├── CashMovement.php
│   │   └── Sale.php
│   └── Providers/
├── database/migrations/
├── resources/views/
│   ├── auth/
│   ├── layouts/
│   ├── inventory/
│   ├── finances/
│   ├── pos-control/
│   │   ├── index.blade.php        ← Monitoreo (solo sesiones abiertas)
│   │   └── registers.blade.php   ← Gestión de Cajas (CRUD con IP/Hostname)
│   ├── administration/
│   │   ├── cuadre/
│   │   │   └── index.blade.php
│   │   └── invoices/
│   │       ├── index.blade.php
│   │       └── show.blade.php
│   ├── configuraciones/
│   │   ├── parametros.blade.php
│   │   └── usuarios.blade.php
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
| `massivePriceAdjustment(Request $request)` | `/products/massive-adjustment` | POST | Realiza ajustes masivos de precio (aumentos o descuentos en porcentaje o monto fijo) aplicando a múltiples productos mediante filtros por categoría, departamento, marca, proveedor, o seleccionando productos específicos mediante una tabla dinámica. Registra una traza en `AuditLog`. |

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
| `index()` | `/settings` | GET | Muestra la configuración actual: `private_code_start`, `private_code_mode`, `tax_type`, `tax_amount` y `tax_included`. |
| `update(Request $request)` | `/settings` | POST | Actualiza la configuración global, incluyendo el comportamiento del IVA (Porcentaje o Fijo, e inclusión en precio base) para que el punto de venta (CapyPOS) lo aplique dinámicamente. |

---

## 💰 Módulo de Finanzas
### CurrencyController (`app/Http/Controllers/CurrencyController.php`)
### PaymentMethodController (`app/Http/Controllers/PaymentMethodController.php`)
### CreditController (`app/Http/Controllers/Finances/CreditController.php`)
### CustomerController (`app/Http/Controllers/CustomerController.php`)
| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/currencies` | GET | Renderiza la vista `finances.currencies.index`. |
| `fetchAll()` | `/api/currencies` | GET | Retorna JSON con todas las monedas y sus métodos de pago. Las tasas de cambio se calculan de manera inversa (Ej: Para el Bolívar (Base = 1), el USD se almacena como el equivalente en Bolívares de 1 USD, para facilitar el cálculo contable). |
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

### PosEventController (`app/Http/Controllers/PosEventController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index(Request $request)` | `/pos-control/events` | GET | Muestra el registro de Operaciones Autorizadas (como reportes Z, retiros, apertura de gavetas). Permite filtrar por tipo de evento y búsqueda libre (supervisor, detalles, etc). |

---

## 📦 Modelos

### PosEvent (`app/Models/PosEvent.php`)

Registra eventos operacionales y fiscales disparados desde CapyPOS (ej. Apertura de Gaveta, Reporte Z, Reporte X).

| Campo | Tipo |
|-------|------|
| `cash_session_id` | foreignId |
| `user_id` | foreignId |
| `supervisor_id` | foreignId (nullable) |
| `event_type` | string |
| `description` | text (nullable) |

### FiscalReport (`app/Models/FiscalReport.php`)

Almacena la trama completa devuelta por la impresora fiscal y el correlativo del reporte Z o X.

| Campo | Tipo |
|-------|------|
| `pos_event_id` | foreignId |
| `report_type` | string (z_report, x_report) |
| `report_number` | string |
| `raw_data` | text |

### ReturnedProduct (`app/Models/ReturnedProduct.php`)

Almacena los productos individuales que han sido devueltos a la tienda en una Devolución/Nota de Crédito para auditoría y posible retorno a inventario.

| Campo | Tipo |
|-------|------|
| `sale_id` | foreignId |
| `product_id` | foreignId |
| `quantity_returned` | decimal |
| `amount` | decimal |
| `reason` | text (nullable) |
| `status` | string (pending_review, restocked, discarded) |



### AuditLog (`app/Models/AuditLog.php`)

Almacena el historial de cambios importantes en el sistema (ej: ajustes masivos de precio) para propósitos de auditoría y rastreo de acciones por usuario.

| Campo | Tipo |
|-------|------|
| `user_id` | foreignId |
| `action` | string |
| `model_type` | string (nullable) |
| `model_id` | unsignedBigInteger (nullable) |
| `old_values` | json (nullable) |
| `new_values` | json (nullable) |
| `details` | json (nullable) |

---

### PosEvent (`app/Models/PosEvent.php`)

Registra operaciones sensibles o excepcionales realizadas en el Punto de Venta (ej. anulaciones, retiros de efectivo, apertura de gaveta) con detalles del autorizador para auditoría de caja.

| Campo | Tipo |
|-------|------|
| `cash_session_id` | foreignId |
| `cashier_id` | foreignId |
| `authorizer_id` | foreignId (nullable) |
| `event_type` | string |
| `description` | text |
| `metadata` | json (nullable) |

---

### Promotion (`app/Models/Promotion.php`)

Motor de descuentos dinámicos. Utiliza relaciones polimórficas para aplicarse a nivel de Producto, Categoría, Departamento o Moneda/Método de Pago.

| Campo | Tipo |
|-------|------|
| `name` | string |
| `promotable_type` | string (polymorphic) |
| `promotable_id` | unsignedBigInteger |
| `discount_type` | string (percentage, fixed) |
| `discount_value` | decimal |
| `start_date` | date |
| `end_date` | date |
| `active` | boolean |

---

### CreditLevel (`app/Models/CreditLevel.php`)

Niveles de fidelización para clientes a crédito, escalando su límite automáticamente según su historial de compras.

| Campo | Tipo |
|-------|------|
| `name` | string |
| `required_purchases` | integer |
| `limit_increase_percentage` | decimal |

---

### CreditAccount (`app/Models/CreditAccount.php`)

Representa una deuda o cuenta por cobrar de un cliente, vinculada a una factura (`Sale`) específica.

| Campo | Tipo |
|-------|------|
| `customer_id` | foreignId |
| `sale_id` | foreignId |
| `amount` | decimal |
| `paid_amount` | decimal |
| `status` | string (pending, partial, paid) |
| `due_date` | date |

**Relaciones principales:** `customer()`, `sale()`, `payments()`, `installments()`.

---

### CreditPayment (`app/Models/CreditPayment.php`)

Registra los abonos realizados por los clientes para amortizar sus cuentas por cobrar (`CreditAccount`).

| Campo | Tipo |
|-------|------|
| `credit_account_id` | foreignId (nullable) |
| `customer_id` | foreignId |
| `amount` | decimal |
| `payment_method_id` | foreignId |
| `cash_session_id` | foreignId |
| `user_id` | foreignId |
| `notes` | text (nullable) |

---

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
### Customer (`app/Models/Customer.php`)
### CreditAccount (`app/Models/CreditAccount.php`)
### CreditPayment (`app/Models/CreditPayment.php`)
### CashRegister (`app/Models/CashRegister.php`)

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

### ProductBatch (`app/Models/ProductBatch.php`)

| Campo | Tipo |
|-------|------|
| `product_id` | integer (FK) |
| `batch_number` | string |
| `provider_id` | integer (FK) - Nullable |
| `brand_id` | integer (FK) - Nullable |
| `expiry_date` | date - Nullable |
| `initial_quantity` | decimal:3 |
| `current_quantity` | decimal:3 |

**Relaciones:**
`product()` -> `Product`
`provider()` -> `Provider`
`brand()` -> `Brand`

**Uso:** 
Maneja el stock por lotes (FIFO). Las entradas de inventario crean nuevos lotes y las salidas los descuentan ordenadamente.

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
| `store(Request $request)` | `/inventory-adjustments` | POST | Registra un nuevo ajuste y gestiona los **Lotes (ProductBatches)** mediante metodología **FIFO**. Las entradas (`in`) crean lotes nuevos, las salidas (`out`) descuentan el stock de los lotes más viejos activos. Un conteo físico (`set`) calcula la diferencia e ingresa un lote de ajuste o descuenta lotes según sea necesario. |
| `searchProducts(Request $request)` | `/inventory-adjustments/search-products` | GET | Retorna resultados de búsqueda JSON (AJAX) para seleccionar productos en el formulario de ajuste. |

---

### PrintController (`app/Http/Controllers/Inventory/PrintController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `index()` | `/inventory/prints` | GET | Interfaz para preparar la cola de impresión de etiquetas y habladores. |
| `search(Request $request)` | `/inventory/prints/search` | GET | Búsqueda AJAX de productos activos. |
| `generate(Request $request)` | `/inventory/prints/generate` | POST | Genera la vista de impresión en HTML para el navegador. Configurable por tipo (`labels`, `talkers`), código (`ean`, `private`) y dimensiones. |

---

## 🛒 Módulo de Ventas (Integración CapyPOS)

### Sale (`app/Models/Sale.php`)

| Campo | Tipo |
|-------|------|
| `cash_session_id` | integer (FK) |
| `user_id` | integer (FK) |
| `customer_id` | integer (FK) - Nullable |
| `payment_method` | string |
| `total_amount` | decimal:2 |
| `tax_amount` | decimal:2 |
| `tendered_amount` | decimal:2 |
| `change_amount` | decimal:2 |
| `status` | enum (`completed`, `voided`, `refunded`) |
| `ticket_number` | string (Único) |
| `notes` | text |

**Relaciones:**
`cashSession()` -> `CashSession`
`user()` -> `User`
`customer()` -> `Customer`
`items()` -> `SaleItem` (hasMany)

---

### Customer (`app/Models/Customer.php`)

| Campo | Tipo |
|-------|------|
| `name` | string |
| `document_id` | string (Único) |
| `phone` | string |
| `email` | string |
| `address` | text |

**Relaciones:**
`sales()` -> `Sale` (hasMany)

---

### SaleItem (`app/Models/SaleItem.php`)

| Campo | Tipo |
|-------|------|
| `sale_id` | integer (FK) |
| `product_id` | integer (FK) |
| `product_name` | string |
| `product_code` | string |
| `quantity` | decimal:3 |
| `unit_price` | decimal:2 |
| `subtotal` | decimal:2 |

---

### PosIntegrationController (`app/Http/Controllers/Api/PosIntegrationController.php`)

| Método | Ruta | Tipo | Descripción |
|--------|------|------|-------------|
| `checkSession` | `/api/pos/session-status` | GET | Verifica si el cajero tiene un turno abierto y devuelve la configuración global `pos_config` (`tax_type`, `tax_amount`, `tax_included`, `currencies`, `payment_methods`). |
| `storeSale` | `/api/pos/sales` | POST | Recibe el carrito, descuenta stock global y **descuenta de lotes (FIFO)**, registra la venta y sus ítems. |
| `searchCustomers` | `/api/pos/customers` | GET | Busca clientes por nombre o DNI. |
| `storeCustomer` | `/api/pos/customers` | POST | Crea un cliente de forma rápida desde la caja. |
| `withdrawCash` | `/api/pos/session/withdraw` | POST | Registra un retiro de efectivo en la caja actual. |
| `closeSession` | `/api/pos/session/close` | POST | Cierra el turno del cajero validando el efectivo físico (Reporte Z). |
| `logEvent` | `/api/pos/session/log-event` | POST | Registra eventos de punto de venta (gaveta, reportes Z y X, autorizaciones). |
| `getSale` | `/api/pos/sales/{ticket}` | GET | Busca una factura interna y sus productos para gestionar devoluciones. |
| `storeRefund` | `/api/pos/refund` | POST | Registra los productos devueltos a la tienda tras emitir una Nota de Crédito. |

---

## 🗺️ Rutas (`routes/web.php` y `routes/api.php`)

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
| `2026_07_08_000001_create_pos_control_tables.php` | Tablas `cash_registers`, `cash_sessions`, `cash_movements` |
| `2026_07_09_000001_add_ip_to_cash_registers.php` | Agrega `hostname` e `ip_address` a `cash_registers` |

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
- ✅ Mensajes, alertas y validaciones en español, utilizando **SweetAlert2** para modales estéticos
- ✅ Ajuste Masivo de Precios con filtros por Categoría, Departamento, Marca, Proveedor o Selección Específica.
- ✅ Registro de auditoría (AuditLog) para trazabilidad de cambios críticos.

---

## 🖥️ Módulo Control POS (2026-07-08 / 2026-07-09)

### Descripción
Módulo de monitoreo y gestión de cajas registradoras conectadas a CapyPOS.

### Pantallas

| Ruta | Vista | Descripción |
|---|---|---|
| `GET /pos-control` | `pos-control/index` | **Monitoreo**: muestra solo cajas con sesión abierta en tiempo real |
| `GET /pos-control/registers` | `pos-control/registers` | **Gestión de Cajas**: CRUD completo de cajas físicas |

### Modelo de Datos

```
CashRegister (cajas físicas)
  ├── number       → Nº de caja (único, ej: 003)
  ├── name         → Nombre descriptivo
  ├── location     → Ubicación física
  ├── hostname     → Nombre del PC asignado (ej: CAJA-01)
  ├── ip_address   → IP del PC en la red local (ej: 192.168.1.100)
  └── active       → Activa/inactiva

CashSession (turnos de caja)
  ├── cash_register_id, user_id
  ├── status (open/closed), turn_number
  ├── opening_amount, expected_amount, actual_amount, difference
  ├── total_sales, total_returns, total_withdrawals, pending_invoices
  └── opened_at, closed_at, closing_notes

CashMovement (retiros/depósitos dentro de un turno)
  ├── cash_session_id, user_id
  ├── type (withdrawal/deposit/adjustment)
  ├── amount, reason, notes
```

### Control de Acceso por IP y Hostname

Cuando se registra una caja en **Gestión de Cajas**, se puede asignar:
- **Hostname**: nombre del PC (ej: `CAJA-01`). PHP obtiene el hostname real con `gethostname()` en el servidor CapyPOS.
- **IP del PC**: dirección IP en la red local (ej: `192.168.1.100`).

**Lógica de validación** (en `PosIntegrationController@checkSession`):
- Si la caja tiene **IP registrada** → se valida contra `$request->ip()`
- Si la caja tiene **Hostname registrado** → se valida contra el header `X-Hostname` (enviado automáticamente por CapyPOS desde `gethostname()`)
- Si **ambos** están registrados → **ambos deben coincidir**
- Si **ninguno** está registrado → cualquier PC puede acceder
- **Excepción de Loopback (Localhost)**: Si la IP entrante es `127.0.0.1` o `::1` (CapyPOS y CapyControl están en la misma PC), la validación de IP se omite automáticamente y se confía únicamente en la validación de `X-Hostname` para identificar la caja de forma segura.
- Si no coinciden → CapyPOS muestra pantalla "Acceso No Autorizado" con detalle de qué se esperaba vs. lo recibido

### Integración CapyControl ↔ CapyPOS (API)

```
GET  /api/pos/session-status   → checkSession() — valida sesión + IP + Hostname
POST /api/pos/session/open     → openSession()  — abre turno directamente desde CapyPOS
POST /api/pos/sales            → storeSale()    — procesa venta y actualiza stock
POST /api/pos/session/close    → closeSession() — cierra turno
POST /api/pos/session/withdraw → withdrawCash() — retiro parcial
GET  /api/pos/customers        → searchCustomers()
POST /api/pos/customers        → storeCustomer()
```

Headers requeridos en cada llamada de CapyPOS:
- `X-User-Id`: ID del usuario autenticado en CapyPOS
- `X-Hostname`: hostname del PC (leído de `<meta name="pc-hostname">` puesto por `gethostname()`)

### Seeders de Prueba

El archivo `PosControlSeeder.php` crea cajas **003, 004, 009, 010, 014** con sesiones de ejemplo para demostración. Para eliminarlo en producción, remover la llamada al seeder en `DatabaseSeeder.php`.

---

## ⚙️ Módulo Configuraciones (Usuarios y Roles) - 2026-07-09

### Descripción
Gestión centralizada del Acceso Basado en Roles (RBAC) con un enfoque híbrido: Los usuarios heredan permisos de un **Rol Base**, pero pueden tener **Permisos Extras** aditivos.

### Modelos y Controladores
- **Role (`app/Models/Role.php`)**: Guarda `name`, `description`, `permissions` (array JSON) y `is_system` (boolean).
- **User (`app/Models/User.php`)**: Actualizado con `role_id` y `permissions` (JSON). Tiene métodos combinados:
  - `effectivePermissions()`: Fusiona permisos del Rol con permisos propios del Usuario.
  - `hasPermission($permission)`: Retorna `true` si el usuario o su rol tienen el permiso (los admins siempre retornan true).
- **UserController**: CRUD de usuarios con asignación de Rol y Permisos extra. Define la constante `ALL_PERMISSIONS` y sus etiquetas amigables.
- **RoleController**: CRUD de roles. Protege los roles con `is_system = true` (como Administrador) de ser eliminados o modificar sus permisos.

### Vistas e Interfaz
- `resources/views/configuraciones/usuarios.blade.php`: Vista unificada con pestañas dinámicas (JS) para gestionar tanto Usuarios como Roles. Usa modales nativos de CapyControl (`modal-overlay`) y carga dinámica.
- `resources/views/configuraciones/parametros.blade.php`: Vista "placeholder" para futuras configuraciones generales.

### Características Especiales
- **Protección del Admin**: No se puede eliminar al último Administrador del sistema.
- **Auto-protección**: El usuario logueado no puede eliminarse a sí mismo.
- **Seguridad de Roles**: No se pueden eliminar roles que tengan usuarios asignados, forzando la reasignación primero.


## 🔒 Seguridad RBAC y Experiencia de Usuario - 2026-07-09

### Control de Acceso (Middleware)
- Se implementó el middleware `CheckPermission` registrado bajo el alias `permission`.
- Protege todas las rutas del backend agrupándolas según los permisos (`inventory.view`, `pos_control.manage`, `configuraciones.edit`, etc.).
- Las peticiones web no autorizadas retornan 403 (Acceso Denegado), y las peticiones AJAX retornan un JSON de error 403.

### Interfaz Adaptativa en el Frontend
- El archivo `app.blade.php` ha sido actualizado para usar directivas de blade `@if(Auth::user()->hasPermission(...))`.
- Los dropdowns y enlaces del Topbar (Inventario, Finanzas, Control POS, Configuraciones) se ocultan físicamente si el usuario no posee el permiso respectivo, garantizando una UI limpia.

### Mejoras en Validación de Formularios
- Se ajustaron `UserController` y `RoleController` para forzar la validación de peticiones mediante `Validator::make` y el retorno estricto de JSON. Esto solucionó un conflicto nativo de Laravel 11 donde los errores de validación redireccionaban devolviendo HTML en lugar de avisos de error.
- Se implementó un diccionario de traducciones al español localmente en los Controladores para solventar el renderizado en crudo de mensajes como `validation.min.string` en las alertas nativas de SweetAlert2.

---

## 📦 Gestión Avanzada de Lotes y Ajustes Multi-Producto - 2026-07-10

### Descripción
Se ha rediseñado el módulo de Ajustes de Inventario para soportar la visualización y gestión avanzada de Lotes (ProductBatches), permitiendo procesar múltiples productos de manera simultánea en una sola operación de ajuste.

### Novedades
- **Tabla Principal de Ajustes:** Se incluyó la columna "Lote" para visualización rápida de los lotes afectados en cada movimiento, formateado como insignias en texto para facilitar la identificación. Al hacer hover sobre el botón de ciclo de vida se previsualiza la información del lote.
- **Modal Multi-Producto (Entradas/Salidas Masivas):** El formulario fue refactorizado para permitir la adición dinámica de múltiples filas (productos). El "Motivo" y el "Tipo de Movimiento" (Entrada/Salida/Conteo) aplican globalmente a toda la tanda.
- **Trazabilidad Extendida (Ciclo de Vida):** Al hacer clic sobre una fila de la tabla, se abre un modal interactivo que muestra el historial de vida del lote vinculado (Cantidad inicial ingresada, cuántas se vendieron en CapyPOS, cuántas se restaron por daños y si hubo algún reconteo físico).

### Adaptaciones en el Controlador
- `InventoryAdjustmentController@store`: El endpoint ahora procesa un array estructurado (`products`) y ejecuta las transacciones y validaciones en bucle dentro de un `DB::beginTransaction()`, garantizando la atomicidad. Genera entradas múltiples en `InventoryAdjustments` pero compartiendo el mismo contexto (fecha, tipo, motivo).
- `InventoryAdjustmentController@getBatchLifecycle`: Nuevo método encargado de leer las relaciones pivote entre Lotes y Ajustes para desglosar la historia cronológica del lote ("Vendidas" leyendo la palabra "Venta" en el motivo, "Daños/Mermas" en el resto de salidas).

### Optimización de Rendimiento
- **Paginación Global en Inventario:** Se implementó paginación (`paginate(20)`) en las vistas principales de Productos, Departamentos, Categorías, Marcas y Proveedores para mejorar el rendimiento del sistema y evitar cuellos de botella al cargar grandes volúmenes de datos. Se incluyeron enlaces de navegación estilo Bootstrap 4 en todas las tablas.

### Funcionalidades Extendidas (2026-07-10)
- **Trazabilidad de Ventas:** Al hacer clic en un ajuste de tipo Salida asociado a una Venta desde el POS, el sistema despliega automáticamente un modal con los **Detalles de la Venta** (Ticket, Fecha, Cajero, Método de Pago y Total).
- **Edición de Lotes:** Se habilitó la posibilidad de modificar atributos clave (como números de lote y fechas de vencimiento) directamente sobre las Entradas, desde el panel principal de Ajustes.
- **Sincronización Transaccional POS:** Se actualizó \PosIntegrationController\ para que no solo descuente las unidades del inventario global, sino que ahora asocia estrictamente las deducciones a nivel de lote usando la tabla pivote \inventory_adjustment_batch\. Este proceso sigue de forma automática y estricta el método FIFO.
- **Filtro de Lotes Terminados:** Se integró un nuevo filtro en el dropdown de Tipos de Ajuste que permite visualizar exclusivamente las entradas de inventario en donde el lote generado haya alcanzado un stock actual de cero unidades (0).
- **Filtro Agrupado por Stock:** Se añadió un modo de vista especial dentro del panel de Ajustes. Al seleccionar el filtro "Stock", la tabla de historial muta dinámicamente y se transforma en un reporte consolidado de inventario, agrupando por producto e indicando la cantidad de stock actual sin mezclarlo con el listado detallado de movimientos.
- **Configuración PWA e Identidad Visual:** Se añadió soporte completo PWA (Progressive Web App) instalable con iconos oficiales en tamaños requeridos. Se unificó la tipografía general a 'Poppins' (Google Fonts) en todas las plantillas para alinearse con el ecosistema. Además, se implementó un indicador visual del estado de vencimiento en los lotes (Vigente, Por Vencer, Vencido) directamente en la interfaz de Ajustes y Conteo.

---

## 💳 Opciones Avanzadas de Métodos de Pago - 2026-07-12

### Descripción
Se documentó e incorporó la interfaz visual de las configuraciones avanzadas para la creación y edición de Métodos de Pago en el módulo Financiero. Estas opciones (checkboxes) permiten modelar estrictamente cómo CapyPOS y el motor de cierres reaccionan ante cada forma de cobro.

### Funcionamiento de Opciones Clave:
- **Denominación Real:** Indica que es dinero físico. En módulos avanzados exige conteo por billetes y es el único método permitido para **Retiros de Caja**.
- **Administra Serial:** Usado para Gift Cards, cheques o cupones. El sistema exige un serial y valida que no haya sido consumido previamente.
- **Permite Vuelto:** Le indica al POS si puede permitir pagos por montos superiores al total para generar una devolución en efectivo (Efectivo sí, Tarjetas/Zelle no).
- **Auto Declarar (POS):** Indica que el dinero se asume ya "en el banco" y se cuadra automáticamente. Por ende, **el cajero no deberá contar ni declarar este dinero** durante su Cierre de Turno en el POS.
- **Auto Depositar (POS):** Al finalizar el turno, el monto recolectado se asume directamente como depositado o trasladado a la cuenta bancaria sin intervención administrativa manual.
- **Usado en POS:** Activa o desactiva la visibilidad del método en la pantalla del cajero (CapyPOS).
- **Usado en Fact. Adm.:** Activa o desactiva la visibilidad del método en la facturación o cobros directos desde el panel administrativo (CapyControl).
- **Verificación Electrónica:** Fuerza al POS a pedir un "Número de Referencia" de forma obligatoria al recibir el pago (ideal para Transferencias y Pago Móvil).
- **Avance de Efectivo:** Permite usar el método para procesar cobros por encima de la venta total con el fin de entregar efectivo al cliente.

**UI en CapyControl:** Se incorporaron *tooltips* explicativos nativos en los formularios de creación y edición para que los administradores conozcan de manera inmediata el impacto de cada opción.


### Gestión de Caja: Reporte X, Declarar (Arqueo Parcial), Cierre de Turno y Reporte Z

> **IMPORTANTE:** Reporte X ≠ Declarar. Son conceptos distintos.

| Acción | Descripción | Efecto en sistema | Efecto en esperado |
|--------|-------------|-------------------|--------------------|
| **Reporte X** | Imprime en la impresora fiscal un resumen del turno actual sin resetear nada. Es solo informativo. | Ninguno | Ninguno |
| **Declarar (Arqueo Parcial)** | El cajero físicamente cuenta el dinero y declara cuánto tiene. El sistema registra un retiro por ese monto. | Crea `CashMovement` tipo `withdrawal` | **Reduce** el `expected_amount` por el monto declarado |
| **Cierre de Turno** | Finaliza la sesión del cajero en el sistema. **No obliga a declarar montos**. Solo cierra el turno. | Cambia estado a `closed` | Sin cambio (queda como estaba) |
| **Reporte Z** | Emitido únicamente por la impresora fiscal. Representa el total del día y **no tiene relación directa con un cierre de turno del sistema**. | Ninguno en el sistema | Ninguno |

#### Flujo recomendado para múltiples cajeros en un mismo día:
1. Cajero A abre turno → trabaja → hace **Arqueo Parcial** (opcional, para extraer dinero durante el turno) → hace **Cierre de Turno** cuando termina.
2. Cajero B abre otro turno en la misma caja → repite.
3. Al final del día, el administrador hace el **Reporte Z** físico de la impresora fiscal (que agrupa TODAS las ventas del día de todos los turnos).

#### Cierre de Turno sin Declaración:
Al presionar el botón de apagado (Power) en CapyPOS, el sistema pregunta:
- **"Solo salir"**: Sale de la sesión web sin cerrar el turno en el sistema.
- **"Finalizar Turno"**: Cierra el turno directamente en CapyControl. El `actual_amount` y `difference` quedan en `null` (sin conciliación formal). Si se desea registrar diferencias, primero se debe hacer un **Declarar (Arqueo Parcial)** desde **F11 Opciones → Declarar**.

---

### Actualizaciones Recientes (13/07/2026)

#### Módulo de Administración (CapyControl):
- **Cuadre General y Cierres Forzados:**
  - El modal de *Cierre Forzado* se rediseñó para cargar dinámicamente **solo los métodos de pago que registraron movimientos o ventas** durante el turno. Si una caja no tuvo actividad, no se exigirá ninguna declaración y el sistema cuadrará con la base inicial automáticamente.
  - El código se optimizó empleando AJAX (`fetch`) hacia el backend (`/admin/cuadre/{session}/declaration-fields`) que evalúa en tiempo real las operaciones de `SalePayment` y `CashMovement`.
- **Módulo de Facturas:**
  - **Visualización Tipo Ticket:** Se añadió la funcionalidad de abrir facturas directamente como tickets no-fiscales usando diseño web (HTML puro estilo recibo). Cuenta con la capacidad directa de ser **impreso** en impresoras térmicas.
  - **Filtros Avanzados:** El buscador de facturas se expandió. La casilla "Producto" no solo busca facturas donde aparezca el nombre, sino que está vinculado directamente para cruzar y buscar por **código interno** o **código de barras EAN**.
  - **UI (Dropdowns):** Se unificó el apartado de "Acciones" en la tabla para presentar un menú desplegable moderno, reparando posibles colisiones de estilos y optimizando espacio visual.

#### Actualizaciones del 14/07/2026:
- **Trazabilidad de Devoluciones (Notas de Crédito):**
  - **Base de Datos:** Se añadió el campo `refund_parent_sale_id` a la tabla `sales` para guardar el enlace directo entre una nueva venta y la factura original devuelta.
  - **Backend (PosIntegrationController):** El endpoint de guardado de ventas `storeSale` ahora captura y procesa el `refund_parent_sale_id` enviado por CapyPOS.
  - **Administración:** La vista de índice (`index.blade.php`) y visualización de facturas (`show.blade.php`) ahora identifican si una factura provino de una devolución, mostrando un icono de intercambio amarillo y una alerta informativa con el ticket de origen.
- **Configuración de Empresa y Modo "No Fiscal":**
  - **Base de Datos & Parámetros:** Se añadieron nuevos parámetros a la tabla `settings`: `company_name`, `company_rif`, `company_location`, `company_branch` y `is_fiscal`.
  - **Controlador (`ParameterController`):** Modificado para extraer y validar los datos de la empresa y la modalidad de impresión.
  - **Vistas:** Actualizada la vista de parámetros (`parametros.blade.php`) para incluir una nueva tarjeta con el formulario de "Datos de la Empresa y Modalidad".
  - **Exportación a POS:** `PosIntegrationController` inyecta ahora los datos de la empresa y la configuración `is_fiscal` dentro del objeto global `pos_config` enviado al Punto de Venta al iniciar sesión, y también a través de los endpoints de validación rápida (`checkSession` y `openSession`) garantizando sincronización en tiempo real.

## 🎁 Módulo de Promociones y Descuentos - 2026-07-16
### Descripción
Se creó un sistema completo para gestionar promociones y descuentos dinámicos (porcentaje o monto fijo) asignables a distintos niveles del inventario y finanzas. 

### Modelo de Datos (`app/Models/Promotion.php`)
- **Migración (`2026_07_16_150420_create_promotions_table`)**: Define la tabla `promotions` utilizando relaciones polimórficas (`promotable_id` y `promotable_type`) lo cual permite que un descuento apunte a un `Product`, `Category`, `Department`, `Currency`, o `PaymentMethod`. 
- Incorpora atributos como `name`, `discount_type` ('percentage' o 'fixed'), `discount_value`, `start_date`, `end_date`, y un toggle de activación `active`.

### Controlador (`app/Http/Controllers/PromotionController.php`)
- Gestiona el CRUD completo mediante peticiones asíncronas JSON.
- Implementa métodos para listar (DataTable), crear, y alternar el estado (toggle) sin recargar la página.

### Vistas e Interfaz (`resources/views/inventory/promotions/index.blade.php`)
- **Modal de Creación Inteligente**: Usando `Select2` y `Flatpickr`, el formulario de creación es dinámico. Al elegir un "Nivel de Aplicación" (ej. Categoría o Moneda), el campo inferior se vacía y repuebla usando opciones maestras ocultas, logrando un filtrado instantáneo para seleccionar el objetivo correcto.
- **Frontend Mejorado**: Se aplicaron micro-animaciones, tablas limpias, y alertas estéticas. El ancho del modal fue ajustado (800px) para acomodar los selectores cómodamente.

### Integración con CapyPOS
- **Backend (PosIntegrationController)**: Expone el endpoint `/api/pos/promotions` que entrega la lista de promociones activas (cuya fecha de inicio/fin abarque el día actual) para el POS. Los productos buscados ahora exportan su `category_id` y `department_id` para que el punto de venta (CapyPOS) pueda aplicar la lógica de descuentos.

## ⚙️ Actualizaciones Recientes (22/07/2026)
### Mejoras de Interfaz y Experiencia de Usuario (UI/UX)
- **Paginación en Tablas:** Se implementó paginación nativa de Laravel (Bootstrap 5) en diversas tablas, como Productos, Clientes, Cuentas por Cobrar y Operaciones Autorizadas, con soporte para 20 registros por página preservando los parámetros de búsqueda (Query Strings).
- **Alertas SwalFire:** Se integraron alertas atractivas no intrusivas en la parte superior derecha de la pantalla (Toast de SweetAlert2) para notificar con éxito acciones críticas, por ejemplo, la confirmación de la carga de lotes de inventario y ajustes masivos.

### Ampliación de Entidades en Ajustes y Descuentos
- **Marcas y Proveedores:** Las entidades de Marca (Brand) y Proveedor (Provider) fueron incorporadas tanto en la interfaz y lógica de los **Ajustes de Inventario**, permitiendo filtrar y aplicar lotes de productos según su proveedor/marca. De la misma manera, se incluyeron en el **Módulo de Promociones y Descuentos**, para aplicar rebajas porcentuales o fijas de manera global a una marca o proveedor específico en CapyPOS.

### Módulo de Operaciones Autorizadas
- **Registro PosEvent:** Se creó el modelo, controlador y vista (PosEventController) en el backoffice que documenta todas las acciones sensibles que ocurren en caja (ej: devoluciones, retiros, cancelaciones de facturas) y quién las autorizó, permitiendo a los administradores mantener un historial inmutable de auditoría.

### 💳 Módulo de Cuentas por Cobrar (Créditos)
- **Modelos:** Se amplió Customer (incluyendo límite y deuda actual) y PaymentMethod (bandera de crédito). Se crearon CreditAccount (facturas pendientes) y CreditPayment (abonos).
- **Integración API POS:** En PosIntegrationController, cuando se recibe un pago de crédito en la venta (storeSale), se genera la deuda del cliente validando su límite, y el monto a crédito no se suma al dinero físico de la caja (expected_amount). Se agregó el endpoint /api/pos/credit/pay para el cobro o abono de deudas. Los abonos distribuyen el pago (FIFO) en las cuentas pendientes y el cajero recibe este dinero ingresándolo al saldo de la caja de su turno activo.
- **Controladores y Vistas:** Se implementó CustomerController (CRUD de clientes) y CreditController (estado de cuenta detallado de la deuda por cada factura).
- **Sistema de Niveles de Crédito:** Se implementó el modelo `CreditLevel` con configuración de incremento automático. En el backend de CapyControl, el modelo `Customer` verifica el total de compras del cliente y ajusta automáticamente (multiplicador) el límite de crédito del cliente si este sube de nivel.
