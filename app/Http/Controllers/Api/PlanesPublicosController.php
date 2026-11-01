<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SistemaSaas;

/**
 * GET /api/planes-publicos/{slug}
 *
 * Lista pública (sin clave) de los planes activos de un sistema y sus precios, para que el
 * sitio de cada sistema (por ejemplo su landing) muestre los precios que se cargan acá,
 * sin duplicarlos. Solo expone datos que ya son públicos: nombre, descripción y precios.
 */
class PlanesPublicosController extends Controller
{
    public function show(string $slug)
    {
        $sistema = SistemaSaas::where('slug', $slug)->where('activo', true)->first();

        abort_unless($sistema, 404);

        $planes = $sistema->planes()->with('escalas')->where('activo', true)->orderBy('precio')->get()
            ->map(fn (Plan $p) => [
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
                'descripcion' => $p->descripcion,
                'moneda' => $p->moneda,
                'precio' => (float) $p->precio,
                'por_unidad' => (bool) $p->cobro_por_unidad,
                'unidad' => $p->unidad_nombre ?: 'unidades',
                'mensual' => $p->esMensual(),
                'meses' => $p->mesesOpciones(),
                'escalas' => $p->cobro_por_unidad
                    ? $p->escalas->map(fn ($e) => [
                        'desde' => (int) $e->desde,
                        'hasta' => $e->hasta ? (int) $e->hasta : null,
                        'precio' => (float) $e->precio,
                    ])->values()
                    : [],
            ])->values();

        return response()->json(['sistema' => $sistema->slug, 'planes' => $planes])
            ->header('Cache-Control', 'public, max-age=300');
    }
}
