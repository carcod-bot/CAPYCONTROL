# ðŸ“‹ GuÃ­a de ConstrucciÃ³n â€” CapyControl

> **Sistema**: CapyControl (Panel de AdministraciÃ³n e Inventario)  
> **Framework**: Laravel 11  
> **Ãšltima actualizaciÃ³n**: 2026-07-09

---

## ðŸ“ Estructura General del Proyecto

```
capycontrol/
â”œâ”€â”€ app/
â”‚   â”œâ”€â”€ Http/Controllers/
â”‚   â”‚   â”œâ”€â”€ Controller.php
â”‚   â”‚   â”œâ”€â”€ AuthController.php
â”‚   â”‚   â”œâ”€â”€ HomeController.php
â”‚   â”‚   â”œâ”€â”€ DepartmentController.php
â”‚   â”‚   â”œâ”€â”€ CategoryController.php
â”‚   â”‚   â”œâ”€â”€ BrandController.php
â”‚   â”‚   â”œâ”€â”€ ProviderController.php
â”‚   â”‚   â”œâ”€â”€ ProductController.php
â”‚   â”‚   â”œâ”€â”€ ParameterController.php
â”‚   â”‚   â”œâ”€â”€ SettingController.php
â”‚   â”‚   â”œâ”€â”€ CurrencyController.php
â”‚   â”‚   â”œâ”€â”€ PaymentMethodController.php
â”‚   â”‚   â”œâ”€â”€ CustomerController.php
â”‚   â”‚   â”œâ”€â”€ Finances/
â”‚   â”‚   â”‚   â””â”€â”€ CreditController.php
â”‚   â”‚   â”œâ”€â”€ CashRegisterController.php
â”‚   â”‚   â”œâ”€â”€ CashSessionController.php
â”‚   â”‚   â”œâ”€â”€ Administration/
â”‚   â”‚   â”‚   â”œâ”€â”€ CuadreController.php
â”‚   â”‚   â”‚   â””â”€â”€ InvoiceController.php
â”‚   â”‚   â””â”€â”€ Api/PosIntegrationController.php
â”‚   â”œâ”€â”€ Models/
â”‚   â”‚   â”œâ”€â”€ User.php
â”‚   â”‚   â”œâ”€â”€ Department.php
â”‚   â”‚   â”œâ”€â”€ Category.php
â”‚   â”‚   â”œâ”€â”€ Brand.php
â”‚   â”‚   â”œâ”€â”€ Provider.php
â”‚   â”‚   â”œâ”€â”€ Product.php
â”‚   â”‚   â”œâ”€â”€ Setting.php
â”‚   â”‚   â”œâ”€â”€ Currency.php
â”‚   â”‚   â”œâ”€â”€ PaymentMethod.php
â”‚   â”‚   â”œâ”€â”€ Customer.php
â”‚   â”‚   â”œâ”€â”€ CreditAccount.php
â”‚   â”‚   â”œâ”€â”€ CreditPayment.php
â”‚   â”‚   â”œâ”€â”€ CashRegister.php
â”‚   â”‚   â”œâ”€â”€ CashSession.php
â”‚   â”‚   â”œâ”€â”€ CashMovement.php
â”‚   â”‚   â””â”€â”€ Sale.php
â”‚   â””â”€â”€ Providers/
â”œâ”€â”€ database/migrations/
â”œâ”€â”€ resources/views/
â”‚   â”œâ”€â”€ auth/
â”‚   â”œâ”€â”€ layouts/
â”‚   â”œâ”€â”€ inventory/
â”‚   â”œâ”€â”€ finances/
â”‚   â”œâ”€â”€ pos-control/
â”‚   â”‚   â”œâ”€â”€ index.blade.php        â† Monitoreo (solo sesiones abiertas)
â”‚   â”‚   â””â”€â”€ registers.blade.php   â† GestiÃ³n de Cajas (CRUD con IP/Hostname)
â”‚   â”œâ”€â”€ administration/
â”‚   â”‚   â”œâ”€â”€ cuadre/
â”‚   â”‚   â”‚   â””â”€â”€ index.blade.php
â”‚   â”‚   â””â”€â”€ invoices/
â”‚   â”‚       â”œâ”€â”€ index.blade.php
â”‚   â”‚       â””â”€â”€ show.blade.php
â”‚   â”œâ”€â”€ configuraciones/
â”‚   â”‚   â”œâ”€â”€ parametros.blade.php
â”‚   â”‚   â””â”€â”€ usuarios.blade.php
â”‚   â”œâ”€â”€ home.blade.php
â”‚   â””â”€â”€ welcome.blade.php
â”œâ”€â”€ routes/
â”‚   â”œâ”€â”€ web.php
â”‚   â””â”€â”€ console.php
â””â”€â”€ public/
```

---

## ðŸ” MÃ³dulo de AutenticaciÃ³n

### AuthController (`app/Http/Controllers/AuthController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `showLogin()` | `/login` | GET | Muestra el formulario de login. Si el usuario ya estÃ¡ autenticado, redirige al home. |
| `login(Request $request)` | `/login` | POST | Procesa el inicio de sesiÃ³n. Valida `username` y `password`. Usa `Auth::attempt()` con opciÃ³n "recordarme". Mensajes de error en espaÃ±ol. |
| `logout(Request $request)` | `/logout` | POST | Cierra la sesiÃ³n del usuario, invalida la sesiÃ³n y regenera el token CSRF. |
| `toggleDarkMode(Request $request)` | `/toggle-dark-mode` | POST | Alterna el modo oscuro del usuario autenticado. Retorna JSON con el estado actual de `dark_mode`. |

---

## ðŸ  MÃ³dulo Home

### HomeController (`app/Http/Controllers/HomeController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/` | GET | Renderiza la vista principal `home`. Requiere autenticaciÃ³n. |

---

## ðŸ¢ MÃ³dulo de Departamentos

### DepartmentController (`app/Http/Controllers/DepartmentController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/departments` | GET | Lista todos los departamentos ordenados por nombre. Renderiza vista `inventory.departments.index`. |
| `store(Request $request)` | `/departments` | POST | Crea un nuevo departamento. Valida nombre (Ãºnico, mÃ¡x. 255) y descripciÃ³n (mÃ¡x. 500). Soporta respuestas AJAX y redirecciÃ³n. |
| `update(Request $request, Department)` | `/departments/{department}` | PUT | Actualiza un departamento existente. Valida unicidad excluyendo el ID actual. Soporta AJAX y redirecciÃ³n. |
| `destroy(Request $request, Department)` | `/departments/{department}` | DELETE | Elimina un departamento. Soporta AJAX y redirecciÃ³n. |

**Rutas excluidas del resource:** `show`

---

## ðŸ“‚ MÃ³dulo de CategorÃ­as

### CategoryController (`app/Http/Controllers/CategoryController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/categories` | GET | Lista categorÃ­as con su departamento asociado (eager loading). Carga departamentos activos para el formulario. |
| `store(Request $request)` | `/categories` | POST | Crea una nueva categorÃ­a. Valida nombre (Ãºnico), descripciÃ³n y `department_id` (debe existir). Soporta AJAX. |
| `getByDepartment($department_id)` | `/departments/{department}/categories` | GET | Retorna JSON con categorÃ­as activas filtradas por departamento. Usado para carga dinÃ¡mica de selects. |
| `update(Request $request, Category)` | `/categories/{category}` | PUT | Actualiza una categorÃ­a existente. Valida unicidad excluyendo el ID actual. |
| `destroy(Request $request, Category)` | `/categories/{category}` | DELETE | Elimina una categorÃ­a. Soporta AJAX y redirecciÃ³n. |

**Rutas excluidas del resource:** `create`, `show`

