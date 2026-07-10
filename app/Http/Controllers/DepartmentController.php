<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->paginate(20)->withQueryString();
        return view('inventory.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string|max:500',
        ]);

        $department = Department::create($request->only('name', 'description'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Departamento creado exitosamente', 'data' => $department]);
        }
        return redirect()->route('departments.index')->with('success', 'Departamento creado exitosamente');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:500',
        ]);

        $department->update($request->only('name', 'description'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Departamento actualizado exitosamente', 'data' => $department]);
        }
        return redirect()->route('departments.index')->with('success', 'Departamento actualizado exitosamente');
    }

    public function destroy(Request $request, Department $department)
    {
        $department->delete();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Departamento eliminado exitosamente']);
        }
        return redirect()->route('departments.index')->with('success', 'Departamento eliminado exitosamente');
    }
}
