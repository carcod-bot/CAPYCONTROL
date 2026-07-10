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
            'batch_generation_mode' => Setting::get('batch_generation_mode', 'auto_date'),
            'default_batch_prefix' => Setting::get('default_batch_prefix', 'LOTE-'),
            'batch_next_number' => Setting::get('batch_next_number', '1'),
        ];
        return view('inventory.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'private_code_start' => 'required|integer|min:1',
            'private_code_mode' => 'required|in:incremental,personalizado',
            'batch_generation_mode' => 'required|in:auto_date,sequential',
            'default_batch_prefix' => 'nullable|string|max:20',
            'batch_next_number' => 'nullable|integer|min:1',
        ]);

        Setting::set('private_code_start', $request->private_code_start);
        Setting::set('private_code_mode', $request->private_code_mode);
        Setting::set('batch_generation_mode', $request->batch_generation_mode);
        Setting::set('default_batch_prefix', $request->default_batch_prefix ?? '');
        if ($request->batch_generation_mode === 'sequential') {
            Setting::set('batch_next_number', $request->batch_next_number ?? 1);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Configuración actualizada exitosamente']);
        }
        return redirect()->route('settings.index')
            ->with('success', 'Configuración actualizada exitosamente');
    }
}
