<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * All available permissions in the system.
     */
    const ALL_PERMISSIONS = [
        'capycontrol.access',
        'capypos.access',
        'dashboard.view',
        'inventory.view',
        'inventory.edit',
        'finances.view',
        'finances.edit',
        'pos_control.view',
        'pos_control.manage',
        'pos_control.sessions',
        'configuraciones.view',
        'configuraciones.edit',
    ];

    /**
     * Friendly labels for each permission key.
     */
    const PERMISSION_LABELS = [
        'capycontrol.access'   => ['label' => 'Acceso a CapyControl',       'group' => 'Aplicaciones'],
        'capypos.access'       => ['label' => 'Acceso a CapyPOS',            'group' => 'Aplicaciones'],
        'dashboard.view'       => ['label' => 'Ver Dashboard',               'group' => 'CapyControl — Módulos'],
        'inventory.view'       => ['label' => 'Ver Inventario',              'group' => 'CapyControl — Módulos'],
        'inventory.edit'       => ['label' => 'Editar Inventario',           'group' => 'CapyControl — Módulos'],
        'finances.view'        => ['label' => 'Ver Finanzas',                'group' => 'CapyControl — Módulos'],
        'finances.edit'        => ['label' => 'Editar Finanzas',             'group' => 'CapyControl — Módulos'],
        'pos_control.view'     => ['label' => 'Ver Control POS',             'group' => 'CapyControl — Módulos'],
        'pos_control.manage'   => ['label' => 'Gestionar Cajas',             'group' => 'CapyControl — Módulos'],
        'pos_control.sessions' => ['label' => 'Abrir / Cerrar Turnos',       'group' => 'CapyControl — Módulos'],
        'configuraciones.view' => ['label' => 'Ver Configuraciones',         'group' => 'CapyControl — Módulos'],
        'configuraciones.edit' => ['label' => 'Editar Usuarios y Roles',     'group' => 'CapyControl — Módulos'],
    ];

    public function index()
    {
        $users = User::with('roleModel')->orderBy('username')->get();
        $roles = Role::orderBy('name')->get();

        return view('configuraciones.usuarios', [
            'users'             => $users,
            'roles'             => $roles,
            'allPermissions'    => self::ALL_PERMISSIONS,
            'permissionLabels'  => self::PERMISSION_LABELS,
        ]);
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'string'   => 'El campo :attribute debe ser de tipo texto.',
            'max'      => 'El campo :attribute no debe superar :max caracteres.',
            'min'      => 'El campo :attribute debe tener al menos :min caracteres.',
            'unique'   => 'El valor de :attribute ya está registrado.',
            'confirmed'=> 'La confirmación de la contraseña no coincide.',
            'exists'   => 'La opción seleccionada es inválida.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'username'              => 'required|string|max:60|unique:users,username',
            'password'              => 'required|string|min:6|confirmed',
            'role_id'               => 'nullable|exists:roles,id',
            'extra_permissions'     => 'nullable|array',
            'extra_permissions.*'   => 'string|in:' . implode(',', self::ALL_PERMISSIONS),
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Determine system role string (admin if role is "Administrador")
        $role = $request->role_id ? Role::find($request->role_id) : null;
        $roleStr = ($role && $role->name === 'Administrador') ? 'admin' : 'user';

        User::create([
            'username'    => $request->username,
            'password'    => Hash::make($request->password),
            'role'        => $roleStr,
            'role_id'     => $request->role_id,
            'permissions' => $request->extra_permissions ?? [],
            'dark_mode'   => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario creado exitosamente.']);
    }

    public function update(Request $request, User $user)
    {
        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'string'   => 'El campo :attribute debe ser de tipo texto.',
            'max'      => 'El campo :attribute no debe superar :max caracteres.',
            'min'      => 'El campo :attribute debe tener al menos :min caracteres.',
            'unique'   => 'El valor de :attribute ya está registrado.',
            'confirmed'=> 'La confirmación de la contraseña no coincide.',
            'exists'   => 'La opción seleccionada es inválida.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'username'              => 'required|string|max:60|unique:users,username,' . $user->id,
            'password'              => 'nullable|string|min:6|confirmed',
            'role_id'               => 'nullable|exists:roles,id',
            'extra_permissions'     => 'nullable|array',
            'extra_permissions.*'   => 'string|in:' . implode(',', self::ALL_PERMISSIONS),
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        $role    = $request->role_id ? Role::find($request->role_id) : null;
        $roleStr = ($role && $role->name === 'Administrador') ? 'admin' : 'user';

        $data = [
            'username'    => $request->username,
            'role'        => $roleStr,
            'role_id'     => $request->role_id,
            'permissions' => $request->extra_permissions ?? [],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => true, 'message' => 'Usuario actualizado.']);
    }

    public function destroy(User $user)
    {
        // Protect: cannot delete yourself or the last admin
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'No puedes eliminarte a ti mismo.'], 403);
        }

        $adminCount = User::where('role', 'admin')->count();
        if ($user->role === 'admin' && $adminCount <= 1) {
            return response()->json(['success' => false, 'message' => 'No puedes eliminar al único administrador del sistema.'], 403);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'Usuario eliminado.']);
    }
}
