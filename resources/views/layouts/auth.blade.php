<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capycontrol - Ingreso</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .auth-split-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }
        .auth-banner {
            flex: 1;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
            display: none;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .auth-banner::before, .auth-banner::after {
            content: '';
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            transform: rotate(-45deg);
        }
        .auth-banner::before { width: 300px; height: 80px; bottom: 20%; left: -50px; }
        .auth-banner::after { width: 400px; height: 100px; bottom: 5%; left: 10%; }
        @media (min-width: 900px) { .auth-banner { display: flex; } }
        .auth-banner h1 { font-size: 3.5rem; font-weight: 700; margin-bottom: 1rem; z-index: 1; line-height: 1.2; }
        .auth-banner p { font-size: 1.1rem; opacity: 0.9; max-width: 500px; z-index: 1; line-height: 1.6; }
        
        .auth-form-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background);
            padding: 2rem;
        }
        @media (min-width: 900px) { .auth-form-wrapper { flex: 0 0 500px; } }
        
        .auth-container { width: 100%; max-width: 400px; text-align: center; }
        .auth-logo { font-size: 2.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; letter-spacing: -0.5px; }
        .auth-logo span { color: var(--primary); }
        .auth-subtitle { color: var(--text-muted); margin-bottom: 2.5rem; font-size: 0.95rem; font-weight: 400; }
        
        .form-group { text-align: left; margin-bottom: 1.5rem; position: relative; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-main); font-size: 0.9rem; }
        .input-icon-wrapper { position: relative; }
        .input-icon-wrapper i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.2rem; transition: color 0.3s; }
        .form-group input { width: 100%; padding: 0.85rem 1rem 0.85rem 3rem; border: 1.5px solid var(--border); border-radius: 0.75rem; outline: none; transition: all 0.3s; font-family: 'Inter', sans-serif; font-size: 0.95rem; background: var(--surface); color: var(--text-main); }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); background: var(--surface); }
        .form-group input:focus + i, .input-icon-wrapper:focus-within i { color: var(--primary); }
        
        .btn-auth {
            width: 100%; padding: 0.9rem; background: var(--primary); color: white; border: none; border-radius: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 1rem; margin-top: 1rem; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3); background: linear-gradient(to right, var(--primary), #1d4ed8);
        }
        .btn-auth:hover { opacity: 0.9; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4); }
        .alert { padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
    </style>
</head>
<body class="{{ $darkMode ?? false ? 'dark-mode' : '' }}">
    <div class="auth-split-layout">
        <div class="auth-banner">
            <h1>Gestión Inteligente</h1>
            <p>Control de inventario, stock y administración de manera rápida y eficiente.</p>
        </div>
        <div class="auth-form-wrapper">
            @yield('content')
        </div>
    </div>
</body>
</html>
