<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        // 1. KPI: Ventas de Hoy y Tickets (Asumiendo que status = completed o paid)
        $today = \Carbon\Carbon::today();
        $salesToday = \App\Models\Sale::whereDate('created_at', $today)
            ->whereIn('status', ['completed', 'paid', 'approved', 'closed'])
            ->get();
            
        $todayRevenue = $salesToday->sum('total_amount');
        $todayTickets = $salesToday->count();

        // 2. KPI: Cuentas por Cobrar Pendientes
        $outstandingCredit = \App\Models\CreditAccount::where('status', '!=', 'paid')
            ->sum(\Illuminate\Support\Facades\DB::raw('amount - paid_amount'));

        // 3. KPI: Turnos Activos
        $activeSessions = \App\Models\CashSession::where('status', 'open')->count();

        // 4. Chart: Últimos 7 días
        $startDate = \Carbon\Carbon::today()->subDays(6);
        $endDate = \Carbon\Carbon::today()->endOfDay();
        
        $salesData = \App\Models\Sale::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'paid', 'approved', 'closed'])
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartDates = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $chartDates[] = $date->format('d/m');
            $chartData[] = isset($salesData[$dateString]) ? $salesData[$dateString] : 0;
        }

        // 5. Alerta Stock Bajo
        $lowStockProducts = \App\Models\Product::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        // 6. Últimas Ventas
        $recentSales = \App\Models\Sale::with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('home', compact(
            'todayRevenue',
            'todayTickets',
            'outstandingCredit',
            'activeSessions',
            'chartDates',
            'chartData',
            'lowStockProducts',
            'recentSales'
        ));
    }
}
