@extends('layouts.auth')

@section('content')
<div class="auth-container">
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: center;">
        <img src="{{ asset('img/empresa.png') }}" alt="Capycore" style="height: 45px; width: auto;">
    </div>
    <div class="auth-logo">Capy<span>control</span></div>
    <div class="auth-subtitle">Ingresa a tu cuenta para continuar</div>

    @if ($errors->any())
        <div class="alert alert-danger" style="background-color: #fee2e2; color: #991b1b;">
            @foreach ($errors->all() as $error)
                <div><i class='bx bx-error-circle'></i> {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="username">Usuario</label>
            <div class="input-icon-wrapper">
                <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="tu_usuario" required>
                <i class='bx bx-user'></i>
            </div>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="input-icon-wrapper">
                <input type="password" name="password" id="password" placeholder="••••••••" required>
                <i class='bx bx-lock-alt'></i>
            </div>
        </div>
        <button type="submit" class="btn-auth">Iniciar Sesión</button>
    </form>
</div>
@endsection
