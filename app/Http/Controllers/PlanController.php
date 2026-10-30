<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\SistemaSaas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function index(Request $request)
    {
        $sistemas = SistemaSaas::orderBy('nombre')->get();

        $planes = Plan::with('sistema')
            ->when($request->filled('sistema'), fn ($q) => $q->where('id_sistema', $request->sistema))
            ->orderBy('id_sistema')
            ->orderBy('precio')
            ->get();

        return view('planes.index', compact('planes', 'sistemas'));
    }

    public function create(Request $request)
    {
        $sistemas = SistemaSaas::orderBy('nombre')->get();
        $plan = new Plan([
            'id_sistema' => $request->query('sistema'),
            'tipo' => 'recurrente',
            'duracion_meses' => 1,
            'moneda' => 'ARS',
            'unidad_minima' => 1,
            'activo' => true,
        ]);

        return view('planes.create', compact('plan', 'sistemas'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        Plan::create($data);

        return redirect()->route('planes.index', ['sistema' => $data['id_sistema']])
            ->with('success', 'Plan creado.');
    }

    public function edit(Plan $plan)
    {
        $sistemas = SistemaSaas::orderBy('nombre')->get();

        return view('planes.edit', compact('plan', 'sistemas'));
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validar($request, $plan));

        return redirect()->route('planes.index', ['sistema' => $plan->id_sistema])
            ->with('success', 'Plan actualizado. Las suscripciones ya creadas conservan el precio que tenían.');
    }

    protected function validar(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'id_sistema' => 'required|exists:sistemas_saas,id_sistema',
            'codigo' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('planes', 'codigo')
                    ->where('id_sistema', $request->input('id_sistema'))
                    ->ignore($plan?->id_plan, 'id_plan'),
            ],
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'tipo' => 'required|in:recurrente,unico',
            'duracion_meses' => 'nullable|integer|min:1|max:120',
            'precio' => 'required|numeric|min:0',
            'moneda' => 'required|string|size:3',
            'unidad_nombre' => 'nullable|string|max:50',
            'unidad_minima' => 'nullable|integer|min:1',
            'unidad_maxima' => 'nullable|integer|min:1|gte:unidad_minima',
        ]);

        $data['moneda'] = strtoupper($data['moneda']);
        $data['cobro_por_unidad'] = $request->boolean('cobro_por_unidad');
        $data['activo'] = $request->boolean('activo');
        $data['unidad_minima'] = $data['unidad_minima'] ?? 1;

        // Una recurrente necesita período; sin período no se puede renovar
        if ($data['tipo'] === 'recurrente' && empty($data['duracion_meses'])) {
            $data['duracion_meses'] = 1;
        }

        if (! $data['cobro_por_unidad']) {
            $data['unidad_nombre'] = null;
            $data['unidad_maxima'] = null;
            $data['unidad_minima'] = 1;
        }

        return $data;
    }
}
