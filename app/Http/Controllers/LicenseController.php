<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SysGuard;
use App\Models\SystemLicense;

class LicenseController extends Controller
{
    public function index()
    {
        // If it's already licensed, we can still show it or redirect to home
        $isValid = SysGuard::verifyState();
        $systemId = SysGuard::getSystemId();
        $license = SystemLicense::orderBy('id', 'desc')->first();
        $companyName = \App\Models\Setting::get('company_name', 'CapyControl');
        $companyBranch = \App\Models\Setting::get('company_branch', '');

        return view('license.activate', compact('isValid', 'systemId', 'license', 'companyName', 'companyBranch'));
    }

    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'company_name' => 'required|string|max:255',
            'company_branch' => 'nullable|string|max:255'
        ]);

        \App\Models\Setting::set('company_name', $request->company_name);
        \App\Models\Setting::set('company_branch', $request->company_branch ?? '');

        $guard = new SysGuard("CapyControl Administración");
        $success = $guard->activateLicense($request->license_key);

        if ($success) {
            return redirect()->route('home')->with('success', 'Licencia activada correctamente.');
        } else {
            return back()->with('error', 'Llave de licencia inválida o corrupta.');
        }
    }
}
