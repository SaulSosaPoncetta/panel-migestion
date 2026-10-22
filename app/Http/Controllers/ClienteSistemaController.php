<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteSistema;
use App\Models\Vinculacion;
use App\Services\VinculadorContable;
use Illuminate\Http\Request;

class ClienteSistemaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    public function store(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'id_sistema' => 'required|exists:sistemas_saas,id_sistema',
            'referencia_externa' => 'nullable|string|max:255',
        ]);

        $clienteSistema = ClienteSistema::create([
            'id_cliente' => $cliente->id_cliente,
            'id_sistema' => $data['id_sistema'],
            'referencia_externa' => $data['referencia_externa'] ?? null,
        ]);

        // Si ahora tiene GestiónComercial y Contabilidad, queda la integración lista para confirmar
        app(VinculadorContable::class)->evaluar($cliente);

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cuenta vinculada. Ahora podés crearle una suscripción.');
    }

    public function destroy(Cliente $cliente, ClienteSistema $clienteSistema)
    {
        abort_unless((int) $clienteSistema->id_cliente === (int) $cliente->id_cliente, 404);

        // Si esta cuenta participaba de una integración automática, se corta en los dos sistemas
        Vinculacion::where('estado', 'vinculada')
            ->where(fn ($q) => $q->where('id_cs_comercial', $clienteSistema->id_cliente_sistema)
                ->orWhere('id_cs_contable', $clienteSistema->id_cliente_sistema))
            ->get()
            ->each(fn ($v) => app(VinculadorContable::class)->desvincular($v));

        $clienteSistema->delete();

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cuenta desvinculada.');
    }
}
