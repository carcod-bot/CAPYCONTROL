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
            'default_batch_prefix' => Setting::get('default_batch_prefix', 'LOTE-'),
        ];
        return view('inventory.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'private_code_start' => 'required|integer|min:1',
            'private_code_mode' => 'required|in:incremental,personalizado',
            'default_batch_prefix' => 'required|string|max:20',
        ]);

        Setting::set('private_code_start', $request->private_code_start);
        Setting::set('private_code_mode', $request->private_code_mode);
        Setting::set('default_batch_prefix', $request->default_batch_prefix);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Configuración actualizada exitosamente']);
        }
        return redirect()->route('settings.index')
            ->with('success', 'Configuración actualizada exitosamente');
    }
}
