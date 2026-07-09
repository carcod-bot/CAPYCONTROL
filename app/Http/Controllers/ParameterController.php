<?php

namespace App\Http\Controllers;

class ParameterController extends Controller
{
    public function index()
    {
        $settings = [
            'tax_type' => \App\Models\Setting::get('tax_type', 'percentage'),
            'tax_amount' => \App\Models\Setting::get('tax_amount', '16.00'),
            'local_currency' => \App\Models\Setting::get('local_currency', ''),
        ];
        return view('configuraciones.parametros', compact('settings'));
    }

    public function update(\Illuminate\Http\Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Parameter update called', $request->all());
        
        $request->validate([
            'tax_type' => 'required|in:percentage,fixed',
            'tax_amount' => 'required|numeric|min:0',
        ]);

        \App\Models\Setting::set('tax_type', $request->tax_type);
        \App\Models\Setting::set('tax_amount', $request->tax_amount);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Parámetros actualizados exitosamente']);
        }
        return redirect()->route('config.parametros')
            ->with('success', 'Parámetros actualizados exitosamente');
    }
}
