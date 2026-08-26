<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteSistema;
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

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cuenta vinculada. Ahora podés crearle una suscripción.');
    }

    public function destroy(Cliente $cliente, ClienteSistema $clienteSistema)
    {
        $clienteSistema->delete();

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cuenta desvinculada.');
    }
}
