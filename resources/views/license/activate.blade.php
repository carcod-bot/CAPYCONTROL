@extends('layouts.auth')

@section('content')
<div class="auth-container">
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: center;">
        <img src="{{ asset('img/empresa.png') }}" alt="Logo" style="height: 45px; width: auto;" onerror="this.style.display='none'">
    </div>
    <div class="auth-logo">Activa tu <span>Licencia</span></div>
    <div class="auth-subtitle" style="text-align: center; margin-bottom: 1.5rem; font-size: 0.9rem;">Tu licencia es inválida o ha expirado. Por favor, contacta a soporte con tu ID.</div>

    @if(session('error'))
        <div class="alert alert-danger" style="background-color: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 1rem;">
            <div><i class='bx bx-error-circle'></i> {{ session('error') }}</div>
        </div>
    @endif

    <div class="form-group">
        <label>ID de tu Sistema:</label>
        <div style="display: flex; gap: 8px;">
            <input type="text" value="{{ $systemId }}" readonly id="sysId" style="flex: 1; text-align: center; font-weight: bold; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; background: #f9fafb; font-family: monospace;">
            <button type="button" class="btn-auth" style="width: auto; padding: 0 15px; margin: 0; display: flex; align-items: center; justify-content: center;" onclick="navigator.clipboard.writeText(document.getElementById('sysId').value); alert('ID copiado al portapapeles');" title="Copiar ID">
                <i class='bx bx-copy' style="font-size: 1.2rem;"></i>
            </button>
        </div>
    </div>

    

    <form method="POST" action="{{ route('license.activate.post') }}">
        @csrf
        <div class="form-group">
            <label>Organización (Obligatorio para el Keygen):</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" name="company_name" value="{{ old('company_name', $companyName) }}" id="sysOrg" required style="flex: 1; text-align: center; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; background: #fff; font-family: monospace;">
                <button type="button" class="btn-auth" style="width: auto; padding: 0 15px; margin: 0; display: flex; align-items: center; justify-content: center;" onclick="navigator.clipboard.writeText(document.getElementById('sysOrg').value); alert('Organización copiada al portapapeles');" title="Copiar Organización">
                    <i class='bx bx-copy' style="font-size: 1.2rem;"></i>
                </button>
            </div>
            @error('company_name')
                <div style="color: #991b1b; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Sucursal (Opcional):</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" name="company_branch" value="{{ old('company_branch', $companyBranch) }}" id="sysBranch" style="flex: 1; text-align: center; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; background: #fff; font-family: monospace;">
                <button type="button" class="btn-auth" style="width: auto; padding: 0 15px; margin: 0; display: flex; align-items: center; justify-content: center;" onclick="navigator.clipboard.writeText(document.getElementById('sysBranch').value); alert('Sucursal copiada al portapapeles');" title="Copiar Sucursal">
                    <i class='bx bx-copy' style="font-size: 1.2rem;"></i>
                </button>
            </div>
        </div>
        
        
        <div class="form-group">
            <label for="license_key">Llave de Licencia:</label>
            <div>
                <textarea id="license_key" name="license_key" required rows="4" placeholder="Pega aquí la llave generada..." style="width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; font-family: monospace; resize: none; background: #fff;"></textarea>
            </div>
            @error('license_key')
                <div style="color: #991b1b; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-auth" style="margin-top: 1.5rem; width: 100%;">Activar Sistema</button>
    </form>
    
    <div style="margin-top: 1.5rem; text-align: center;">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" style="background: none; border: none; color: #6b7280; text-decoration: underline; cursor: pointer; font-family: inherit; font-size: 0.9rem;">Cerrar Sesión</button>
        </form>
    </div>
</div>
@endsection
