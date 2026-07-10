<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('department')->orderBy('name')->paginate(20)->withQueryString();
        $departments = Department::where('active', true)->orderBy('name')->get();
        return view('inventory.categories.index', compact('categories', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id'
        ]);

        $category = Category::create($request->only('name', 'description', 'department_id'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Categoría creada exitosamente', 'data' => $category]);
        }
        return redirect()->route('categories.index')->with('success', 'Categoría creada exitosamente');
    }

    public function getByDepartment($department_id)
    {
        $categories = Category::where('department_id', $department_id)
                              ->where('active', true)
                              ->orderBy('name')
                              ->get(['id', 'name']);
        return response()->json($categories);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($request->only('name', 'description'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Categoría actualizada exitosamente', 'data' => $category]);
        }
        return redirect()->route('categories.index')->with('success', 'Categoría actualizada exitosamente');
    }

    public function destroy(Request $request, Category $category)
    {
        $category->delete();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
        }
        return redirect()->route('categories.index')->with('success', 'Categoría eliminada exitosamente');
    }
}
