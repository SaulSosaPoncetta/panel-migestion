<?php

namespace App\Http\Controllers;

use App\Models\SistemaSaas;
use Illuminate\Http\Request;

class SistemaSaasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function index()
    {
        $sistemas = SistemaSaas::withCount('clienteSistemas')->orderBy('nombre')->get();

        return view('sistemas.index', compact('sistemas'));
    }

    public function create()
    {
        return view('sistemas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:sistemas_saas,slug|alpha_dash',
            'url_base' => 'required|url',
            'webhook_url' => 'nullable|url',
        ]);

        $sistema = SistemaSaas::create($data);

        return redirect()->route('sistemas.show', $sistema)
            ->with('success', 'Sistema creado. Copiá la API key y el webhook secret para configurarlo del lado de '.$sistema->nombre.'.')
            ->with('mostrar_credenciales', true);
    }

    public function show(SistemaSaas $sistema)
    {
        // Mostramos las credenciales sin ocultar solo en esta vista puntual (recién creado / bajo demanda)
        $sistema->makeVisible(['api_key', 'webhook_secret']);
        $clientes = $sistema->clienteSistemas()->with(['cliente', 'suscripciones'])->paginate(15);

        return view('sistemas.show', compact('sistema', 'clientes'));
    }
}
