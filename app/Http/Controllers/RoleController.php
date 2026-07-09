<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    const SYSTEM_ROLE = 'Administrador';

    public function index()
    {
        return response()->json(Role::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'string'   => 'El campo :attribute debe ser de tipo texto.',
            'max'      => 'El campo :attribute no debe superar :max caracteres.',
            'unique'   => 'El nombre de este rol ya existe.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        $role = Role::create([
            'name'        => $request->name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_system'   => false,
        ]);

        return response()->json(['success' => true, 'role' => $role]);
    }

    public function update(Request $request, Role $role)
    {
        $messages = [
            'required' => 'El campo :attribute es obligatorio.',
            'string'   => 'El campo :attribute debe ser de tipo texto.',
            'max'      => 'El campo :attribute no debe superar :max caracteres.',
            'unique'   => 'El nombre de este rol ya existe.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'        => 'required|string|max:60|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        if ($role->is_system && $role->name === self::SYSTEM_ROLE) {
            // Allow editing description but not permissions of system Admin role
            $role->update(['description' => $request->description]);
        } else {
            $role->update([
                'name'        => $request->name,
                'description' => $request->description,
                'permissions' => $request->permissions ?? [],
            ]);
        }

        return response()->json(['success' => true, 'role' => $role->fresh()]);
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return response()->json(['success' => false, 'message' => 'Los roles del sistema no pueden eliminarse.'], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Este rol tiene usuarios asignados. Reasígnalos antes de eliminar.'], 409);
        }

        $role->delete();
        return response()->json(['success' => true]);
    }
}
