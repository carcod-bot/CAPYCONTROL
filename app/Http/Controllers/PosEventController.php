<?php

namespace App\Http\Controllers;

use App\Models\PosEvent;
use Illuminate\Http\Request;

class PosEventController extends Controller
{
    public function index(Request $request)
    {
        $query = PosEvent::with(['session.cashRegister', 'user']);

        // Filtrar por texto libre (detalles, supervisor)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($builder) use ($q) {
                $builder->where('supervisor_username', 'LIKE', "%{$q}%")
                        ->orWhere('details', 'LIKE', "%{$q}%")
                        ->orWhere('event_type', 'LIKE', "%{$q}%")
                        ->orWhereHas('user', function($u) use ($q) {
                            $u->where('username', 'LIKE', "%{$q}%")
                              ->orWhere('name', 'LIKE', "%{$q}%");
                        });
            });
        }

        // Filtrar por tipo de evento
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        $events = $query->latest()->paginate(20)->withQueryString();

        return view('pos-control.events.index', compact('events'));
    }
}
