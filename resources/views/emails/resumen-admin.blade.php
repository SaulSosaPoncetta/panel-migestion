<!doctype html>
<html lang="es">
<body style="font-family:Arial,Helvetica,sans-serif;color:#1f2937;font-size:14px;line-height:1.5;">
<h3 style="margin-bottom:4px;">Resumen de cobros · {{ $resumen['fecha'] }}</h3>

@if(count($resumen['transferencias']) || $resumen['transferencias_por_revisar'] > 0)
    <p><strong>Transferencias esperando tu revisión:</strong> {{ $resumen['transferencias_por_revisar'] }}
        — <a href="{{ route('pagos.index', ['estado' => 'pendiente', 'medio' => 'transferencia']) }}">revisar</a></p>
@endif

@if(count($resumen['vencidas']))
    <p><strong>Pasaron a vencida hoy ({{ count($resumen['vencidas']) }}):</strong></p>
    <ul>
        @foreach($resumen['vencidas'] as $l)<li>{{ $l }}</li>@endforeach
    </ul>
@endif

@if(count($resumen['por_vencer']))
    <p><strong>Vencen en los próximos días ({{ count($resumen['por_vencer']) }}):</strong></p>
    <ul>
        @foreach($resumen['por_vencer'] as $l)<li>{{ $l }}</li>@endforeach
    </ul>
@endif

<p style="color:#6b7280;font-size:12px;">MiGestión Panel · aviso automático.</p>
</body>
</html>
