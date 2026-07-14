<?php

namespace App\Http\Controllers;

class ParameterController extends Controller
{
    public function index()
    {
        $settings = [
            'tax_type' => \App\Models\Setting::get('tax_type', 'percentage'),
            'tax_amount' => \App\Models\Setting::get('tax_amount', '16.00'),
            'tax_included' => \App\Models\Setting::get('tax_included', 'false'),
            'local_currency' => \App\Models\Setting::get('local_currency', ''),
            'company_name' => \App\Models\Setting::get('company_name', 'CapyPOS'),
            'company_rif' => \App\Models\Setting::get('company_rif', 'J-000000000'),
            'company_location' => \App\Models\Setting::get('company_location', 'Ubicación Central'),
            'company_branch' => \App\Models\Setting::get('company_branch', 'Sucursal Principal'),
            'is_fiscal' => \App\Models\Setting::get('is_fiscal', 'true'),
        ];
        return view('configuraciones.parametros', compact('settings'));
    }

    public function update(\Illuminate\Http\Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Parameter update called', $request->all());
        
        $request->validate([
            'tax_type' => 'required|in:percentage,fixed',
            'tax_amount' => 'required|numeric|min:0',
            'tax_included' => 'required|in:true,false',
            'company_name' => 'required|string|max:255',
            'company_rif' => 'required|string|max:50',
            'company_location' => 'required|string|max:255',
            'company_branch' => 'required|string|max:255',
            'is_fiscal' => 'required|in:true,false',
        ]);

        \App\Models\Setting::set('tax_type', $request->tax_type);
        \App\Models\Setting::set('tax_amount', $request->tax_amount);
        \App\Models\Setting::set('tax_included', $request->tax_included);
        \App\Models\Setting::set('company_name', $request->company_name);
        \App\Models\Setting::set('company_rif', $request->company_rif);
        \App\Models\Setting::set('company_location', $request->company_location);
        \App\Models\Setting::set('company_branch', $request->company_branch);
        \App\Models\Setting::set('is_fiscal', $request->is_fiscal);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Parámetros actualizados exitosamente']);
        }
        return redirect()->route('config.parametros')
            ->with('success', 'Parámetros actualizados exitosamente');
    }
}
