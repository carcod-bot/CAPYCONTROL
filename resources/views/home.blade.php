@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<style>
    /* CSS Grid Layout for Dashboard */
    .dashboard-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
    }
    .kpi-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .kpi-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }
    .kpi-icon.revenue { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .kpi-icon.tickets { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .kpi-icon.credit { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .kpi-icon.sessions { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    .kpi-info { flex: 1; }
    .kpi-title { font-size: 0.9rem; color: var(--text-muted); font-weight: 500; margin-bottom: 0.25rem; }
    .kpi-value { font-size: 1.5rem; font-weight: 700; color: var(--text-main); }

    /* Main Grid: Chart + Lists */
    .main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 992px) {
        .main-grid { grid-template-columns: 1fr; }
    }

    .dashboard-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .panel-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    /* Lists styling */
    .list-group {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background: var(--background);
        border: 1px solid var(--border);
        border-radius: 8px;
        transition: border-color 0.2s;
    }
    .list-item:hover { border-color: var(--primary); }
    .item-main { display: flex; flex-direction: column; gap: 0.25rem; }
    .item-title { font-weight: 600; font-size: 0.95rem; color: var(--text-main); }
    .item-subtitle { font-size: 0.8rem; color: var(--text-muted); }
    .item-badge {
        padding: 0.25rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .badge-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
</style>

<div class="dashboard-container">
    <!-- Top KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon revenue"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="kpi-info">
                <div class="kpi-title">Ventas de Hoy</div>
                <div class="kpi-value">${{ number_format($todayRevenue, 2) }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon tickets"><i class="fa-solid fa-receipt"></i></div>
            <div class="kpi-info">
                <div class="kpi-title">Tickets Emitidos</div>
                <div class="kpi-value">{{ $todayTickets }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon credit"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <div class="kpi-info">
                <div class="kpi-title">Cuentas por Cobrar</div>
                <div class="kpi-value">${{ number_format($outstandingCredit, 2) }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon sessions"><i class="fa-solid fa-cash-register"></i></div>
            <div class="kpi-info">
                <div class="kpi-title">Turnos Activos</div>
                <div class="kpi-value">{{ $activeSessions }}</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-grid">
        <!-- Chart -->
        <div class="dashboard-panel">
            <h3 class="panel-title"><i class="fa-solid fa-chart-line text-primary"></i> Tendencia de Ventas (7 Días)</h3>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Right Column Lists -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Low Stock -->
            <div class="dashboard-panel">
                <h3 class="panel-title"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Alerta de Inventario</h3>
                <div class="list-group">
                    @forelse($lowStockProducts as $product)
                    <div class="list-item">
                        <div class="item-main">
                            <span class="item-title">{{ $product->name }}</span>
                            <span class="item-subtitle">Cód: {{ $product->private_code }}</span>
                        </div>
                        <span class="item-badge badge-danger">{{ number_format($product->stock, 2) }} en stock</span>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="fa-solid fa-check-circle" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;"></i><br>
                        Inventario saludable
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="dashboard-panel">
                <h3 class="panel-title"><i class="fa-solid fa-clock text-info"></i> Últimas Ventas</h3>
                <div class="list-group">
                    @forelse($recentSales as $sale)
                    <div class="list-item">
                        <div class="item-main">
                            <span class="item-title">{{ $sale->ticket_number }}</span>
                            <span class="item-subtitle">{{ $sale->customer ? $sale->customer->name : 'Consumidor Final' }}</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: var(--text-main);">${{ number_format($sale->total_amount, 2) }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $sale->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">Sin ventas recientes</div>
                    @endforelse
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDarkMode = document.body.classList.contains('dark-mode');
        const textColor = isDarkMode ? '#e2e8f0' : '#475569';
        const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Data injected from PHP
        const chartDates = {!! json_encode(array_reverse($chartDates)) !!};
        const chartData = {!! json_encode(array_reverse($chartData)) !!};

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartDates,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: chartData,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#1e293b' : '#ffffff',
                        titleColor: isDarkMode ? '#f8fafc' : '#0f172a',
                        bodyColor: isDarkMode ? '#cbd5e1' : '#475569',
                        borderColor: gridColor,
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: textColor, font: { family: "'Poppins', sans-serif" } }
                    },
                    y: {
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { 
                            color: textColor, 
                            font: { family: "'Poppins', sans-serif" },
                            callback: function(value) { return '$' + value; }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    });
</script>
@endpush
