<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'private_code_start' => Setting::get('private_code_start', '1'),
            'private_code_mode' => Setting::get('private_code_mode', 'incremental'),
            'tax_type' => Setting::get('tax_type', 'percentage'),
            'tax_amount' => Setting::get('tax_amount', '16.00'),
        ];
        return view('inventory.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'private_code_start' => 'required|integer|min:1',
            'private_code_mode' => 'required|in:incremental,personalizado',
            'tax_type' => 'required|in:percentage,fixed',
            'tax_amount' => 'required|numeric|min:0',
        ]);

        Setting::set('private_code_start', $request->private_code_start);
        Setting::set('private_code_mode', $request->private_code_mode);
        Setting::set('tax_type', $request->tax_type);
        Setting::set('tax_amount', $request->tax_amount);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Configuración actualizada exitosamente']);
        }
        return redirect()->route('settings.index')
            ->with('success', 'Configuración actualizada exitosamente');
    }
}
