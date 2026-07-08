<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Capycontrol - @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() + 600 }}">
    <!-- jQuery and Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @stack('styles')
</head>
<body class="{{ Auth::user() && Auth::user()->dark_mode ? 'dark-mode' : '' }}">
    <div class="app-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-inner">
                <div class="topbar-left">
                    <a href="{{ route('home') }}" class="topbar-logo">
                        <img src="http://localhost/capynom/public/img/logo.png" alt="Logo">
                        <span><span style="color: var(--text-main);">Capy</span><span style="color: var(--primary);">control</span></span>
                    </a>
                    <nav class="topbar-nav">
                        <a href="{{ route('home') }}" class="topbar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i> Dashboard
                        </a>

                        <div class="topbar-dropdown" id="inventarioDropdown">
                            <button class="topbar-dropdown-toggle {{ request()->routeIs('products.*', 'categories.*', 'departments.*', 'settings.*', 'brands.*', 'providers.*') ? 'active' : '' }}" onclick="toggleTopbarDropdown('inventarioDropdown')">
                                <i class="fa-solid fa-box"></i> Inventario <i class="fa-solid fa-chevron-down chevron"></i>
                            </button>
                            <div class="topbar-dropdown-menu">
                                <a href="{{ route('products.index') }}" class="topbar-dropdown-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-cube"></i> Productos
                                </a>
                                <a href="{{ route('categories.index') }}" class="topbar-dropdown-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-tags"></i> Categorías
                                </a>
                                <a href="{{ route('departments.index') }}" class="topbar-dropdown-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-layer-group"></i> Departamentos
                                </a>
                                <a href="{{ route('brands.index') }}" class="topbar-dropdown-item {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-copyright"></i> Marcas
                                </a>
                                <a href="{{ route('providers.index') }}" class="topbar-dropdown-item {{ request()->routeIs('providers.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-truck"></i> Proveedores
                                </a>
                                <a href="{{ route('settings.index') }}" class="topbar-dropdown-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-cog"></i> Configuración
                                </a>
                                <div style="border-top: 1px solid var(--border); margin: 0.5rem 0;"></div>
                                <a href="{{ route('inventory-adjustments.index') }}" class="topbar-dropdown-item {{ request()->routeIs('inventory-adjustments.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-scale-balanced"></i> Ajustes y Conteo
                                </a>
                            </div>
                        </div>

                        <div class="topbar-dropdown" id="finanzasDropdown">
                            <button class="topbar-dropdown-toggle {{ request()->routeIs('currencies.*') ? 'active' : '' }}" onclick="toggleTopbarDropdown('finanzasDropdown')">
                                <i class="fa-solid fa-money-bill-transfer"></i> Finanzas <i class="fa-solid fa-chevron-down chevron"></i>
                            </button>
                            <div class="topbar-dropdown-menu">
                                <a href="{{ route('currencies.index') }}" class="topbar-dropdown-item {{ request()->routeIs('currencies.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-coins"></i> Monedas y Métodos de Pago
                                </a>
                            </div>
                        </div>

                        <div class="topbar-dropdown" id="posControlDropdown">
                            <button class="topbar-dropdown-toggle {{ request()->routeIs('pos-control.*') ? 'active' : '' }}" onclick="toggleTopbarDropdown('posControlDropdown')">
                                <i class="fa-solid fa-cash-register"></i> Control POS <i class="fa-solid fa-chevron-down chevron"></i>
                            </button>
                            <div class="topbar-dropdown-menu">
                                <a href="{{ route('pos-control.index') }}" class="topbar-dropdown-item {{ request()->routeIs('pos-control.index') ? 'active' : '' }}">
                                    <i class="fa-solid fa-desktop"></i> Monitoreo de Cajas
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>

                <div class="topbar-right">
                    <button onclick="toggleDarkMode()" class="dark-mode-btn" title="Alternar Modo Oscuro">
                        <i class="fa-solid {{ Auth::user() && Auth::user()->dark_mode ? 'fa-sun' : 'fa-moon' }}"></i>
                    </button>

                    <div class="topbar-user" id="userDropdown">
                        <button class="topbar-user-btn" onclick="toggleTopbarDropdown('userDropdown')">
                            <div class="topbar-user-avatar">
                                {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                            </div>
                            <span class="topbar-user-name">{{ Auth::user()->username }}</span>
                            <i class="fa-solid fa-chevron-down chevron"></i>
                        </button>
                        <div class="topbar-user-dropdown">
                            <div class="topbar-user-info">
                                <div class="name">{{ Auth::user()->username }}</div>
                                <div class="role">Administrador</div>
                            </div>
                            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="topbar-user-action">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Global Loader -->
        <div class="global-loader-overlay" id="globalLoaderApp" style="display: none;">
            <i class="fa-solid fa-circle-notch fa-spin fa-3x" style="color: var(--primary);"></i>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
            </div>

            <div class="content-wrapper">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleDarkMode() {
            fetch('{{ route("toggle-dark-mode") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(() => window.location.reload());
        }

        // Topbar Dropdown Toggle
        function toggleTopbarDropdown(id) {
            const el = document.getElementById(id);
            const wasOpen = el.classList.contains('open');
            
            // Close all dropdowns first
            document.querySelectorAll('.topbar-dropdown, .topbar-user').forEach(d => d.classList.remove('open'));
            
            // Toggle the clicked one
            if (!wasOpen) el.classList.add('open');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.topbar-dropdown') && !e.target.closest('.topbar-user')) {
                document.querySelectorAll('.topbar-dropdown, .topbar-user').forEach(d => d.classList.remove('open'));
            }
        });

        // Modal Functions
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('open');
            }
        });

        // Global AJAX Helper
        function showGlobalLoader() {
            document.getElementById('globalLoaderApp').style.display = 'flex';
        }
        function hideGlobalLoader() {
            document.getElementById('globalLoaderApp').style.display = 'none';
        }

        async function submitAjaxForm(formElement, url, successCallback) {
            showGlobalLoader();
            const formData = new FormData(formElement);
            // Si el form tiene _method, ya va en formData
            
            try {
                const response = await fetch(url, {
                    method: 'POST', // Siempre POST para Fetch con FormData (Laravel spoofing)
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    if(successCallback) successCallback(data);
                } else {
                    alert(data.message || 'Error en la operación');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión con el servidor.');
            }
            hideGlobalLoader();
        }

        async function deleteAjax(url, successCallback) {
            if(!confirm('¿Estás seguro de eliminar este registro?')) return;
            showGlobalLoader();
            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    if(successCallback) successCallback(data);
                } else {
                    alert(data.message || 'Error al eliminar');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión.');
            }
            hideGlobalLoader();
        }
    </script>
    @stack('scripts')
</body>
</html>
