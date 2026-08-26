<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Suscripcion;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $totalClientes = Cliente::count();

        $suscripcionesPorEstado = Suscripcion::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $cobradoEsteMes = Pago::where('estado', 'aprobado')
            ->whereMonth('fecha_pago', now()->month)
            ->whereYear('fecha_pago', now()->year)
            ->sum('monto');

        $proximosVencimientos = Suscripcion::with('clienteSistema.cliente', 'clienteSistema.sistema')
            ->where('tipo', 'recurrente')
            ->whereNotNull('proxima_fecha_cobro')
            ->orderBy('proxima_fecha_cobro')
            ->take(8)
            ->get();

        return view('home', compact(
            'totalClientes',
            'suscripcionesPorEstado',
            'cobradoEsteMes',
            'proximosVencimientos'
        ));
    }
}
