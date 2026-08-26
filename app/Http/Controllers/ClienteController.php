<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\SistemaSaas;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function index(Request $request)
    {
        $clientes = Cliente::withCount('clienteSistemas')
            ->when($request->buscar, function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            })
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $sistemas = SistemaSaas::where('activo', true)->orderBy('nombre')->get();

        return view('clientes.create', compact('sistemas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:clientes,email',
            'telefono' => 'nullable|string|max:50',
            'identificacion_fiscal' => 'nullable|string|max:50',
        ]);

        $cliente = Cliente::create($data);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente creado. Ahora sumale una cuenta en alguno de tus sistemas.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['clienteSistemas.sistema', 'clienteSistemas.suscripciones.pagos']);
        $sistemas = SistemaSaas::where('activo', true)->orderBy('nombre')->get();

        return view('clientes.show', compact('cliente', 'sistemas'));
    }
}