---

## ðŸ·ï¸ MÃ³dulo de Marcas

### BrandController (`app/Http/Controllers/BrandController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/brands` | GET | Lista todas las marcas ordenadas por nombre. Renderiza vista `inventory.brands.index`. |
| `store(Request $request)` | `/brands` | POST | Crea una nueva marca. Valida nombre (Ãºnico, mÃ¡x. 255) y descripciÃ³n. Soporta AJAX. |
| `update(Request $request, Brand)` | `/brands/{brand}` | PUT | Actualiza una marca existente. |
| `destroy(Request $request, Brand)` | `/brands/{brand}` | DELETE | Elimina una marca. **Protege la marca "GenÃ©rico"** de ser eliminada (retorna error 403). |

**Rutas excluidas del resource:** `create`, `show`

---

## ðŸšš MÃ³dulo de Proveedores

### ProviderController (`app/Http/Controllers/ProviderController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/providers` | GET | Lista todos los proveedores ordenados por nombre. Renderiza vista `inventory.providers.index`. |
| `store(Request $request)` | `/providers` | POST | Crea un nuevo proveedor. Valida nombre (Ãºnico, mÃ¡x. 255) y descripciÃ³n. Soporta AJAX. |
| `update(Request $request, Provider)` | `/providers/{provider}` | PUT | Actualiza un proveedor existente. |
| `destroy(Request $request, Provider)` | `/providers/{provider}` | DELETE | Elimina un proveedor. **Protege el proveedor "GenÃ©rico"** de ser eliminado (retorna error 403). |

**Rutas excluidas del resource:** `create`, `show`

---

## ðŸ“¦ MÃ³dulo de Productos

### ProductController (`app/Http/Controllers/ProductController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index(Request $request)` | `/products` | GET | Lista productos con filtros avanzados: bÃºsqueda por cÃ³digo (`search_code`), filtro por `category_id`, `brand_id`, rango de precio (`price_min`, `price_max`). Carga relaciones `department`, `category`, `brand`, `provider` con eager loading. Prepara datos para el modal de creaciÃ³n. |
| `create()` | `/products/create` | GET | Muestra formulario de creaciÃ³n. Carga departamentos activos, categorÃ­as activas, genera cÃ³digo privado automÃ¡tico y obtiene modo de cÃ³digo. |
| `store(Request $request)` | `/products` | POST | Crea un producto. Valida todos los campos incluyendo imagen (mÃ¡x. 2MB, formatos jpeg/png/jpg/gif/webp). Asigna automÃ¡ticamente `department_id` desde la categorÃ­a. Asigna marca y proveedor "GenÃ©rico" si no se especifican. Almacena imagen en disco `public`. |
| `edit(Product $product)` | `/products/{product}/edit` | GET | Retorna datos del producto para ediciÃ³n. Soporta respuesta JSON (AJAX) o vista Blade. Carga departamentos, categorÃ­as, marcas y proveedores. |
| `update(Request $request, Product)` | `/products/{product}` | PUT | Actualiza un producto existente. Elimina imagen anterior si se sube una nueva. Misma lÃ³gica de validaciÃ³n y genÃ©ricos que `store`. |
| `destroy(Request $request, Product)` | `/products/{product}` | DELETE | Elimina un producto y su imagen asociada del disco. |
| `massivePriceAdjustment(Request $request)` | `/products/massive-adjustment` | POST | Realiza ajustes masivos de precio (aumentos o descuentos en porcentaje o monto fijo) aplicando a mÃºltiples productos mediante filtros por categorÃ­a, departamento, marca, proveedor, o seleccionando productos especÃ­ficos mediante una tabla dinÃ¡mica. Registra una traza en `AuditLog`. |

**Rutas excluidas del resource:** `show`

**LÃ³gica especial:**
- GeneraciÃ³n automÃ¡tica de cÃ³digo privado (incremental o personalizado)
- AsignaciÃ³n automÃ¡tica de marca/proveedor "GenÃ©rico" cuando no se especifica
- DerivaciÃ³n automÃ¡tica de `department_id` desde la categorÃ­a seleccionada
- GestiÃ³n de imÃ¡genes con almacenamiento en disco pÃºblico

---

## âš™ï¸ MÃ³dulo de ConfiguraciÃ³n

### SettingController (`app/Http/Controllers/SettingController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/settings` | GET | Muestra la configuraciÃ³n actual: `private_code_start`, `private_code_mode`, `tax_type`, `tax_amount` y `tax_included`. |
| `update(Request $request)` | `/settings` | POST | Actualiza la configuraciÃ³n global, incluyendo el comportamiento del IVA (Porcentaje o Fijo, e inclusiÃ³n en precio base) para que el punto de venta (CapyPOS) lo aplique dinÃ¡micamente. |

---

## ðŸ’° MÃ³dulo de Finanzas
### CurrencyController (`app/Http/Controllers/CurrencyController.php`)
### PaymentMethodController (`app/Http/Controllers/PaymentMethodController.php`)
### CreditController (`app/Http/Controllers/Finances/CreditController.php`)
### CustomerController (`app/Http/Controllers/CustomerController.php`)
| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/currencies` | GET | Renderiza la vista `finances.currencies.index`. |
| `fetchAll()` | `/api/currencies` | GET | Retorna JSON con todas las monedas y sus mÃ©todos de pago. Las tasas de cambio se calculan de manera inversa (Ej: Para el BolÃ­var (Base = 1), el USD se almacena como el equivalente en BolÃ­vares de 1 USD, para facilitar el cÃ¡lculo contable). |
| `store(Request $request)` | `/api/currencies` | POST | Crea una nueva moneda. Valida cÃ³digo (Ãºnico), descripciÃ³n, sÃ­mbolo, decimales, tasa de cambio, cÃ³digo ISO, observaciÃ³n y flags (`is_default`, `is_active`, `used_in_pos`). Si se marca como predeterminada, desmarca las demÃ¡s. |
| `update(Request $request, Currency)` | `/api/currencies/{currency}` | PUT | Actualiza una moneda. Misma validaciÃ³n que `store`. Gestiona la moneda predeterminada. |
| `destroy(Currency)` | `/api/currencies/{currency}` | DELETE | Elimina una moneda. |

### PaymentMethodController (`app/Http/Controllers/PaymentMethodController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `store(Request $request)` | `/api/payment-methods` | POST | Crea un mÃ©todo de pago asociado a una moneda. Valida: `currency_id`, `code`, `description`, `value`, lÃ­mites de cambio/compra, y mÃºltiples flags booleanos (denominaciÃ³n real, permite cambio, verificaciÃ³n electrÃ³nica, adelanto efectivo, serial admin, auto-declarar, auto-depositar, facturaciÃ³n admin). |
| `update(Request $request, PaymentMethod)` | `/api/payment-methods/{paymentMethod}` | PUT | Actualiza un mÃ©todo de pago existente. |
| `destroy(PaymentMethod)` | `/api/payment-methods/{paymentMethod}` | DELETE | Elimina un mÃ©todo de pago. |

---

## ðŸ§¾ MÃ³dulo de Control POS (Puntos de Venta)

### CashRegisterController (`app/Http/Controllers/CashRegisterController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/pos-control` | GET | Muestra el dashboard de monitoreo de cajas con estadÃ­sticas y listado. |
| `store(Request $request)` | `/pos-control/registers` | POST | Crea una nueva caja registradora. |
| `update(Request $request, CashRegister)` | `/pos-control/registers/{cashRegister}` | PUT | Actualiza informaciÃ³n de una caja. |
| `destroy(Request $request, CashRegister)` | `/pos-control/registers/{cashRegister}` | DELETE | Elimina una caja. |
| `sessions(CashRegister)` | `/pos-control/registers/{cashRegister}/sessions` | GET | Retorna el historial de sesiones de una caja en formato JSON. |

