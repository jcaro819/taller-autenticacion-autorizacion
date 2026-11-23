<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class MetricaController extends Controller
{
    /** RF 3.1 / 3.2 — GET /api/admin/metricas (sólo admin). */
    public function index(): JsonResponse
    {
        // Lanza 403 automáticamente si el Gate 'ver-metricas' lo niega.
        Gate::authorize('ver-metricas');

        return response()->json([
            'total_eventos' => Evento::count(),
            'cupo_total_ofertado' => (int) Evento::sum('cupo_maximo'),
            'total_organizadores' => User::where('role', User::ROLE_ORGANIZADOR)->count(),
            'total_usuarios' => User::count(),
            'monto_global_recaudado' => config('eventhub.monto_recaudado'),
            'moneda' => config('eventhub.moneda'),
        ]);
    }
}
