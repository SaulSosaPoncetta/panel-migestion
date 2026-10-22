<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Vinculacion;
use App\Services\VinculadorContable;

/**
 * Acciones del superadmin sobre la integración GestiónComercial → Contabilidad de un cliente.
 */
class VinculacionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    /** Busca si el cliente tiene los dos sistemas y vincula si los datos coinciden. */
    public function revisar(Cliente $cliente, VinculadorContable $vinculador)
    {
        $vinculacion = $vinculador->evaluar($cliente);

        return redirect()->route('clientes.show', $cliente)->with(...$this->mensaje($vinculacion,
            'El cliente no tiene cuenta en los dos sistemas (o falta la referencia externa en alguno).'));
    }

    /** Confirma (o reintenta) la vinculación: vuelve a generar el token y se lo entrega a GestiónComercial. */
    public function vincular(Vinculacion $vinculacion, VinculadorContable $vinculador)
    {
        $vinculador->vincular($vinculacion);

        return redirect()->route('clientes.show', $vinculacion->id_cliente)->with(...$this->mensaje($vinculacion));
    }

    public function desvincular(Vinculacion $vinculacion, VinculadorContable $vinculador)
    {
        $vinculador->desvincular($vinculacion);

        return redirect()->route('clientes.show', $vinculacion->id_cliente)->with(
            $vinculacion->mensaje ? 'error' : 'success',
            $vinculacion->mensaje ?: 'Integración desvinculada en los dos sistemas.'
        );
    }

    private function mensaje(?Vinculacion $v, string $sinVinculacion = ''): array
    {
        return match ($v?->estado) {
            null => ['error', $sinVinculacion],
            'vinculada' => ['success', 'Integración activa: las facturas de GestiónComercial ya pasan solas a la contabilidad.'],
            'por_confirmar' => ['error', $v->mensaje],
            default => ['error', 'No se pudo vincular: ' . $v->mensaje],
        };
    }
}