### CashSessionController (`app/Http/Controllers/CashSessionController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `open(Request $request)` | `/pos-control/sessions/open` | POST | Abre un nuevo turno en una caja registradora con un fondo inicial. |
| `close(Request $request, CashSession)` | `/pos-control/sessions/{cashSession}/close` | POST | Cierra el turno actual, registrando monto real y diferencia. |
| `withdraw(Request $request, CashSession)` | `/pos-control/sessions/{cashSession}/withdraw` | POST | Registra un retiro de dinero en efectivo de la caja. |
| `deposit(Request $request, CashSession)` | `/pos-control/sessions/{cashSession}/deposit` | POST | Registra un depÃ³sito de dinero en la caja. |
| `show(CashSession)` | `/pos-control/sessions/{cashSession}` | GET | Devuelve los detalles de una sesiÃ³n especÃ­fica. |

### PosEventController (`app/Http/Controllers/PosEventController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index(Request $request)` | `/pos-control/events` | GET | Muestra el registro de Operaciones Autorizadas (como reportes Z, retiros, apertura de gavetas). Permite filtrar por tipo de evento y bÃºsqueda libre (supervisor, detalles, etc). |

---

## ðŸ“¦ Modelos

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

Almacena los productos individuales que han sido devueltos a la tienda en una DevoluciÃ³n/Nota de CrÃ©dito para auditorÃ­a y posible retorno a inventario.

| Campo | Tipo |
|-------|------|
| `sale_id` | foreignId |
| `product_id` | foreignId |
| `quantity_returned` | decimal |
| `amount` | decimal |
| `reason` | text (nullable) |
| `status` | string (pending_review, restocked, discarded) |



### AuditLog (`app/Models/AuditLog.php`)

Almacena el historial de cambios importantes en el sistema (ej: ajustes masivos de precio) para propÃ³sitos de auditorÃ­a y rastreo de acciones por usuario.

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

Registra operaciones sensibles o excepcionales realizadas en el Punto de Venta (ej. anulaciones, retiros de efectivo, apertura de gaveta) con detalles del autorizador para auditorÃ­a de caja.

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

Motor de descuentos dinÃ¡micos. Utiliza relaciones polimÃ³rficas para aplicarse a nivel de Producto, CategorÃ­a, Departamento o Moneda/MÃ©todo de Pago.

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

Niveles de fidelizaciÃ³n para clientes a crÃ©dito, escalando su lÃ­mite automÃ¡ticamente segÃºn su historial de compras.

| Campo | Tipo |
|-------|------|
| `name` | string |
| `required_purchases` | integer |
| `limit_increase_percentage` | decimal |

---

### CreditAccount (`app/Models/CreditAccount.php`)

Representa una deuda o cuenta por cobrar de un cliente, vinculada a una factura (`Sale`) especÃ­fica.

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

**MÃ©todos personalizados:**

| MÃ©todo | Retorno | DescripciÃ³n |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `department()` | belongsTo | Department |
| `category()` | belongsTo | Category |
| `brand()` | belongsTo | Brand |
| `provider()` | belongsTo | Provider |

**MÃ©todos personalizados:**

| MÃ©todo | Retorno | DescripciÃ³n |
|--------|---------|-------------|
| `generatePrivateCode()` | string (estÃ¡tico) | Genera el siguiente cÃ³digo privado basado en la configuraciÃ³n (`incremental` o `personalizado`). Calcula el mÃ¡ximo cÃ³digo existente y retorna el siguiente valor. |

---

### Setting (`app/Models/Setting.php`)

| Campo | Tipo |
|-------|------|
| `key` | string |
| `value` | string |

**MÃ©todos personalizados:**

| MÃ©todo | Retorno | DescripciÃ³n |
|--------|---------|-------------|
| `get(string $key, string $default)` | string (estÃ¡tico) | Obtiene el valor de una configuraciÃ³n por clave. Retorna el valor por defecto si no existe. |
| `set(string $key, string $value)` | void (estÃ¡tico) | Crea o actualiza una configuraciÃ³n (usa `updateOrCreate`). |

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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
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

| RelaciÃ³n | Tipo | Modelo relacionado |
|----------|------|--------------------|
| `product()` | belongsTo | Product |
| `user()` | belongsTo | User |

---

### InventoryAdjustmentController (`app/Http/Controllers/InventoryAdjustmentController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index(Request $request)` | `/inventory-adjustments` | GET | Muestra el historial de ajustes y conteos fÃ­sicos. Permite filtrar por tipo y producto. |
| `store(Request $request)` | `/inventory-adjustments` | POST | Registra un nuevo ajuste y gestiona los **Lotes (ProductBatches)** mediante metodologÃ­a **FIFO**. Las entradas (`in`) crean lotes nuevos, las salidas (`out`) descuentan el stock de los lotes mÃ¡s viejos activos. Un conteo fÃ­sico (`set`) calcula la diferencia e ingresa un lote de ajuste o descuenta lotes segÃºn sea necesario. |
| `searchProducts(Request $request)` | `/inventory-adjustments/search-products` | GET | Retorna resultados de bÃºsqueda JSON (AJAX) para seleccionar productos en el formulario de ajuste. |

---

### PrintController (`app/Http/Controllers/Inventory/PrintController.php`)

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `index()` | `/inventory/prints` | GET | Interfaz para preparar la cola de impresiÃ³n de etiquetas y habladores. |
| `search(Request $request)` | `/inventory/prints/search` | GET | BÃºsqueda AJAX de productos activos. |
| `generate(Request $request)` | `/inventory/prints/generate` | POST | Genera la vista de impresiÃ³n en HTML para el navegador. Configurable por tipo (`labels`, `talkers`), cÃ³digo (`ean`, `private`) y dimensiones. |

---

## ðŸ›’ MÃ³dulo de Ventas (IntegraciÃ³n CapyPOS)

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
| `ticket_number` | string (Ãšnico) |
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
| `document_id` | string (Ãšnico) |
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

| MÃ©todo | Ruta | Tipo | DescripciÃ³n |
|--------|------|------|-------------|
| `checkSession` | `/api/pos/session-status` | GET | Verifica si el cajero tiene un turno abierto y devuelve la configuraciÃ³n global `pos_config` (`tax_type`, `tax_amount`, `tax_included`, `currencies`, `payment_methods`). |
| "storeSale" | /api/pos/sales | POST | Recibe el carrito, descuenta stock global y **descuenta de lotes (FIFO)**, registra la venta y sus ítems. Valida idempotencia con ticket_number. |
| `searchCustomers` | `/api/pos/customers` | GET | Busca clientes por nombre o DNI. |
| `storeCustomer` | `/api/pos/customers` | POST | Crea un cliente de forma rÃ¡pida desde la caja. |
| `withdrawCash` | `/api/pos/session/withdraw` | POST | Registra un retiro de efectivo en la caja actual. |
| `closeSession` | `/api/pos/session/close` | POST | Cierra el turno del cajero validando el efectivo fÃ­sico (Reporte Z). |
| `logEvent` | `/api/pos/session/log-event` | POST | Registra eventos de punto de venta (gaveta, reportes Z y X, autorizaciones). |
| `getSale` | `/api/pos/sales/{ticket}` | GET | Busca una factura interna y sus productos para gestionar devoluciones. |
| `storeRefund` | `/api/pos/refund` | POST | Registra los productos devueltos a la tienda tras emitir una Nota de CrÃ©dito. |
| "getSyncData" | /api/pos/sync-data | GET | Exporta catálogo completo (productos, clientes, usuarios) para el modo offline de CapyPOS. |
| "syncSessions" | /api/pos/session/sync-sessions | POST | Recibe transacciones procesadas offline (aperturas, cierres, cobros). |

