<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Product;
use App\Models\Category;
use App\Models\Department;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\Brand;
use App\Models\Provider;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::with('promotable')->orderBy('created_at', 'desc')->paginate(15);
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($promotions);
        }
        
        $products = Product::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        $departments = Department::where('active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::all();
        $brands = Brand::where('active', true)->get();
        $providers = Provider::where('active', true)->get();
        
        return view('inventory.promotions.index', compact('promotions', 'products', 'categories', 'departments', 'currencies', 'paymentMethods', 'brands', 'providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'promotable_type' => 'required|string',
            'promotable_id' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $promotion = new Promotion();
        $promotion->name = $request->name;
        $promotion->discount_type = $request->discount_type;
        $promotion->discount_value = $request->discount_value;
        $promotion->promotable_type = $request->promotable_type;
        $promotion->promotable_id = $request->promotable_id;
        $promotion->start_date = $request->start_date;
        $promotion->end_date = $request->end_date;
        $promotion->active = $request->has('active') || $request->active == 'true' || $request->active == '1';
        $promotion->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Promoción creada exitosamente', 'promotion' => $promotion]);
        }
        
        return redirect()->route('promotions.index')->with('success', 'Promoción creada exitosamente');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'promotable_type' => 'required|string',
            'promotable_id' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $promotion->name = $request->name;
        $promotion->discount_type = $request->discount_type;
        $promotion->discount_value = $request->discount_value;
        $promotion->promotable_type = $request->promotable_type;
        $promotion->promotable_id = $request->promotable_id;
        $promotion->start_date = $request->start_date;
        $promotion->end_date = $request->end_date;
        $promotion->active = $request->has('active') || $request->active == 'true' || $request->active == '1';
        $promotion->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Promoción actualizada exitosamente', 'promotion' => $promotion]);
        }
        
        return redirect()->route('promotions.index')->with('success', 'Promoción actualizada exitosamente');
    }

    public function destroy(Request $request, Promotion $promotion)
    {
        $promotion->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Promoción eliminada exitosamente']);
        }
        
        return redirect()->route('promotions.index')->with('success', 'Promoción eliminada exitosamente');
    }
}
