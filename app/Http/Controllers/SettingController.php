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
        ];
        return view('inventory.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'private_code_start' => 'required|integer|min:1',
            'private_code_mode' => 'required|in:incremental,personalizado',
        ]);

        Setting::set('private_code_start', $request->private_code_start);
        Setting::set('private_code_mode', $request->private_code_mode);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Configuración actualizada exitosamente']);
        }
        return redirect()->route('settings.index')
            ->with('success', 'Configuración actualizada exitosamente');
    }
}