---

## ðŸ—ºï¸ Rutas (`routes/web.php` y `routes/api.php`)

### Rutas PÃºblicas
| MÃ©todo HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/login` | `AuthController@showLogin` | `login` |
| POST | `/login` | `AuthController@login` | â€” |
| POST | `/logout` | `AuthController@logout` | `logout` |

### Rutas Protegidas (middleware `auth`)
| MÃ©todo HTTP | URI | Controlador | Nombre |
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
| MÃ©todo HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/departments/{department}/categories` | `CategoryController@getByDepartment` | `departments.categories` |

### ConfiguraciÃ³n
| MÃ©todo HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/settings` | `SettingController@index` | `settings.index` |
| POST | `/settings` | `SettingController@update` | `settings.update` |

### Finanzas (API)
| MÃ©todo HTTP | URI | Controlador | Nombre |
|-------------|-----|-------------|--------|
| GET | `/currencies` | `CurrencyController@index` | `currencies.index` |
| GET | `/api/currencies` | `CurrencyController@fetchAll` | â€” |
| POST | `/api/currencies` | `CurrencyController@store` | â€” |
| PUT | `/api/currencies/{currency}` | `CurrencyController@update` | â€” |
| DELETE | `/api/currencies/{currency}` | `CurrencyController@destroy` | â€” |
| POST | `/api/payment-methods` | `PaymentMethodController@store` | â€” |
| PUT | `/api/payment-methods/{paymentMethod}` | `PaymentMethodController@update` | â€” |
| DELETE | `/api/payment-methods/{paymentMethod}` | `PaymentMethodController@destroy` | â€” |

---

## ðŸŽ¨ Vistas (`resources/views/`)

| Vista | DescripciÃ³n |
|-------|-------------|
| `auth/` | Vistas de autenticaciÃ³n (login) |
| `layouts/` | Layouts base del sistema |
| `home.blade.php` | Dashboard principal |
| `inventory/` | Vistas del mÃ³dulo de inventario |
| `finances/` | Vistas del mÃ³dulo de finanzas |
| `welcome.blade.php` | Vista de bienvenida predeterminada de Laravel |

---

## ðŸ—„ï¸ Migraciones

| MigraciÃ³n | DescripciÃ³n |
|-----------|-------------|
| `0001_01_01_000000_create_users_table.php` | Tabla de usuarios del sistema |
| `0001_01_01_000001_create_cache_table.php` | Tabla de cachÃ© de Laravel |
| `0001_01_01_000002_create_jobs_table.php` | Tabla de trabajos en cola |
| `0001_01_01_000003_create_inventory_tables.php` | Tablas base de inventario (departamentos, categorÃ­as, productos, settings) |
| `2026_06_30_020129_add_department_id_to_categories_table.php` | Agrega `department_id` a categorÃ­as |
| `2026_06_30_023400_create_brands_table.php` | Tabla de marcas |
| `2026_06_30_023401_create_providers_table.php` | Tabla de proveedores |
| `2026_06_30_023402_add_brand_and_provider_to_products_table.php` | Agrega `brand_id` y `provider_id` a productos |
| `2026_07_01_003835_create_currencies_table.php` | Tabla de monedas |
| `2026_07_01_003844_create_payment_methods_table.php` | Tabla de mÃ©todos de pago |
| `2026_07_08_000001_create_pos_control_tables.php` | Tablas `cash_registers`, `cash_sessions`, `cash_movements` |
| `2026_07_09_000001_add_ip_to_cash_registers.php` | Agrega `hostname` e `ip_address` a `cash_registers` |

---

## ðŸ”— Diagrama de Relaciones entre Modelos

```
Department (1) â”€â”€â†’ (N) Category (1) â”€â”€â†’ (N) Product
                                              â†‘
Brand (1) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ (N)
Provider (1) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ (N)

Currency (1) â”€â”€â†’ (N) PaymentMethod

Setting (clave-valor independiente)
User (independiente con roles y permisos)
```

---

## âš™ï¸ CaracterÃ­sticas del Sistema

- âœ… AutenticaciÃ³n por usuario y contraseÃ±a
- âœ… Modo oscuro por usuario (toggle vÃ­a AJAX)
- âœ… CRUD completo de Departamentos
- âœ… CRUD completo de CategorÃ­as (con relaciÃ³n a Departamentos)
- âœ… CRUD completo de Marcas (protecciÃ³n de "GenÃ©rico")
- âœ… CRUD completo de Proveedores (protecciÃ³n de "GenÃ©rico")
- âœ… CRUD completo de Productos con:
  - Filtros avanzados (cÃ³digo, categorÃ­a, marca, rango de precio)
  - GeneraciÃ³n automÃ¡tica de cÃ³digo privado
  - GestiÃ³n de imÃ¡genes
  - AsignaciÃ³n automÃ¡tica de genÃ©ricos
- âœ… Sistema de configuraciÃ³n clave-valor
- âœ… GestiÃ³n de Monedas (con moneda predeterminada)
- âœ… GestiÃ³n de MÃ©todos de Pago (asociados a monedas)
- âœ… Sistema de roles (`admin`) y permisos (JSON)
- âœ… Soporte dual: respuestas AJAX/JSON y redirecciones tradicionales
- âœ… Carga dinÃ¡mica de categorÃ­as por departamento
- âœ… ProtecciÃ³n de rutas con middleware `auth`
- âœ… Mensajes, alertas y validaciones en espaÃ±ol, utilizando **SweetAlert2** para modales estÃ©ticos
- âœ… Ajuste Masivo de Precios con filtros por CategorÃ­a, Departamento, Marca, Proveedor o SelecciÃ³n EspecÃ­fica.
- âœ… Registro de auditorÃ­a (AuditLog) para trazabilidad de cambios crÃ­ticos.

---

## ðŸ–¥ï¸ MÃ³dulo Control POS (2026-07-08 / 2026-07-09)

### DescripciÃ³n
MÃ³dulo de monitoreo y gestiÃ³n de cajas registradoras conectadas a CapyPOS.

### Pantallas

| Ruta | Vista | DescripciÃ³n |
|---|---|---|
| `GET /pos-control` | `pos-control/index` | **Monitoreo**: muestra solo cajas con sesiÃ³n abierta en tiempo real |
| `GET /pos-control/registers` | `pos-control/registers` | **GestiÃ³n de Cajas**: CRUD completo de cajas fÃ­sicas |

### Modelo de Datos

```
CashRegister (cajas fÃ­sicas)
  â”œâ”€â”€ number       â†’ NÂº de caja (Ãºnico, ej: 003)
  â”œâ”€â”€ name         â†’ Nombre descriptivo
  â”œâ”€â”€ location     â†’ UbicaciÃ³n fÃ­sica
  â”œâ”€â”€ hostname     â†’ Nombre del PC asignado (ej: CAJA-01)
  â”œâ”€â”€ ip_address   â†’ IP del PC en la red local (ej: 192.168.1.100)
  â””â”€â”€ active       â†’ Activa/inactiva

CashSession (turnos de caja)
  â”œâ”€â”€ cash_register_id, user_id
  â”œâ”€â”€ status (open/closed), turn_number
  â”œâ”€â”€ opening_amount, expected_amount, actual_amount, difference
  â”œâ”€â”€ total_sales, total_returns, total_withdrawals, pending_invoices
  â””â”€â”€ opened_at, closed_at, closing_notes

CashMovement (retiros/depÃ³sitos dentro de un turno)
  â”œâ”€â”€ cash_session_id, user_id
  â”œâ”€â”€ type (withdrawal/deposit/adjustment)
  â”œâ”€â”€ amount, reason, notes
```

### Control de Acceso por IP y Hostname

Cuando se registra una caja en **GestiÃ³n de Cajas**, se puede asignar:
- **Hostname**: nombre del PC (ej: `CAJA-01`). PHP obtiene el hostname real con `gethostname()` en el servidor CapyPOS.
- **IP del PC**: direcciÃ³n IP en la red local (ej: `192.168.1.100`).

**LÃ³gica de validaciÃ³n** (en `PosIntegrationController@checkSession`):
- Si la caja tiene **IP registrada** â†’ se valida contra `$request->ip()`
- Si la caja tiene **Hostname registrado** â†’ se valida contra el header `X-Hostname` (enviado automÃ¡ticamente por CapyPOS desde `gethostname()`)
- Si **ambos** estÃ¡n registrados â†’ **ambos deben coincidir**
- Si **ninguno** estÃ¡ registrado â†’ cualquier PC puede acceder
- **ExcepciÃ³n de Loopback (Localhost)**: Si la IP entrante es `127.0.0.1` o `::1` (CapyPOS y CapyControl estÃ¡n en la misma PC), la validaciÃ³n de IP se omite automÃ¡ticamente y se confÃ­a Ãºnicamente en la validaciÃ³n de `X-Hostname` para identificar la caja de forma segura.
- Si no coinciden â†’ CapyPOS muestra pantalla "Acceso No Autorizado" con detalle de quÃ© se esperaba vs. lo recibido

### IntegraciÃ³n CapyControl â†” CapyPOS (API)

```
GET  /api/pos/session-status   â†’ checkSession() â€” valida sesiÃ³n + IP + Hostname
POST /api/pos/session/open     â†’ openSession()  â€” abre turno directamente desde CapyPOS
POST /api/pos/sales            â†’ storeSale()    â€” procesa venta y actualiza stock
POST /api/pos/session/close    â†’ closeSession() â€” cierra turno
POST /api/pos/session/withdraw â†’ withdrawCash() â€” retiro parcial
GET  /api/pos/customers        â†’ searchCustomers()
POST /api/pos/customers        â†’ storeCustomer()
```

Headers requeridos en cada llamada de CapyPOS:
- `X-User-Id`: ID del usuario autenticado en CapyPOS
- `X-Hostname`: hostname del PC (leÃ­do de `<meta name="pc-hostname">` puesto por `gethostname()`)

### Seeders de Prueba

El archivo `PosControlSeeder.php` crea cajas **003, 004, 009, 010, 014** con sesiones de ejemplo para demostraciÃ³n. Para eliminarlo en producciÃ³n, remover la llamada al seeder en `DatabaseSeeder.php`.

---

## âš™ï¸ MÃ³dulo Configuraciones (Usuarios y Roles) - 2026-07-09

### DescripciÃ³n
GestiÃ³n centralizada del Acceso Basado en Roles (RBAC) con un enfoque hÃ­brido: Los usuarios heredan permisos de un **Rol Base**, pero pueden tener **Permisos Extras** aditivos.

### Modelos y Controladores
- **Role (`app/Models/Role.php`)**: Guarda `name`, `description`, `permissions` (array JSON) y `is_system` (boolean).
- **User (`app/Models/User.php`)**: Actualizado con `role_id` y `permissions` (JSON). Tiene mÃ©todos combinados:
  - `effectivePermissions()`: Fusiona permisos del Rol con permisos propios del Usuario.
  - `hasPermission($permission)`: Retorna `true` si el usuario o su rol tienen el permiso (los admins siempre retornan true).
- **UserController**: CRUD de usuarios con asignaciÃ³n de Rol y Permisos extra. Define la constante `ALL_PERMISSIONS` y sus etiquetas amigables.
- **RoleController**: CRUD de roles. Protege los roles con `is_system = true` (como Administrador) de ser eliminados o modificar sus permisos.

### Vistas e Interfaz
- `resources/views/configuraciones/usuarios.blade.php`: Vista unificada con pestaÃ±as dinÃ¡micas (JS) para gestionar tanto Usuarios como Roles. Usa modales nativos de CapyControl (`modal-overlay`) y carga dinÃ¡mica.
- `resources/views/configuraciones/parametros.blade.php`: Vista "placeholder" para futuras configuraciones generales.

### CaracterÃ­sticas Especiales
- **ProtecciÃ³n del Admin**: No se puede eliminar al Ãºltimo Administrador del sistema.
- **Auto-protecciÃ³n**: El usuario logueado no puede eliminarse a sÃ­ mismo.
- **Seguridad de Roles**: No se pueden eliminar roles que tengan usuarios asignados, forzando la reasignaciÃ³n primero.


## ðŸ”’ Seguridad RBAC y Experiencia de Usuario - 2026-07-09

### Control de Acceso (Middleware)
- Se implementÃ³ el middleware `CheckPermission` registrado bajo el alias `permission`.
- Protege todas las rutas del backend agrupÃ¡ndolas segÃºn los permisos (`inventory.view`, `pos_control.manage`, `configuraciones.edit`, etc.).
- Las peticiones web no autorizadas retornan 403 (Acceso Denegado), y las peticiones AJAX retornan un JSON de error 403.

### Interfaz Adaptativa en el Frontend
- El archivo `app.blade.php` ha sido actualizado para usar directivas de blade `@if(Auth::user()->hasPermission(...))`.
- Los dropdowns y enlaces del Topbar (Inventario, Finanzas, Control POS, Configuraciones) se ocultan fÃ­sicamente si el usuario no posee el permiso respectivo, garantizando una UI limpia.

### Mejoras en ValidaciÃ³n de Formularios
- Se ajustaron `UserController` y `RoleController` para forzar la validaciÃ³n de peticiones mediante `Validator::make` y el retorno estricto de JSON. Esto solucionÃ³ un conflicto nativo de Laravel 11 donde los errores de validaciÃ³n redireccionaban devolviendo HTML en lugar de avisos de error.
- Se implementÃ³ un diccionario de traducciones al espaÃ±ol localmente en los Controladores para solventar el renderizado en crudo de mensajes como `validation.min.string` en las alertas nativas de SweetAlert2.

---

## ðŸ“¦ GestiÃ³n Avanzada de Lotes y Ajustes Multi-Producto - 2026-07-10

### DescripciÃ³n
Se ha rediseÃ±ado el mÃ³dulo de Ajustes de Inventario para soportar la visualizaciÃ³n y gestiÃ³n avanzada de Lotes (ProductBatches), permitiendo procesar mÃºltiples productos de manera simultÃ¡nea en una sola operaciÃ³n de ajuste.

### Novedades
- **Tabla Principal de Ajustes:** Se incluyÃ³ la columna "Lote" para visualizaciÃ³n rÃ¡pida de los lotes afectados en cada movimiento, formateado como insignias en texto para facilitar la identificaciÃ³n. Al hacer hover sobre el botÃ³n de ciclo de vida se previsualiza la informaciÃ³n del lote.
- **Modal Multi-Producto (Entradas/Salidas Masivas):** El formulario fue refactorizado para permitir la adiciÃ³n dinÃ¡mica de mÃºltiples filas (productos). El "Motivo" y el "Tipo de Movimiento" (Entrada/Salida/Conteo) aplican globalmente a toda la tanda.
- **Trazabilidad Extendida (Ciclo de Vida):** Al hacer clic sobre una fila de la tabla, se abre un modal interactivo que muestra el historial de vida del lote vinculado (Cantidad inicial ingresada, cuÃ¡ntas se vendieron en CapyPOS, cuÃ¡ntas se restaron por daÃ±os y si hubo algÃºn reconteo fÃ­sico).

### Adaptaciones en el Controlador
- `InventoryAdjustmentController@store`: El endpoint ahora procesa un array estructurado (`products`) y ejecuta las transacciones y validaciones en bucle dentro de un `DB::beginTransaction()`, garantizando la atomicidad. Genera entradas mÃºltiples en `InventoryAdjustments` pero compartiendo el mismo contexto (fecha, tipo, motivo).
- `InventoryAdjustmentController@getBatchLifecycle`: Nuevo mÃ©todo encargado de leer las relaciones pivote entre Lotes y Ajustes para desglosar la historia cronolÃ³gica del lote ("Vendidas" leyendo la palabra "Venta" en el motivo, "DaÃ±os/Mermas" en el resto de salidas).

### OptimizaciÃ³n de Rendimiento
- **PaginaciÃ³n Global en Inventario:** Se implementÃ³ paginaciÃ³n (`paginate(20)`) en las vistas principales de Productos, Departamentos, CategorÃ­as, Marcas y Proveedores para mejorar el rendimiento del sistema y evitar cuellos de botella al cargar grandes volÃºmenes de datos. Se incluyeron enlaces de navegaciÃ³n estilo Bootstrap 4 en todas las tablas.

### Funcionalidades Extendidas (2026-07-10)
- **Trazabilidad de Ventas:** Al hacer clic en un ajuste de tipo Salida asociado a una Venta desde el POS, el sistema despliega automÃ¡ticamente un modal con los **Detalles de la Venta** (Ticket, Fecha, Cajero, MÃ©todo de Pago y Total).
- **EdiciÃ³n de Lotes:** Se habilitÃ³ la posibilidad de modificar atributos clave (como nÃºmeros de lote y fechas de vencimiento) directamente sobre las Entradas, desde el panel principal de Ajustes.
- **SincronizaciÃ³n Transaccional POS:** Se actualizÃ³ \PosIntegrationController\ para que no solo descuente las unidades del inventario global, sino que ahora asocia estrictamente las deducciones a nivel de lote usando la tabla pivote \inventory_adjustment_batch\. Este proceso sigue de forma automÃ¡tica y estricta el mÃ©todo FIFO.
- **Filtro de Lotes Terminados:** Se integrÃ³ un nuevo filtro en el dropdown de Tipos de Ajuste que permite visualizar exclusivamente las entradas de inventario en donde el lote generado haya alcanzado un stock actual de cero unidades (0).
- **Filtro Agrupado por Stock:** Se aÃ±adiÃ³ un modo de vista especial dentro del panel de Ajustes. Al seleccionar el filtro "Stock", la tabla de historial muta dinÃ¡micamente y se transforma en un reporte consolidado de inventario, agrupando por producto e indicando la cantidad de stock actual sin mezclarlo con el listado detallado de movimientos.
- **ConfiguraciÃ³n PWA e Identidad Visual:** Se aÃ±adiÃ³ soporte completo PWA (Progressive Web App) instalable con iconos oficiales en tamaÃ±os requeridos. Se unificÃ³ la tipografÃ­a general a 'Poppins' (Google Fonts) en todas las plantillas para alinearse con el ecosistema. AdemÃ¡s, se implementÃ³ un indicador visual del estado de vencimiento en los lotes (Vigente, Por Vencer, Vencido) directamente en la interfaz de Ajustes y Conteo.

---

## ðŸ’³ Opciones Avanzadas de MÃ©todos de Pago - 2026-07-12

### DescripciÃ³n
Se documentÃ³ e incorporÃ³ la interfaz visual de las configuraciones avanzadas para la creaciÃ³n y ediciÃ³n de MÃ©todos de Pago en el mÃ³dulo Financiero. Estas opciones (checkboxes) permiten modelar estrictamente cÃ³mo CapyPOS y el motor de cierres reaccionan ante cada forma de cobro.

### Funcionamiento de Opciones Clave:
- **DenominaciÃ³n Real:** Indica que es dinero fÃ­sico. En mÃ³dulos avanzados exige conteo por billetes y es el Ãºnico mÃ©todo permitido para **Retiros de Caja**.
- **Administra Serial:** Usado para Gift Cards, cheques o cupones. El sistema exige un serial y valida que no haya sido consumido previamente.
- **Permite Vuelto:** Le indica al POS si puede permitir pagos por montos superiores al total para generar una devoluciÃ³n en efectivo (Efectivo sÃ­, Tarjetas/Zelle no).
- **Auto Declarar (POS):** Indica que el dinero se asume ya "en el banco" y se cuadra automÃ¡ticamente. Por ende, **el cajero no deberÃ¡ contar ni declarar este dinero** durante su Cierre de Turno en el POS.
- **Auto Depositar (POS):** Al finalizar el turno, el monto recolectado se asume directamente como depositado o trasladado a la cuenta bancaria sin intervenciÃ³n administrativa manual.
- **Usado en POS:** Activa o desactiva la visibilidad del mÃ©todo en la pantalla del cajero (CapyPOS).
- **Usado en Fact. Adm.:** Activa o desactiva la visibilidad del mÃ©todo en la facturaciÃ³n o cobros directos desde el panel administrativo (CapyControl).
- **VerificaciÃ³n ElectrÃ³nica:** Fuerza al POS a pedir un "NÃºmero de Referencia" de forma obligatoria al recibir el pago (ideal para Transferencias y Pago MÃ³vil).
- **Avance de Efectivo:** Permite usar el mÃ©todo para procesar cobros por encima de la venta total con el fin de entregar efectivo al cliente.

**UI en CapyControl:** Se incorporaron *tooltips* explicativos nativos en los formularios de creaciÃ³n y ediciÃ³n para que los administradores conozcan de manera inmediata el impacto de cada opciÃ³n.


### GestiÃ³n de Caja: Reporte X, Declarar (Arqueo Parcial), Cierre de Turno y Reporte Z

> **IMPORTANTE:** Reporte X â‰  Declarar. Son conceptos distintos.

| AcciÃ³n | DescripciÃ³n | Efecto en sistema | Efecto en esperado |
|--------|-------------|-------------------|--------------------|
| **Reporte X** | Imprime en la impresora fiscal un resumen del turno actual sin resetear nada. Es solo informativo. | Ninguno | Ninguno |
| **Declarar (Arqueo Parcial)** | El cajero fÃ­sicamente cuenta el dinero y declara cuÃ¡nto tiene. El sistema registra un retiro por ese monto. | Crea `CashMovement` tipo `withdrawal` | **Reduce** el `expected_amount` por el monto declarado |
| **Cierre de Turno** | Finaliza la sesiÃ³n del cajero en el sistema. **No obliga a declarar montos**. Solo cierra el turno. | Cambia estado a `closed` | Sin cambio (queda como estaba) |
| **Reporte Z** | Emitido Ãºnicamente por la impresora fiscal. Representa el total del dÃ­a y **no tiene relaciÃ³n directa con un cierre de turno del sistema**. | Ninguno en el sistema | Ninguno |

#### Flujo recomendado para mÃºltiples cajeros en un mismo dÃ­a:
1. Cajero A abre turno â†’ trabaja â†’ hace **Arqueo Parcial** (opcional, para extraer dinero durante el turno) â†’ hace **Cierre de Turno** cuando termina.
2. Cajero B abre otro turno en la misma caja â†’ repite.
3. Al final del dÃ­a, el administrador hace el **Reporte Z** fÃ­sico de la impresora fiscal (que agrupa TODAS las ventas del dÃ­a de todos los turnos).

#### Cierre de Turno sin DeclaraciÃ³n:
Al presionar el botÃ³n de apagado (Power) en CapyPOS, el sistema pregunta:
- **"Solo salir"**: Sale de la sesiÃ³n web sin cerrar el turno en el sistema.
- **"Finalizar Turno"**: Cierra el turno directamente en CapyControl. El `actual_amount` y `difference` quedan en `null` (sin conciliaciÃ³n formal). Si se desea registrar diferencias, primero se debe hacer un **Declarar (Arqueo Parcial)** desde **F11 Opciones â†’ Declarar**.

---

### Actualizaciones Recientes (13/07/2026)

#### MÃ³dulo de AdministraciÃ³n (CapyControl):
- **Cuadre General y Cierres Forzados:**
  - El modal de *Cierre Forzado* se rediseÃ±Ã³ para cargar dinÃ¡micamente **solo los mÃ©todos de pago que registraron movimientos o ventas** durante el turno. Si una caja no tuvo actividad, no se exigirÃ¡ ninguna declaraciÃ³n y el sistema cuadrarÃ¡ con la base inicial automÃ¡ticamente.
  - El cÃ³digo se optimizÃ³ empleando AJAX (`fetch`) hacia el backend (`/admin/cuadre/{session}/declaration-fields`) que evalÃºa en tiempo real las operaciones de `SalePayment` y `CashMovement`.
- **MÃ³dulo de Facturas:**
  - **VisualizaciÃ³n Tipo Ticket:** Se aÃ±adiÃ³ la funcionalidad de abrir facturas directamente como tickets no-fiscales usando diseÃ±o web (HTML puro estilo recibo). Cuenta con la capacidad directa de ser **impreso** en impresoras tÃ©rmicas.
  - **Filtros Avanzados:** El buscador de facturas se expandiÃ³. La casilla "Producto" no solo busca facturas donde aparezca el nombre, sino que estÃ¡ vinculado directamente para cruzar y buscar por **cÃ³digo interno** o **cÃ³digo de barras EAN**.
  - **UI (Dropdowns):** Se unificÃ³ el apartado de "Acciones" en la tabla para presentar un menÃº desplegable moderno, reparando posibles colisiones de estilos y optimizando espacio visual.

#### Actualizaciones del 14/07/2026:
- **Trazabilidad de Devoluciones (Notas de CrÃ©dito):**
  - **Base de Datos:** Se aÃ±adiÃ³ el campo `refund_parent_sale_id` a la tabla `sales` para guardar el enlace directo entre una nueva venta y la factura original devuelta.
  - **Backend (PosIntegrationController):** El endpoint de guardado de ventas `storeSale` ahora captura y procesa el `refund_parent_sale_id` enviado por CapyPOS.
  - **AdministraciÃ³n:** La vista de Ã­ndice (`index.blade.php`) y visualizaciÃ³n de facturas (`show.blade.php`) ahora identifican si una factura provino de una devoluciÃ³n, mostrando un icono de intercambio amarillo y una alerta informativa con el ticket de origen.
- **ConfiguraciÃ³n de Empresa y Modo "No Fiscal":**
  - **Base de Datos & ParÃ¡metros:** Se aÃ±adieron nuevos parÃ¡metros a la tabla `settings`: `company_name`, `company_rif`, `company_location`, `company_branch` y `is_fiscal`.
  - **Controlador (`ParameterController`):** Modificado para extraer y validar los datos de la empresa y la modalidad de impresiÃ³n.
  - **Vistas:** Actualizada la vista de parÃ¡metros (`parametros.blade.php`) para incluir una nueva tarjeta con el formulario de "Datos de la Empresa y Modalidad".
  - **ExportaciÃ³n a POS:** `PosIntegrationController` inyecta ahora los datos de la empresa y la configuraciÃ³n `is_fiscal` dentro del objeto global `pos_config` enviado al Punto de Venta al iniciar sesiÃ³n, y tambiÃ©n a travÃ©s de los endpoints de validaciÃ³n rÃ¡pida (`checkSession` y `openSession`) garantizando sincronizaciÃ³n en tiempo real.

## ðŸŽ MÃ³dulo de Promociones y Descuentos - 2026-07-16
### DescripciÃ³n
Se creÃ³ un sistema completo para gestionar promociones y descuentos dinÃ¡micos (porcentaje o monto fijo) asignables a distintos niveles del inventario y finanzas. 

### Modelo de Datos (`app/Models/Promotion.php`)
- **MigraciÃ³n (`2026_07_16_150420_create_promotions_table`)**: Define la tabla `promotions` utilizando relaciones polimÃ³rficas (`promotable_id` y `promotable_type`) lo cual permite que un descuento apunte a un `Product`, `Category`, `Department`, `Currency`, o `PaymentMethod`. 
- Incorpora atributos como `name`, `discount_type` ('percentage' o 'fixed'), `discount_value`, `start_date`, `end_date`, y un toggle de activaciÃ³n `active`.

### Controlador (`app/Http/Controllers/PromotionController.php`)
- Gestiona el CRUD completo mediante peticiones asÃ­ncronas JSON.
- Implementa mÃ©todos para listar (DataTable), crear, y alternar el estado (toggle) sin recargar la pÃ¡gina.

### Vistas e Interfaz (`resources/views/inventory/promotions/index.blade.php`)
- **Modal de CreaciÃ³n Inteligente**: Usando `Select2` y `Flatpickr`, el formulario de creaciÃ³n es dinÃ¡mico. Al elegir un "Nivel de AplicaciÃ³n" (ej. CategorÃ­a o Moneda), el campo inferior se vacÃ­a y repuebla usando opciones maestras ocultas, logrando un filtrado instantÃ¡neo para seleccionar el objetivo correcto.
- **Frontend Mejorado**: Se aplicaron micro-animaciones, tablas limpias, y alertas estÃ©ticas. El ancho del modal fue ajustado (800px) para acomodar los selectores cÃ³modamente.

### IntegraciÃ³n con CapyPOS
- **Backend (PosIntegrationController)**: Expone el endpoint `/api/pos/promotions` que entrega la lista de promociones activas (cuya fecha de inicio/fin abarque el dÃ­a actual) para el POS. Los productos buscados ahora exportan su `category_id` y `department_id` para que el punto de venta (CapyPOS) pueda aplicar la lÃ³gica de descuentos.

## âš™ï¸ Actualizaciones Recientes (22/07/2026)
### Mejoras de Interfaz y Experiencia de Usuario (UI/UX)
- **PaginaciÃ³n en Tablas:** Se implementÃ³ paginaciÃ³n nativa de Laravel (Bootstrap 5) en diversas tablas, como Productos, Clientes, Cuentas por Cobrar y Operaciones Autorizadas, con soporte para 20 registros por pÃ¡gina preservando los parÃ¡metros de bÃºsqueda (Query Strings).
- **Alertas SwalFire:** Se integraron alertas atractivas no intrusivas en la parte superior derecha de la pantalla (Toast de SweetAlert2) para notificar con Ã©xito acciones crÃ­ticas, por ejemplo, la confirmaciÃ³n de la carga de lotes de inventario y ajustes masivos.

### AmpliaciÃ³n de Entidades en Ajustes y Descuentos
- **Marcas y Proveedores:** Las entidades de Marca (Brand) y Proveedor (Provider) fueron incorporadas tanto en la interfaz y lÃ³gica de los **Ajustes de Inventario**, permitiendo filtrar y aplicar lotes de productos segÃºn su proveedor/marca. De la misma manera, se incluyeron en el **MÃ³dulo de Promociones y Descuentos**, para aplicar rebajas porcentuales o fijas de manera global a una marca o proveedor especÃ­fico en CapyPOS.

### MÃ³dulo de Operaciones Autorizadas
- **Registro PosEvent:** Se creÃ³ el modelo, controlador y vista (PosEventController) en el backoffice que documenta todas las acciones sensibles que ocurren en caja (ej: devoluciones, retiros, cancelaciones de facturas) y quiÃ©n las autorizÃ³, permitiendo a los administradores mantener un historial inmutable de auditorÃ­a.

### ðŸ’³ MÃ³dulo de Cuentas por Cobrar (CrÃ©ditos)
- **Modelos:** Se ampliÃ³ Customer (incluyendo lÃ­mite y deuda actual) y PaymentMethod (bandera de crÃ©dito). Se crearon CreditAccount (facturas pendientes) y CreditPayment (abonos).
- **IntegraciÃ³n API POS:** En PosIntegrationController, cuando se recibe un pago de crÃ©dito en la venta (storeSale), se genera la deuda del cliente validando su lÃ­mite, y el monto a crÃ©dito no se suma al dinero fÃ­sico de la caja (expected_amount). Se agregÃ³ el endpoint /api/pos/credit/pay para el cobro o abono de deudas. Los abonos distribuyen el pago (FIFO) en las cuentas pendientes y el cajero recibe este dinero ingresÃ¡ndolo al saldo de la caja de su turno activo.
- **Controladores y Vistas:** Se implementÃ³ CustomerController (CRUD de clientes) y CreditController (estado de cuenta detallado de la deuda por cada factura).
- **Sistema de Niveles de CrÃ©dito:** Se implementÃ³ el modelo `CreditLevel` con configuraciÃ³n de incremento automÃ¡tico. En el backend de CapyControl, el modelo `Customer` verifica el total de compras del cliente y ajusta automÃ¡ticamente (multiplicador) el lÃ­mite de crÃ©dito del cliente si este sube de nivel.

### ?? Dashboard y Estadísticas Rápidas (23/07/2026)
- **Controlador (`HomeController.php`):** Se integraron las consultas hacia los modelos `Sale`, `CreditAccount`, `CashSession` y `Product` para nutrir la vista de inicio del administrador.
- **Vista (`home.blade.php`):** Se transformó de una vista en blanco a un panel interactivo moderno usando CSS Grid, variables CSS dinámicas y Chart.js.
  - Se visualizan en tiempo real: Ventas del día, Cantidad de Tickets, Monto total de Cuentas por Cobrar y Turnos Activos.
  - Un gráfico muestra la tendencia de ventas de los últimos 7 días.
  - Tableros secundarios que listan las últimas 5 ventas (en tiempo real) y un monitor que alerta sobre los productos cuyo inventario sea igual o inferior a 10 unidades (stock crítico).


#### Actualizaciones del 23/07/2026:
- **GestiÃ³n de POS en Modo HÃ­brido/Off-Line:**
  - **SincronizaciÃ³n de Cajas (Sesiones):** Se desarrollÃ³ el endpoint /api/pos/session/sync-sessions en el PosIntegrationController para recibir en bloque las sesiones abiertas, cerradas, declaraciones de efectivo y pagos de crÃ©dito procesados de forma local por el Punto de Venta sin internet.
  - **RecepciÃ³n de Datos Completos (Usuarios):** Se corrigiÃ³ la lÃ³gica en /api/pos/sync-data cambiando el constructor por un Query Builder (DB::table('users')->get()) para garantizar que se empaqueten los campos ocultos (como la contraseÃ±a y token) y el POS pueda autenticar a los empleados localmente.
  - **Idempotencia (Ventas):** El endpoint /api/pos/sales ahora respeta el 	icket_number nativo generado por CapyPOS de forma asÃ­ncrona. Se implementÃ³ una barrera que bloquea (con estado success silencioso) la duplicaciÃ³n de ventas y de descuentos de inventario si el POS reenvÃ­a transacciones debido a tiempos de espera (timeout) originados por intermitencias del Wi-Fi.
  - **SweetAlert2 para AlineaciÃ³n:** Todas las confirmaciones para "Alinear Cajas" (descarga forzada de ventas desconectadas) desde la vista de Puntos de Venta (Dashboard) fueron migradas de alertas nativas del navegador a modales de SweetAlert2 (Swal.fire), manteniendo la coherencia estÃ©tica con el resto de la interfaz.


#### Actualizaciones del 24/07/2026:
- **Limpieza de Entorno y Seguridad:** Se eliminaron los mÃºltiples scripts temporales de prueba en la raÃ­z del proyecto para asegurar un entorno de producciÃ³n limpio.
- **AuditorÃ­a Estricta de SincronizaciÃ³n:** Se ratifica el requerimiento y validaciÃ³n del encabezado HTTP X-User-Id en las peticiones de subida de ventas offline (/api/pos/sales). Esto garantiza que, incluso cuando la sincronizaciÃ³n ocurre de forma invisible en segundo plano, toda factura importada desde una caja desconectada mantenga la trazabilidad exacta e inmutable del cajero original en la base de datos central.

#### Actualizaciones del 27/07/2026:
- **Rendimiento del Entorno y Cargas de SesiÃ³n:**
  - Se configurÃ³ el entorno local (.env) para gestionar las sesiones y el cachÃ© mediante el sistema de archivos (SESSION_DRIVER=file y CACHE_STORE=file). Esto reduce drÃ¡sticamente la latencia generada por las continuas consultas a la base de datos que se realizaban en el entorno local.
  - Se ajustÃ³ el archivo de inicializaciÃ³n de Node.js actualizÃ¡ndolo a v24 LTS para asegurar que el motor de **Vite 8** y **Tailwind 4** puedan compilar eficientemente los assets en el servidor.
- **TransiciÃ³n y Experiencia de Usuario (UI/UX):**
  - **Dark Mode DinÃ¡mico:** Se modificÃ³ la funcionalidad de cambio a modo oscuro (	oggleDarkMode en pp.blade.php). En vez de hacer un location.reload() tras la peticiÃ³n, ahora alterna las clases (.dark-mode) directamente en el cliente mediante Javascript.
  - **TransiciÃ³n Global:** Se incorporÃ³ en esources/css/app.css una regla base global (	ransition-colors duration-300) que provee animaciones suaves a todos los cambios de fondo, texto y bordes.
- **Correcciones en MÃ³dulo de Productos y Búsquedas:**
  - Se solucionÃ³ un bug en la vista de inventario de productos (index.blade.php) donde el botÃ³n de "Nuevo Producto" apuntaba al ID incorrecto productModal. Se ajustÃ³ para apuntar al nombre de la ventana correcto createProductModal.
  - Se aplicaron optimizaciones para evitar recargas completas innecesarias en las búsquedas de productos y clientes.
- **Correcciones Financieras Críticas (Precisión Decimal y Abonos a Crédito):**
  - **Bug de Cuotas Fantasmas:** Se solucionó un problema crítico originado por el estándar de flotantes (IEEE 754) en PHP. La resta sucesiva del abono contra las deudas dejaba residuos microscópicos (ej. `0.00000000001`), lo que provocaba que las facturas no se marcaran como "pagadas" en su totalidad y permanecieran pendientes en el sistema POS. Se incluyó un `epsilon` de mitigación (`+ 0.01`) al comparar montos en el `PosIntegrationController` garantizando el cierre absoluto de las deudas y cuotas.
  - **Trazabilidad de Abonos en Cuotas:** Las cuotas individuales (`credit_installments`) ahora almacenan detalladamente las llaves foráneas completas del pago: `payment_cash_session_id`, `payment_user_id` y `payment_method_id` (Caja, Cajero y Vía de Pago). Esta información ahora se expone visualmente en el desglose interactivo del perfil del cliente (Cronograma de Pagos) de CapyControl.

